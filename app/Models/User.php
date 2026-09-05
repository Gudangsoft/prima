<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Role as RoleEnum;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nidn',
        'phone_number',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'otp_expires_at' => 'datetime',
            'otp_verified_at' => 'datetime',
            'otp_last_sent_at' => 'datetime',
            'otp_attempts' => 'integer',
        ];
    }

    /**
     * Siapa yang boleh masuk panel: setiap user yang punya minimal satu role
     * yang dikenal aplikasi. Gerbang OTP ditangani middleware terpisah.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(RoleEnum::values());
    }

    public function isDosen(): bool
    {
        return $this->hasRole(RoleEnum::Dosen->value);
    }

    public function isAdminLppm(): bool
    {
        return $this->hasRole(RoleEnum::AdminLppm->value);
    }

    public function isReviewer(): bool
    {
        return $this->hasRole(RoleEnum::Reviewer->value);
    }

    public function isPimpinan(): bool
    {
        return $this->hasRole(RoleEnum::Pimpinan->value);
    }

    /** Boleh melakukan persetujuan institusi (gerbang tunggal: admin LPPM atau pimpinan). */
    public function canApproveInstitution(): bool
    {
        return $this->hasAnyRole(RoleEnum::institutionalApprovers());
    }

    public function hasCompletedOtpProfile(): bool
    {
        return filled($this->phone_number);
    }

    /** Hanya super admin yang boleh menyamar (impersonate) pengguna lain. */
    public function canImpersonate(): bool
    {
        return $this->hasRole(RoleEnum::SuperAdmin->value);
    }

    /** Super admin tidak boleh disamar. */
    public function canBeImpersonated(): bool
    {
        return ! $this->hasRole(RoleEnum::SuperAdmin->value);
    }
}
