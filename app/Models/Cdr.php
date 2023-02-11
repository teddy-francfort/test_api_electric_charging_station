<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property positive-int      $id
 * @property string            $ref
 * @property Carbon            $start_datetime
 * @property Carbon            $end_datetime
 * @property int|null          $total_energy
 * @property positive-int|null $total_cost
 * @property positive-int      $evse_id
 * @property Carbon|null       $created_at
 * @property Carbon|null       $updated_at
 * @property Evse              $evse
 */
final class Cdr extends Model
{
    /** @var array<string,string> */
    protected $casts = [
        'start_datetime' => 'timestamp',
        'end_datetime' => 'timestamp',
    ];

    public function evse(): BelongsTo
    {
        return $this->belongsTo(Evse::class);
    }
}
