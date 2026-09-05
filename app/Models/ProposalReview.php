<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReviewRecommendation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Penugasan + hasil penilaian seorang reviewer atas satu usulan.
 * Baris dibuat saat penugasan (skor/rekomendasi null), diisi saat dinilai.
 */
class ProposalReview extends Model
{
    protected $fillable = [
        'proposal_id',
        'reviewer_id',
        'skor',
        'rekomendasi',
        'catatan',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'skor' => 'integer',
            'rekomendasi' => ReviewRecommendation::class,
            'submitted_at' => 'datetime',
        ];
    }

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }

    /** @return BelongsTo<Proposal, self> */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    /** @return BelongsTo<User, self> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
