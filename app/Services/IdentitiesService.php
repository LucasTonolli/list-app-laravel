<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

final class IdentitiesService
{
    public function register(): string
    {
        $user = User::create();

        return $user->uuid;
    }
}
