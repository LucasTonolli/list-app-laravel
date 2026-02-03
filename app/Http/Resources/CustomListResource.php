<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class CustomListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $properties =  [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'items_count' => $this->items_count ?? 0,
            'shared_with_count' => $this->shared_with_count ?? 0,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'items' => ListItemResource::collection($this->whenLoaded('items')),
        ];

        return $properties;
    }
}
