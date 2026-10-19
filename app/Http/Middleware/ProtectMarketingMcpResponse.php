<?php

namespace App\Http\Middleware;

use App\Support\SensitiveData;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ProtectMarketingMcpResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(in_array($request->method(), ['GET', 'HEAD'], true), 405);

        $requestId = (string) Str::uuid();
        $request->attributes->set('marketing_mcp_request_id', $requestId);

        $response = $next($request);

        if ($response instanceof JsonResponse) {
            $response->setData(SensitiveData::redactKeys($response->getData(true)));
        }

        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }
}
