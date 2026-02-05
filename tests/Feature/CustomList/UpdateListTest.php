<?php

use App\Models\CustomList;
use App\Models\User;

describe('List Update', function (): void {

    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->list = CustomList::factory()->create([
            'owner_uuid' => $this->user->uuid,
            'title' => 'Original Title'
        ]);
    });

    it('allows the owner to update the title', function () {
        $response = $this->actingAs($this->user)
            ->patchJson("/api/lists/{$this->list->uuid}", [
                'title' => 'Updated Title'
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('list.title', 'Updated Title');

        $this->assertDatabaseHas('custom_lists', [
            'uuid' => $this->list->uuid,
            'title' => 'Updated Title'
        ]);
    });

    it('denies update to non-owner users', function () {
        $stranger = User::factory()->create();

        $response = $this->actingAs($stranger)
            ->patchJson("/api/lists/{$this->list->uuid}", [
                'title' => 'Hacked Title'
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('custom_lists', [
            'uuid' => $this->list->uuid,
            'title' => 'Original Title'
        ]);
    });

    it('denies update to shared editors', function () {
        $editor = User::factory()->create();
        $this->list->sharedWith()->attach($editor->uuid, ['role' => 'editor']);

        $response = $this->actingAs($editor)
            ->patchJson("/api/lists/{$this->list->uuid}", [
                'title' => 'Editor Title'
            ]);

        $response->assertStatus(403);
    });

    it('requires title to be provided', function () {
        $response = $this->actingAs($this->user)
            ->patchJson("/api/lists/{$this->list->uuid}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    it('rejects title longer than 40 characters', function () {
        $longTitle = str_repeat('x', 41);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/lists/{$this->list->uuid}", [
                'title' => $longTitle
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    it('requires authentication', function () {
        $response = $this->patchJson("/api/lists/{$this->list->uuid}", [
            'title' => 'Test'
        ]);

        $response->assertStatus(401);
    });

});
