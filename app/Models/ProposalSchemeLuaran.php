<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu baris target luaran pada skema (Wajib atau Tambahan/opsional).
 * Dikelola Admin LPPM lewat form Skema Usulan; ditampilkan ke dosen sebagai
 * syarat/anjuran luaran saat memilih skema.
 */
class ProposalSchemeLuaran extends Model
{
    protected $fillable = [
        'proposal_scheme_id',
        'jenis_luaran',
        'wajib',
        'keterangan',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'wajib' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    /** @return BelongsTo<ProposalScheme, self> */
    public function scheme(): BelongsTo
    {
        return $this->belongsTo(ProposalScheme::class, 'proposal_scheme_id');
    }
}
