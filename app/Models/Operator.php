<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int         $id
 * @property string      $name
 * @property string      $access_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class Operator extends Model
{
}
