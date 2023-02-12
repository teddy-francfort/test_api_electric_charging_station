<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\TotalCostCast;
use App\Casts\TotalEnergyCast;
use App\Models\EloquentQueryBuilders\CdrEloquentQueryBuilder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property positive-int $id
 * @property string       $ref
 * @property Carbon       $start_datetime
 * @property Carbon       $end_datetime
 * @property float|null   $total_energy
 * @property string|null  $total_cost
 * @property positive-int $evse_id
 * @property Carbon|null  $created_at
 * @property Carbon|null  $updated_at
 * @property Evse         $evse
 */
final class Cdr extends Model
{
    /** @var array<string,string> */
    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'total_energy' => TotalEnergyCast::class,
        'total_cost' => TotalCostCast::class,
    ];

    public function newEloquentBuilder($query)
    {
        return new CdrEloquentQueryBuilder($query);
    }

    public static function query(): CdrEloquentQueryBuilder
    {
        /** @var CdrEloquentQueryBuilder $builder */
        $builder = parent::query();

        return $builder;
    }

    public function evse(): BelongsTo
    {
        return $this->belongsTo(Evse::class);
    }
}
