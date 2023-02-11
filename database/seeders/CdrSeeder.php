<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Evse;
use Carbon\CarbonImmutable;
use Database\Factories\CdrFactory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

final class CdrSeeder extends Seeder
{
    public function run(): void
    {
        Evse::query()->get()->each(function ($evse, int|string $key) {
            /** @var Evse $evse */
            return CdrFactory::new()
                ->for($evse)
                ->count(5)
                ->sequence(function (Sequence $sequence) use ($key) {
                    $date = CarbonImmutable::now()->addDays($sequence->index);

                    return [
                    'ref' => 'CDR'.(string) $key.($sequence->index + 1),
                    'start_datetime' => $date,
                    'end_datetime' => $date->addHours(2),
                ];
                })
                ->create();
        });
    }
}
