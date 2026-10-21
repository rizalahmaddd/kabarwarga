<?php

namespace Database\Factories;

use App\Models\Household;
use App\Models\Letter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Letter>
 */
class LetterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => Letter::newCode(),
            'household_id' => Household::factory(),
            'type' => 'domisili',
            'purpose' => 'Membuka rekening bank',
            'person' => [
                'name' => fake()->name(),
                'nik' => fake()->numerify('3201############'),
                'gender' => 'L',
                'birth_place' => fake()->city(),
                'birth_date' => fake()->date('Y-m-d', '-20 years'),
                'religion' => 'Islam',
                'job' => 'Karyawan Swasta',
                'marital_status' => 'Kawin',
            ],
            'status' => Letter::PENDING,
        ];
    }
}
