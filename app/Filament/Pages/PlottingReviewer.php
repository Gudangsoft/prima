<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Actions\Proposal\AssignReviewers;
use App\Enums\ProposalStatus;
use App\Filament\Pages\Concerns\OversightTablePage;
use App\Filament\Resources\ProposalResource;
use App\Filament\Resources\ProposalResource\Support\WorkflowForms;
use App\Models\Proposal;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * "Plotting Reviewer" (grup Pengelolaan Reviewer): usulan yang siap / sedang
 * dinilai beserta reviewer yang ditugaskan, dengan aksi penugasan.
 */
class PlottingReviewer extends OversightTablePage
{
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'Pengelolaan Reviewer';

    protected static ?string $navigationLabel = 'Plotting Reviewer';

    protected static ?int $navigationSort = 20;

    protected static ?string $title = 'Plotting Reviewer';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Proposal::query()
                    ->with(['submitter', 'reviews.reviewer'])
                    ->withCount([
                        'reviews',
                        'reviews as reviews_submitted_count' => fn ($q) => $q->whereNotNull('submitted_at'),
                    ])
                    ->whereIn('status', [ProposalStatus::ApprovedLppm->value, ProposalStatus::UnderReview->value]),
            )
            ->columns([
                Tables\Columns\TextColumn::make('judul')->label('Judul')->limit(55)->wrap()->searchable(),
                Tables\Columns\TextColumn::make('submitter.name')->label('Pengusul')->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')->badge()
                    ->formatStateUsing(fn (ProposalStatus $state): string => $state->label())
                    ->color(fn (ProposalStatus $state): string => $state->color()),
                Tables\Columns\TextColumn::make('reviews')
                    ->label('Reviewer Ditugaskan')
                    ->badge()
                    ->getStateUsing(fn (Proposal $record): array => $record->reviews->map(fn ($rev) => $rev->reviewer?->name ?? '-')->all())
                    ->placeholder('Belum ada'),
                Tables\Columns\TextColumn::make('reviews_submitted_count')
                    ->label('Penilaian Masuk')
                    ->formatStateUsing(fn ($state, Proposal $record): string => "{$state} / {$record->reviews_count}")
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    ProposalStatus::ApprovedLppm->value => ProposalStatus::ApprovedLppm->label(),
                    ProposalStatus::UnderReview->value => ProposalStatus::UnderReview->label(),
                ]),
                Tables\Filters\Filter::make('belum_ada_reviewer')
                    ->label('Belum ada reviewer')
                    ->query(fn ($q) => $q->doesntHave('reviews')),
            ])
            ->actions([
                Tables\Actions\Action::make('tugaskan')
                    ->label('Tugaskan Reviewer')
                    ->icon('heroicon-o-user-plus')
                    ->color('warning')
                    ->visible(fn (Proposal $record): bool => auth()->user()->can('assignReviewer', $record))
                    ->form(WorkflowForms::penugasanReviewer())
                    ->action(function (Proposal $record, array $data): void {
                        app(AssignReviewers::class)($record, auth()->user(), array_map('intval', $data['reviewers']));
                        Notification::make()->title('Reviewer ditugaskan')->success()->send();
                    }),

                Tables\Actions\Action::make('buka')
                    ->label('Detail')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Proposal $record): string => ProposalResource::getUrl('view', ['record' => $record])),
            ])
            ->defaultSort('updated_at', 'desc');
    }
}
