<?php

declare(strict_types=1);

namespace App\Enums;

/** Bidang fokus RIRN pada usulan. */
enum BidangFokus: string
{
    case PanganPertanian = 'pangan_pertanian';
    case EnergiEbt = 'energi_ebt';
    case KesehatanObat = 'kesehatan_obat';
    case Transportasi = 'transportasi';
    case Tik = 'tik';
    case Hankam = 'hankam';
    case MaterialMaju = 'material_maju';
    case Kemaritiman = 'kemaritiman';
    case Kebencanaan = 'kebencanaan';
    case SosialHumaniora = 'sosial_humaniora';
    case Multidisiplin = 'multidisiplin';

    public function label(): string
    {
        return match ($this) {
            self::PanganPertanian => 'Pangan - Pertanian',
            self::EnergiEbt => 'Energi - Energi Baru dan Terbarukan',
            self::KesehatanObat => 'Kesehatan - Obat',
            self::Transportasi => 'Transportasi',
            self::Tik => 'Teknologi Informasi dan Komunikasi',
            self::Hankam => 'Pertahanan dan Keamanan',
            self::MaterialMaju => 'Material Maju',
            self::Kemaritiman => 'Kemaritiman',
            self::Kebencanaan => 'Kebencanaan',
            self::SosialHumaniora => 'Sosial Humaniora, Seni Budaya, Pendidikan',
            self::Multidisiplin => 'Multidisiplin dan Lintas Sektoral',
        };
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->label()])->all();
    }
}
