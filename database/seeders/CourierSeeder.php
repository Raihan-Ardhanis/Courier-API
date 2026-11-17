<?php

namespace Database\Seeders;

use App\Models\Courier;
use Illuminate\Database\Seeder;

class CourierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $missingCouriers = max(0, 20 - Courier::query()->count());

        Courier::factory()->count($missingCouriers)->create();
    }
}
