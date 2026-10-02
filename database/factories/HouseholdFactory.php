<?php

namespace Database\Factories;

use App\Models\Household;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Household>
 */
class HouseholdFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => strtoupper(fake()->bothify('?#-#')),
            'head_name' => fake()->name('male'),
            'occupancy_status' => fake()->randomElement(['pemilik', 'kontrak']),
            'kk_number' => fake()->numerify('3201##############'),
            'phone' => fake()->phoneNumber(),
            'is_active' => true,
            'note' => null,
        ];
    }
}
