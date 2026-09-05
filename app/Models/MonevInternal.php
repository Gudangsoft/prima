<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MonevRekomendasi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Hasil monitoring & evaluasi internal PT atas satu usulan yang berjalan.
 */
class MonevInternal extends Model
{
    protected $table = 'monev_internal';

    protected $fillable = [
        'proposal_id',
        'penilai_id',
        'tanggal_monev',
        'skor_capaian',
        'catatan',
        'rekomendasi',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_monev' => 'date',
            'skor_capaian' => 'integer',
            'rekomendasi' => MonevRekomendasi::class,
        ];
    }

    /** @return BelongsTo<Proposal, self> */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    /** @return BelongsTo<User, self> */
    public function penilai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penilai_id');
    }
}
