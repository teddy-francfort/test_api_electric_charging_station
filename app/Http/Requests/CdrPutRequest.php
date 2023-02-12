<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Cdr;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

final class CdrPutRequest extends FormRequest
{
    /**
     * Handle a failed validation attempt.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator): void
    {
        $validationException = new ValidationException($validator);
        $validationException->status(Response::HTTP_BAD_REQUEST);
        throw $validationException->errorBag($this->errorBag)->redirectTo($this->getRedirectUrl());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var string $dateFormat */
        $dateFormat = config('ocpi.date_format');

        return [
            'id' => ['required', 'string', 'max:36'],
            'evse_uid' => ['required', 'string', 'max:36'],
            'start_datetime' => ['required', 'date_format:'.$dateFormat],
            'end_datetime' => ['required', 'date_format:'.$dateFormat],
            'total_energy' => ['required', 'decimal:0,3'],
            'total_cost' => ['required', 'string', 'decimal:0,2'],
        ];
    }

    public function getCdr(): Cdr
    {
        $cdr = new Cdr();
        $cdr->ref = $this->string('id')->toString();
        /** @var string $dateFormat */
        $dateFormat = config('ocpi.date_format');
        /** @var Carbon $startDateTime */
        $startDateTime = Carbon::createFromFormat($dateFormat, $this->string('start_datetime')->toString());
        $cdr->start_datetime = $startDateTime;
        /** @var Carbon $endDateTime */
        $endDateTime = Carbon::createFromFormat($dateFormat, $this->string('end_datetime')->toString());
        $cdr->end_datetime = $endDateTime;
        $cdr->total_energy = $this->float('total_energy');
        $cdr->total_cost = $this->string('total_cost')->toString();

        return $cdr;
    }
}
