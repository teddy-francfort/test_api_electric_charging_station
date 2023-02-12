<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

final class OcpiLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     */
    public function handle(Request $request, \Closure $next): Response|JsonResponse|RedirectResponse
    {
        Log::channel('ocpi')->info('', [
            'url' => $request->url(),
            'headers' => $request->headers->all(),
            'body' => $request->getContent(),
        ]);

        $response = $next($request);

        Log::channel('ocpi')->info('', [
            'http_status' => $response->status(),
            'headers' => $response->headers->all(),
            'body' => $response->getContent(),
        ]);

        return $response;
    }
}
