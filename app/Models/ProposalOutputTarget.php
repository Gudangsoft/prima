<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Target luaran usulan per urutan tahun. */
class ProposalOutputTarget extends Model
{
    protected $fillable = [
        'proposal_id',
        'tahun_ke',
        'kelompok_luaran',
        'jenis_luaran',
        'target',
        'keterangan',
    ];

    protected function casts(): array
    {
        return ['tahun_ke' => 'integer'];
    }

    /** @return BelongsTo<Proposal, self> */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }
}
