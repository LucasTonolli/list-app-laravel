<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids, HasApiTokens;

    protected $primaryKey = 'uuid';

    public function ownedLists(): HasMany
    {
        return $this->hasMany(CustomList::class);
    }

    public function sharedLists(): BelongsToMany
    {
        return $this->belongsToMany(CustomList::class)->wherePivot('role', 'editor');
    }

    public function lists(): Collection
    {
        return collect([
            'owned' => $this->ownedLists(),
            'shared' => $this->sharedLists(),
        ]);
    }
}
