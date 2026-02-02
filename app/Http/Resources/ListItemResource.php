<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'list_uuid' => $this->custom_list_uuid,
            'name' => $this->name,
            'description' => $this->description,
            'completed' => $this->completed,
            'version' => $this->version,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'locked_at' => $this->locked_at,
            'locked_by' => $this->locked_by
        ];
    }
}
