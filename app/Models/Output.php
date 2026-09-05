<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OutputType;
use App\Enums\OutputValidationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bukti luaran (publikasi / HKI / produk / lainnya) yang diunggah dosen dan
 * divalidasi Admin LPPM.
 */
class Output extends Model
{
    protected $fillable = [
        'proposal_id',
        'jenis_luaran',
        'judul_luaran',
        'bukti_file',
        'tautan',
        'status_validasi',
        'catatan_validasi',
        'validated_by',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'jenis_luaran' => OutputType::class,
            'status_validasi' => OutputValidationStatus::class,
            'validated_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Proposal, self> */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    /** @return BelongsTo<User, self> */
    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
