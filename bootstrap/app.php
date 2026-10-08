<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
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
        $middleware->appendToGroup('web', \App\Http\Middleware\EnsureAccountIsUnlocked::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\LogPageVisit::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\ProtectMultimediaSharedModules::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\ProtectDigitalMarketingModules::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\ProtectClientManagement::class);
        $middleware->alias([
            'multimedia.client' => \App\Http\Middleware\EnsureMultimediaClientAccess::class,
            'digital-marketing.client' => \App\Http\Middleware\EnsureDigitalMarketingClientAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $exception, Request $request) {
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
