<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class OAuthDiscoveryController extends Controller
{
    public function protectedResource(): JsonResponse
    {
        return response()->json([
            'resource' => config('marketing-mcp.resource'),
            'authorization_servers' => [config('marketing-mcp.issuer')],
            'bearer_methods_supported' => ['header'],
            'scopes_supported' => ['marketing:read', 'offline_access'],
        ]);
    }

    public function authorizationServer(): JsonResponse
    {
        $issuer = rtrim((string) config('marketing-mcp.issuer'), '/');

        return response()->json([
            'issuer' => $issuer,
            'authorization_endpoint' => $issuer.'/oauth/authorize',
            'token_endpoint' => $issuer.'/oauth/token',
            'response_types_supported' => ['code'],
            'grant_types_supported' => ['authorization_code', 'refresh_token'],
            'token_endpoint_auth_methods_supported' => ['none'],
            'code_challenge_methods_supported' => ['S256'],
            'scopes_supported' => ['marketing:read', 'offline_access'],
        ]);
    }
}
