<?php

namespace App\Services\Auth\Api;

use App\Http\Resources\Auth\Api\UserResource;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SignupService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Register a new user and return access token.
     *
     * @param array<string, mixed> $userData
     *
     * @throws ValidationException
     *
     * @return array<string, mixed>
     */
    public function signup(array $userData): array
    {
        // Check if user already exists
        $existingUser = $this->userRepository->findByField('email', $userData['email'])->first();

        if ($existingUser) {
            throw ValidationException::withMessages([
                'email' => ['The email has already been taken.'],
            ]);
        }

        // Create new user
        $user = $this->userRepository->create([
            'name'     => $userData['name'],
            'email'    => $userData['email'],
            'password' => Hash::make($userData['password']),
        ]);

        // Create access token
        $tokenResult = $user->createToken('Personal Access Token');
        $token       = $tokenResult->token;
        $token->save();

        return [
            'user'  => new UserResource($user),
            'token' => [
                'access_token' => $tokenResult->accessToken,
                'token_type'   => 'Bearer',
                'expires_at'   => $token->expires_at->toDateTimeString(),
            ],
        ];
    }

    /**
     * Validate user registration data.
     *
     * @param array<string, mixed> $userData
     *
     * @return array<string, string>
     */
    public function validateSignupData(array $userData): array
    {
        $rules = [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed', // pragma: allowlist secret
        ];

        return $rules;
    }
}
