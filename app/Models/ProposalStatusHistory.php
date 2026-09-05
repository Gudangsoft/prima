<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProposalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu baris jejak audit setiap kali status usulan berubah. Append-only:
 * tidak pernah di-update, hanya punya created_at.
 */
class ProposalStatusHistory extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'proposal_status_histories';

    protected $fillable = [
        'proposal_id',
        'changed_by',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProposalStatus::class,
            'created_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Proposal, self> */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    /** @return BelongsTo<User, self> */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
