<?php

namespace Tests\Unit;

use App\Repositories\UserRepository;
use App\Services\Auth\Api\LoginService;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class LoginServiceTest extends TestCase
{
    private LoginService $loginService;

    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = Mockery::mock(UserRepository::class);
        $this->loginService   = new LoginService($this->userRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_service_instance_is_created_correctly()
    {
        $this->assertInstanceOf(LoginService::class, $this->loginService);
    }

    public function test_password_verification_works_correctly()
    {
        $password       = 'password123'; // pragma: allowlist secret
        $hashedPassword = Hash::make($password);

        $this->assertTrue(Hash::check($password, $hashedPassword));
        $this->assertFalse(Hash::check('wrongpassword', $hashedPassword)); // pragma: allowlist secret
    }
}
