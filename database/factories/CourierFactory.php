<?php

namespace Database\Factories;

use App\Models\Courier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Courier>
 */
class CourierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->unique()->numerify('08##########'),
            'email' => fake()->unique()->safeEmail(),
            'address' => fake()->address(),
            'vehicle_type' => fake()->randomElement(['Motorcycle', 'Car', 'Van']),
            'plate_number' => strtoupper(fake()->unique()->bothify('?? #### ??')),
            'level' => fake()->numberBetween(1, 5),
            'is_active' => fake()->boolean(90),
        ];
    }
}
