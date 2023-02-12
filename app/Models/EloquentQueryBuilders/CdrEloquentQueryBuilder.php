<?php

declare(strict_types=1);

namespace App\Models\EloquentQueryBuilders;

use App\Models\Operator;
use Illuminate\Database\Eloquent\Builder;

final class CdrEloquentQueryBuilder extends Builder
{
    public function whereOperatorIs(Operator $operator): self
    {
        /** @var self $qb */
        $qb = $this->whereRelation('evse', 'operator_id', '=', $operator->id);

        return $qb;
    }
}
