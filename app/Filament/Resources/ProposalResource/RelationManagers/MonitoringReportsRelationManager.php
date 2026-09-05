<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProposalResource\RelationManagers;

use App\Actions\Proposal\SubmitMonitoringReport;
use App\Enums\ReportType;
use App\Models\MonitoringReport;
use App\Models\Proposal;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Laporan kemajuan / akhir. Dosen pemilik mengunggah; peran lain hanya membaca.
 */
class MonitoringReportsRelationManager extends RelationManager
{
    protected static string $relationship = 'monitoringReports';

    protected static ?string $title = 'Laporan Monev';

    protected static ?string $icon = 'heroicon-o-document-chart-bar';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        /** @var Proposal $proposal */
        $proposal = $this->getOwnerRecord();
        $user = auth()->user();

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (ReportType $state): string => $state->label()),

                Tables\Columns\TextColumn::make('tanggal_submit')
                    ->label('Tanggal')
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('ringkasan')
                    ->label('Ringkasan')
                    ->wrap()
                    ->limit(80)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('submittedBy.name')
                    ->label('Diunggah oleh')
                    ->placeholder('—'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('unggahLaporan')
                    ->label('Unggah Laporan')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->visible(fn (): bool => $user->can('submitReport', $proposal))
                    ->form([
                        Forms\Components\Select::make('jenis')
                            ->label('Jenis laporan')
                            ->options(ReportType::options())
                            ->required()
                            ->native(false),

                        Forms\Components\FileUpload::make('file_laporan')
                            ->label('Berkas laporan (PDF)')
                            ->disk('local')
                            ->directory('reports')
                            ->visibility('private')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize((int) config('sip2m.uploads.report_max_kb'))
                            ->required(),

                        Forms\Components\Textarea::make('ringkasan')
                            ->label('Ringkasan capaian')
                            ->rows(4)
                            ->maxLength(3000),
                    ])
                    ->action(function (array $data) use ($proposal, $user): void {
                        app(SubmitMonitoringReport::class)(
                            $proposal,
                            $user,
                            ReportType::from($data['jenis']),
                            $data['file_laporan'],
                            $data['ringkasan'] ?? null,
                        );
                        Notification::make()->title('Laporan terkirim')->success()->send();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('unduh')
                    ->label('Unduh')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (MonitoringReport $record): string => route('download.report', $record))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('tanggal_submit', 'desc');
    }
}
