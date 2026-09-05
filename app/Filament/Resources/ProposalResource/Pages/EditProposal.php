<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProposalResource\Pages;

use App\Actions\Proposal\ChangeProposalStatus;
use App\Enums\ProposalStatus;
use App\Filament\Resources\ProposalResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProposal extends EditRecord
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
                ->visible(fn (): bool => auth()->user()->can('submit', $this->getRecord()))
                ->action(function (): void {
                    app(ChangeProposalStatus::class)(
                        $this->getRecord(),
                        ProposalStatus::Submitted,
                        auth()->user(),
                        'Usulan dikirim oleh pengusul.',
                    );

                    Notification::make()->title('Usulan berhasil dikirim')->success()->send();

                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->getRecord()]));
                }),

            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
