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
        'template_path',
        'aktif',
        'tanggal_buka',
        'tanggal_tutup',
    ];

    protected function casts(): array
    {
        return [
            'kategori' => Kategori::class,
            'aktif' => 'boolean',
            'dana_min' => 'float',
            'dana_max' => 'float',
            'tanggal_buka' => 'date',
            'tanggal_tutup' => 'date',
        ];
    }

    /** @return HasMany<Proposal> */
    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class, 'scheme_id');
    }

    /** @return HasMany<ProposalSchemeLuaran> */
    public function luarans(): HasMany
    {
        return $this->hasMany(ProposalSchemeLuaran::class)->orderBy('urutan');
    }

    /** @return HasMany<ProposalSchemeLuaran> */
    public function luaranWajib(): HasMany
    {
        return $this->luarans()->where('wajib', true);
    }

    /** @return HasMany<ProposalSchemeLuaran> */
    public function luaranTambahan(): HasMany
    {
        return $this->luarans()->where('wajib', false);
    }

    /** @param Builder<self> $query */
    public function scopeAktif(Builder $query): void
    {
        $query->where('aktif', true);
    }

    /**
     * Skema yang benar-benar bisa dipilih dosen sekarang: aktif, DAN (bila
     * periode diisi) tanggal hari ini berada dalam rentang buka—tutup. Gaya
     * "Buka Usulan" BIMA — dipakai di menu dosen, form pengajuan, & cek
     * eligibilitas; scopeAktif() polos tetap dipakai admin untuk kelola data.
     *
     * @param Builder<self> $query
     */
    public function scopeTersedia(Builder $query): void
    {
        $hariIni = now()->toDateString();

        $query->where('aktif', true)
            ->where(fn (Builder $q) => $q->whereNull('tanggal_buka')->orWhereDate('tanggal_buka', '<=', $hariIni))
            ->where(fn (Builder $q) => $q->whereNull('tanggal_tutup')->orWhereDate('tanggal_tutup', '>=', $hariIni));
    }

    /** @param Builder<self> $query */
    public function scopeKategori(Builder $query, Kategori|string $kategori): void
    {
        $query->where('kategori', $kategori instanceof Kategori ? $kategori->value : $kategori);
    }

    /** Status periode saat ini, untuk ditampilkan ke admin (badge di tabel Skema Usulan). */
    public function statusPeriode(): string
    {
        if (! $this->aktif) {
            return 'Nonaktif';
        }

        $hariIni = now()->startOfDay();

        if ($this->tanggal_buka?->isAfter($hariIni)) {
            return 'Belum Dibuka';
        }

        if ($this->tanggal_tutup?->isBefore($hariIni)) {
            return 'Sudah Ditutup';
        }

        return 'Terbuka';
    }

    public function statusPeriodeColor(): string
    {
        return match ($this->statusPeriode()) {
            'Terbuka' => 'success',
            'Belum Dibuka' => 'warning',
            'Sudah Ditutup', 'Nonaktif' => 'danger',
            default => 'gray',
        };
    }

    /** Rentang tanggal buka—tutup dalam format singkat, atau null bila tak diatur. */
    public function periodeLabel(): ?string
    {
        if ($this->tanggal_buka === null && $this->tanggal_tutup === null) {
            return null;
        }

        $fmt = fn ($d) => $d?->translatedFormat('d M Y') ?? '—';

        return $fmt($this->tanggal_buka).' s/d '.$fmt($this->tanggal_tutup);
    }

    public function templateUrl(): ?string
    {
        return $this->template_path ? Storage::disk('public')->url($this->template_path) : null;
    }

    /**
     * Ringkasan luaran wajib & tambahan dalam satu baris teks, untuk
     * ditampilkan ke dosen (mis. hint di form pengajuan). Null bila skema
     * belum punya target luaran sama sekali.
     */
    public function luaranSummary(): ?string
    {
        $wajib = $this->luarans->where('wajib', true)->pluck('jenis_luaran');
        $tambahan = $this->luarans->where('wajib', false)->pluck('jenis_luaran');

        $bagian = collect([
            $wajib->isNotEmpty() ? 'Luaran Wajib: '.$wajib->implode(', ') : null,
            $tambahan->isNotEmpty() ? 'Luaran Tambahan (opsional): '.$tambahan->implode(', ') : null,
        ])->filter();

        return $bagian->isNotEmpty() ? $bagian->implode(' · ') : null;
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
