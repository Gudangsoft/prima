<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProposalResource\Pages;

use App\Actions\Proposal\ChangeProposalStatus;
use App\Actions\Proposal\RecordApproval;
use App\Actions\Proposal\RecordFundingDecision;
use App\Enums\FundingStatus;
use App\Enums\MemberApprovalStatus;
use App\Enums\MemberType;
use App\Enums\ProposalStatus;
use App\Filament\Resources\ProposalResource;
use App\Filament\Resources\ProposalResource\Support\WorkflowForms;
use App\Models\ProposalMember;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewProposal extends ViewRecord
{
    protected static string $resource = ProposalResource::class;

    protected static string $view = 'filament.resources.proposal-resource.pages.view-proposal';

    /** Baris keanggotaan user saat ini yang masih menunggu persetujuan. */
    private function pendingMembership(): ?ProposalMember
    {
        return $this->record->members()
            ->where('user_id', auth()->id())
            ->where('jenis', MemberType::Dosen->value)
            ->where('status', MemberApprovalStatus::Menunggu->value)
            ->first();
    }

    private function respondMembership(bool $setuju): void
    {
        $this->pendingMembership()?->update([
            'status' => $setuju
                ? MemberApprovalStatus::Menyetujui->value
                : MemberApprovalStatus::Menolak->value,
        ]);

        Notification::make()
            ->title($setuju ? 'Keikutsertaan disetujui' : 'Keikutsertaan ditolak')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('submit')
                ->label('Kirim Usulan')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Kirim usulan ke LPPM?')
                ->modalDescription('Setelah dikirim, usulan tidak dapat diedit lagi hingga ada keputusan dari LPPM.')
                ->visible(fn (): bool => $this->record->user_id === auth()->id()
                    && $this->record->status === ProposalStatus::Draft)
                ->disabled(fn (): bool => ! auth()->user()->can('submit', $this->record))
                ->tooltip(fn (): ?string => match (true) {
                    blank($this->record->file_proposal) => 'Unggah berkas proposal lebih dulu.',
                    ! $this->record->allDosenMembersApproved() => 'Menunggu persetujuan seluruh anggota dosen.',
                    default => null,
                })
                ->action(function (): void {
                    app(ChangeProposalStatus::class)(
                        $this->record,
                        ProposalStatus::Submitted,
                        auth()->user(),
                        'Usulan dikirim oleh pengusul.',
                    );
                    Notification::make()->title('Usulan berhasil dikirim')->success()->send();
                }),

            Actions\Action::make('setujuiKeikutsertaan')
                ->label('Setujui Keikutsertaan')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->pendingMembership() !== null)
                ->action(fn () => $this->respondMembership(true)),

            Actions\Action::make('tolakKeikutsertaan')
                ->label('Tolak Keikutsertaan')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->pendingMembership() !== null)
                ->action(fn () => $this->respondMembership(false)),

            Actions\Action::make('setujui')
                ->label('Setujui')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->form(WorkflowForms::catatanKeputusan(wajib: false))
                ->visible(fn (): bool => auth()->user()->can('decideApproval', $this->record))
                ->action(function (array $data): void {
                    app(RecordApproval::class)($this->record, auth()->user(), true, $data['catatan'] ?? null);
                    Notification::make()->title('Usulan disetujui')->success()->send();
                }),

            Actions\Action::make('tolak')
                ->label('Tolak')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form(WorkflowForms::catatanKeputusan(wajib: true))
                ->visible(fn (): bool => auth()->user()->can('decideApproval', $this->record))
                ->action(function (array $data): void {
                    app(RecordApproval::class)($this->record, auth()->user(), false, $data['catatan']);
                    Notification::make()->title('Usulan ditolak')->success()->send();
                }),

            Actions\Action::make('tetapkanPendanaan')
                ->label('Tetapkan Pendanaan')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->form(WorkflowForms::penetapanPendanaan())
                ->visible(fn (): bool => auth()->user()->can('decideFunding', $this->record))
                ->action(function (array $data): void {
                    app(RecordFundingDecision::class)(
                        $this->record,
                        auth()->user(),
                        FundingStatus::from($data['status_danai']),
                        (float) ($data['jumlah_dana'] ?? 0),
                        $data['sk_pendanaan'] ?? null,
                        $data['file_sk'] ?? null,
                    );
                    Notification::make()->title('Pendanaan ditetapkan')->success()->send();
                }),

            Actions\EditAction::make()
                ->visible(fn (): bool => auth()->user()->can('update', $this->record)),
        ];
    }
}
