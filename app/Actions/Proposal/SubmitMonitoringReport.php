<?php

declare(strict_types=1);

namespace App\Actions\Proposal;

use App\Enums\ProposalStatus;
use App\Enums\ReportType;
use App\Models\MonitoringReport;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Dosen mengunggah laporan kemajuan / akhir untuk usulan yang didanai.
 *
 * - Laporan pertama menggeser "funded" -> "in_progress".
 * - Laporan "akhir" menggeser "in_progress" -> "reported".
 */
class SubmitMonitoringReport
{
    public function __invoke(
        Proposal $proposal,
        User $dosen,
        ReportType $jenis,
        string $fileLaporan,
        ?string $ringkasan = null,
    ): MonitoringReport {
        return DB::transaction(function () use ($proposal, $dosen, $jenis, $fileLaporan, $ringkasan): MonitoringReport {
            $report = MonitoringReport::create([
                'proposal_id' => $proposal->getKey(),
                'jenis' => $jenis->value,
                'file_laporan' => $fileLaporan,
                'ringkasan' => $ringkasan,
                'tanggal_submit' => now()->toDateString(),
                'submitted_by' => $dosen->getKey(),
            ]);

            $change = app(ChangeProposalStatus::class);

            if ($proposal->status === ProposalStatus::Funded) {
                $change($proposal, ProposalStatus::InProgress, $dosen, 'Laporan diunggah; pelaksanaan berjalan.');
            }

            if ($jenis === ReportType::Akhir && $proposal->refresh()->status === ProposalStatus::InProgress) {
                $change($proposal, ProposalStatus::Reported, $dosen, 'Laporan akhir diunggah.');
            }

            return $report;
        });
    }
}
