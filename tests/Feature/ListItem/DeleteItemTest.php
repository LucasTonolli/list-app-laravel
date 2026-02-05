<?php

use App\Models\CustomList;
use App\Models\ListItem;
use App\Models\User;

describe('List Item Delete', function (): void {

    beforeEach(function () {
        $this->owner = User::factory()->create();
        $this->list = CustomList::factory()->create(['owner_uuid' => $this->owner->uuid]);
        $this->item = $this->list->items()->create([
            'name' => 'Item to delete',
            'custom_list_uuid' => $this->list->uuid,
            'completed' => false,
            'version' => 1
        ]);
    });

    it('allows the owner to delete an item', function () {
        $response = $this->actingAs($this->owner)
            ->deleteJson("/api/lists/{$this->list->uuid}/items/{$this->item->uuid}");

        $response->assertStatus(200)
            ->assertJsonPath('deleted', true);

        $this->assertDatabaseMissing('list_items', [
            'uuid' => $this->item->uuid
        ]);
    });

    it('allows a shared editor to delete an item', function () {
        $editor = User::factory()->create();
        $this->list->sharedWith()->attach($editor->uuid, ['role' => 'editor']);

        $response = $this->actingAs($editor)
            ->deleteJson("/api/lists/{$this->list->uuid}/items/{$this->item->uuid}");

        $response->assertStatus(200);
    });

    it('denies access to non-shared users', function () {
        $stranger = User::factory()->create();

        $response = $this->actingAs($stranger)
            ->deleteJson("/api/lists/{$this->list->uuid}/items/{$this->item->uuid}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('list_items', [
            'uuid' => $this->item->uuid
        ]);
    });

    it('denies delete for item from different list', function () {
        $otherList = CustomList::factory()->create(['owner_uuid' => $this->owner->uuid]);

        $response = $this->actingAs($this->owner)
            ->deleteJson("/api/lists/{$otherList->uuid}/items/{$this->item->uuid}");

        $response->assertStatus(403);
    });

    it('returns 404 for non-existent item', function () {
        $fakeUuid = '00000000-0000-0000-0000-000000000000';

        $response = $this->actingAs($this->owner)
            ->deleteJson("/api/lists/{$this->list->uuid}/items/{$fakeUuid}");

        $response->assertStatus(404);
    });

    it('requires authentication', function () {
        $response = $this->deleteJson("/api/lists/{$this->list->uuid}/items/{$this->item->uuid}");

        $response->assertStatus(401);
    });
});
