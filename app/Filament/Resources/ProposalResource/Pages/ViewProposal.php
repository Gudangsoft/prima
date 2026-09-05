<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProposalResource\Pages;

use App\Actions\Proposal\ChangeProposalStatus;
use App\Actions\Proposal\RecordApproval;
use App\Actions\Proposal\RecordFundingDecision;
use App\Enums\FundingStatus;
use App\Enums\ProposalStatus;
use App\Filament\Resources\ProposalResource;
use App\Filament\Resources\ProposalResource\Support\WorkflowForms;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewProposal extends ViewRecord
{
    protected static string $resource = ProposalResource::class;

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
                ->visible(fn (): bool => auth()->user()->can('submit', $this->record))
                ->action(function (): void {
                    app(ChangeProposalStatus::class)(
                        $this->record,
                        ProposalStatus::Submitted,
                        auth()->user(),
                        'Usulan dikirim oleh pengusul.',
                    );
                    Notification::make()->title('Usulan berhasil dikirim')->success()->send();
                }),

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
