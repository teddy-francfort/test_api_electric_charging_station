<?php

declare(strict_types=1);

namespace App\Models\EloquentQueryBuilders;

use Illuminate\Database\Eloquent\Builder;

final class OperatorEloquentQueryBuilder extends Builder
{
    public function whereAccessTokenIs(string $accessToken): self
    {
        return $this->where('access_token', '=', $accessToken);
    }
}
