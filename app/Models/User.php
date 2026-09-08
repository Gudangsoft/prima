<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Role as RoleEnum;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar
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
        'nuptk',
        'nidn',
        'sinta_id',
        'sinta_score_overall_v2',
        'sinta_score_3yr_v2',
        'sinta_score_overall_v3',
        'sinta_score_3yr_v3',
        'scopus_id',
        'scopus_h_index',
        'scopus_articles',
        'scopus_citation',
        'wos_score',
        'kompetensi',
        'phone_number',
        'telepon',
        'avatar_path',
        'jabatan',
        'pendidikan_terakhir',
        'unit_kerja',
        'bio',
        'program_studi_id',
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
            'sinta_score_overall_v2' => 'float',
            'sinta_score_3yr_v2' => 'float',
            'sinta_score_overall_v3' => 'float',
            'sinta_score_3yr_v3' => 'float',
            'scopus_h_index' => 'integer',
            'scopus_articles' => 'integer',
            'scopus_citation' => 'integer',
            'wos_score' => 'integer',
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

    /** Foto profil untuk topbar & tempat lain (fallback ke inisial otomatis). */
    public function getFilamentAvatarUrl(): ?string
    {
        return filled($this->avatar_path)
            ? Storage::disk('public')->url($this->avatar_path)
            : null;
    }

    /** Usulan yang diajukan user ini (sebagai dosen). @return HasMany<Proposal> */
    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    /** @return BelongsTo<ProgramStudi, self> */
    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    /** Penugasan penilaian yang diterima user ini (sebagai reviewer). @return HasMany<ProposalReview> */
    public function reviewAssignments(): HasMany
    {
        return $this->hasMany(ProposalReview::class, 'reviewer_id');
    }

    /** Undangan sebagai anggota tim usulan orang lain. @return HasMany<ProposalMember> */
    public function memberInvitations(): HasMany
    {
        return $this->hasMany(ProposalMember::class);
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
