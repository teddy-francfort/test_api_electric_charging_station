<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Operator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Response as FacadeResponse;

final class AuthWithOperatorToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, \Closure $next): Response|JsonResponse|RedirectResponse
    {
        $accessToken = $request->bearerToken();

        if (null === $accessToken) {
            abort(FacadeResponse::json([], 401));
        }

        /** @var Operator|null $operator */
        $operator = Operator::query()
            ->whereAccessTokenIs($accessToken)
            ->first();

        if (null === $operator) {
            abort(FacadeResponse::json([], 401));
        }

        /** @var Route $route */
        $route = $request->route();
        $route->setParameter('operatorRequesting', $operator);

        return $next($request);
    }
}
