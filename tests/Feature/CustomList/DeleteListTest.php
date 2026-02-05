<?php

use App\Models\CustomList;
use App\Models\User;

describe('List Delete', function (): void {

    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->list = CustomList::factory()->create(['owner_uuid' => $this->user->uuid]);
    });

    it('allows the owner to delete the list', function () {
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/lists/{$this->list->uuid}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('custom_lists', [
            'uuid' => $this->list->uuid
        ]);
    });

    it('denies deletion to non-owner users', function () {
        $stranger = User::factory()->create();

        $response = $this->actingAs($stranger)
            ->deleteJson("/api/lists/{$this->list->uuid}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('custom_lists', [
            'uuid' => $this->list->uuid
        ]);
    });

    it('denies deletion to shared editors', function () {
        $editor = User::factory()->create();
        $this->list->sharedWith()->attach($editor->uuid, ['role' => 'editor']);

        $response = $this->actingAs($editor)
            ->deleteJson("/api/lists/{$this->list->uuid}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('custom_lists', [
            'uuid' => $this->list->uuid
        ]);
    });

    it('cascades deletion to list items', function () {
        $this->list->items()->create([
            'name' => 'Item to delete',
            'custom_list_uuid' => $this->list->uuid
        ]);

        $this->assertDatabaseCount('list_items', 1);

        $this->actingAs($this->user)
            ->deleteJson("/api/lists/{$this->list->uuid}");

        $this->assertDatabaseCount('list_items', 0);
    });

    it('returns 404 for non-existent list', function () {
        $fakeUuid = '00000000-0000-0000-0000-000000000000';

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/lists/{$fakeUuid}");

        $response->assertStatus(404);
    });

    it('requires authentication', function () {
        $response = $this->deleteJson("/api/lists/{$this->list->uuid}");

        $response->assertStatus(401);
    });
});
