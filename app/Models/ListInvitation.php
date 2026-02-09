<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListInvitation extends Model
{
    use HasUuids, Prunable;

    protected $primaryKey = 'uuid';

    protected $fillable = [
        'custom_list_uuid',
        'token',
        'expires_at',
        'max_uses',
        'uses'
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(CustomList::class, 'custom_list_uuid');
    }

    public function prunable()
    {
        return static::where('expires_at', '<', now());
    }
}
