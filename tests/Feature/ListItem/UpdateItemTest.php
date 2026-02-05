<?php

use App\Models\CustomList;
use App\Models\ListItem;
use App\Models\User;

describe('List Item Update', function (): void {

    beforeEach(function () {
        $this->owner = User::factory()->create();
        $this->list = CustomList::factory()->create(['owner_uuid' => $this->owner->uuid]);
        $this->item = $this->list->items()->create([
            'name' => 'Original name',
            'description' => 'Original description',
            'custom_list_uuid' => $this->list->uuid,
            'completed' => false,
            'version' => 1
        ]);
    });

    it('allows the owner to update an item with correct version', function () {
        $response = $this->actingAs($this->owner)
            ->patchJson("/api/lists/{$this->list->uuid}/items/{$this->item->uuid}", [
                'name' => 'Updated name',
                'description' => 'Updated description',
                'version' => 1
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('item.name', 'Updated name')
            ->assertJsonPath('item.version', 2);

        $this->assertDatabaseHas('list_items', [
            'uuid' => $this->item->uuid,
            'name' => 'Updated name',
            'version' => 2
        ]);
    });

    it('allows a shared editor to update an item', function () {
        $editor = User::factory()->create();
        $this->list->sharedWith()->attach($editor->uuid, ['role' => 'editor']);

        $response = $this->actingAs($editor)
            ->patchJson("/api/lists/{$this->list->uuid}/items/{$this->item->uuid}", [
                'name' => 'Editor update',
                'version' => 1
            ]);

        $response->assertStatus(200);
    });

    it('fails with wrong version (optimistic locking)', function () {
        $response = $this->actingAs($this->owner)
            ->patchJson("/api/lists/{$this->list->uuid}/items/{$this->item->uuid}", [
                'name' => 'Outdated update',
                'version' => 99
            ]);

        $response->assertStatus(409);

        $this->assertDatabaseHas('list_items', [
            'uuid' => $this->item->uuid,
            'name' => 'Original name',
            'version' => 1
        ]);
    });

    it('denies access to non-shared users', function () {
        $stranger = User::factory()->create();

        $response = $this->actingAs($stranger)
            ->patchJson("/api/lists/{$this->list->uuid}/items/{$this->item->uuid}", [
                'name' => 'Hacked',
                'version' => 1
            ]);

        $response->assertStatus(403);
    });

    it('denies update for item from different list', function () {
        $otherList = CustomList::factory()->create(['owner_uuid' => $this->owner->uuid]);

        $response = $this->actingAs($this->owner)
            ->patchJson("/api/lists/{$otherList->uuid}/items/{$this->item->uuid}", [
                'name' => 'Wrong list',
                'version' => 1
            ]);

        $response->assertStatus(403);
    });

    it('requires name to be provided', function () {
        $response = $this->actingAs($this->owner)
            ->patchJson("/api/lists/{$this->list->uuid}/items/{$this->item->uuid}", [
                'version' => 1
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    it('requires version to be provided', function () {
        $response = $this->actingAs($this->owner)
            ->patchJson("/api/lists/{$this->list->uuid}/items/{$this->item->uuid}", [
                'name' => 'Updated'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['version']);
    });

    it('requires authentication', function () {
        $response = $this->patchJson("/api/lists/{$this->list->uuid}/items/{$this->item->uuid}", [
            'name' => 'Test',
            'version' => 1
        ]);

        $response->assertStatus(401);
    });
});
