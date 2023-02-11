<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int         $id
 * @property string      $ref
 * @property string      $address
 * @property int         $operator_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Operator    $operator
 */
final class Evse extends Model
{
    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }
}
