<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FundingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Penetapan pendanaan untuk satu usulan (maksimal satu baris per usulan).
 */
class FundingDecision extends Model
{
    protected $fillable = [
        'proposal_id',
        'status_danai',
        'jumlah_dana',
        'sk_pendanaan',
        'file_sk',
        'decided_by',
    ];

    protected function casts(): array
    {
        return [
            'status_danai' => FundingStatus::class,
            'jumlah_dana' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Proposal, self> */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    /** @return BelongsTo<User, self> */
    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
