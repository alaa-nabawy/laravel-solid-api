<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Tests\PassportTestHelper;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use PassportTestHelper, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpPassport();
    }

    public function test_user_can_signup_successfully()
    {
        $userData = [
            'name'                  => $this->faker->name,
            'email'                 => $this->faker->unique()->safeEmail,
            'password'              => 'password123', // pragma: allowlist secret
            'password_confirmation' => 'password123', // pragma: allowlist secret
        ];

        $response = $this->postJson('/api/v1/auth/signup', $userData);

        // Debug: dump the actual response if test fails
        if ($response->status() !== 201) {
            dump('Response status: ' . $response->status());
            dump('Response content: ' . $response->content());
        }

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at',
                    ],
                    'token' => [
                        'access_token',
                        'token_type',
                        'expires_at',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
            'name'  => $userData['name'],
        ]);
    }

    public function test_signup_fails_with_invalid_data()
    {
        $invalidData = [
            'name'                  => '',
            'email'                 => 'invalid-email',
            'password'              => '123', // pragma: allowlist secret
            'password_confirmation' => '456', // pragma: allowlist secret
        ];

        $response = $this->postJson('/api/v1/auth/signup', $invalidData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors',
            ]);
    }

    public function test_signup_fails_with_existing_email()
    {
        $existingUser = User::factory()->create();

        $userData = [
            'name'                  => $this->faker->name,
            'email'                 => $existingUser->email,
            'password'              => 'password123', // pragma: allowlist secret
            'password_confirmation' => 'password123', // pragma: allowlist secret
        ];

        $response = $this->postJson('/api/v1/auth/signup', $userData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors',
            ]);
    }

    public function test_user_can_login_successfully()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'), // pragma: allowlist secret
        ]);

        $loginData = [
            'email'    => $user->email,
            'password' => 'password123', // pragma: allowlist secret
        ];

        $response = $this->postJson('/api/v1/auth/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token' => [
                        'access_token',
                        'token_type',
                        'expires_at',
                    ],
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'), // pragma: allowlist secret
        ]);

        $loginData = [
            'email'    => $user->email,
            'password' => 'wrongpassword', // pragma: allowlist secret
        ];

        $response = $this->postJson('/api/v1/auth/login', $loginData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors',
            ]);
    }

    public function test_login_fails_with_invalid_data()
    {
        $invalidData = [
            'email'    => 'invalid-email',
            'password' => '', // pragma: allowlist secret
        ];

        $response = $this->postJson('/api/v1/auth/login', $invalidData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors',
            ]);
    }

    public function test_authenticated_user_can_logout()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
            ]);
    }

    public function test_unauthenticated_user_cannot_logout()
    {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_get_profile()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data'    => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_get_profile()
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }

    public function test_signup_throws_validation_exception_for_existing_email_in_service()
    {
        // Create a user first
        $existingUser = User::factory()->create();

        // Mock the repository to simulate the service layer validation
        $userData = [
            'name'                  => $this->faker->name,
            'email'                 => $existingUser->email,
            'password'              => 'password123', // pragma: allowlist secret
            'password_confirmation' => 'password123', // pragma: allowlist secret
        ];

        $response = $this->postJson('/api/v1/auth/signup', $userData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors',
            ]);
    }

    public function test_login_with_remember_me_functionality()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'), // pragma: allowlist secret
        ]);

        $loginData = [
            'email'       => $user->email,
            'password'    => 'password123', // pragma: allowlist secret
            'remember_me' => true,
        ];

        $response = $this->postJson('/api/v1/auth/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token' => [
                        'access_token',
                        'token_type',
                        'expires_at',
                    ],
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    public function test_logout_with_null_user()
    {
        // Test the logout method when user is null (edge case)
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401);
    }
}
