<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\Role as RoleEnum;
use App\Policies\RolePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role as SpatieRole;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Super admin melewati semua pemeriksaan policy/permission.
        Gate::before(function ($user, string $ability) {
            return $user->hasRole(RoleEnum::SuperAdmin->value) ? true : null;
        });

        // Model peran (Spatie) bukan di App\Models -> daftarkan policy manual.
        Gate::policy(SpatieRole::class, RolePolicy::class);

        // Listener didaftarkan otomatis oleh Laravel 12 dari folder app/Listeners
        // berdasarkan type-hint argumen handle():
        //   - ResetOtpVerificationOnLogin  <- Illuminate\Auth\Events\Login
        //   - SendProposalStatusNotification <- App\Events\ProposalStatusChanged
    }
}
