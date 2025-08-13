<?php

namespace Tests\Unit;

use App\Http\Requests\Auth\Api\SignupRequest;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class SignupRequestTest extends TestCase
{
    private SignupRequest $signupRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signupRequest = new SignupRequest;
    }

    public function test_authorize_returns_true()
    {
        $this->assertTrue($this->signupRequest->authorize());
    }

    public function test_validation_passes_with_valid_data()
    {
        $validData = [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'password123', // pragma: allowlist secret
            'password_confirmation' => 'password123', // pragma: allowlist secret
        ];

        $validator = Validator::make($validData, $this->signupRequest->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_missing_name()
    {
        $invalidData = [
            'email'                 => 'john@example.com',
            'password'              => 'password123', // pragma: allowlist secret
            'password_confirmation' => 'password123', // pragma: allowlist secret
        ];

        $validator = Validator::make($invalidData, $this->signupRequest->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_empty_name()
    {
        $invalidData = [
            'name'                  => '',
            'email'                 => 'john@example.com',
            'password'              => 'password123', // pragma: allowlist secret
            'password_confirmation' => 'password123', // pragma: allowlist secret
        ];

        $validator = Validator::make($invalidData, $this->signupRequest->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_name_too_long()
    {
        $invalidData = [
            'name'                  => str_repeat('a', 256), // 256 characters
            'email'                 => 'john@example.com',
            'password'              => 'password123', // pragma: allowlist secret
            'password_confirmation' => 'password123', // pragma: allowlist secret
        ];

        $validator = Validator::make($invalidData, $this->signupRequest->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_missing_email()
    {
        $invalidData = [
            'name'                  => 'John Doe',
            'password'              => 'password123', // pragma: allowlist secret
            'password_confirmation' => 'password123', // pragma: allowlist secret
        ];

        $validator = Validator::make($invalidData, $this->signupRequest->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_invalid_email_format()
    {
        $invalidData = [
            'name'                  => 'John Doe',
            'email'                 => 'invalid-email-format',
            'password'              => 'password123', // pragma: allowlist secret
            'password_confirmation' => 'password123', // pragma: allowlist secret
        ];

        $validator = Validator::make($invalidData, $this->signupRequest->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_duplicate_email()
    {
        // Create a user with existing email
        User::factory()->create(['email' => 'existing@example.com']);

        $invalidData = [
            'name'                  => 'John Doe',
            'email'                 => 'existing@example.com',
            'password'              => 'password123', // pragma: allowlist secret
            'password_confirmation' => 'password123', // pragma: allowlist secret
        ];

        $validator = Validator::make($invalidData, $this->signupRequest->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_missing_password()
    {
        $invalidData = [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password_confirmation' => 'password123', // pragma: allowlist secret
        ];

        $validator = Validator::make($invalidData, $this->signupRequest->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_short_password()
    {
        $invalidData = [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => '1234567', // 7 characters
            'password_confirmation' => '1234567',
        ];

        $validator = Validator::make($invalidData, $this->signupRequest->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_mismatched_password_confirmation()
    {
        $invalidData = [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'password123', // pragma: allowlist secret
            'password_confirmation' => 'differentpassword', // pragma: allowlist secret
        ];

        $validator = Validator::make($invalidData, $this->signupRequest->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_missing_password_confirmation()
    {
        $invalidData = [
            'name'     => 'John Doe',
            'email'    => 'john@example.com',
            'password' => 'password123', // pragma: allowlist secret
        ];

        $validator = Validator::make($invalidData, $this->signupRequest->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('password_confirmation', $validator->errors()->toArray());
    }

    public function test_custom_messages_are_defined()
    {
        $messages = $this->signupRequest->messages();

        $expectedMessages = [
            'name.required'                  => 'Name is required',
            'name.max'                       => 'Name cannot exceed 255 characters',
            'email.required'                 => 'Email is required',
            'email.email'                    => 'Please provide a valid email address',
            'email.unique'                   => 'This email is already registered',
            'password.required'              => 'Password is required',
            'password.min'                   => 'Password must be at least 8 characters',
            'password.confirmed'             => 'Password confirmation does not match',
            'password_confirmation.required' => 'Password confirmation is required',
        ];

        foreach ($expectedMessages as $key => $expectedMessage) {
            $this->assertArrayHasKey($key, $messages);
            $this->assertEquals($expectedMessage, $messages[$key]);
        }
    }

    public function test_validation_with_custom_messages()
    {
        $invalidData = [
            'name'                  => '',
            'email'                 => 'invalid-email',
            'password'              => '123',
            'password_confirmation' => '456',
        ];

        $validator = Validator::make(
            $invalidData,
            $this->signupRequest->rules(),
            $this->signupRequest->messages()
        );

        $this->assertFalse($validator->passes());

        $errors = $validator->errors()->toArray();
        $this->assertContains('Name is required', $errors['name']);
        $this->assertContains('Please provide a valid email address', $errors['email']);
        $this->assertContains('Password must be at least 8 characters', $errors['password']);
    }
}
