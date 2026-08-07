<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListItem extends Model
{
    use HasUuids;

    protected $primaryKey = 'uuid';

    protected $fillable = [
        'name',
        'custom_list_uuid',
        'description',
        'completed',
        'version',
        'locked_by',
        'locked_at',
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(CustomList::class, 'custom_list_uuid');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
