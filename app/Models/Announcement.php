<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AnnouncementType;
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
        'jenis',
        'isi',
        'tanggal_terbit',
        'lampiran_pdf',
        'gambar_sampul',
        'disematkan',
        'terbit',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'jenis' => AnnouncementType::class,
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

    /** @param  Builder<self>  $query */
    public function scopeJenis(Builder $query, AnnouncementType $jenis): void
    {
        $query->where('jenis', $jenis);
    }

    public function lampiranUrl(): ?string
    {
        return $this->lampiran_pdf ? Storage::disk('public')->url($this->lampiran_pdf) : null;
    }

    public function gambarSampulUrl(): ?string
    {
        return $this->gambar_sampul ? Storage::disk('public')->url($this->gambar_sampul) : null;
    }
}
