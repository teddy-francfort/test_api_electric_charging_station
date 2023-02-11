<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Operator;
use Database\Factories\EvseFactory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

final class EvseSeeder extends Seeder
{
    public function run(): void
    {
        Operator::query()->get()->each(function ($operator, int|string $key) {
            /** @var Operator $operator */
            return EvseFactory::new()
                ->for($operator)
                ->count(3)
                ->sequence(fn (Sequence $sequence) => [
                    'ref' => 'EVSE'.(string) $key.($sequence->index + 1),
                ])
                ->create();
        });
    }
}
