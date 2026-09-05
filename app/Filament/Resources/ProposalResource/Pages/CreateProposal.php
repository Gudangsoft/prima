<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProposalResource\Pages;

use App\Enums\Kategori;
use App\Filament\Resources\ProposalResource;
use Filament\Resources\Pages\CreateRecord;
use Livewire\Attributes\Url;

class CreateProposal extends CreateRecord
{
    protected static string $resource = ProposalResource::class;

    /** Konteks kategori dari menu dosen (Penelitian / Pengabdian); mengunci pilihan skema. */
    #[Url]
    public ?string $kat = null;

    public function mount(): void
    {
        parent::mount();

        if (! array_key_exists((string) $this->kat, Kategori::options())) {
            $this->kat = null;
        }
    }

    public function getTitle(): string
    {
        return match ($this->kat) {
            'penelitian' => 'Ajukan Usulan Penelitian',
            'pengabdian' => 'Ajukan Usulan Pengabdian',
            default => 'Ajukan Usulan Baru',
        };
    }

    /** Pengusul selalu user yang sedang login; status awal = draft (default model). */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Usulan tersimpan sebagai draf';
    }
}
