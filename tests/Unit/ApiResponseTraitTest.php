<?php

namespace Tests\Unit;

use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Tests\TestCase;

class ApiResponseTraitTest extends TestCase
{
    use ApiResponseTrait;

    public function test_not_found_response()
    {
        $response = $this->notFoundResponse('Resource not found');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Resource not found', $data['message']);
    }

    public function test_unauthorized_response()
    {
        $response = $this->unauthorizedResponse('Unauthorized access');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Unauthorized access', $data['message']);
    }

    public function test_forbidden_response()
    {
        $response = $this->forbiddenResponse('Access forbidden');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Access forbidden', $data['message']);
    }

    public function test_server_error_response()
    {
        $response = $this->serverErrorResponse('Server error occurred');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Server error occurred', $data['message']);
    }

    public function test_no_content_response()
    {
        $response = $this->noContentResponse('No content available');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('No content available', $data['message']);
    }

    public function test_paginated_response()
    {
        // Create a mock paginated data object
        $paginatedData = new class
        {
            public function items()
            {
                return ['item1', 'item2'];
            }

            public function currentPage()
            {
                return 1;
            }

            public function lastPage()
            {
                return 5;
            }

            public function perPage()
            {
                return 10;
            }

            public function total()
            {
                return 50;
            }

            public function firstItem()
            {
                return 1;
            }

            public function lastItem()
            {
                return 10;
            }
        };

        $response = $this->paginatedResponse($paginatedData, 'Data retrieved');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Data retrieved', $data['message']);
        $this->assertEquals(['item1', 'item2'], $data['data']);
        $this->assertArrayHasKey('pagination', $data);
        $this->assertEquals(1, $data['pagination']['current_page']);
        $this->assertEquals(5, $data['pagination']['last_page']);
        $this->assertEquals(10, $data['pagination']['per_page']);
        $this->assertEquals(50, $data['pagination']['total']);
        $this->assertEquals(1, $data['pagination']['from']);
        $this->assertEquals(10, $data['pagination']['to']);
    }

    public function test_error_response_with_errors()
    {
        $errors   = ['field1' => ['Error message 1'], 'field2' => ['Error message 2']];
        $response = $this->errorResponse('Validation failed', Response::HTTP_UNPROCESSABLE_ENTITY, $errors);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Validation failed', $data['message']);
        $this->assertEquals($errors, $data['errors']);
    }

    public function test_success_response_with_null_data()
    {
        $response = $this->successResponse(null, 'Success message');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Success message', $data['message']);
        $this->assertArrayNotHasKey('data', $data);
    }
}
