<?php

declare(strict_types=1);

namespace Database\Factories;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cdr>
 */
final class CdrFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = CarbonImmutable::now();

        return [
            'ref' => 'CDR-'.fake()->randomNumber(5),
            'start_datetime' => $date,
            'end_datetime' => $date->addHours(2),
            'total_energy' => fake()->numberBetween(1000, 40000),
            'total_cost' => fake()->numberBetween(5000, 50000),
            'evse_id' => EvseFactory::new(),
        ];
    }
}
