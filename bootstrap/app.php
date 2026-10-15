<?php

use App\Http\Middleware\EnsureAccountIsUnlocked;
use App\Http\Middleware\EnsureDigitalMarketingClientAccess;
use App\Http\Middleware\EnsureMultimediaClientAccess;
use App\Http\Middleware\LogPageVisit;
use App\Http\Middleware\ProtectClientManagement;
use App\Http\Middleware\ProtectDigitalMarketingModules;
use App\Http\Middleware\ProtectMultimediaSharedModules;
use App\Support\SensitiveData;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'oauth/threads/deauthorize',
            'oauth/threads/data-deletion',
        ]);
        $middleware->appendToGroup('web', EnsureAccountIsUnlocked::class);
        $middleware->appendToGroup('web', LogPageVisit::class);
        $middleware->appendToGroup('web', ProtectMultimediaSharedModules::class);
        $middleware->appendToGroup('web', ProtectDigitalMarketingModules::class);
        $middleware->appendToGroup('web', ProtectClientManagement::class);
        $middleware->alias([
            'multimedia.client' => EnsureMultimediaClientAccess::class,
            'digital-marketing.client' => EnsureDigitalMarketingClientAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $exception, Request $request) {
            $isMarketingMcp = $request->is('api/marketing-mcp', 'api/marketing-mcp/*')
                || $request->is('api/oauth-marketing-mcp', 'api/oauth-marketing-mcp/*');

            if ($isMarketingMcp) {
                $status = match (true) {
                    $exception instanceof AuthenticationException => 401,
                    $exception instanceof AuthorizationException => 403,
                    $exception instanceof ValidationException => 422,
                    $exception instanceof HttpResponseException => $exception->getResponse()->getStatusCode(),
                    $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
                    default => 500,
                };
                $code = match ($status) {
                    401 => 'unauthenticated',
                    403 => 'forbidden',
                    404 => 'not_found',
                    405 => 'method_not_allowed',
                    419 => 'session_expired',
                    422 => 'validation_failed',
                    423 => 'account_locked',
                    429 => 'rate_limited',
                    default => $status >= 500 ? 'server_error' : 'request_failed',
                };
                $message = match ($status) {
                    401 => 'Token autentikasi tidak valid atau sudah berakhir.',
                    403 => 'Permintaan tidak diizinkan.',
                    404 => 'Resource tidak ditemukan.',
                    405 => 'Metode HTTP tidak diizinkan. Endpoint ini hanya mendukung operasi baca.',
                    422 => 'Parameter permintaan tidak valid.',
                    423 => 'Akun tidak aktif atau terkunci.',
                    429 => 'Batas permintaan terlampaui. Coba lagi nanti.',
                    default => $status >= 500
                        ? 'Terjadi kendala saat memproses permintaan.'
                        : 'Permintaan tidak dapat diproses.',
                };
                $requestId = (string) ($request->attributes->get('marketing_mcp_request_id') ?: Str::uuid());
                $error = ['code' => $code, 'message' => $message];

                if ($exception instanceof ValidationException) {
                    $error['details'] = SensitiveData::redactKeys($exception->errors());
                }

                return response()->json([
                    'error' => $error,
                    'meta' => ['request_id' => $requestId],
                ], $status)->withHeaders([
                    'Cache-Control' => 'no-store, private',
                    'Pragma' => 'no-cache',
                    'X-Content-Type-Options' => 'nosniff',
                    'X-Request-Id' => $requestId,
                ]);
            }

            // Respons API tetap berbentuk JSON agar integrasi tidak rusak.
            if ($request->expectsJson()) {
                return null;
            }

            // Biarkan alur login, validasi formulir, dan respons redirect ditangani Laravel.
            if ($exception instanceof AuthenticationException
                || $exception instanceof ValidationException
                || $exception instanceof HttpResponseException) {
                return null;
            }

            $status = match (true) {
                $exception instanceof AuthorizationException => 403,
                $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
                default => 500,
            };

            if ($status < 400) {
                return null;
            }

            if (view()->exists("errors.{$status}")) {
                return response()->view("errors.{$status}", [], $status);
            }

            $content = match (true) {
                $status === 401 => [
                    'title' => 'Silakan login kembali',
                    'message' => 'Sesi login tidak ditemukan atau sudah berakhir. Login kembali untuk melanjutkan.',
                    'showLogin' => true,
                ],
                $status === 405 => [
                    'title' => 'Tindakan tidak dapat dilakukan',
                    'message' => 'Halaman menerima cara akses yang tidak sesuai. Kembali ke halaman sebelumnya, lalu ulangi tindakan Anda.',
                ],
                $status >= 500 => [
                    'title' => 'Terjadi kendala pada sistem',
                    'message' => 'Permintaan Anda belum dapat diproses. Coba muat ulang halaman; bila masih gagal, hubungi admin.',
                ],
                default => [
                    'title' => 'Permintaan belum dapat diproses',
                    'message' => 'Periksa kembali data atau halaman yang Anda buka, lalu coba lagi.',
                ],
            };

            return response()->view('errors.friendly', $content, $status);
        });
    })->create();
