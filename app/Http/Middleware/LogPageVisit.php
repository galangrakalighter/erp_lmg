<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogService;
use App\Services\ActivityPageDescription;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogPageVisit
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
        private readonly ActivityPageDescription $pageDescription,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldLog($request, $response)) {
            $routeName = $request->route()?->getName();

            $this->activityLog->record([
                'action' => 'VISIT',
                'module' => $this->moduleFromRequest($request),
                'description' => $this->pageDescription->describe($request),
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
        } elseif ($this->shouldLogMutation($request, $response) && ! $this->activityLog->hasRecordedActivity()) {
            $mutation = $this->pageDescription->describeMutation($request);
            $input = $request->except([
                '_token', '_method', 'password', 'password_confirmation', 'remember_token',
                'token', 'access_token', 'refresh_token', 'api_key', 'secret',
            ]);

            $this->activityLog->record([
                'action' => $mutation['action'],
                'module' => $this->moduleFromRequest($request),
                'description' => $mutation['description'],
                'target_name' => $mutation['target_name'],
                'target_type' => 'request',
                'context' => [
                    'route_name' => $request->route()?->getName(),
                    'path' => '/'.ltrim($request->path(), '/'),
                ],
                'old_data' => [],
                'new_data' => $input,
            ]);
        }

        return $response;
    }

    private function shouldLogMutation(Request $request, Response $response): bool
    {
        return $request->user() !== null
            && in_array(strtoupper($request->method()), ['POST', 'PUT', 'PATCH', 'DELETE'], true)
            && $response->getStatusCode() < 400;
    }

    private function shouldLog(Request $request, Response $response): bool
    {
        return $request->user() !== null
            && $request->isMethod('GET')
            && ! $request->ajax()
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
