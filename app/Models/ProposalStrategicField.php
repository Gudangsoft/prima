<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Entri "8 Bidang Strategis" pada usulan. */
class ProposalStrategicField extends Model
{
    protected $fillable = [
        'proposal_id',
        'bidang',
        'rumusan_masalah',
        'uraian_kegiatan',
    ];

    /** @return BelongsTo<Proposal, self> */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }
}
