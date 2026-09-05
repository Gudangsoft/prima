<?php

declare(strict_types=1);

namespace App\Actions\Proposal;

use App\Enums\MonevRekomendasi;
use App\Models\MonevInternal;
use App\Models\Proposal;
use App\Models\User;

/**
 * Menyimpan / memperbarui hasil monev internal PT untuk satu usulan
 * (satu baris per usulan).
 */
class RecordMonevInternal
{
    public function __invoke(
        Proposal $proposal,
        User $actor,
        string $tanggal,
        ?int $skor,
        MonevRekomendasi $rekomendasi,
        ?string $catatan = null,
        ?int $penilaiId = null,
    ): MonevInternal {
        return MonevInternal::updateOrCreate(
            ['proposal_id' => $proposal->getKey()],
            [
                'tanggal_monev' => $tanggal,
                'skor_capaian' => $skor === null ? null : max(0, min(100, $skor)),
                'rekomendasi' => $rekomendasi->value,
                'catatan' => $catatan,
                'penilai_id' => $penilaiId,
                'created_by' => $actor->getKey(),
            ],
        );
    }
}
