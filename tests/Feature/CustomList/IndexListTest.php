<?php

use App\Models\CustomList;
use App\Models\User;
use Illuminate\Support\Facades\Log;

describe('List Index', function (): void {

    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    it('returns all lists owned by the user', function () {
        CustomList::factory()->count(3)->create(['owner_uuid' => $this->user->uuid]);

        $response = $this->actingAs($this->user)->getJson('/api/lists');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'lists')
            ->assertJsonStructure([
                'lists' => [
                    '*' => ['uuid', 'title']
                ]
            ]);
    });

    it('returns lists shared with the user', function () {
        $owner = User::factory()->create();
        $sharedList = CustomList::factory()->create(['owner_uuid' => $owner->uuid]);
        $sharedList->sharedWith()->attach($this->user->uuid, ['role' => 'editor']);

        $response = $this->actingAs($this->user)->getJson('/api/lists');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'lists');
    });

    it('returns both owned and shared lists', function () {
        CustomList::factory()->count(2)->create(['owner_uuid' => $this->user->uuid]);

        $owner = User::factory()->create();
        $sharedList = CustomList::factory()->create(['owner_uuid' => $owner->uuid]);
        $sharedList->sharedWith()->attach($this->user->uuid, ['role' => 'editor']);

        $response = $this->actingAs($this->user)->getJson('/api/lists');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'lists');
    });

    it('does not return lists from other users', function () {
        $otherUser = User::factory()->create();
        CustomList::factory()->count(2)->create(['owner_uuid' => $otherUser->uuid]);
        CustomList::factory()->create(['owner_uuid' => $this->user->uuid]);

        $response = $this->actingAs($this->user)->getJson('/api/lists');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'lists');
    });

    it('requires authentication', function () {
        $response = $this->getJson('/api/lists');

        $response->assertStatus(401);
    });
});
