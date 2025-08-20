<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecureHeadersMiddlewareTest extends TestCase
{
    public function test_secure_headers_are_set_on_api_routes()
    {
        // Make a request to any API endpoint
        $response = $this->getJson('/api/health');

        // Assert the response is successful
        $response->assertStatus(200);

        // Assert secure headers are present
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_secure_headers_are_set_on_authenticated_api_routes()
    {
        // Make a request to an authenticated API endpoint without auth (should still have headers)
        $response = $this->getJson('/api/v1/auth/me');

        // Even though this will return 401, headers should still be set
        $response->assertStatus(401);

        // Assert secure headers are present
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_secure_headers_are_set_on_post_requests()
    {
        // Make a POST request to test headers on different HTTP methods
        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'password', // pragma: allowlist secret
        ]);

        // Even though this will return validation errors, headers should still be set
        $response->assertStatus(422);

        // Assert secure headers are present
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
