<?php

namespace App\Http\Middleware;

use App\Support\SensitiveData;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class ProtectMarketingMcpCrudResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(in_array($request->method(), ['POST', 'PATCH'], true), 405);

        $requestId = (string) Str::uuid();
        $request->attributes->set('marketing_mcp_request_id', $requestId);
        $response = $next($request);

        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);
            $confirmationToken = $request->routeIs('api.oauth-marketing-crud.*.confirmation')
                ? data_get($data, 'data.confirmation_token')
                : null;
            $data = SensitiveData::redactKeys($data);
            if (is_string($confirmationToken) && strlen($confirmationToken) === 64) {
                data_set($data, 'data.confirmation_token', $confirmationToken);
            }
            $response->setData($data);
        }

        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }
}
