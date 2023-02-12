<?php

declare(strict_types=1);

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/** @implements  CastsAttributes<string,int> */
final class TotalCostCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param int|null                            $value
     * @param array<string, mixed>                $attributes
     */
    public function get($model, string $key, $value, array $attributes): null|string
    {
        if (null === $value) {
            return null;
        }

        return number_format(round($value / 100, 2), 2, '.', '');
    }

    /**
     * Prepare the given value for storage.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string|null                         $value
     * @param array<string, mixed>                $attributes
     */
    public function set($model, string $key, $value, array $attributes): null|int
    {
        if (null === $value) {
            return null;
        }

        return (int) ((float) $value * 100);
    }
}
