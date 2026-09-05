<?php

declare(strict_types=1);

namespace App\Actions\Proposal;

use App\Enums\OutputValidationStatus;
use App\Enums\ProposalStatus;
use App\Models\Output;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Admin LPPM memvalidasi / menolak satu bukti luaran.
 *
 * Bila seluruh luaran usulan berstatus "valid" dan usulan masih "reported",
 * usulan otomatis naik ke "output_validated".
 */
class RecordOutputValidation
{
    public function __invoke(
        Output $output,
        User $actor,
        OutputValidationStatus $status,
        ?string $catatan = null,
    ): Output {
        return DB::transaction(function () use ($output, $actor, $status, $catatan): Output {
            $output->update([
                'status_validasi' => $status->value,
                'catatan_validasi' => $catatan,
                'validated_by' => $actor->getKey(),
                'validated_at' => now(),
            ]);

            $proposal = $output->proposal;

            $allValid = $proposal->outputs()
                ->where('status_validasi', '!=', OutputValidationStatus::Valid->value)
                ->doesntExist();

            if ($status === OutputValidationStatus::Valid
                && $allValid
                && $proposal->status === ProposalStatus::Reported) {
                app(ChangeProposalStatus::class)(
                    $proposal,
                    ProposalStatus::OutputValidated,
                    $actor,
                    'Seluruh luaran telah tervalidasi.',
                );
            }

            return $output->refresh();
        });
    }
}
