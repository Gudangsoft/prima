<?php

declare(strict_types=1);

namespace App\Enums;

enum FundingStatus: string
{
    case Didanai = 'didanai';
    case DidanaiSebagian = 'didanai_sebagian';
    case TidakDidanai = 'tidak_didanai';

    public function label(): string
    {
        return match ($this) {
            self::Didanai => 'Didanai',
            self::DidanaiSebagian => 'Didanai Sebagian',
            self::TidakDidanai => 'Tidak Didanai',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Didanai => 'success',
            self::DidanaiSebagian => 'warning',
            self::TidakDidanai => 'danger',
        };
    }

    public function isFunded(): bool
    {
        return $this !== self::TidakDidanai;
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->label()])->all();
    }
}
