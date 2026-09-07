<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Slide banner di header (hero) halaman publik.
 */
class HeroSlide extends Model
{
    protected $fillable = [
        'gambar',
        'judul',
        'subjudul',
        'tautan',
        'urutan',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
            'aktif' => 'boolean',
        ];
    }

    /** @param  Builder<self>  $query */
    public function scopeAktif(Builder $query): void
    {
        $query->where('aktif', true)->orderBy('urutan');
    }

    public function gambarUrl(): ?string
    {
        return $this->gambar ? Storage::disk('public')->url($this->gambar) : null;
    }
}
