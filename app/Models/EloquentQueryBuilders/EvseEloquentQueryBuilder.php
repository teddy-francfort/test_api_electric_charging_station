<?php

declare(strict_types=1);

namespace App\Models\EloquentQueryBuilders;

use Illuminate\Database\Eloquent\Builder;

final class EvseEloquentQueryBuilder extends Builder
{
    public function whereRefIs(string $ref): self
    {
        return $this->where('ref', '=', $ref);
    }
}
