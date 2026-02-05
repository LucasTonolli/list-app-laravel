<?php

describe('Identity Registration', function (): void {

    it('creates an anonymous user and returns a token', function () {
        $response = $this->postJson('/api/identities');

        $response->assertStatus(200)
            ->assertJsonStructure(['token']);

        expect($response->json('token'))->toBeString()->not->toBeEmpty();

        $this->assertDatabaseCount('users', 1);
    });

    it('creates unique users for each registration', function () {
        $response1 = $this->postJson('/api/identities');
        $response2 = $this->postJson('/api/identities');

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        expect($response1->json('token'))->not->toBe($response2->json('token'));

        $this->assertDatabaseCount('users', 2);
    });

});
