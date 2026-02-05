<?php

use App\Models\User;

describe('List Store', function (): void {

    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    it('creates a new list with valid title', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/lists', ['title' => 'Minha Lista']);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'list' => ['uuid', 'title']
            ])
            ->assertJsonPath('list.title', 'Minha Lista');

        $this->assertDatabaseHas('custom_lists', [
            'title' => 'Minha Lista',
            'owner_uuid' => $this->user->uuid
        ]);
    });

    it('requires a title', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/lists', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    it('requires title to be a string', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/lists', ['title' => 12345]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    it('rejects title longer than 40 characters', function () {
        $longTitle = str_repeat('a', 41);

        $response = $this->actingAs($this->user)
            ->postJson('/api/lists', ['title' => $longTitle]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    it('accepts title with exactly 40 characters', function () {
        $title = str_repeat('a', 40);

        $response = $this->actingAs($this->user)
            ->postJson('/api/lists', ['title' => $title]);

        $response->assertStatus(201);
    });

    it('requires authentication', function () {
        $response = $this->postJson('/api/lists', ['title' => 'Test']);

        $response->assertStatus(401);
    });

});
