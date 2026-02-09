<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListInvitationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $properties = [
            'uuid' => $this->uuid,
            'max_uses' => $this->max_uses,
            'created_at' => $this->created_at,
            'expires_at' => $this->expires_at,

        ];

        if ($request->routeIs('lists.invitations.show')) {
            $properties['list_title'] = $this->list->title;

            $properties['accept_url'] = route('lists.invitations.accept', ['list' => $this->custom_list_uuid, 'invitation' => $this]);
        } else {
            $properties['list_uuid'] = $this->custom_list_uuid;
            $properties['token'] = $this->token;
            $properties['share_url'] = route('lists.invitations.show', ['list' => $this->custom_list_uuid, 'invitation' => $this]);
        }

        return $properties;
    }
}
