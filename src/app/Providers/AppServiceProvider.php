<?php

namespace App\Providers;

use App\Payments\DepositGateway;
use App\Payments\DepositPayments;
use App\Payments\FakeDepositGateway;
use App\Payments\PayPayGateway;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(DepositPayments::class, fn () => new DepositPayments($this->depositGateway()));
    }

    /**
     * The gateway for order deposits, or null when deposits are off.
     */
    private function depositGateway(): ?DepositGateway
    {
        return match (config('payment.driver')) {
            null, '' => null,
            'fake' => $this->app->isProduction()
                ? throw new RuntimeException('The fake payment driver cannot be used in production.')
                : new FakeDepositGateway,
            'paypay' => new PayPayGateway(
                config('payment.paypay.api_key'),
                config('payment.paypay.api_secret'),
                config('payment.paypay.merchant_id'),
                config('payment.paypay.production'),
            ),
            default => throw new RuntimeException('Unknown payment driver: '.config('payment.driver')),
        };
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        if ($hotFile = config('app.vite_hot_file')) {
            Vite::useHotFile($hotFile);
        }
    }
}
