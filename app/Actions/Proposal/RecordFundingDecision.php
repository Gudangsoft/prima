<?php

declare(strict_types=1);

namespace App\Actions\Proposal;

use App\Enums\FundingStatus;
use App\Enums\ProposalStatus;
use App\Models\FundingDecision;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Admin LPPM menetapkan pendanaan atas usulan berstatus "under_review".
 * Menyimpan/memperbarui satu baris funding_decisions lalu mentransisikan
 * status ke "funded" (bila didanai/sebagian) atau "rejected" (bila tidak).
 */
class RecordFundingDecision
{
    public function __invoke(
        Proposal $proposal,
        User $actor,
        FundingStatus $status,
        float $jumlahDana = 0,
        ?string $nomorSk = null,
        ?string $fileSk = null,
    ): Proposal {
        return DB::transaction(function () use ($proposal, $actor, $status, $jumlahDana, $nomorSk, $fileSk): Proposal {
            FundingDecision::updateOrCreate(
                ['proposal_id' => $proposal->getKey()],
                [
                    'status_danai' => $status->value,
                    'jumlah_dana' => $status->isFunded() ? $jumlahDana : 0,
                    'sk_pendanaan' => $nomorSk,
                    'file_sk' => $fileSk,
                    'decided_by' => $actor->getKey(),
                ],
            );

            $note = $status->isFunded()
                ? sprintf(
                    'Ditetapkan %s: Rp %s%s',
                    $status->label(),
                    number_format($jumlahDana, 0, ',', '.'),
                    $nomorSk ? " (SK: {$nomorSk})" : '',
                )
                : 'Tidak didanai berdasarkan hasil penilaian reviewer.';

            return app(ChangeProposalStatus::class)(
                $proposal,
                $status->isFunded() ? ProposalStatus::Funded : ProposalStatus::Rejected,
                $actor,
                $note,
            );
        });
    }
}
