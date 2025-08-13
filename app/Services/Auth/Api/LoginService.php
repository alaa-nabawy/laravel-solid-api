<?php

namespace App\Services\Auth\Api;

use App\Http\Resources\Auth\Api\UserResource;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Authenticate user and return access token.
     *
     * @param array<string, mixed> $credentials
     *
     * @throws ValidationException
     *
     * @return array<string, mixed>
     */
    public function login(array $credentials): array
    {
        $user = $this->userRepository->findByField('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $tokenResult = $user->createToken('Personal Access Token');
        $token       = $tokenResult->token;

        if (isset($credentials['remember_me']) && $credentials['remember_me']) {
            $token->expires_at = now()->addWeeks(1);
        }

        $token->save();

        return [
            'token' => [
                'access_token' => $tokenResult->accessToken,
                'token_type'   => 'Bearer',
                'expires_at'   => $token->expires_at->toDateTimeString(),
            ],
            'user' => new UserResource($user),
        ];
    }

    /**
     * Revoke user's access token.
     */
    public function logout(\App\Models\User $user): bool
    {
        $user->tokens()->delete();

        return true;
    }
}
