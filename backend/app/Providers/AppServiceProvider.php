<?php

namespace App\Providers;

use App\Domain\Contracts\MedicineRepository;
use App\Domain\Contracts\PurchaseRepository;
use App\Domain\Contracts\SaleRepository;
use App\Domain\Contracts\SupplierRepository;
use App\Domain\Contracts\UserRepository;
use App\Infrastructure\Persistence\EloquentMedicineRepository;
use App\Infrastructure\Persistence\EloquentPurchaseRepository;
use App\Infrastructure\Persistence\EloquentSaleRepository;
use App\Infrastructure\Persistence\EloquentSupplierRepository;
use App\Infrastructure\Persistence\EloquentUserRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MedicineRepository::class, EloquentMedicineRepository::class);
        $this->app->bind(PurchaseRepository::class, EloquentPurchaseRepository::class);
        $this->app->bind(SupplierRepository::class, EloquentSupplierRepository::class);
        $this->app->bind(SaleRepository::class, EloquentSaleRepository::class);
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
    }

    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            $email = Str::lower((string) $request->input('email'));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });
    }
}
