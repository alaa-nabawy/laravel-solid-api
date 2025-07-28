<?php

namespace App\Services\User;

use App\Repositories\UserRepository;

class UserDeleteService
{
    public function __construct(private UserRepository $userRepository) {}

    public function delete(array $data)
    {
        // Your delete logic goes here
    }
}
