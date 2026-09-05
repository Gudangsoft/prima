<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Actions\Proposal\AssignReviewers;
use App\Actions\Proposal\ChangeProposalStatus;
use App\Actions\Proposal\RecordApproval;
use App\Actions\Proposal\RecordFundingDecision;
use App\Enums\BidangFokus;
use App\Enums\FundingStatus;
use App\Enums\Kategori;
use App\Enums\MemberType;
use App\Enums\MonevRekomendasi;
use App\Enums\ProposalStatus;
use App\Filament\Resources\ProposalResource\Pages;
use App\Filament\Resources\ProposalResource\RelationManagers\CatatanHarianRelationManager;
use App\Filament\Resources\ProposalResource\RelationManagers\MonitoringReportsRelationManager;
use App\Filament\Resources\ProposalResource\RelationManagers\OutputsRelationManager;
use App\Filament\Resources\ProposalResource\RelationManagers\ReviewsRelationManager;
use App\Filament\Resources\ProposalResource\RelationManagers\StatusHistoriesRelationManager;
use App\Filament\Resources\ProposalResource\Support\ProposalWizard;
use App\Filament\Resources\ProposalResource\Support\WorkflowForms;
use App\Models\Proposal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProposalResource extends Resource
{
    protected static ?string $model = Proposal::class;

    protected static ?string $slug = 'usulan';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Monitoring';

    protected static ?string $navigationLabel = 'Monitoring Usulan';

    protected static ?string $modelLabel = 'Usulan';

    protected static ?string $pluralModelLabel = 'Usulan';

    protected static ?int $navigationSort = 10;

    /**
     * Untuk dosen murni, menu usulan dimunculkan lewat dropdown "Penelitian" /
     * "Pengabdian" (lihat AdminPanelProvider), jadi item bawaan grup "Monitoring"
     * ini disembunyikan agar tidak ganda.
     */
    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user !== null
            && (! $user->hasRole('dosen') || $user->hasAnyRole(['admin_lppm', 'pimpinan', 'super_admin', 'reviewer']));
    }

    /** Batasi baris sesuai peran; pengawas melihat semua, dosen hanya miliknya, reviewer yang ditugaskan. */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['scheme', 'submitter.programStudi', 'fundingDecision']);
        $user = auth()->user();

        if ($user->hasAnyRole(['admin_lppm', 'pimpinan', 'super_admin'])) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user): void {
            $q->where('user_id', $user->getKey())
                ->orWhereHas('members', fn (Builder $m) => $m->where('user_id', $user->getKey()));

            if ($user->isReviewer()) {
                $q->orWhereHas('reviews', fn (Builder $r) => $r->where('reviewer_id', $user->getKey()));
            }
        });
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Wizard::make(ProposalWizard::steps())
                ->columnSpanFull()
                ->skippable(),
        ]);
    }

    /**
     * Baris "Identitas Anggota" untuk infolist. Untuk anggota dosen, ketua
     * (submitter) selalu ditaruh paling atas dengan status "Menyetujui".
     *
     * @return array<int, array<string, mixed>>
     */
    public static function anggotaRows(Proposal $record, bool $dosen): array
    {
        if ($dosen) {
            $ketua = [[
                'identitas' => $record->submitter?->nidn,
                'nama' => $record->submitter?->name,
                'institusi' => $record->submitter?->unit_kerja,
                'prodi' => $record->submitter?->programStudi?->nama,
                'jenjang' => null,
                'jenis' => 'Dosen',
                'tugas' => 'Ketua Pengusul',
                'status' => 'Menyetujui',
            ]];

            $anggota = $record->members
                ->where('jenis', MemberType::Dosen)
                ->map(fn ($m): array => [
                    'identitas' => $m->identitas_no,
                    'nama' => $m->nama,
                    'institusi' => $m->institusi,
                    'prodi' => $m->prodi,
                    'jenjang' => $m->jenjang,
                    'jenis' => 'Dosen',
                    'tugas' => $m->tugas,
                    'status' => $m->status?->label() ?? 'Menunggu',
                ])
                ->values()
                ->all();

            return [...$ketua, ...$anggota];
        }

        return $record->members
            ->where('jenis', '!=', MemberType::Dosen)
            ->map(fn ($m): array => [
                'identitas' => $m->identitas_no,
                'nama' => $m->nama,
                'institusi' => $m->institusi,
                'prodi' => $m->prodi,
                'jenjang' => $m->jenjang,
                'jenis' => $m->jenis?->label() ?? '—',
                'tugas' => $m->tugas,
                'status' => null,
            ])
            ->values()
            ->all();
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Identitas Usulan')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('judul')->weight('bold')->columnSpanFull(),
                    Infolists\Components\TextEntry::make('kelompok_skema')->label('Kelompok Skema')->placeholder('—'),
                    Infolists\Components\TextEntry::make('scheme.nama_skema')->label('Ruang Lingkup'),
                    Infolists\Components\TextEntry::make('scheme.kategori')
                        ->label('Kategori')->badge()
                        ->formatStateUsing(fn (Kategori $state): string => $state->label())
                        ->color(fn (Kategori $state): string => $state->color()),
                    Infolists\Components\TextEntry::make('bidang_fokus')
                        ->label('Bidang Fokus')
                        ->formatStateUsing(fn (?BidangFokus $state): string => $state?->label() ?? '—'),
                    Infolists\Components\TextEntry::make('tema')->label('Tema Penelitian')->placeholder('—'),
                    Infolists\Components\TextEntry::make('topik')->label('Topik Penelitian')->placeholder('—'),
                    Infolists\Components\TextEntry::make('rumpun_ilmu')->label('Rumpun Ilmu (Level 3)')->placeholder('—'),
                    Infolists\Components\TextEntry::make('target_tkt')->label('Target TKT')->placeholder('—'),
                    Infolists\Components\TextEntry::make('lama_kegiatan')
                        ->label('Lama Kegiatan')->formatStateUsing(fn (?int $state): string => ($state ?? 1).' Tahun'),
                    Infolists\Components\TextEntry::make('tahun_usulan')->label('Tahun Usulan')->placeholder('—'),
                    Infolists\Components\TextEntry::make('tahun_anggaran')->label('Tahun Pelaksanaan'),
                    Infolists\Components\TextEntry::make('makro_riset')->label('Nama Makro Riset')->placeholder('—'),
                    Infolists\Components\TextEntry::make('submitter.sinta_id')->label('Profil SINTA Ketua')->placeholder('—'),
                    Infolists\Components\TextEntry::make('status')
                        ->badge()
                        ->formatStateUsing(fn (ProposalStatus $state): string => $state->label())
                        ->color(fn (ProposalStatus $state): string => $state->color()),
                    Infolists\Components\TextEntry::make('file_proposal')
                        ->label('Berkas Proposal')
                        ->placeholder('Belum diunggah')
                        ->formatStateUsing(fn (?string $state): string => $state ? basename($state) : '—'),
                ]),

            Infolists\Components\Section::make('Identitas Anggota Dosen')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('anggota_dosen')
                        ->hiddenLabel()
                        ->state(fn (Proposal $record): array => static::anggotaRows($record, dosen: true))
                        ->columns(5)
                        ->schema([
                            Infolists\Components\TextEntry::make('identitas')->label('NUPTK/NIDN'),
                            Infolists\Components\TextEntry::make('nama')->label('Nama'),
                            Infolists\Components\TextEntry::make('institusi')->label('Institusi')->placeholder('—'),
                            Infolists\Components\TextEntry::make('prodi')->label('Prodi')->placeholder('—'),
                            Infolists\Components\TextEntry::make('status')
                                ->label('Status')->badge()
                                ->color(fn (string $state): string => $state === 'Menyetujui' ? 'success' : ($state === 'Menolak' ? 'danger' : 'warning')),
                            Infolists\Components\TextEntry::make('tugas')->label('Tugas')->columnSpanFull()->placeholder('—'),
                        ]),
                ]),

            Infolists\Components\Section::make('Identitas Anggota Non Dosen')
                ->visible(fn (Proposal $record): bool => $record->members->where('jenis', '!=', MemberType::Dosen)->isNotEmpty())
                ->schema([
                    Infolists\Components\RepeatableEntry::make('anggota_non_dosen')
                        ->hiddenLabel()
                        ->state(fn (Proposal $record): array => static::anggotaRows($record, dosen: false))
                        ->columns(5)
                        ->schema([
                            Infolists\Components\TextEntry::make('jenis')->label('Jenis'),
                            Infolists\Components\TextEntry::make('identitas')->label('No Identitas')->placeholder('—'),
                            Infolists\Components\TextEntry::make('jenjang')->label('Jenjang')->placeholder('—'),
                            Infolists\Components\TextEntry::make('nama')->label('Nama'),
                            Infolists\Components\TextEntry::make('institusi')->label('Institusi')->placeholder('—'),
                            Infolists\Components\TextEntry::make('tugas')->label('Tugas')->columnSpanFull()->placeholder('—'),
                        ]),
                ]),

            Infolists\Components\Section::make('Substansi dan Luaran')
                ->schema([
                    Infolists\Components\TextEntry::make('makro_riset')->label('Nama Makro Riset')->placeholder('—'),
                    Infolists\Components\TextEntry::make('abstrak')->label('Abstrak')->prose()->columnSpanFull(),
                    Infolists\Components\TextEntry::make('substansi_file')
                        ->label('Berkas Substansi')->placeholder('—')
                        ->formatStateUsing(fn (?string $state): string => $state ? basename($state) : '—'),
                    Infolists\Components\RepeatableEntry::make('outputTargets')
                        ->label('Target Luaran')
                        ->columns(5)
                        ->schema([
                            Infolists\Components\TextEntry::make('tahun_ke')->label('Urutan Tahun')
                                ->formatStateUsing(fn (int $state): string => 'Tahun ke-'.$state),
                            Infolists\Components\TextEntry::make('kelompok_luaran')->label('Kelompok Luaran'),
                            Infolists\Components\TextEntry::make('jenis_luaran')->label('Jenis Luaran'),
                            Infolists\Components\TextEntry::make('target')->label('Target')->placeholder('—'),
                            Infolists\Components\TextEntry::make('keterangan')->label('Keterangan')->placeholder('—'),
                        ]),
                ]),

            Infolists\Components\Section::make('8 Bidang Strategis')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('strategicFields')
                        ->hiddenLabel()
                        ->columns(3)
                        ->schema([
                            Infolists\Components\TextEntry::make('bidang')->label('Bidang'),
                            Infolists\Components\TextEntry::make('rumusan_masalah')->label('Rumusan Masalah')->placeholder('—'),
                            Infolists\Components\TextEntry::make('uraian_kegiatan')->label('Uraian Kegiatan')->placeholder('—'),
                        ])
                        ->placeholder('Data tidak tersedia'),
                ]),

            Infolists\Components\Section::make('Rancangan Anggaran Biaya (RAB)')
                ->schema([
                    Infolists\Components\TextEntry::make('total_rab')
                        ->label('Total Anggaran yang diajukan')
                        ->money('IDR', locale: 'id'),
                    Infolists\Components\RepeatableEntry::make('rabItems')
                        ->hiddenLabel()
                        ->columns(6)
                        ->schema([
                            Infolists\Components\TextEntry::make('tahun_ke')->label('Th')
                                ->formatStateUsing(fn (int $state): string => 'Ke-'.$state),
                            Infolists\Components\TextEntry::make('kelompok')->label('Kelompok'),
                            Infolists\Components\TextEntry::make('komponen')->label('Komponen'),
                            Infolists\Components\TextEntry::make('item')->label('Item'),
                            Infolists\Components\TextEntry::make('satuan')->label('Satuan'),
                            Infolists\Components\TextEntry::make('total')->label('Total')->money('IDR', locale: 'id'),
                        ])
                        ->placeholder('Belum ada rincian RAB'),
                ]),

            Infolists\Components\Section::make('Mitra')
                ->visible(fn (Proposal $record): bool => $record->partners->isNotEmpty())
                ->schema([
                    Infolists\Components\RepeatableEntry::make('partners')
                        ->hiddenLabel()
                        ->columns(4)
                        ->schema([
                            Infolists\Components\TextEntry::make('nama_mitra')->label('Nama Mitra'),
                            Infolists\Components\TextEntry::make('institusi')->label('Institusi')->placeholder('—'),
                            Infolists\Components\TextEntry::make('negara')->label('Negara')->placeholder('—'),
                            Infolists\Components\TextEntry::make('dana')->label('Dana')->money('IDR', locale: 'id'),
                            Infolists\Components\TextEntry::make('alamat')->label('Alamat')->columnSpanFull()->placeholder('—'),
                        ]),
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

            Infolists\Components\Section::make('Monev Internal PT')
                ->columns(3)
                ->visible(fn (Proposal $record): bool => $record->monevInternal !== null)
                ->schema([
                    Infolists\Components\TextEntry::make('monevInternal.tanggal_monev')
                        ->label('Tanggal monev')->date('d M Y'),
                    Infolists\Components\TextEntry::make('monevInternal.skor_capaian')
                        ->label('Skor capaian')->placeholder('—'),
                    Infolists\Components\TextEntry::make('monevInternal.rekomendasi')
                        ->label('Rekomendasi')->badge()
                        ->formatStateUsing(fn (?MonevRekomendasi $state): string => $state?->label() ?? '—')
                        ->color(fn (?MonevRekomendasi $state): string => $state?->color() ?? 'gray'),
                    Infolists\Components\TextEntry::make('monevInternal.penilai.name')
                        ->label('Penilai')->placeholder('—'),
                    Infolists\Components\TextEntry::make('monevInternal.catatan')
                        ->label('Catatan')->columnSpanFull()->placeholder('—'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        $user = auth()->user();
        $isPengawas = $user->hasAnyRole(['admin_lppm', 'pimpinan', 'reviewer', 'super_admin']);

        return $table
            ->striped()
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('no')
                    ->label('No')
                    ->rowIndex()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul Usulan')
                    ->weight('bold')
                    ->wrap()
                    ->limit(120)
                    ->searchable(isIndividual: true)
                    ->description(fn (Proposal $record): string => collect([
                        $record->scheme?->nama_skema,
                        $record->scheme?->kategori?->label(),
                        'TA '.$record->tahun_anggaran,
                    ])->filter()->implode('  ·  ')),

                Tables\Columns\TextColumn::make('submitter.name')
                    ->label('Ketua Pengusul')
                    ->searchable(isIndividual: true)
                    ->description(fn (Proposal $record): string => collect([
                        $record->submitter?->nidn ? 'NIDN '.$record->submitter->nidn : null,
                        $record->submitter?->programStudi?->nama,
                    ])->filter()->implode('  ·  '))
                    ->visible($isPengawas),

                Tables\Columns\TextColumn::make('tahun_anggaran')
                    ->label('Tahun')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('fundingDecision.jumlah_dana')
                    ->label('Dana')
                    ->money('IDR', locale: 'id')
                    ->placeholder('—')
                    ->alignEnd()
                    ->visible($isPengawas),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (ProposalStatus $state): string => $state->label())
                    ->color(fn (ProposalStatus $state): string => $state->color())
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordUrl(fn (Proposal $record): string => static::getUrl('view', ['record' => $record]))
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->multiple()
                    ->options(ProposalStatus::options()),

                Tables\Filters\TernaryFilter::make('penilaian_masuk')
                    ->label('Penilaian reviewer masuk')
                    ->queries(
                        true: fn (Builder $q) => $q->whereHas('reviews', fn (Builder $r) => $r->whereNotNull('submitted_at')),
                        false: fn (Builder $q) => $q->whereDoesntHave('reviews', fn (Builder $r) => $r->whereNotNull('submitted_at')),
                        blank: fn (Builder $q) => $q,
                    ),

                Tables\Filters\SelectFilter::make('scheme_id')
                    ->label('Skema')
                    ->relationship('scheme', 'nama_skema')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('kategori')
                    ->label('Kategori')
                    ->options(Kategori::options())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
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
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Detail')
                    ->button()
                    ->color('gray')
                    ->size('sm'),

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
                    ->label('Penugasan Reviewer')
                    ->icon('heroicon-o-user-plus')
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
            CatatanHarianRelationManager::class,
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
