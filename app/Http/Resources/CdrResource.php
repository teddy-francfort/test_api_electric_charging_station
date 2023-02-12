<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Cdr;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CdrResource extends JsonResource
{
    /** @var Cdr */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->ref,
            'evse_uid' => $this->resource->evse->ref,
            'start_datetime' => $this->resource->start_datetime->toIso8601ZuluString(),
            'end_datetime' => $this->resource->end_datetime->toIso8601ZuluString(),
            'total_energy' => $this->resource->total_energy,
            'total_cost' => $this->resource->total_cost,
        ];
    }
}
