<?php

namespace App\Services\User;

use App\Repositories\UserRepository;

class UserCreateService
{
    public function __construct(private UserRepository $userRepository) {}

    public function create(array $data)
    {
        // Your create logic goes here
    }
}
