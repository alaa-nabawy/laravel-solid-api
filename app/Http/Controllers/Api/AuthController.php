<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\Api\LoginRequest;
use App\Http\Requests\Auth\Api\SignupRequest;
use App\Http\Resources\Auth\Api\UserResource;
use App\Services\Auth\Api\LoginService;
use App\Services\Auth\Api\SignupService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * User login.
     *
     * @throws ValidationException
     */
    public function login(LoginRequest $request, LoginService $loginService): JsonResponse
    {
        try {
            $result = $loginService->login($request->validated());

            return $this->successResponse($result, 'Login successful');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        }
    }

    /**
     * User registration.
     *
     * @throws ValidationException
     */
    public function signup(SignupRequest $request, SignupService $signupService): JsonResponse
    {
        try {
            $result = $signupService->signup($request->validated());

            return $this->createdResponse($result, 'Registration successful');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        }
    }

    /**
     * User logout.
     */
    public function logout(Request $request, LoginService $loginService): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            $loginService->logout($user);
        }

        return $this->successResponse(null, 'Logout successful');
    }

    /**
     * Get authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        return $this->successResponse(new UserResource($request->user()), 'User data retrieved successfully');
    }
}
