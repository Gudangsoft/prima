<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ApprovalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Keputusan persetujuan institusi (gerbang tunggal): dibuat oleh Admin LPPM
 * atau Pimpinan atas usulan berstatus "submitted".
 */
class ProposalApproval extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'proposal_id',
        'approved_by',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApprovalStatus::class,
            'created_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Proposal, self> */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    /** @return BelongsTo<User, self> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
