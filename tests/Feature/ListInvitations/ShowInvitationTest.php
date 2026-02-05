<?php

use App\Models\CustomList;
use App\Models\ListInvitation;
use App\Models\User;

describe('List Invitation Show', function (): void {

    beforeEach(function () {
        $this->owner = User::factory()->create();
        $this->list = CustomList::factory()->create(['owner_uuid' => $this->owner->uuid]);
        $this->invitation = $this->list->invitations()->create([
            'custom_list_uuid' => $this->list->uuid,
            'token' => 'test-token-12345',
            'max_uses' => 5,
            'uses' => 0,
            'expires_at' => now()->addMinutes(5)
        ]);
    });

    it('shows invitation details without authentication', function () {
        $response = $this->getJson("/api/lists/{$this->list->uuid}/invitations/{$this->invitation->token}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'invitation' => ['uuid', 'max_uses', 'accept_url', 'expires_at']
            ]);
    });

    it('shows invitation details with authentication', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson("/api/lists/{$this->list->uuid}/invitations/{$this->invitation->token}");

        $response->assertStatus(200);
    });

    it('returns 404 for invalid token', function () {
        $response = $this->getJson("/api/lists/{$this->list->uuid}/invitations/invalid-token");

        $response->assertStatus(404);
    });

    it('returns 404 for token from different list', function () {
        $otherList = CustomList::factory()->create(['owner_uuid' => $this->owner->uuid]);

        $response = $this->getJson("/api/lists/{$otherList->uuid}/invitations/{$this->invitation->token}");

        $response->assertStatus(404);
    });
});
