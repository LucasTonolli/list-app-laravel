<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

final class IdentityService
{
    public function register(): string
    {
        $user = User::create();
        $token = $user->createToken('token')->plainTextToken;

        return $token;
    }
}
