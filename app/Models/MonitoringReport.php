<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReportType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Laporan kemajuan / akhir yang diunggah dosen untuk usulan yang didanai.
 */
class MonitoringReport extends Model
{
    protected $fillable = [
        'proposal_id',
        'jenis',
        'file_laporan',
        'ringkasan',
        'tanggal_submit',
        'submitted_by',
    ];

    protected function casts(): array
    {
        return [
            'jenis' => ReportType::class,
            'tanggal_submit' => 'date',
        ];
    }

    /** @return BelongsTo<Proposal, self> */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    /** @return BelongsTo<User, self> */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
