<?php

namespace App\Models;

use App\Enums\Roles;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids, HasApiTokens;

    protected $primaryKey = 'uuid';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected function casts(): array
    {
        return [
            'role' => Roles::class
        ];
    }

    public function ownedLists(): HasMany
    {
        return $this->hasMany(CustomList::class);
    }

    public function sharedLists(): BelongsToMany
    {
        return $this->belongsToMany(CustomList::class)->using(CustomListUser::class)->wherePivot('role', Roles::Editor);
    }

    public function lists(): BelongsToMany
    {
        return $this->belongsToMany(CustomList::class)->using(CustomListUser::class);
    }
}
