<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Actions\Proposal\AssignReviewers;
use App\Actions\Proposal\ChangeProposalStatus;
use App\Actions\Proposal\RecordApproval;
use App\Actions\Proposal\RecordFundingDecision;
use App\Enums\FundingStatus;
use App\Enums\Kategori;
use App\Enums\ProposalStatus;
use App\Filament\Resources\ProposalResource\Pages;
use App\Filament\Resources\ProposalResource\RelationManagers\MonitoringReportsRelationManager;
use App\Filament\Resources\ProposalResource\RelationManagers\OutputsRelationManager;
use App\Filament\Resources\ProposalResource\RelationManagers\ReviewsRelationManager;
use App\Filament\Resources\ProposalResource\RelationManagers\StatusHistoriesRelationManager;
use App\Filament\Resources\ProposalResource\Support\WorkflowForms;
use App\Models\Proposal;
use App\Models\ProposalScheme;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProposalResource extends Resource
{
    protected static ?string $model = Proposal::class;

    protected static ?string $slug = 'usulan';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Usulan';

    protected static ?string $navigationLabel = 'Usulan';

    protected static ?string $modelLabel = 'Usulan';

    protected static ?string $pluralModelLabel = 'Usulan';

    protected static ?int $navigationSort = 20;

    /** Batasi baris sesuai peran; pengawas melihat semua, dosen hanya miliknya, reviewer yang ditugaskan. */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['scheme', 'submitter']);
        $user = auth()->user();

        if ($user->hasAnyRole(['admin_lppm', 'pimpinan', 'super_admin'])) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user): void {
            $q->where('user_id', $user->getKey());

            if ($user->isReviewer()) {
                $q->orWhereHas('reviews', fn (Builder $r) => $r->where('reviewer_id', $user->getKey()));
            }
        });
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Wizard::make([
                Forms\Components\Wizard\Step::make('Skema & Tahun Anggaran')
                    ->icon('heroicon-o-rectangle-stack')
                    ->schema([
                        Forms\Components\Select::make('scheme_id')
                            ->label('Skema usulan')
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->options(fn (): array => ProposalScheme::query()
                                ->aktif()
                                ->orderBy('nama_skema')
                                ->get()
                                ->mapWithKeys(fn (ProposalScheme $s): array => [
                                    $s->id => "{$s->nama_skema} — {$s->kategori->label()}",
                                ])
                                ->all())
                            ->helperText('Hanya skema yang sedang aktif yang ditampilkan.'),

                        Forms\Components\Select::make('tahun_anggaran')
                            ->label('Tahun anggaran')
                            ->required()
                            ->native(false)
                            ->options(fn (): array => collect(range((int) now()->year - 1, (int) now()->year + 2))
                                ->mapWithKeys(fn (int $y): array => [$y => (string) $y])
                                ->all())
                            ->default((int) now()->year),
                    ]),

                Forms\Components\Wizard\Step::make('Substansi')
                    ->icon('heroicon-o-pencil-square')
                    ->schema([
                        Forms\Components\TextInput::make('judul')
                            ->label('Judul usulan')
                            ->required()
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('abstrak')
                            ->label('Abstrak')
                            ->required()
                            ->minLength(100)
                            ->maxLength(5000)
                            ->rows(10)
                            ->columnSpanFull()
                            ->helperText('Minimal 100 karakter.'),
                    ]),

                Forms\Components\Wizard\Step::make('Berkas Proposal')
                    ->icon('heroicon-o-paper-clip')
                    ->schema([
                        Forms\Components\FileUpload::make('file_proposal')
                            ->label('File proposal (PDF)')
                            ->disk('local')
                            ->directory('proposals')
                            ->visibility('private')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize((int) config('sip2m.uploads.proposal_max_kb'))
                            ->downloadable()
                            ->previewable(false)
                            ->helperText('Format PDF, maksimal '.number_format((int) config('sip2m.uploads.proposal_max_kb') / 1024, 0).' MB. '
                                .'Boleh dikosongkan dulu saat menyimpan draf; wajib diisi sebelum mengirim usulan.'),
                    ]),
            ])
                ->columnSpanFull(),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Ringkasan Usulan')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('judul')->columnSpanFull(),
                    Infolists\Components\TextEntry::make('scheme.nama_skema')->label('Skema'),
                    Infolists\Components\TextEntry::make('scheme.kategori')
                        ->label('Kategori')
                        ->badge()
                        ->formatStateUsing(fn (Kategori $state): string => $state->label())
                        ->color(fn (Kategori $state): string => $state->color()),
                    Infolists\Components\TextEntry::make('tahun_anggaran')->label('Tahun anggaran'),
                    Infolists\Components\TextEntry::make('status')
                        ->badge()
                        ->formatStateUsing(fn (ProposalStatus $state): string => $state->label())
                        ->color(fn (ProposalStatus $state): string => $state->color()),
                    Infolists\Components\TextEntry::make('submitter.name')->label('Pengusul'),
                    Infolists\Components\TextEntry::make('submitter.nidn')->label('NIDN')->placeholder('—'),
                    Infolists\Components\TextEntry::make('abstrak')->columnSpanFull()->prose(),
                    Infolists\Components\TextEntry::make('file_proposal')
                        ->label('Berkas')
                        ->placeholder('Belum diunggah')
                        ->formatStateUsing(fn (?string $state): string => $state ? basename($state) : '—'),
                ]),

            Infolists\Components\Section::make('Penetapan Pendanaan')
                ->columns(3)
                ->visible(fn (Proposal $record): bool => $record->fundingDecision !== null)
                ->schema([
                    Infolists\Components\TextEntry::make('fundingDecision.status_danai')
                        ->label('Status')
                        ->badge()
                        ->formatStateUsing(fn (FundingStatus $state): string => $state->label())
                        ->color(fn (FundingStatus $state): string => $state->color()),
                    Infolists\Components\TextEntry::make('fundingDecision.jumlah_dana')
                        ->label('Jumlah dana')
                        ->money('IDR', locale: 'id'),
                    Infolists\Components\TextEntry::make('fundingDecision.sk_pendanaan')
                        ->label('Nomor SK')
                        ->placeholder('—'),
                    Infolists\Components\TextEntry::make('fundingDecision.decidedBy.name')
                        ->label('Ditetapkan oleh')
                        ->placeholder('—'),
                    Infolists\Components\TextEntry::make('fundingDecision.updated_at')
                        ->label('Tanggal penetapan')
                        ->dateTime('d M Y H:i'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        $user = auth()->user();

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul')
                    ->searchable()
                    ->wrap()
                    ->limit(80)
                    ->sortable(),

                Tables\Columns\TextColumn::make('scheme.nama_skema')
                    ->label('Skema')
                    ->toggleable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('scheme.kategori')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn (Kategori $state): string => $state->label())
                    ->color(fn (Kategori $state): string => $state->color())
                    ->toggleable(),

                Tables\Columns\TextColumn::make('submitter.name')
                    ->label('Pengusul')
                    ->searchable()
                    ->visible(fn (): bool => $user->hasAnyRole(['admin_lppm', 'pimpinan', 'reviewer', 'super_admin'])),

                Tables\Columns\TextColumn::make('tahun_anggaran')
                    ->label('Tahun')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (ProposalStatus $state): string => $state->label())
                    ->color(fn (ProposalStatus $state): string => $state->color())
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(ProposalStatus::options()),

                Tables\Filters\SelectFilter::make('scheme_id')
                    ->label('Skema')
                    ->relationship('scheme', 'nama_skema')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('kategori')
                    ->label('Kategori')
                    ->options(Kategori::options())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'],
                        fn (Builder $q, string $value) => $q->whereHas('scheme', fn (Builder $s) => $s->where('kategori', $value)),
                    )),

                Tables\Filters\SelectFilter::make('tahun_anggaran')
                    ->label('Tahun anggaran')
                    ->options(fn (): array => Proposal::query()
                        ->distinct()
                        ->orderByDesc('tahun_anggaran')
                        ->pluck('tahun_anggaran', 'tahun_anggaran')
                        ->all()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('submit')
                    ->label('Kirim Usulan')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Kirim usulan ke LPPM?')
                    ->modalDescription('Setelah dikirim, usulan tidak dapat diedit lagi hingga ada keputusan dari LPPM.')
                    ->visible(fn (Proposal $record): bool => auth()->user()->can('submit', $record))
                    ->action(function (Proposal $record): void {
                        app(ChangeProposalStatus::class)(
                            $record,
                            ProposalStatus::Submitted,
                            auth()->user(),
                            'Usulan dikirim oleh pengusul.',
                        );

                        Notification::make()
                            ->title('Usulan berhasil dikirim')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('setujui')
                        ->label('Setujui (LPPM)')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->form(WorkflowForms::catatanKeputusan(wajib: false))
                        ->visible(fn (Proposal $record): bool => auth()->user()->can('decideApproval', $record))
                        ->action(function (Proposal $record, array $data): void {
                            app(RecordApproval::class)($record, auth()->user(), true, $data['catatan'] ?? null);
                            Notification::make()->title('Usulan disetujui')->success()->send();
                        }),

                    Tables\Actions\Action::make('tolak')
                        ->label('Tolak (LPPM)')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->form(WorkflowForms::catatanKeputusan(wajib: true))
                        ->visible(fn (Proposal $record): bool => auth()->user()->can('decideApproval', $record))
                        ->action(function (Proposal $record, array $data): void {
                            app(RecordApproval::class)($record, auth()->user(), false, $data['catatan']);
                            Notification::make()->title('Usulan ditolak')->success()->send();
                        }),

                    Tables\Actions\Action::make('tugaskanReviewer')
                        ->label('Tugaskan Reviewer')
                        ->icon('heroicon-o-user-plus')
                        ->color('warning')
                        ->form(WorkflowForms::penugasanReviewer())
                        ->visible(fn (Proposal $record): bool => auth()->user()->can('assignReviewer', $record))
                        ->action(function (Proposal $record, array $data): void {
                            app(AssignReviewers::class)($record, auth()->user(), array_map('intval', $data['reviewers']));
                            Notification::make()->title('Reviewer ditugaskan')->success()->send();
                        }),

                    Tables\Actions\Action::make('tetapkanPendanaan')
                        ->label('Tetapkan Pendanaan')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->form(WorkflowForms::penetapanPendanaan())
                        ->visible(fn (Proposal $record): bool => auth()->user()->can('decideFunding', $record))
                        ->action(function (Proposal $record, array $data): void {
                            app(RecordFundingDecision::class)(
                                $record,
                                auth()->user(),
                                FundingStatus::from($data['status_danai']),
                                (float) ($data['jumlah_dana'] ?? 0),
                                $data['sk_pendanaan'] ?? null,
                                $data['file_sk'] ?? null,
                            );
                            Notification::make()->title('Pendanaan ditetapkan')->success()->send();
                        }),
                ])
                    ->label('Alur Kerja')
                    ->icon('heroicon-o-bolt')
                    ->button()
                    ->visible(fn (Proposal $record): bool => auth()->user()->canAny(
                        ['decideApproval', 'assignReviewer', 'decideFunding'],
                        $record,
                    )),

                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ReviewsRelationManager::class,
            MonitoringReportsRelationManager::class,
            OutputsRelationManager::class,
            StatusHistoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProposals::route('/'),
            'create' => Pages\CreateProposal::route('/create'),
            'view' => Pages\ViewProposal::route('/{record}'),
            'edit' => Pages\EditProposal::route('/{record}/edit'),
        ];
    }
}
