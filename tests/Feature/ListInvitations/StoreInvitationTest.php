<?php

use App\Models\CustomList;
use App\Models\User;

describe('List Invitation Store', function (): void {

    beforeEach(function () {
        $this->owner = User::factory()->create();
        $this->list = CustomList::factory()->create(['owner_uuid' => $this->owner->uuid]);
    });

    it('allows the owner to create an invitation', function () {
        $response = $this->actingAs($this->owner)
            ->postJson("/api/lists/{$this->list->uuid}/invitations", [
                'max_uses' => 5
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'invitation' => ['uuid', 'max_uses', 'share_url', 'expires_at']
            ])
            ->assertJsonPath('invitation.max_uses', 5);

        $this->assertDatabaseHas('list_invitations', [
            'custom_list_uuid' => $this->list->uuid,
            'max_uses' => 5
        ]);
    });

    it('creates invitation with default max_uses of 1', function () {
        $response = $this->actingAs($this->owner)
            ->postJson("/api/lists/{$this->list->uuid}/invitations", []);

        $response->assertStatus(200)
            ->assertJsonPath('invitation.max_uses', 1);
    });

    it('creates invitation with expiration time', function () {
        $response = $this->actingAs($this->owner)
            ->postJson("/api/lists/{$this->list->uuid}/invitations", []);

        $response->assertStatus(200);
        expect($response->json('invitation.expires_at'))->not->toBeNull();
    });

    it('generates unique tokens for each invitation', function () {
        $response1 = $this->actingAs($this->owner)
            ->postJson("/api/lists/{$this->list->uuid}/invitations", []);

        $response2 = $this->actingAs($this->owner)
            ->postJson("/api/lists/{$this->list->uuid}/invitations", []);

        expect($response1->json('invitation.share_url'))
            ->not->toBe($response2->json('invitation.share_url'));
    });

    it('prevents a non-owner from creating an invitation', function () {
        $stranger = User::factory()->create();

        $response = $this->actingAs($stranger)
            ->postJson("/api/lists/{$this->list->uuid}/invitations", [
                'max_uses' => 1
            ]);

        $response->assertStatus(403);
    });

    it('prevents a shared editor from creating an invitation', function () {
        $editor = User::factory()->create();
        $this->list->sharedWith()->attach($editor->uuid, ['role' => 'editor']);

        $response = $this->actingAs($editor)
            ->postJson("/api/lists/{$this->list->uuid}/invitations", [
                'max_uses' => 1
            ]);

        $response->assertStatus(403);
    });

    it('rejects max_uses less than 1', function () {
        $response = $this->actingAs($this->owner)
            ->postJson("/api/lists/{$this->list->uuid}/invitations", [
                'max_uses' => 0
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['max_uses']);
    });

    it('rejects non-integer max_uses', function () {
        $response = $this->actingAs($this->owner)
            ->postJson("/api/lists/{$this->list->uuid}/invitations", [
                'max_uses' => 'five'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['max_uses']);
    });

    it('requires authentication', function () {
        $response = $this->postJson("/api/lists/{$this->list->uuid}/invitations", []);

        $response->assertStatus(401);
    });

});
