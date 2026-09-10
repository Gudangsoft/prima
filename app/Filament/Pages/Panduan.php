<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Support\Settings;
use Filament\Pages\Page;

/**
 * "Buku Panduan": panduan penggunaan aplikasi untuk dosen/pengusul dan untuk
 * Admin LPPM. Isi ringkas tersedia langsung di halaman; Super Admin bisa
 * mengunggah PDF panduan lengkap lewat Pengaturan Web.
 */
class Panduan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Buku Panduan';

    protected static ?int $navigationSort = 90;

    protected static ?string $title = 'Buku Panduan';

    protected static string $view = 'filament.pages.panduan';

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    protected function getViewData(): array
    {
        return [
            'bolehLihatAdmin' => auth()->user()?->isActingAs(['admin_lppm', 'pimpinan', 'super_admin']) ?? false,
            'pdfPengguna' => Settings::optionalFileUrl('panduan_pengguna_path'),
            'pdfAdmin' => Settings::optionalFileUrl('panduan_admin_path'),
        ];
    }
}
