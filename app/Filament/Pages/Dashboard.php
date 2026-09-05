<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Dashboard';

    public function getColumns(): int|string|array
    {
        return [
            'default' => 1,
            'lg' => 3,
        ];
    }

    /** Daftar eksplisit — widget khusus halaman lain (mis. MonitoringUsulanStats) tidak ikut. */
    public function getWidgets(): array
    {
        return [
            Widgets\DashboardIdentity::class,

            // Dasbor dosen/pengusul (gaya BIMA) — masing-masing pakai trait ForDosen.
            Widgets\ProfilSayaCard::class,
            Widgets\UsulanSayaStats::class,
            Widgets\UndanganTimCard::class,
            Widgets\RiwayatUsulanCard::class,
            Widgets\UsulanSayaYearChart::class,

            // Dasbor pengawas (admin_lppm / pimpinan / super_admin).
            Widgets\MonitoringUsulanCard::class,
            Widgets\ProfilLembagaCard::class,
            Widgets\MonitoringPelaksanaanCard::class,
            Widgets\ProfilPimpinanCard::class,
            Widgets\ProposalsByStatusChart::class,
            Widgets\ProposalsBySchemeChart::class,
            Widgets\ProposalsByYearChart::class,

            Widgets\AnnouncementsWidget::class,
        ];
    }
}
