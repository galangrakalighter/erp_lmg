<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogPageVisit
{
    public function __construct(private readonly ActivityLogService $activityLog) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldLog($request, $response)) {
            $routeName = $request->route()?->getName();

            $this->activityLog->record([
                'action' => 'VISIT',
                'module' => $this->moduleFromRequest($request),
                'description' => 'Mengunjungi halaman '.($routeName ?: '/'.ltrim($request->path(), '/')),
                'target_name' => $routeName ?: $request->path(),
                'target_type' => 'page',
                'old_data' => [],
                'new_data' => [
                    'method' => $request->method(),
                    'path' => '/'.ltrim($request->path(), '/'),
                    'route_name' => $routeName,
                    'status_code' => $response->getStatusCode(),
                ],
            ]);
        }

        return $response;
    }

    private function shouldLog(Request $request, Response $response): bool
    {
        return $request->user() !== null
            && $request->isMethod('GET')
            && ! $request->expectsJson()
            && ! $request->is('up')
            && $response->getStatusCode() < 400;
    }

    private function moduleFromRequest(Request $request): string
    {
        $routeName = $request->route()?->getName();

        if (is_string($routeName) && str_contains($routeName, '.')) {
            return explode('.', $routeName)[0];
        }

        return $request->segment(1) ?: 'dashboard';
    }
}
