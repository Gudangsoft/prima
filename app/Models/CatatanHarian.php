<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Catatan harian (logbook) pelaksanaan kegiatan yang diisi dosen.
 */
class CatatanHarian extends Model
{
    protected $table = 'catatan_harian';

    protected $fillable = [
        'proposal_id',
        'tanggal',
        'kegiatan',
        'capaian',
        'persentase',
        'berkas',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'persentase' => 'integer',
        ];
    }

    /** @return BelongsTo<Proposal, self> */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    /** @return BelongsTo<User, self> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function berkasUrl(): ?string
    {
        return $this->berkas ? Storage::disk('public')->url($this->berkas) : null;
    }
}
