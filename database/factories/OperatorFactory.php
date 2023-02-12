<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Operator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Operator>
 */
final class OperatorFactory extends Factory
{
    public function create($attributes = [], ?Model $parent = null): Operator
    {
        /** @var Operator $operator */
        $operator = parent::create($attributes, $parent);

        return $operator;
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Operator test',
            'access_token' => Str::random(64),
        ];
    }
}
