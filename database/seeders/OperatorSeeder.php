<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Factories\OperatorFactory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

final class OperatorSeeder extends Seeder
{
    public function run(): void
    {
        OperatorFactory::new()
            ->count(10)
            ->sequence(fn (Sequence $sequence) => [
                'name' => 'Operator '.($sequence->index + 1),
                'access_token' => 'token'.($sequence->index + 1),
                ])
            ->create();
    }
}
