<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Proposal\AssignReviewers;
use App\Actions\Proposal\ChangeProposalStatus;
use App\Actions\Proposal\RecordApproval;
use App\Actions\Proposal\RecordFundingDecision;
use App\Actions\Proposal\RecordOutputValidation;
use App\Actions\Proposal\RecordReview;
use App\Actions\Proposal\SubmitMonitoringReport;
use App\Enums\FundingStatus;
use App\Enums\OutputType;
use App\Enums\OutputValidationStatus;
use App\Enums\ProposalStatus;
use App\Enums\ReportType;
use App\Enums\ReviewRecommendation;
use App\Enums\Role;
use App\Models\Output;
use App\Models\Proposal;
use App\Models\ProposalScheme;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Notification;

/**
 * Data contoh agar dashboard & alur kerja terlihat berisi. Tidak dijalankan
 * bila sudah ada usulan, dan notifikasi email dimatikan selama seeding.
 */
class DemoProposalSeeder extends Seeder
{
    public function run(): void
    {
        if (Proposal::query()->exists()) {
            return;
        }

        Notification::fake();

        $dosen = User::role(Role::Dosen->value)->orderBy('id')->get();
        $admin = User::role(Role::AdminLppm->value)->first();
        $reviewers = User::role(Role::Reviewer->value)->get();
        $schemes = ProposalScheme::query()->get();

        if ($dosen->isEmpty() || $admin === null || $reviewers->isEmpty() || $schemes->isEmpty()) {
            return;
        }

        $tahun = (int) now()->year;
        $blueprint = [
            [ProposalStatus::Draft, 3, $tahun],
            [ProposalStatus::Submitted, 2, $tahun],
            [ProposalStatus::ApprovedLppm, 2, $tahun],
            [ProposalStatus::UnderReview, 2, $tahun],
            [ProposalStatus::Funded, 3, $tahun],
            [ProposalStatus::Rejected, 1, $tahun - 1],
            [ProposalStatus::Reported, 1, $tahun - 1],
            [ProposalStatus::OutputValidated, 1, $tahun - 1],
        ];

        $i = 0;

        foreach ($blueprint as [$target, $jumlah, $ta]) {
            for ($n = 0; $n < $jumlah; $n++) {
                $i++;
                $proposal = Proposal::create([
                    'user_id' => $dosen[$i % $dosen->count()]->id,
                    'scheme_id' => $schemes[$i % $schemes->count()]->id,
                    'judul' => "Usulan Contoh #{$i} — ".$schemes[$i % $schemes->count()]->nama_skema,
                    'abstrak' => 'Abstrak contoh untuk keperluan demonstrasi sistem. '.str_repeat('Lorem ipsum dolor sit amet. ', 6),
                    'file_proposal' => 'proposals/contoh-'.$i.'.pdf',
                    'tahun_anggaran' => $ta,
                    'status' => ProposalStatus::Draft->value,
                ]);

                $this->advanceTo($proposal, $target, $admin, $reviewers);
            }
        }
    }

    private function advanceTo(Proposal $proposal, ProposalStatus $target, User $admin, $reviewers): void
    {
        if ($target === ProposalStatus::Draft) {
            return;
        }

        app(ChangeProposalStatus::class)($proposal, ProposalStatus::Submitted, $proposal->submitter, 'Usulan dikirim oleh pengusul.');
        if ($target === ProposalStatus::Submitted) {
            return;
        }

        if ($target === ProposalStatus::Rejected) {
            app(RecordApproval::class)($proposal->fresh(), $admin, false, 'Tidak sesuai fokus riset institusi.');

            return;
        }

        app(RecordApproval::class)($proposal->fresh(), $admin, true, 'Administrasi lengkap.');
        if ($target === ProposalStatus::ApprovedLppm) {
            return;
        }

        app(AssignReviewers::class)($proposal->fresh(), $admin, $reviewers->pluck('id')->take(2)->all());
        if ($target === ProposalStatus::UnderReview) {
            return;
        }

        foreach ($reviewers->take(2) as $k => $reviewer) {
            app(RecordReview::class)(
                $proposal->fresh(),
                $reviewer,
                80 + $k * 5,
                ReviewRecommendation::Danai,
                'Layak didanai dengan catatan minor.',
            );
        }

        app(RecordFundingDecision::class)(
            $proposal->fresh(),
            $admin,
            FundingStatus::Didanai,
            20_000_000 + ($proposal->id % 3) * 5_000_000,
            'SK-'.str_pad((string) $proposal->id, 3, '0', STR_PAD_LEFT).'/LPPM/'.$proposal->tahun_anggaran,
        );
        if ($target === ProposalStatus::Funded) {
            return;
        }

        app(SubmitMonitoringReport::class)($proposal->fresh(), $proposal->submitter, ReportType::Kemajuan, 'reports/contoh-kemajuan-'.$proposal->id.'.pdf', 'Capaian 60%.');
        app(SubmitMonitoringReport::class)($proposal->fresh(), $proposal->submitter, ReportType::Akhir, 'reports/contoh-akhir-'.$proposal->id.'.pdf', 'Kegiatan selesai 100%.');
        if ($target === ProposalStatus::Reported) {
            Output::create([
                'proposal_id' => $proposal->id,
                'jenis_luaran' => OutputType::Publikasi->value,
                'judul_luaran' => 'Artikel jurnal (dalam proses review)',
                'status_validasi' => OutputValidationStatus::Pending->value,
            ]);

            return;
        }

        $output = Output::create([
            'proposal_id' => $proposal->id,
            'jenis_luaran' => OutputType::Publikasi->value,
            'judul_luaran' => 'Artikel jurnal nasional terakreditasi',
            'status_validasi' => OutputValidationStatus::Pending->value,
        ]);
        app(RecordOutputValidation::class)($output, $admin, OutputValidationStatus::Valid, 'Bukti sesuai.');
    }
}
