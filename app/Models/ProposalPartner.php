<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Mitra usulan. */
class ProposalPartner extends Model
{
    protected $fillable = [
        'proposal_id',
        'nama_mitra',
        'institusi',
        'alamat',
        'negara',
        'surel',
        'surat_kesanggupan',
        'dana',
    ];

    protected function casts(): array
    {
        return ['dana' => 'decimal:2'];
    }

    /** @return BelongsTo<Proposal, self> */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }
}
