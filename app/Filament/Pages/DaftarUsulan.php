<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\ProposalStatus;
use App\Filament\Exports\ProposalExporter;
use App\Filament\Resources\ProposalResource;
use App\Models\Proposal;
use Filament\Actions\Action as HeaderAction;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

/**
 * Halaman "List Usulan …" gaya BIMA: tujuan klik salah satu kartu ringkasan di
 * atas tabel Monitoring Usulan. Menampilkan daftar usulan yang tersaring pada
 * satu kelompok status, dengan tombol Kembali, tombol Excel, dan pencarian judul.
 *
 * Baris dibatasi ulang oleh ProposalResource::getEloquentQuery() sehingga
 * dosen tetap hanya melihat miliknya sendiri.
 */
class DaftarUsulan extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.pages.table-page';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'daftar-usulan';

    #[Url]
    public string $grup = 'dikirim';

    /** Definisi tiap kelompok: label judul + status yang tercakup (null = filter khusus). */
    private const GRUP = [
        'draft' => [
            'judul' => 'Draf',
            'status' => [ProposalStatus::Draft->value],
        ],
        'dikirim' => [
            'judul' => 'Dikirim',
            'status' => [
                ProposalStatus::Submitted->value,
                ProposalStatus::ApprovedLppm->value,
                ProposalStatus::UnderReview->value,
                ProposalStatus::Funded->value,
                ProposalStatus::Rejected->value,
                ProposalStatus::InProgress->value,
                ProposalStatus::Reported->value,
                ProposalStatus::OutputValidated->value,
            ],
        ],
        'belum-ditinjau' => [
            'judul' => 'Belum Ditinjau',
            'status' => [ProposalStatus::Submitted->value],
        ],
        'disetujui' => [
            'judul' => 'Disetujui',
            'status' => [
                ProposalStatus::ApprovedLppm->value,
                ProposalStatus::UnderReview->value,
                ProposalStatus::Funded->value,
                ProposalStatus::InProgress->value,
                ProposalStatus::Reported->value,
                ProposalStatus::OutputValidated->value,
            ],
        ],
        'ditolak' => [
            'judul' => 'Ditolak',
            'status' => [ProposalStatus::Rejected->value],
        ],
        'didanai' => [
            'judul' => 'Didanai',
            'status' => [
                ProposalStatus::Funded->value,
                ProposalStatus::InProgress->value,
                ProposalStatus::Reported->value,
                ProposalStatus::OutputValidated->value,
            ],
        ],
        'review-masuk' => [
            'judul' => 'Hasil Review Masuk',
            'status' => null,
        ],
    ];

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function mount(): void
    {
        abort_unless(array_key_exists($this->grup, self::GRUP), 404);
    }

    public function getTitle(): string
    {
        return 'List Usulan '.self::GRUP[$this->grup]['judul'];
    }

    /** URL halaman untuk satu kelompok — dipakai kartu ringkasan. */
    public static function urlFor(string $grup): string
    {
        return static::getUrl(['grup' => $grup]);
    }

    protected function getHeaderActions(): array
    {
        return [
            HeaderAction::make('kembali')
                ->label('Kembali')
                ->icon('heroicon-m-arrow-left')
                ->color('primary')
                ->url(ProposalResource::getUrl()),
        ];
    }

    public function table(Table $table): Table
    {
        $isPengawas = auth()->user()->hasAnyRole(['admin_lppm', 'pimpinan', 'super_admin']);

        return $table
            ->query($this->tableQuery())
            ->striped()
            ->defaultPaginationPageOption(25)
            ->searchPlaceholder('Cari Judul')
            ->headerActions([
                ExportAction::make()
                    ->label('Excel')
                    ->icon('heroicon-o-table-cells')
                    ->color('success')
                    ->exporter(ProposalExporter::class)
                    ->visible($isPengawas),
            ])
            ->columns([
                Tables\Columns\ViewColumn::make('pengusul')
                    ->label('Pengusul')
                    ->view('filament.tables.columns.usulan-pengusul'),

                Tables\Columns\TextColumn::make('scheme.nama_skema')
                    ->label('Skema')
                    ->weight('bold')
                    ->color('primary')
                    ->wrap(),

                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul')
                    ->weight('bold')
                    ->color('primary')
                    ->wrap()
                    ->searchable()
                    ->url(fn (Proposal $record): string => ProposalResource::getUrl('view', ['record' => $record])),

                Tables\Columns\IconColumn::make('file_proposal')
                    ->label('PDF')
                    ->alignCenter()
                    ->icon(fn (?string $state): string => $state ? 'heroicon-s-document-text' : 'heroicon-o-minus')
                    ->color(fn (?string $state): string => $state ? 'danger' : 'gray')
                    ->url(fn (Proposal $record): ?string => $record->file_proposal ? route('download.proposal', $record) : null)
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    private function tableQuery(): Builder
    {
        $query = ProposalResource::getEloquentQuery();
        $statuses = self::GRUP[$this->grup]['status'];

        return is_array($statuses)
            ? $query->whereIn('status', $statuses)
            : $query->whereHas('reviews', fn (Builder $r) => $r->whereNotNull('submitted_at'));
    }
}
