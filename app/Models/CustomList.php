<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomList extends Model
{
    use HasUuids;

    protected $primaryKey = 'uuid';

    protected $fillable = [
        'title',
        'owner_uuid',
    ];


    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function sharedWith(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role');
    }

    public function editors(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->wherePivot('role', 'editor');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ListItem::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
