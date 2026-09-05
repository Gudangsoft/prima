<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Status usulan sekaligus state machine-nya.
 *
 * Alur happy-path:
 *   draft -> submitted -> approved_lppm -> under_review -> funded
 *         -> in_progress -> reported -> output_validated
 *
 * Cabang penolakan: submitted / approved_lppm / under_review -> rejected.
 * Cabang revisi   : submitted -> draft (dikembalikan admin);
 *                   reported -> in_progress (laporan diminta perbaikan).
 */
enum ProposalStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case ApprovedLppm = 'approved_lppm';
    case UnderReview = 'under_review';
    case Funded = 'funded';
    case Rejected = 'rejected';
    case InProgress = 'in_progress';
    case Reported = 'reported';
    case OutputValidated = 'output_validated';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Submitted => 'Diajukan',
            self::ApprovedLppm => 'Disetujui LPPM',
            self::UnderReview => 'Dalam Penilaian',
            self::Funded => 'Didanai',
            self::Rejected => 'Ditolak',
            self::InProgress => 'Pelaksanaan',
            self::Reported => 'Laporan Masuk',
            self::OutputValidated => 'Luaran Tervalidasi',
        };
    }

    /** Warna badge Filament. */
    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Submitted => 'info',
            self::ApprovedLppm => 'info',
            self::UnderReview => 'warning',
            self::Funded => 'success',
            self::Rejected => 'danger',
            self::InProgress => 'primary',
            self::Reported => 'warning',
            self::OutputValidated => 'success',
        };
    }

    /**
     * Peta transisi yang diizinkan.
     *
     * @return array<string, list<self>>
     */
    public static function map(): array
    {
        return [
            self::Draft->value => [self::Submitted],
            self::Submitted->value => [self::ApprovedLppm, self::Rejected, self::Draft],
            self::ApprovedLppm->value => [self::UnderReview, self::Rejected],
            self::UnderReview->value => [self::Funded, self::Rejected],
            self::Funded->value => [self::InProgress],
            self::Rejected->value => [],
            self::InProgress->value => [self::Reported],
            self::Reported->value => [self::OutputValidated, self::InProgress],
            self::OutputValidated->value => [],
        ];
    }

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return self::map()[$this->value] ?? [];
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public function isTerminal(): bool
    {
        return $this->allowedTransitions() === [];
    }

    /** @return array<string,string> value => label untuk komponen Select/filter. */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $s) => [$s->value => $s->label()])
            ->all();
    }
}
