<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Kategori;
use Database\Factories\ProposalSchemeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Skema usulan (mis. "Penelitian Dasar", "Pengabdian Kemitraan Masyarakat").
 * Dikelola Admin LPPM; dosen memilih salah satu skema aktif saat mengajukan.
 */
class ProposalScheme extends Model
{
    /** @use HasFactory<ProposalSchemeFactory> */
    use HasFactory;

    protected $fillable = [
        'nama_skema',
        'kategori',
        'deskripsi',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'kategori' => Kategori::class,
            'aktif' => 'boolean',
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
}
