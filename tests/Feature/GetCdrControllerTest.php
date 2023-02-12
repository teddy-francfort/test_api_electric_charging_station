<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Resources\CdrResource;
use App\Models\Cdr;
use App\Models\Operator;
use Database\Factories\CdrFactory;
use Database\Factories\OperatorFactory;
use Tests\TestCase;

final class GetCdrControllerTest extends TestCase
{
    /**
     * Route: GET /ocpi/cdrs/{cdr:ref}.
     */
    public function testRequestWithoutAuthorisationHeaderReturns401(): void
    {
        // arrange
        $cdr = CdrFactory::new()->create();

        // act
        $response = $this->getJson(route('ocpi.cdrs.show', ['cdr' => $cdr->ref]));

        // assert
        $response->assertStatus(401)
            ->assertExactJson([]);
    }

    /**
     * Route: GET /ocpi/cdrs/{cdr:ref}.
     */
    public function testRequestWithNonExistingAuthorisationHeaderReturns401(): void
    {
        // arrange
        $cdr = CdrFactory::new()->create();
        $invalidToken = 'token_invalid_123';

        // act
        $response = $this->withToken($invalidToken)->getJson(route('ocpi.cdrs.show', ['cdr' => $cdr->ref]));

        // assert
        $this->assertDatabaseMissing(Operator::class, ['access_token' => $invalidToken]);
        $response->assertStatus(401)
            ->assertExactJson([]);
    }

    /**
     * Route: GET /ocpi/cdrs/{cdr:ref}.
     */
    public function testRequestWithUnexistingEvseRefReturns404(): void
    {
        // arrange
        $cdr = CdrFactory::new()->create();
        $token = $cdr->evse->operator->access_token;
        $unknownRef = 'UNKNOWN-REF';

        // act
        $response = $this->withToken($token)->getJson(route('ocpi.cdrs.show', ['cdr' => $unknownRef]));

        // assert
        $this->assertDatabaseMissing(Cdr::class, ['ref' => $unknownRef]);
        $response->assertStatus(404)
            ->assertExactJson([]);
    }

    /**
     * Route: GET /ocpi/cdrs/{cdr:ref}.
     */
    public function testRequestWithDifferentOperatorReturns404(): void
    {
        // arrange
        $cdr = CdrFactory::new()->create();
        $otherOperator = OperatorFactory::new()->create();

        // act
        $response = $this->withToken($otherOperator->access_token)
            ->getJson(route('ocpi.cdrs.show', ['cdr' => $cdr->ref]));

        // assert
        static::assertNotSame($cdr->evse->operator_id, $otherOperator->id);
        $response->assertStatus(404)
            ->assertExactJson([]);
    }

    /**
     * Route: GET /ocpi/cdrs/{cdr:ref}.
     */
    public function testRequestWithValidOperatorTokenIsAllowed(): void
    {
        // arrange
        $cdr = CdrFactory::new()->create();
        $token = $cdr->evse->operator->access_token;

        // act
        $response = $this->withToken($token)->getJson(route('ocpi.cdrs.show', ['cdr' => $cdr->ref]));

        // assert
        $response->assertStatus(200)
            ->assertExactJson(CdrResource::make($cdr)->jsonSerialize());
    }
}
