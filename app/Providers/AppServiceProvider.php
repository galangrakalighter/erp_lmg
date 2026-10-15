<?php

namespace App\Providers;

use App\Models\AuditTrail;
use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ActivityLogService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Passport::tokensCan([
            'marketing:read' => 'Membaca data marketing ERP yang dapat diakses pengguna.',
            'offline_access' => 'Mempertahankan koneksi dengan refresh token tanpa meminta login berulang.',
        ]);
        Passport::tokensExpireIn(now()->addHour());
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::authorizationView('oauth.authorize');

        foreach (['created', 'updated', 'deleted'] as $event) {
            Event::listen("eloquent.{$event}: *", function (string $eventName, array $payload): void {
                $model = $payload[0] ?? null;

                if ($model instanceof Model && ! $model instanceof AuditTrail) {
                    app(ActivityLogService::class)->recordModelEvent($eventName, $model);
                }
            });
        }

        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }
    }
}
