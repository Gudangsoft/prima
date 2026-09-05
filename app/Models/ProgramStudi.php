<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Jenjang;
use Database\Factories\ProgramStudiFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Program studi institusi. Sumber: input manual atau impor CSV
 * ("Sinkronisasi Prodi").
 */
class ProgramStudi extends Model
{
    /** @use HasFactory<ProgramStudiFactory> */
    use HasFactory;

    protected $table = 'program_studi';

    protected $fillable = ['kode', 'nama', 'jenjang', 'fakultas', 'aktif'];

    protected function casts(): array
    {
        return [
            'jenjang' => Jenjang::class,
            'aktif' => 'boolean',
        ];
    }

    public function getNamaLengkapAttribute(): string
    {
        return trim(($this->jenjang?->value ? $this->jenjang->value.' ' : '').$this->nama);
    }

    /** @return HasMany<User> */
    public function dosen(): HasMany
    {
        return $this->hasMany(User::class, 'program_studi_id');
    }

    /** @param  Builder<self>  $query */
    public function scopeAktif(Builder $query): void
    {
        $query->where('aktif', true);
    }
}
