<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProposalStatus;
use App\Observers\ProposalObserver;
use Database\Factories\ProposalFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
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
        'judul',
        'abstrak',
        'file_proposal',
        'status',
        'tahun_anggaran',
    ];

    /** Status awal setiap usulan baru. */
    protected $attributes = [
        'status' => ProposalStatus::Draft->value,
    ];

    protected function casts(): array
    {
        return [
            'status' => ProposalStatus::class,
            'tahun_anggaran' => 'integer',
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
