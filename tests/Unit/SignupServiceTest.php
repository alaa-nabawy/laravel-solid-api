<?php

namespace Tests\Unit;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\Auth\Api\SignupService;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\PassportTestHelper;
use Tests\TestCase;

class SignupServiceTest extends TestCase
{
    use PassportTestHelper, WithFaker;

    private SignupService $signupService;

    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpPassport();
        $this->userRepository = Mockery::mock(UserRepository::class);
        $this->signupService  = new SignupService($this->userRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_password_hashing_works_correctly()
    {
        $password       = 'password123'; // pragma: allowlist secret
        $hashedPassword = Hash::make($password);

        $this->assertTrue(Hash::check($password, $hashedPassword));
        $this->assertFalse(Hash::check('wrongpassword', $hashedPassword)); // pragma: allowlist secret
    }

    public function test_service_instance_is_created_correctly()
    {
        $this->assertInstanceOf(SignupService::class, $this->signupService);
    }

    public function test_password_is_hashed_correctly()
    {
        $password       = 'password123'; // pragma: allowlist secret
        $hashedPassword = Hash::make($password);

        $this->assertTrue(Hash::check($password, $hashedPassword));
        $this->assertFalse(Hash::check('wrongpassword', $hashedPassword)); // pragma: allowlist secret
    }

    public function test_validate_signup_data_returns_correct_rules()
    {
        $userData = [
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'password' => 'password123', // pragma: allowlist secret
        ];

        $rules = $this->signupService->validateSignupData($userData);

        $expectedRules = [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed', // pragma: allowlist secret
        ];

        $this->assertEquals($expectedRules, $rules);
    }

    public function test_signup_throws_validation_exception_when_user_exists()
    {
        $userData = [
            'name'     => 'Test User',
            'email'    => 'existing@example.com',
            'password' => 'password123', // pragma: allowlist secret
        ];

        $existingUser        = new User;
        $existingUser->email = 'existing@example.com';

        // Mock the repository to return an existing user
        $this->userRepository
            ->shouldReceive('findByField')
            ->with('email', 'existing@example.com')
            ->andReturn(collect([$existingUser]));

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The email has already been taken.');

        $this->signupService->signup($userData);
    }

    public function test_signup_creates_user_and_token_successfully()
    {
        $userData = [
            'name'     => 'Test User',
            'email'    => 'new@example.com',
            'password' => 'password123', // pragma: allowlist secret
        ];

        // Mock repository to return no existing user
        $this->userRepository
            ->shouldReceive('findByField')
            ->with('email', 'new@example.com')
            ->andReturn(collect([]));

        // Create a real user for token creation
        $user = User::factory()->create([
            'name'     => 'Test User',
            'email'    => 'new@example.com',
            'password' => Hash::make('password123'), // pragma: allowlist secret
        ]);

        // Mock repository to return the created user
        $this->userRepository
            ->shouldReceive('create')
            ->with(Mockery::on(function ($data) {
                return $data['name'] === 'Test User' &&
                       $data['email'] === 'new@example.com' &&
                       is_string($data['password']) &&
                       strlen($data['password']) > 10; // Hashed password check
            }))
            ->andReturn($user);

        $result = $this->signupService->signup($userData);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('access_token', $result['token']);
        $this->assertArrayHasKey('token_type', $result['token']);
        $this->assertArrayHasKey('expires_at', $result['token']);
        $this->assertEquals('Bearer', $result['token']['token_type']);
    }
}
