<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Evse>
 */
final class EvseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ref' => 'EVSE-'.fake()->randomNumber(5),
            'address' => fake()->randomNumber(3).' rue de '.fake()->lastName(),
            'operator_id' => OperatorFactory::new(),
        ];
    }
}
