<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BidangFokus;
use App\Enums\MemberApprovalStatus;
use App\Enums\MemberType;
use App\Enums\ProposalStatus;
use App\Observers\ProposalObserver;
use Database\Factories\ProposalFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Usulan penelitian / pengabdian yang diajukan seorang dosen.
 *
 * Perubahan `status` selalu lewat App\Actions\Proposal\ChangeProposalStatus
 * agar tervalidasi state machine-nya; ProposalObserver mencatat tiap transisi
 * ke proposal_status_histories secara otomatis.
 */
#[ObservedBy(ProposalObserver::class)]
class Proposal extends Model
{
    /** @use HasFactory<ProposalFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'scheme_id',
        'kelompok_skema',
        'bidang_fokus',
        'tema',
        'topik',
        'rumpun_ilmu',
        'target_tkt',
        'lama_kegiatan',
        'makro_riset',
        'judul',
        'abstrak',
        'file_proposal',
        'substansi_file',
        'status',
        'tahun_anggaran',
        'tahun_usulan',
    ];

    /** Status awal setiap usulan baru. */
    protected $attributes = [
        'status' => ProposalStatus::Draft->value,
    ];

    protected function casts(): array
    {
        return [
            'status' => ProposalStatus::class,
            'bidang_fokus' => BidangFokus::class,
            'tahun_anggaran' => 'integer',
            'tahun_usulan' => 'integer',
            'target_tkt' => 'integer',
            'lama_kegiatan' => 'integer',
        ];
    }

    /**
     * Konteks transisi yang dititipkan ke observer (tidak disimpan ke kolom).
     * Diisi oleh ChangeProposalStatus sebelum save().
     */
    public ?string $transitionNote = null;

    public ?int $transitionActorId = null;

    /** @return BelongsTo<User, self> */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<ProposalScheme, self> */
    public function scheme(): BelongsTo
    {
        return $this->belongsTo(ProposalScheme::class, 'scheme_id');
    }

    /** @return HasMany<ProposalStatusHistory> */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(ProposalStatusHistory::class)->latest('created_at');
    }

    /** @return HasMany<ProposalApproval> */
    public function approvals(): HasMany
    {
        return $this->hasMany(ProposalApproval::class)->latest('created_at');
    }

    /** @return HasMany<ProposalReview> */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProposalReview::class);
    }

    /** @return HasOne<FundingDecision> */
    public function fundingDecision(): HasOne
    {
        return $this->hasOne(FundingDecision::class);
    }

    /** @return HasMany<MonitoringReport> */
    public function monitoringReports(): HasMany
    {
        return $this->hasMany(MonitoringReport::class)->latest('tanggal_submit');
    }

    /** @return HasMany<Output> */
    public function outputs(): HasMany
    {
        return $this->hasMany(Output::class)->latest();
    }

    /** @return HasMany<CatatanHarian> */
    public function catatanHarian(): HasMany
    {
        return $this->hasMany(CatatanHarian::class)->latest('tanggal');
    }

    /** @return HasMany<ProposalMember> */
    public function members(): HasMany
    {
        return $this->hasMany(ProposalMember::class);
    }

    /** @return HasMany<ProposalOutputTarget> */
    public function outputTargets(): HasMany
    {
        return $this->hasMany(ProposalOutputTarget::class)->orderBy('tahun_ke');
    }

    /** @return HasMany<ProposalStrategicField> */
    public function strategicFields(): HasMany
    {
        return $this->hasMany(ProposalStrategicField::class);
    }

    /** @return HasMany<ProposalRabItem> */
    public function rabItems(): HasMany
    {
        return $this->hasMany(ProposalRabItem::class)->orderBy('tahun_ke');
    }

    /** @return HasMany<ProposalPartner> */
    public function partners(): HasMany
    {
        return $this->hasMany(ProposalPartner::class);
    }

    /** Total RAB seluruh tahun. */
    protected function totalRab(): Attribute
    {
        return Attribute::get(fn (): float => (float) $this->rabItems
            ->sum(fn (ProposalRabItem $i): float => $i->total));
    }

    /** Semua anggota dosen sudah menyetujui keikutsertaannya. */
    public function allDosenMembersApproved(): bool
    {
        return $this->members()
            ->where('jenis', MemberType::Dosen->value)
            ->where('status', '!=', MemberApprovalStatus::Menyetujui->value)
            ->doesntExist();
    }

    /** @return HasOne<MonevInternal> */
    public function monevInternal(): HasOne
    {
        return $this->hasOne(MonevInternal::class);
    }

    public function isAssignedReviewer(User $user): bool
    {
        return $this->reviews()->where('reviewer_id', $user->getKey())->exists();
    }

    public function canTransitionTo(ProposalStatus $target): bool
    {
        return $this->status->canTransitionTo($target);
    }

    /** @param Builder<self> $query */
    public function scopeOwnedBy(Builder $query, User $user): void
    {
        $query->where('user_id', $user->getKey());
    }

    /** @param Builder<self> $query */
    public function scopeStatus(Builder $query, ProposalStatus|string $status): void
    {
        $query->where('status', $status instanceof ProposalStatus ? $status->value : $status);
    }

    /** @param Builder<self> $query */
    public function scopeForYear(Builder $query, int $year): void
    {
        $query->where('tahun_anggaran', $year);
    }
}
