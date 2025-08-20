<?php

namespace Tests\Feature;

use Illuminate\Support\Str;
use Tests\TestCase;

class RequestIdMiddlewareTest extends TestCase
{
    public function test_request_id_is_generated_when_missing()
    {
        // Make a request without X-Request-Id header
        $response = $this->getJson('/api/health');

        // Assert the response is successful
        $response->assertStatus(200);

        // Assert X-Request-Id header is present in response
        $this->assertTrue($response->headers->has('X-Request-Id'));

        // Assert the generated request ID is a valid UUID
        $requestId = $response->headers->get('X-Request-Id');
        $this->assertTrue(Str::isUuid($requestId));
    }

    public function test_request_id_is_preserved_when_provided()
    {
        // Generate a custom request ID
        $customRequestId = Str::uuid()->toString();

        // Make a request with custom X-Request-Id header
        $response = $this->withHeaders([
            'X-Request-Id' => $customRequestId,
        ])->getJson('/api/health');

        // Assert the response is successful
        $response->assertStatus(200);

        // Assert the same request ID is returned in response
        $response->assertHeader('X-Request-Id', $customRequestId);
    }

    public function test_request_id_works_on_different_http_methods()
    {
        // Test with POST request
        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'password', // pragma: allowlist secret
        ]);

        // Assert X-Request-Id header is present
        $this->assertTrue($response->headers->has('X-Request-Id'));

        // Assert the generated request ID is a valid UUID
        $requestId = $response->headers->get('X-Request-Id');
        $this->assertTrue(Str::isUuid($requestId));
    }

    public function test_request_id_is_preserved_across_authenticated_routes()
    {
        // Generate a custom request ID
        $customRequestId = Str::uuid()->toString();

        // Make a request to an authenticated endpoint with custom request ID
        $response = $this->withHeaders([
            'X-Request-Id' => $customRequestId,
        ])->getJson('/api/v1/auth/me');

        // Even though this will return 401, request ID should still be preserved
        $response->assertStatus(401);
        $response->assertHeader('X-Request-Id', $customRequestId);
    }

    public function test_invalid_request_id_is_replaced_with_valid_uuid()
    {
        // Make a request with invalid X-Request-Id header
        $response = $this->withHeaders([
            'X-Request-Id' => 'invalid-uuid',
        ])->getJson('/api/health');

        // Assert the response is successful
        $response->assertStatus(200);

        // Assert X-Request-Id header is present and is a valid UUID (not the invalid one)
        $this->assertTrue($response->headers->has('X-Request-Id'));
        $requestId = $response->headers->get('X-Request-Id');
        $this->assertTrue(Str::isUuid($requestId));
        $this->assertNotEquals('invalid-uuid', $requestId);
    }
}
