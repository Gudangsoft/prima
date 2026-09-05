<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\Role as RoleEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Tangkap kesalahan atribut saat dev, tanpa mengganggu eager-loading Filament.
        Model::preventSilentlyDiscardingAttributes(! app()->isProduction());

        // Super admin melewati semua pemeriksaan policy/permission.
        Gate::before(function ($user, string $ability) {
            return $user->hasRole(RoleEnum::SuperAdmin->value) ? true : null;
        });

        // Listener didaftarkan otomatis oleh Laravel 12 dari folder app/Listeners
        // berdasarkan type-hint argumen handle():
        //   - ResetOtpVerificationOnLogin  <- Illuminate\Auth\Events\Login
        //   - SendProposalStatusNotification <- App\Events\ProposalStatusChanged
    }
}
