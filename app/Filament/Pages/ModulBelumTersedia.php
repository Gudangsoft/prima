<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;

/**
 * Placeholder untuk menu BIMA yang belum punya modul di SIP2M
 * (Konsorsium, Prototipe, Kekayaan Intelektual).
 */
class ModulBelumTersedia extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'modul-belum-tersedia';

    protected static string $view = 'filament.pages.modul-belum-tersedia';

    private const MODUL = [
        'konsorsium' => 'Konsorsium',
        'prototipe' => 'Prototipe',
        'kekayaan-intelektual' => 'Kekayaan Intelektual',
    ];

    #[Url]
    public string $modul = 'konsorsium';

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public static function urlFor(string $modul): string
    {
        return static::getUrl(['modul' => $modul]);
    }

    public function mount(): void
    {
        if (! array_key_exists($this->modul, self::MODUL)) {
            $this->modul = 'konsorsium';
        }
    }

    public function getTitle(): string
    {
        return self::MODUL[$this->modul];
    }

    public function getModulLabel(): string
    {
        return self::MODUL[$this->modul] ?? Str::headline($this->modul);
    }
}
