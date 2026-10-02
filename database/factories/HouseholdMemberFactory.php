<?php

namespace Database\Factories;

use App\Models\Household;
use App\Models\HouseholdMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HouseholdMember>
 */
class HouseholdMemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(['L', 'P']);

        return [
            'household_id' => Household::factory(),
            'nik' => fake()->numerify('3201##############'),
            'name' => fake()->name($gender === 'L' ? 'male' : 'female'),
            'gender' => $gender,
            'birth_place' => fake()->city(),
            'birth_date' => fake()->date('Y-m-d', '-18 years'),
            'religion' => 'Islam',
            'education' => 'SMA / Sederajat',
            'job' => fake()->jobTitle(),
            'marital_status' => 'Kawin',
            'family_relation' => 'Kepala Keluarga',
            'occupancy_status' => 'pemilik',
            'phone' => fake()->phoneNumber(),
        ];
    }
}
