<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListItem extends Model
{
    use HasUuids;

    protected $primaryKey = 'uuid';

    protected array $fillable = [
        'name',
        'list_id',
        'description',
        'completed',
        'version',
        'locked_by',
        'locked_at',
    ];

    protected function casts()
    {
        return [
            'locked_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime'
        ];
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(Lists::class);
    }
}
