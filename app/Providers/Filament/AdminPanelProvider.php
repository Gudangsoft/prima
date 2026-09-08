<?php

namespace App\Providers\Filament;

use App\Filament\Auth\EditProfile;
use App\Filament\Auth\Login;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Kegiatan;
use App\Filament\Pages\ModulBelumTersedia;
use App\Http\Middleware\EnsureOtpVerified;
use App\Models\ProposalScheme;
use App\Support\Settings;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $primary = (string) Settings::get('primary_color', '#3B5BD9');

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName((string) Settings::get('app_name', 'SIP2M'))
            ->brandLogo($this->assetOrDefault('logo_path', 'images/logo-sip2m.svg'))
            ->brandLogoHeight('3rem')
            ->favicon($this->assetOrDefault('favicon_path', 'images/favicon.svg'))
            ->login(Login::class)
            ->passwordReset()
            ->profile(EditProfile::class, isSimple: false)
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->font('Nunito Sans')
            ->colors([
                'primary' => Color::hex($primary),
                'info' => Color::hex($primary),
                'gray' => Color::Slate,
            ])
            ->topNavigation()
            ->maxContentWidth('full')
            ->navigationGroups([
                // Menu dosen/pengusul (gaya BIMA) — grup kosong otomatis disembunyikan
                // untuk peran pengawas.
                NavigationGroup::make('Penelitian')->icon('heroicon-o-magnifying-glass'),
                NavigationGroup::make('Pengabdian')->icon('heroicon-o-document-text'),
                NavigationGroup::make('Konsorsium')->icon('heroicon-o-share'),
                NavigationGroup::make('Prototipe')->icon('heroicon-o-cube'),
                NavigationGroup::make('Kekayaan Intelektual')->icon('heroicon-o-lock-closed'),

                NavigationGroup::make('Data Pendukung'),
                NavigationGroup::make('Monitoring'),
                NavigationGroup::make('Pengelolaan Reviewer'),
                NavigationGroup::make('Pengaturan'),
            ])
            ->navigationItems($this->dosenNavigationItems())
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => <<<'HTML'
                    <style>
                        /* Latar konten abu-abu muda seperti BIMA. */
                        .fi-main { background-color: #eef1f6; }
                        :is(.dark) .fi-main { background-color: rgb(17 24 39); }
                    </style>
                    HTML,
            )
            ->renderHook(
                PanelsRenderHook::BODY_START,
                fn (): string => Blade::render('<x-impersonation-banner />'),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
                fn (): string => Blade::render(
                    '<p class="text-center text-sm text-gray-500 dark:text-gray-400">{{ $note }}</p>',
                    ['note' => (string) Settings::get('login_note', 'Sistem Informasi Penelitian & Pengabdian — LPPM')],
                ),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): string => Blade::render(
                    '<p class="text-center text-xs text-gray-400">'
                    .'Butuh bantuan akun? Hubungi Admin LPPM.'
                    .'</p>',
                ),
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                EnsureOtpVerified::class,
            ]);
    }

    /**
     * Menu atas untuk dosen/pengusul (gaya BIMA): dropdown Penelitian & Pengabdian
     * yang fungsional, plus placeholder Konsorsium / Prototipe / Kekayaan Intelektual.
     *
     * @return array<int, NavigationItem>
     */
    private function dosenNavigationItems(): array
    {
        $isDosen = static fn (): bool => auth()->user()?->hasRole('dosen')
            && ! auth()->user()->hasAnyRole(['admin_lppm', 'pimpinan', 'super_admin']);

        $items = [];

        foreach (['Penelitian' => 'penelitian', 'Pengabdian' => 'pengabdian'] as $group => $kategori) {
            $sort = 0;

            // Gaya BIMA: submenu HANYA berisi skema aktif, satu item per skema ->
            // ke tabel Usulan skema itu (bisa lihat usulan yang sudah ada, lalu
            // "Ajukan Usulan Baru" dari sana) — bukan langsung ke form kosong.
            foreach (ProposalScheme::query()->aktif()->kategori($kategori)->orderBy('nama_skema')->get() as $skema) {
                $items[] = NavigationItem::make($skema->nama_skema)
                    ->group($group)->sort(++$sort)->visible($isDosen)
                    ->url(fn (): string => Kegiatan::urlFor($kategori, 'usulan', $skema->id))
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.pages.kegiatan')
                        && request()->input('kategori') === $kategori
                        && (int) request()->input('skema') === $skema->id);
            }
        }

        foreach (['Konsorsium' => 'konsorsium', 'Prototipe' => 'prototipe', 'Kekayaan Intelektual' => 'kekayaan-intelektual'] as $group => $modul) {
            $items[] = NavigationItem::make('Belum tersedia')
                ->group($group)->visible($isDosen)
                ->url(fn (): string => ModulBelumTersedia::urlFor($modul))
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.pages.modul-belum-tersedia')
                    && request()->input('modul') === $modul);
        }

        return $items;
    }

    /** URL berkas unggahan branding (disk public) atau aset bawaan (root-relative). */
    private function assetOrDefault(string $settingKey, string $default): string
    {
        $path = Settings::get($settingKey);

        if (filled($path)) {
            return Storage::disk('public')->url((string) $path);
        }

        return '/'.ltrim($default, '/');
    }
}
