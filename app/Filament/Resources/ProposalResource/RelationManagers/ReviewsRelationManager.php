<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProposalResource\RelationManagers;

use App\Actions\Proposal\AssignReviewers;
use App\Actions\Proposal\RecordReview;
use App\Enums\ReviewRecommendation;
use App\Filament\Resources\ProposalResource\Support\WorkflowForms;
use App\Models\Proposal;
use App\Models\ProposalReview;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Penugasan & hasil penilaian reviewer untuk satu usulan.
 * Admin LPPM menugaskan; reviewer yang bersangkutan mengisi penilaiannya.
 */
class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    protected static ?string $title = 'Penilaian Reviewer';

    protected static ?string $icon = 'heroicon-o-clipboard-document-check';

    public function isReadOnly(): bool
    {
        return true; // matikan aksi CRUD bawaan; pakai aksi kustom di bawah.
    }

    public function table(Table $table): Table
    {
        /** @var Proposal $proposal */
        $proposal = $this->getOwnerRecord();
        $user = auth()->user();

        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('reviewer.name')->label('Reviewer'),

                Tables\Columns\TextColumn::make('skor')
                    ->label('Skor')
                    ->alignCenter()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('rekomendasi')
                    ->label('Rekomendasi')
                    ->badge()
                    ->formatStateUsing(fn (?ReviewRecommendation $state): string => $state?->label() ?? 'Belum ada')
                    ->color(fn (?ReviewRecommendation $state): string => $state?->color() ?? 'gray'),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Dinilai pada')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Belum dinilai'),

                Tables\Columns\TextColumn::make('catatan')
                    ->label('Catatan')
                    ->wrap()
                    ->limit(60)
                    ->placeholder('—'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('tugaskan')
                    ->label('Tugaskan Reviewer')
                    ->icon('heroicon-o-user-plus')
                    ->visible(fn (): bool => $user->can('assignReviewer', $proposal))
                    ->form(WorkflowForms::penugasanReviewer())
                    ->action(function (array $data) use ($proposal, $user): void {
                        app(AssignReviewers::class)($proposal, $user, array_map('intval', $data['reviewers']));
                        Notification::make()->title('Reviewer ditugaskan')->success()->send();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('isiPenilaian')
                    ->label('Isi Penilaian')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->visible(fn (ProposalReview $record): bool => $record->reviewer_id === $user->getKey()
                        && ! $record->isSubmitted()
                        && $user->can('review', $proposal))
                    ->fillForm(fn (ProposalReview $record): array => [
                        'skor' => $record->skor,
                        'rekomendasi' => $record->rekomendasi?->value,
                        'catatan' => $record->catatan,
                    ])
                    ->form(WorkflowForms::penilaianReviewer())
                    ->action(function (ProposalReview $record, array $data) use ($proposal, $user): void {
                        app(RecordReview::class)(
                            $proposal,
                            $user,
                            (int) $data['skor'],
                            ReviewRecommendation::from($data['rekomendasi']),
                            $data['catatan'] ?? null,
                        );
                        Notification::make()->title('Penilaian tersimpan')->success()->send();
                    }),
            ]);
    }
}
