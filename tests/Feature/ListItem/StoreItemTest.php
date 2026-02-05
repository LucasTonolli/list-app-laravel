<?php

use App\Models\CustomList;
use App\Models\User;

describe('List Item Store', function (): void {

    beforeEach(function () {
        $this->owner = User::factory()->create();
        $this->list = CustomList::factory()->create(['owner_uuid' => $this->owner->uuid]);
    });

    it('allows the owner to add an item', function () {
        $response = $this->actingAs($this->owner)
            ->postJson("/api/lists/{$this->list->uuid}/items", [
                'name' => 'Buy milk',
                'description' => 'From the grocery store'
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'item' => ['uuid', 'name', 'description', 'completed', 'version']
            ])
            ->assertJsonPath('item.name', 'Buy milk')
            ->assertJsonPath('item.completed', false)
            ->assertJsonPath('item.version', 1);

        $this->assertDatabaseHas('list_items', [
            'name' => 'Buy milk',
            'custom_list_uuid' => $this->list->uuid
        ]);
    });

    it('allows a shared editor to add an item', function () {
        $editor = User::factory()->create();
        $this->list->sharedWith()->attach($editor->uuid, ['role' => 'editor']);

        $response = $this->actingAs($editor)
            ->postJson("/api/lists/{$this->list->uuid}/items", [
                'name' => 'Editor item'
            ]);

        $response->assertStatus(201);
    });

    it('denies access to non-shared users', function () {
        $stranger = User::factory()->create();

        $response = $this->actingAs($stranger)
            ->postJson("/api/lists/{$this->list->uuid}/items", [
                'name' => 'Hacked item'
            ]);

        $response->assertStatus(403);
    });

    it('requires name to be provided', function () {
        $response = $this->actingAs($this->owner)
            ->postJson("/api/lists/{$this->list->uuid}/items", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    it('rejects name longer than 100 characters', function () {
        $longName = str_repeat('a', 101);

        $response = $this->actingAs($this->owner)
            ->postJson("/api/lists/{$this->list->uuid}/items", [
                'name' => $longName
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    it('rejects description longer than 1000 characters', function () {
        $longDescription = str_repeat('a', 1001);

        $response = $this->actingAs($this->owner)
            ->postJson("/api/lists/{$this->list->uuid}/items", [
                'name' => 'Valid name',
                'description' => $longDescription
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['description']);
    });

    it('allows item without description', function () {
        $response = $this->actingAs($this->owner)
            ->postJson("/api/lists/{$this->list->uuid}/items", [
                'name' => 'Item without description'
            ]);

        $response->assertStatus(201);
    });

    it('requires authentication', function () {
        $response = $this->postJson("/api/lists/{$this->list->uuid}/items", [
            'name' => 'Test'
        ]);

        $response->assertStatus(401);
    });

});
