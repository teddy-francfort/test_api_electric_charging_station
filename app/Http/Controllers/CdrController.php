<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CdrPutRequest;
use App\Http\Resources\CdrResource;
use App\Models\Cdr;
use App\Models\Evse;
use App\Models\Operator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as ResponseHttp;

final class CdrController extends Controller
{
    /**
     * Store a newly created cdr in storage.
     *
     * Route: PUT /ocpi/cdrs
     */
    public function store(CdrPutRequest $request): JsonResponse
    {
        $cdr = $request->getCdr();

        $refEvse = $request->string('evse_uid');
        /** @var Evse|null $evse */
        $evse = Evse::query()
            ->whereRefIs($refEvse->toString())
            ->first();

        if (null === $evse) {
            abort(Response::json([], 404));
        }

        /** @var Route $route */
        $route = $request->route();
        /** @var Operator $operatorRequesting */
        $operatorRequesting = $route->parameter('operatorRequesting');
        if ($evse->operator->isNot($operatorRequesting)) {
            abort(Response::json([], 404));
        }

        $cdr->evse_id = $evse->id;

        $cdr->save();

        return Response::json([], ResponseHttp::HTTP_OK);
    }

    /**
     * Route: GET /ocpi/cdrs/{cdr:ref}.
     */
    public function show(Request $request, Cdr $cdr): CdrResource|JsonResponse
    {
        /** @var Route $route */
        $route = $request->route();
        /** @var Operator $operatorRequesting */
        $operatorRequesting = $route->parameter('operatorRequesting');
        $cdr = Cdr::query()->whereOperatorIs($operatorRequesting)->first();
        if (null === $cdr) {
            abort(Response::json([], 404));
        }

        return new CdrResource($cdr);
    }
}
