<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\EloquentQueryBuilders\OperatorEloquentQueryBuilder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property positive-int $id
 * @property string       $name
 * @property string       $access_token
 * @property Carbon|null  $created_at
 * @property Carbon|null  $updated_at
 */
final class Operator extends Model
{
    public function newEloquentBuilder($query): OperatorEloquentQueryBuilder
    {
        return new OperatorEloquentQueryBuilder($query);
    }

    public static function query(): OperatorEloquentQueryBuilder
    {
        /** @var OperatorEloquentQueryBuilder $builder */
        $builder = parent::query();

        return $builder;
    }
}
