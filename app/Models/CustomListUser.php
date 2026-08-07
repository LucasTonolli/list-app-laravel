<?php

namespace App\Models;

use App\Enums\Roles;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CustomListUser extends Pivot
{
    protected function casts(): array
    {
        return [
            'role' => Roles::class,
        ];
    }
}
