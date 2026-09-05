<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\FundingDecision;
use App\Models\MonitoringReport;
use App\Models\Output;
use App\Models\Proposal;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Unduhan berkas privat (PDF proposal, laporan, bukti luaran, SK) dengan
 * pemeriksaan otorisasi lewat ProposalPolicy. Berkas disimpan di disk privat
 * sehingga tidak bisa diakses langsung via URL publik.
 */
class FileDownloadController extends Controller
{
    public function proposal(Proposal $proposal): StreamedResponse
    {
        $this->authorize('view', $proposal);

        return $this->stream($proposal->file_proposal, "proposal-{$proposal->id}.pdf");
    }

    public function report(MonitoringReport $report): StreamedResponse
    {
        $this->authorize('view', $report->proposal);

        return $this->stream($report->file_laporan, "laporan-{$report->jenis->value}-{$report->proposal_id}.pdf");
    }

    public function output(Output $output): StreamedResponse
    {
        $this->authorize('view', $output->proposal);

        return $this->stream($output->bukti_file, "luaran-{$output->id}.pdf");
    }

    public function sk(FundingDecision $funding): StreamedResponse
    {
        $this->authorize('view', $funding->proposal);

        return $this->stream($funding->file_sk, "sk-pendanaan-{$funding->proposal_id}.pdf");
    }

    private function stream(?string $path, string $downloadName): StreamedResponse
    {
        abort_if(blank($path) || ! Storage::disk('local')->exists($path), 404, 'Berkas tidak ditemukan.');

        return Storage::disk('local')->download($path, $downloadName);
    }
}
