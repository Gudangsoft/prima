<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Kategori;
use Database\Factories\ProposalSchemeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * Skema usulan (mis. "Penelitian Dasar", "Pengabdian Kemitraan Masyarakat").
 * Dikelola Admin LPPM; dosen memilih salah satu skema aktif saat mengajukan.
 * Berlaku untuk kategori penelitian maupun pengabdian.
 */
class ProposalScheme extends Model
{
    /** @use HasFactory<ProposalSchemeFactory> */
    use HasFactory;

    protected $fillable = [
        'nama_skema',
        'kategori',
        'deskripsi',
        'dana_min',
        'dana_max',
        'target_luaran',
        'template_path',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'kategori' => Kategori::class,
            'aktif' => 'boolean',
            'dana_min' => 'float',
            'dana_max' => 'float',
        ];
    }

    /** @return HasMany<Proposal> */
    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class, 'scheme_id');
    }

    /** @param Builder<self> $query */
    public function scopeAktif(Builder $query): void
    {
        $query->where('aktif', true);
    }

    /** @param Builder<self> $query */
    public function scopeKategori(Builder $query, Kategori|string $kategori): void
    {
        $query->where('kategori', $kategori instanceof Kategori ? $kategori->value : $kategori);
    }

    public function templateUrl(): ?string
    {
        return $this->template_path ? Storage::disk('public')->url($this->template_path) : null;
    }

    /** Kisaran biaya dalam format "Rp x — Rp y" (atau salah satu sisi bila hanya satu batas diisi). */
    public function rentangDanaLabel(): ?string
    {
        $format = fn (float $v): string => 'Rp'.number_format($v, 0, ',', '.');

        return match (true) {
            $this->dana_min !== null && $this->dana_max !== null => $format($this->dana_min).' — '.$format($this->dana_max),
            $this->dana_max !== null => 'Maks. '.$format($this->dana_max),
            $this->dana_min !== null => 'Min. '.$format($this->dana_min),
            default => null,
        };
    }
}
