<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\EloquentQueryBuilders\EvseEloquentQueryBuilder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property positive-int $id
 * @property string       $ref
 * @property string       $address
 * @property int          $operator_id
 * @property Carbon|null  $created_at
 * @property Carbon|null  $updated_at
 * @property Operator     $operator
 */
final class Evse extends Model
{
    public static function query(): EvseEloquentQueryBuilder
    {
        /** @var EvseEloquentQueryBuilder $builder */
        $builder = parent::query();

        return $builder;
    }

    public function newEloquentBuilder($query): EvseEloquentQueryBuilder
    {
        return new EvseEloquentQueryBuilder($query);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }
}
