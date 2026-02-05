<?php

use App\Models\CustomList;
use App\Models\User;

describe('List Show', function (): void {

    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->list = CustomList::factory()->create(['owner_uuid' => $this->user->uuid]);
    });

    it('returns list details for the owner', function () {
        $response = $this->actingAs($this->user)
            ->getJson("/api/lists/{$this->list->uuid}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'list' => ['uuid', 'title', 'items', 'shared_with_count', 'items_count']
            ])
            ->assertJsonPath('list.uuid', $this->list->uuid);
    });

    it('returns list with items count', function () {
        $this->list->items()->create([
            'name' => 'Item 1',
            'custom_list_uuid' => $this->list->uuid
        ]);
        $this->list->items()->create([
            'name' => 'Item 2',
            'custom_list_uuid' => $this->list->uuid
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/lists/{$this->list->uuid}");

        $response->assertStatus(200)
            ->assertJsonPath('list.items_count', 2);
    });

    it('allows shared user to view the list', function () {
        $editor = User::factory()->create();
        $this->list->sharedWith()->attach($editor->uuid, ['role' => 'editor']);

        $response = $this->actingAs($editor)
            ->getJson("/api/lists/{$this->list->uuid}");

        $response->assertStatus(200);
    });

    it('denies access to non-shared users', function () {
        $stranger = User::factory()->create();

        $response = $this->actingAs($stranger)
            ->getJson("/api/lists/{$this->list->uuid}");

        $response->assertStatus(403);
    });

    it('returns 404 for non-existent list', function () {
        $fakeUuid = '00000000-0000-0000-0000-000000000000';

        $response = $this->actingAs($this->user)
            ->getJson("/api/lists/{$fakeUuid}");

        $response->assertStatus(404);
    });

    it('requires authentication', function () {
        $response = $this->getJson("/api/lists/{$this->list->uuid}");

        $response->assertStatus(401);
    });
});
