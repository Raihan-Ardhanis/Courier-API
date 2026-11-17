<?php

namespace Tests\Feature\Controller;

use App\Models\Courier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourierControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_paginates_in_name_order_by_default_and_can_sort_by_registration_date(): void
    {
        Courier::factory()->create([
            'name' => 'Charlie',
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);
        Courier::factory()->create([
            'name' => 'Bob',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);
        Courier::factory()->create([
            'name' => 'Alice',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $this->getJson('/api/couriers?per_page=2')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Alice')
            ->assertJsonPath('data.1.name', 'Bob')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.per_page', 2);

        $this->getJson('/api/couriers?sort=registered_at&per_page=3')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Alice')
            ->assertJsonPath('data.1.name', 'Bob')
            ->assertJsonPath('data.2.name', 'Charlie');
    }

    public function test_index_searches_each_name_term_and_filters_by_level(): void
    {
        Courier::factory()->create([
            'name' => 'Budiono Hadi Agung',
            'level' => 2,
        ]);
        Courier::factory()->create([
            'name' => 'Budi Rahman Agung',
            'level' => 3,
        ]);
        Courier::factory()->create([
            'name' => 'Budi Agung Santoso',
            'level' => 4,
        ]);

        $this->getJson('/api/couriers?search=budi+agung&level=2,3')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Budi Rahman Agung')
            ->assertJsonPath('data.1.name', 'Budiono Hadi Agung');
    }

    public function test_index_can_sort_by_level_or_name_in_both_directions(): void
    {
        Courier::factory()->create(['name' => 'Charlie', 'level' => 2]);
        Courier::factory()->create(['name' => 'Alice', 'level' => 5]);
        Courier::factory()->create(['name' => 'Bob', 'level' => 1]);

        $this->getJson('/api/couriers?sort=level&direction=asc&per_page=3')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Bob')
            ->assertJsonPath('data.1.name', 'Charlie')
            ->assertJsonPath('data.2.name', 'Alice');

        $this->getJson('/api/couriers?sort=level&direction=desc&per_page=3')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Alice')
            ->assertJsonPath('data.1.name', 'Charlie')
            ->assertJsonPath('data.2.name', 'Bob');

        $this->getJson('/api/couriers?sort=name&direction=desc&per_page=3')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Charlie')
            ->assertJsonPath('data.1.name', 'Bob')
            ->assertJsonPath('data.2.name', 'Alice');
    }

    public function test_store_validates_and_persists_a_courier(): void
    {
        $payload = [
            'name' => 'Budi Agung',
            'phone' => '081234567890',
            'email' => 'budi@example.com',
            'address' => 'Jakarta',
            'vehicle_type' => 'Motorcycle',
            'plate_number' => 'B 1234 XYZ',
            'level' => 3,
            'is_active' => true,
        ];

        $this->withToken($this->authenticatedToken())
            ->postJson('/api/couriers', $payload)
            ->assertCreated()
            ->assertJsonPath('data.name', 'Budi Agung')
            ->assertJsonPath('data.level', 3);

        $this->assertDatabaseHas('couriers', [
            'name' => 'Budi Agung',
            'phone' => '081234567890',
            'email' => 'budi@example.com',
            'plate_number' => 'B 1234 XYZ',
            'level' => 3,
        ]);
    }

    public function test_store_rejects_invalid_level_and_duplicate_phone(): void
    {
        Courier::factory()->create(['phone' => '081234567890']);

        $this->withToken($this->authenticatedToken())
            ->postJson('/api/couriers', [
                'name' => 'New Courier',
                'phone' => '081234567890',
                'level' => 6,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone', 'level']);
    }

    public function test_show_returns_the_courier_data(): void
    {
        $courier = Courier::factory()->create([
            'name' => 'Budi Agung',
            'level' => 4,
            'is_active' => true,
        ]);

        $this->getJson("/api/couriers/{$courier->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $courier->id)
            ->assertJsonPath('data.name', 'Budi Agung')
            ->assertJsonPath('data.phone', $courier->phone)
            ->assertJsonPath('data.level', 4)
            ->assertJsonPath('data.is_active', true);
    }

    public function test_update_validates_and_persists_changes_without_rejecting_own_unique_values(): void
    {
        $courier = Courier::factory()->create([
            'phone' => '081234567890',
            'email' => 'budi@example.com',
            'level' => 2,
        ]);

        $token = $this->authenticatedToken();

        $this->withToken($token)
            ->patchJson("/api/couriers/{$courier->id}", [
                'phone' => '081234567890',
                'email' => 'budi@example.com',
                'plate_number' => 'B 1234 ABC',
                'level' => 3,
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.level', 3)
            ->assertJsonPath('data.plate_number', 'B 1234 ABC')
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('couriers', [
            'id' => $courier->id,
            'phone' => '081234567890',
            'email' => 'budi@example.com',
            'plate_number' => 'B 1234 ABC',
            'level' => 3,
            'is_active' => false,
        ]);

        $this->withToken($token)
            ->patchJson("/api/couriers/{$courier->id}", ['level' => 0])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['level']);
    }

    public function test_destroy_removes_the_courier(): void
    {
        $courier = Courier::factory()->create();

        $this->withToken($this->authenticatedToken())
            ->deleteJson("/api/couriers/{$courier->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('couriers', ['id' => $courier->id]);
    }

    private function authenticatedToken(): string
    {
        return User::factory()->create()
            ->createToken('courier-controller-test')
            ->plainTextToken;
    }
}
