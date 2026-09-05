<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

/**
 * Panel "Pengumuman" pada dasbor. Sumber: config('sip2m.announcements').
 */
class AnnouncementsWidget extends Widget
{
    protected static string $view = 'filament.widgets.announcements';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<int, array{tanggal: string, judul: string, isi: string}>
     */
    public function getAnnouncements(): array
    {
        return config('sip2m.announcements', []);
    }
}
