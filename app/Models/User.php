<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

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

    public function ownedLists(): BelongsToMany
    {
        return $this->belongsToMany(Lists::class)->wherePivot('role', 'owner');
    }

    public function sharedLists(): BelongsToMany
    {
        return $this->belongsToMany(Lists::class)->wherePivot('role', 'editor');
    }

    public function lists(): BelongsToMany
    {
        return $this->belongsToMany(Lists::class);
    }
}
