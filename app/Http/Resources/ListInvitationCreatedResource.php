<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListInvitationCreatedResource extends JsonResource
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
            'max_uses' => $this->max_uses,
            'created_at' => $this->created_at,
            'expires_at' => $this->expires_at,
            'list_uuid' => $this->custom_list_uuid,
            'token' => $this->token,
            'share_url' => route('lists.invitations.show', ['list' => $this->custom_list_uuid, 'invitation' => $this]),
        ];
    }
}
