<?php

use App\Models\CustomList;
use App\Models\ListInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Log;

describe('List Invitation Accept', function (): void {

    beforeEach(function () {
        $this->owner = User::factory()->create();
        $this->list = CustomList::factory()->create(['owner_uuid' => $this->owner->uuid]);
        $this->invitation = $this->list->invitations()->create([
            'custom_list_uuid' => $this->list->uuid,
            'token' => 'test-token-accept',
            'max_uses' => 5,
            'uses' => 0,
            'expires_at' => now()->addMinutes(5)
        ]);
    });

    it('allows a user to accept a valid invitation', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/lists/{$this->list->uuid}/invitations/{$this->invitation->token}/accept");
        Log::info($response->json());
        $response->assertStatus(200)
            ->assertJsonPath('accepted', true);

        $this->assertDatabaseHas('custom_list_user', [
            'custom_list_uuid' => $this->list->uuid,
            'user_uuid' => $user->uuid,
            'role' => 'editor'
        ]);
    });

    it('increments invitation uses count', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson("/api/lists/{$this->list->uuid}/invitations/{$this->invitation->token}/accept");

        $this->assertDatabaseHas('list_invitations', [
            'uuid' => $this->invitation->uuid,
            'uses' => 2
        ]);
    });

    it('prevents owner from accepting their own invitation', function () {
        $response = $this->actingAs($this->owner)
            ->postJson("/api/lists/{$this->list->uuid}/invitations/{$this->invitation->token}/accept");

        $response->assertStatus(409);
    });

    it('prevents accepting an expired invitation', function () {
        $this->invitation->update(['expires_at' => now()->subHour()]);
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/lists/{$this->list->uuid}/invitations/{$this->invitation->token}/accept");

        $response->assertStatus(409);
    });

    it('prevents accepting when max uses reached', function () {
        $this->invitation->update(['max_uses' => 1, 'uses' => 1]);
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/lists/{$this->list->uuid}/invitations/{$this->invitation->token}/accept");

        $response->assertStatus(409);
    });

    it('prevents user from accepting same invitation twice', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson("/api/lists/{$this->list->uuid}/invitations/{$this->invitation->token}/accept");

        $response = $this->actingAs($user)
            ->postJson("/api/lists/{$this->list->uuid}/invitations/{$this->invitation->token}/accept");

        $response->assertStatus(409);
    });

    it('allows multiple users to accept the same invitation', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $response1 = $this->actingAs($user1)
            ->postJson("/api/lists/{$this->list->uuid}/invitations/{$this->invitation->token}/accept");

        $response2 = $this->actingAs($user2)
            ->postJson("/api/lists/{$this->list->uuid}/invitations/{$this->invitation->token}/accept");

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        $this->assertDatabaseHas('custom_list_user', ['user_uuid' => $user1->uuid]);
        $this->assertDatabaseHas('custom_list_user', ['user_uuid' => $user2->uuid]);
    });

    it('returns 404 for invalid token', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/lists/{$this->list->uuid}/invitations/invalid-token/accept");

        $response->assertStatus(404);
    });

    it('requires authentication', function () {
        $response = $this->postJson("/api/lists/{$this->list->uuid}/invitations/{$this->invitation->token}/accept");

        $response->assertStatus(401);
    });
});
