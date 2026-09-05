<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Announcement;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

/**
 * Panel "Pengumuman" pada dasbor. Sumber: tabel announcements (terbit).
 */
class AnnouncementsWidget extends Widget
{
    protected static string $view = 'filament.widgets.announcements';

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Announcement::query()->published()->exists();
    }

    /** @return Collection<int, Announcement> */
    public function getAnnouncements(): Collection
    {
        return Announcement::query()->published()->limit(6)->get();
    }
}
