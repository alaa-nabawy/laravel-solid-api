<?php

namespace Tests\Unit;

use App\Http\Requests\Auth\Api\LoginRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class LoginRequestTest extends TestCase
{
    private LoginRequest $loginRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginRequest = new LoginRequest;
    }

    public function test_authorize_returns_true()
    {
        $this->assertTrue($this->loginRequest->authorize());
    }

    public function test_validation_passes_with_valid_data()
    {
        $validData = [
            'email'    => 'test@example.com',
            'password' => 'password123', // pragma: allowlist secret
        ];

        $validator = Validator::make($validData, $this->loginRequest->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_missing_email()
    {
        $invalidData = [
            'password' => 'password123', // pragma: allowlist secret
        ];

        $validator = Validator::make($invalidData, $this->loginRequest->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_invalid_email_format()
    {
        $invalidData = [
            'email'    => 'invalid-email-format',
            'password' => 'password123', // pragma: allowlist secret
        ];

        $validator = Validator::make($invalidData, $this->loginRequest->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_missing_password()
    {
        $invalidData = [
            'email' => 'test@example.com',
        ];

        $validator = Validator::make($invalidData, $this->loginRequest->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_empty_email()
    {
        $invalidData = [
            'email'    => '',
            'password' => 'password123', // pragma: allowlist secret
        ];

        $validator = Validator::make($invalidData, $this->loginRequest->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_empty_password()
    {
        $invalidData = [
            'email'    => 'test@example.com',
            'password' => '',
        ];

        $validator = Validator::make($invalidData, $this->loginRequest->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_custom_messages_are_defined()
    {
        $messages = $this->loginRequest->messages();

        $this->assertArrayHasKey('email.required', $messages);
        $this->assertArrayHasKey('email.email', $messages);
        $this->assertArrayHasKey('password.required', $messages);

        $this->assertEquals('Email is required', $messages['email.required']);
        $this->assertEquals('Please provide a valid email address', $messages['email.email']);
        $this->assertEquals('Password is required', $messages['password.required']);
    }

    public function test_validation_with_custom_messages()
    {
        $invalidData = [
            'email'    => 'invalid-email',
            'password' => '',
        ];

        $validator = Validator::make(
            $invalidData,
            $this->loginRequest->rules(),
            $this->loginRequest->messages()
        );

        $this->assertFalse($validator->passes());

        $errors = $validator->errors()->toArray();
        $this->assertContains('Please provide a valid email address', $errors['email']);
        $this->assertContains('Password is required', $errors['password']);
    }
}
