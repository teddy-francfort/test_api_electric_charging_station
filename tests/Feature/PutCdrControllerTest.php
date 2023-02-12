<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Resources\CdrResource;
use App\Models\Cdr;
use App\Models\Evse;
use App\Models\Operator;
use Carbon\Carbon;
use Database\Factories\CdrFactory;
use Database\Factories\OperatorFactory;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PutCdrControllerTest extends TestCase
{
    /**
     * Route: PUT /ocpi/cdrs.
     */
    public function testRequestWithoutAuthorisationHeaderReturns401(): void
    {
        // arrange
        $cdr = CdrFactory::new()->make();
        $cdrResource = new CdrResource($cdr);
        $data = $cdrResource->jsonSerialize();

        // act
        $this->assertDatabaseEmpty(Cdr::class);
        $response = $this->putJson(route('ocpi.cdrs.store'), $data);

        // assert
        $response->assertStatus(401)
            ->assertExactJson([]);
        $this->assertDatabaseEmpty(Cdr::class);
    }

    /**
     * Route: PUT /ocpi/cdrs.
     */
    public function testRequestWithNonExistingAuthorisationHeaderReturns401(): void
    {
        // arrange
        $cdr = CdrFactory::new()->make();
        $cdrResource = new CdrResource($cdr);
        $data = $cdrResource->jsonSerialize();
        $invalidToken = 'token_invalid_123';

        // act
        $this->assertDatabaseEmpty(Cdr::class);
        $response = $this->withToken($invalidToken)->putJson(route('ocpi.cdrs.store'), $data);

        // assert
        $this->assertDatabaseMissing(Operator::class, ['access_token' => $invalidToken]);
        $response->assertStatus(401)
            ->assertExactJson([]);
        $this->assertDatabaseEmpty(Cdr::class);
    }

    /**
     * Route: PUT /ocpi/cdrs.
     */
    public function testRequestWithUnexistingEvseRefReturns404(): void
    {
        // arrange
        $cdr = CdrFactory::new()->make();
        $cdrResource = new CdrResource($cdr);
        $data = $cdrResource->jsonSerialize();
        $data['evse_uid'] = 'UNKNOWN-EVSE';

        $token = $cdr->evse->operator->access_token;

        // act
        $this->assertDatabaseEmpty(Cdr::class);
        $response = $this->withToken($token)->putJson(route('ocpi.cdrs.store'), $data);

        // assert
        $this->assertDatabaseMissing(Evse::class, ['access_token' => $data['evse_uid']]);
        $response->assertStatus(404)
            ->assertExactJson([]);
        $this->assertDatabaseEmpty(Cdr::class);
    }

    /**
     * Route: PUT /ocpi/cdrs.
     */
    public function testRequestWithDifferentOperatorReturns404(): void
    {
        // arrange
        $cdr = CdrFactory::new()->make();
        $cdrResource = new CdrResource($cdr);
        $data = $cdrResource->jsonSerialize();
        $otherOperator = OperatorFactory::new()->create();

        // act
        $this->assertDatabaseEmpty(Cdr::class);
        $response = $this->withToken($otherOperator->access_token)->putJson(route('ocpi.cdrs.store'), $data);

        // assert
        $this->assertDatabaseMissing(Evse::class, ['access_token' => $data['evse_uid']]);
        $response->assertStatus(404)
            ->assertExactJson([]);
        $this->assertDatabaseEmpty(Cdr::class);
    }

    /**
     * Route: PUT /ocpi/cdrs.
     */
    public function testRequestWithValidOperatorTokenIsAllowed(): void
    {
        // arrange
        $cdr = CdrFactory::new()->make();
        $cdrResource = new CdrResource($cdr);
        $data = $cdrResource->jsonSerialize();
        $token = $cdr->evse->operator->access_token;

        // act
        $this->assertDatabaseEmpty(Cdr::class);
        $response = $this->withToken($token)->putJson(route('ocpi.cdrs.store'), $data);

        // assert
        $response->assertStatus(200)
            ->assertExactJson([]);
        $this->assertDatabaseCount(Cdr::class, 1);

        $dateTimeFormatExpected = 'Y-m-d H:i:s';
        /** @var string $dateFormatJson */
        $dateFormatJson = config('ocpi.date_format');
        /** @var Carbon $startDatetime */
        $startDatetime = Carbon::createFromFormat($dateFormatJson, $data['start_datetime']);
        /** @var Carbon $endDatetime */
        $endDatetime = Carbon::createFromFormat($dateFormatJson, $data['end_datetime']);
        $this->assertDatabaseHas(Cdr::class, [
            'ref' => $data['id'],
            'start_datetime' => $startDatetime->format($dateTimeFormatExpected),
            'end_datetime' => $endDatetime->format($dateTimeFormatExpected),
            'total_energy' => (float) $data['total_energy'] * 1000,
            'total_cost' => (float) $data['total_cost'] * 100,
            'evse_id' => $cdr->evse->id,
        ]);
    }

    /**
     * Route: PUT /ocpi/cdrs.
     *
     * @param array<int,mixed> $testingValues
     *
     * @dataProvider incomingRequestDataProvider
     */
    public function testRequestFailWithInvalidIncomingData(string $key, array $testingValues): void
    {
        // arrange
        $cdr = CdrFactory::new()->make();
        $cdrResource = new CdrResource($cdr);
        $data = $cdrResource->jsonSerialize();
        $token = $cdr->evse->operator->access_token;

        foreach ($testingValues as $testingValue) {
            $data[$key] = $testingValue;
            // act
            $this->assertDatabaseEmpty(Cdr::class);
            $response = $this->withToken($token)->putJson(route('ocpi.cdrs.store'), $data);

            // assert
            $response->assertBadRequest()->assertJsonValidationErrors([$key]);
            $this->assertDatabaseEmpty(Cdr::class);
        }
    }

    /**
     * @return array<int, array{0: string, 1:array<int,mixed>}>
     */
    public function incomingRequestDataProvider(): array
    {
        return [
            ['id', [null, '', 123, Str::random(37)]],
            ['evse_uid', [null, '', 123, Str::random(37)]],
            ['start_datetime', [null, '', 123, '25/08/2023 15:10:06']],
            ['end_datetime', [null, '', 123, '25/08/2023 15:10:06']],
            ['total_energy', [null, '', 12.3456]],
            ['total_cost', [null, '', '12.346']],
        ];
    }
}
