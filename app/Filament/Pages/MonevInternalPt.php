<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Actions\Proposal\RecordMonevInternal;
use App\Enums\MonevRekomendasi;
use App\Enums\ProposalStatus;
use App\Enums\Role;
use App\Filament\Pages\Concerns\OversightTablePage;
use App\Filament\Resources\ProposalResource;
use App\Models\Proposal;
use App\Models\User;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * "Monitoring & Evaluasi Internal PT" (grup Monitoring): monev internal atas
 * usulan yang berjalan — skor capaian, catatan, dan rekomendasi kelanjutan.
 */
class MonevInternalPt extends OversightTablePage
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Monitoring';

    protected static ?string $navigationLabel = 'Monev & Evaluasi Internal PT';

    protected static ?int $navigationSort = 40;

    protected static ?string $title = 'Monitoring & Evaluasi Internal PT';

    private const BERJALAN = [
        ProposalStatus::Funded->value,
        ProposalStatus::InProgress->value,
        ProposalStatus::Reported->value,
        ProposalStatus::OutputValidated->value,
    ];

    public static function getNavigationBadge(): ?string
    {
        $belum = Proposal::query()->whereIn('status', self::BERJALAN)->doesntHave('monevInternal')->count();

        return $belum > 0 ? (string) $belum : null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Proposal::query()
                    ->with(['submitter', 'monevInternal.penilai'])
                    ->whereIn('status', self::BERJALAN),
            )
            ->columns([
                Tables\Columns\TextColumn::make('judul')->label('Judul')->limit(55)->wrap()->searchable(),
                Tables\Columns\TextColumn::make('submitter.name')->label('Pengusul')->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')->badge()
                    ->formatStateUsing(fn (ProposalStatus $state): string => $state->label())
                    ->color(fn (ProposalStatus $state): string => $state->color()),
                Tables\Columns\TextColumn::make('monevInternal.tanggal_monev')
                    ->label('Tgl Monev')->date('d M Y')->placeholder('Belum'),
                Tables\Columns\TextColumn::make('monevInternal.skor_capaian')
                    ->label('Skor')->alignCenter()->placeholder('—'),
                Tables\Columns\TextColumn::make('monevInternal.rekomendasi')
                    ->label('Rekomendasi')->badge()
                    ->formatStateUsing(fn (?MonevRekomendasi $state): string => $state?->label() ?? '—')
                    ->color(fn (?MonevRekomendasi $state): string => $state?->color() ?? 'gray'),
                Tables\Columns\TextColumn::make('monevInternal.penilai.name')
                    ->label('Penilai')->placeholder('—')->toggleable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('belum_monev')
                    ->label('Belum dimonev')
                    ->query(fn ($q) => $q->doesntHave('monevInternal')),
            ])
            ->actions([
                Tables\Actions\Action::make('isiMonev')
                    ->label('Isi Monev')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->visible(fn (Proposal $record): bool => auth()->user()->can('monevInternal', $record))
                    ->fillForm(fn (Proposal $record): array => $record->monevInternal ? [
                        'tanggal_monev' => $record->monevInternal->tanggal_monev?->toDateString(),
                        'skor_capaian' => $record->monevInternal->skor_capaian,
                        'rekomendasi' => $record->monevInternal->rekomendasi?->value,
                        'catatan' => $record->monevInternal->catatan,
                        'penilai_id' => $record->monevInternal->penilai_id,
                    ] : ['tanggal_monev' => now()->toDateString()])
                    ->form([
                        Forms\Components\DatePicker::make('tanggal_monev')
                            ->label('Tanggal monev')->required()->native(false)->maxDate(now()),
                        Forms\Components\TextInput::make('skor_capaian')
                            ->label('Skor capaian (0–100)')->numeric()->minValue(0)->maxValue(100),
                        Forms\Components\Select::make('rekomendasi')
                            ->options(MonevRekomendasi::options())->required()->native(false),
                        Forms\Components\Select::make('penilai_id')
                            ->label('Penilai / tim monev')
                            ->options(fn (): array => User::query()->role(Role::Reviewer->value)->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()->preload(),
                        Forms\Components\Textarea::make('catatan')->rows(4)->maxLength(3000),
                    ])
                    ->action(function (Proposal $record, array $data): void {
                        app(RecordMonevInternal::class)(
                            $record,
                            auth()->user(),
                            $data['tanggal_monev'],
                            $data['skor_capaian'] !== null ? (int) $data['skor_capaian'] : null,
                            MonevRekomendasi::from($data['rekomendasi']),
                            $data['catatan'] ?? null,
                            $data['penilai_id'] ? (int) $data['penilai_id'] : null,
                        );
                        Notification::make()->title('Hasil monev tersimpan')->success()->send();
                    }),

                Tables\Actions\Action::make('buka')
                    ->label('Usulan')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Proposal $record): string => ProposalResource::getUrl('view', ['record' => $record])),
            ])
            ->defaultSort('updated_at', 'desc');
    }
}
