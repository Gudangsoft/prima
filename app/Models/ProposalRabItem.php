<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Satu baris Rancangan Anggaran Biaya. */
class ProposalRabItem extends Model
{
    protected $fillable = [
        'proposal_id',
        'tahun_ke',
        'kelompok',
        'komponen',
        'item',
        'satuan',
        'harga_satuan',
        'volume',
    ];

    protected function casts(): array
    {
        return [
            'tahun_ke' => 'integer',
            'harga_satuan' => 'decimal:2',
            'volume' => 'decimal:2',
        ];
    }

    /** @return Attribute<float, never> */
    protected function total(): Attribute
    {
        return Attribute::get(fn (): float => (float) $this->harga_satuan * (float) $this->volume);
    }

    /** @return BelongsTo<Proposal, self> */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }
}
