<?php

namespace Tests\Feature\Controller;

use App\Models\Courier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_issues_a_token_for_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'secret-password',
        ]);

        $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'secret-password',
        ])
            ->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'secret-password',
        ]);

        $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ])->assertUnauthorized();
    }

    public function test_courier_reads_are_public_but_writes_require_a_valid_token(): void
    {
        $courier = Courier::factory()->create();

        $this->getJson('/api/couriers')->assertOk();
        $this->getJson("/api/couriers/{$courier->id}")->assertOk();

        $payload = [
            'name' => 'New Courier',
            'phone' => '081234567890',
            'level' => 3,
        ];

        $this->postJson('/api/couriers', $payload)->assertUnauthorized();
        $this->patchJson("/api/couriers/{$courier->id}", ['level' => 4])
            ->assertUnauthorized();
        $this->deleteJson("/api/couriers/{$courier->id}")->assertUnauthorized();

        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'secret-password',
        ]);

        $login = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'secret-password',
        ])->assertOk();

        $token = $login->json('token');

        $created = $this->withToken($token)
            ->postJson('/api/couriers', $payload)
            ->assertCreated();

        $createdId = $created->json('data.id');

        $this->withToken($token)
            ->patchJson("/api/couriers/{$createdId}", ['level' => 4])
            ->assertOk()
            ->assertJsonPath('data.level', 4);

        $this->withToken($token)
            ->deleteJson("/api/couriers/{$createdId}")
            ->assertNoContent();
    }

    public function test_logout_revokes_the_current_token(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'secret-password',
        ]);

        $login = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'secret-password',
        ])->assertOk();

        $token = $login->json('token');

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertNoContent();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
