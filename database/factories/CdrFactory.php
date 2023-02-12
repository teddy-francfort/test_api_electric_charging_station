<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Cdr;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cdr>
 */
final class CdrFactory extends Factory
{
    public function make($attributes = [], ?Model $parent = null): Cdr
    {
        /** @var Cdr $cdr */
        $cdr = parent::make($attributes, $parent);

        return $cdr;
    }

    public function create($attributes = [], ?Model $parent = null): Cdr
    {
        /** @var Cdr $cdr */
        $cdr = parent::create($attributes, $parent);

        return $cdr;
    }

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
            'total_energy' => fake()->numberBetween(1000, 40000) / 1000,
            'total_cost' => fake()->numberBetween(500, 5000) / 100,
            'evse_id' => EvseFactory::new(),
        ];
    }
}
