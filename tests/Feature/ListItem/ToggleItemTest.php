<?php

use App\Models\CustomList;
use App\Models\ListItem;
use App\Models\User;

describe('List Item Toggle', function (): void {

    beforeEach(function () {
        $this->owner = User::factory()->create();
        $this->list = CustomList::factory()->create(['owner_uuid' => $this->owner->uuid]);
        $this->item = $this->list->items()->create([
            'name' => 'Task to toggle',
            'custom_list_uuid' => $this->list->uuid,
            'completed' => false,
            'version' => 1
        ]);
    });

    it('toggles item from incomplete to complete', function () {
        $response = $this->actingAs($this->owner)
            ->patchJson("/api/lists/{$this->list->uuid}/items/{$this->item->uuid}/toggle");

        $response->assertStatus(200)
            ->assertJsonPath('item.completed', true)
            ->assertJsonPath('toggle', true);

        $this->assertDatabaseHas('list_items', [
            'uuid' => $this->item->uuid,
            'completed' => true
        ]);
    });

    it('toggles item from complete to incomplete', function () {
        $this->item->update(['completed' => true]);

        $response = $this->actingAs($this->owner)
            ->patchJson("/api/lists/{$this->list->uuid}/items/{$this->item->uuid}/toggle");

        $response->assertStatus(200)
            ->assertJsonPath('item.completed', false);

        $this->assertDatabaseHas('list_items', [
            'uuid' => $this->item->uuid,
            'completed' => false
        ]);
    });

    it('increments version on toggle', function () {
        $response = $this->actingAs($this->owner)
            ->patchJson("/api/lists/{$this->list->uuid}/items/{$this->item->uuid}/toggle");

        $response->assertStatus(200)
            ->assertJsonPath('item.version', 2);
    });

    it('allows a shared editor to toggle', function () {
        $editor = User::factory()->create();
        $this->list->sharedWith()->attach($editor->uuid, ['role' => 'editor']);

        $response = $this->actingAs($editor)
            ->patchJson("/api/lists/{$this->list->uuid}/items/{$this->item->uuid}/toggle");

        $response->assertStatus(200);
    });

    it('denies access to non-shared users', function () {
        $stranger = User::factory()->create();

        $response = $this->actingAs($stranger)
            ->patchJson("/api/lists/{$this->list->uuid}/items/{$this->item->uuid}/toggle");

        $response->assertStatus(403);
    });

    it('denies toggle for item from different list', function () {
        $otherList = CustomList::factory()->create(['owner_uuid' => $this->owner->uuid]);

        $response = $this->actingAs($this->owner)
            ->patchJson("/api/lists/{$otherList->uuid}/items/{$this->item->uuid}/toggle");

        $response->assertStatus(403);
    });

    it('requires authentication', function () {
        $response = $this->patchJson("/api/lists/{$this->list->uuid}/items/{$this->item->uuid}/toggle");

        $response->assertStatus(401);
    });
});
