<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Berita / pengumuman yang tampil di dasbor dan halaman publik.
 */
class Announcement extends Model
{
    protected $fillable = [
        'judul',
        'isi',
        'tanggal_terbit',
        'lampiran_pdf',
        'disematkan',
        'terbit',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_terbit' => 'date',
            'disematkan' => 'boolean',
            'terbit' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, self> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @param  Builder<self>  $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('terbit', true)
            ->where('tanggal_terbit', '<=', now())
            ->orderByDesc('disematkan')
            ->orderByDesc('tanggal_terbit');
    }

    public function lampiranUrl(): ?string
    {
        return $this->lampiran_pdf ? Storage::disk('public')->url($this->lampiran_pdf) : null;
    }
}
