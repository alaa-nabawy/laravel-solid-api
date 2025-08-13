<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

trait ApiResponseTrait
{
    /**
     * Return a success JSON response.
     *
     * @param mixed $data
     */
    protected function successResponse($data = null, string $message = 'Success', int $statusCode = Response::HTTP_OK): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a created resource JSON response.
     *
     * @param mixed $data
     */
    protected function createdResponse($data = null, string $message = 'Resource created successfully'): JsonResponse
    {
        return $this->successResponse($data, $message, Response::HTTP_CREATED);
    }

    /**
     * Return an error JSON response.
     *
     * @param mixed $errors
     */
    protected function errorResponse(string $message = 'An error occurred', int $statusCode = Response::HTTP_BAD_REQUEST, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a validation error JSON response.
     */
    protected function validationErrorResponse(ValidationException $exception): JsonResponse
    {
        return $this->errorResponse(
            'Validation failed',
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $exception->errors()
        );
    }

    /**
     * Return a not found JSON response.
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_NOT_FOUND);
    }

    /**
     * Return an unauthorized JSON response.
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Return a forbidden JSON response.
     */
    protected function forbiddenResponse(string $message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Return an internal server error JSON response.
     */
    protected function serverErrorResponse(string $message = 'Internal server error'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Return a no content JSON response.
     */
    protected function noContentResponse(string $message = 'No content'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
        ], Response::HTTP_NO_CONTENT);
    }

    /**
     * Return a paginated response.
     *
     * @param mixed $paginatedData
     */
    protected function paginatedResponse($paginatedData, string $message = 'Data retrieved successfully'): JsonResponse
    {
        return response()->json([
            'success'    => true,
            'message'    => $message,
            'data'       => $paginatedData->items(),
            'pagination' => [
                'current_page' => $paginatedData->currentPage(),
                'last_page'    => $paginatedData->lastPage(),
                'per_page'     => $paginatedData->perPage(),
                'total'        => $paginatedData->total(),
                'from'         => $paginatedData->firstItem(),
                'to'           => $paginatedData->lastItem(),
            ],
        ]);
    }
}
