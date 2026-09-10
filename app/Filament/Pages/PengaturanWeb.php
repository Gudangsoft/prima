<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Support\Settings;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;

/**
 * "Pengaturan Web" (grup Pengaturan): branding situs & profil lembaga.
 * Tersimpan di tabel `settings`; hanya Super Admin.
 */
class PengaturanWeb extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Pengaturan Web';

    protected static ?int $navigationSort = 30;

    protected static ?string $title = 'Pengaturan Web';

    protected static string $view = 'filament.pages.pengaturan-web';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    /** Key setelan yang dikelola halaman ini. */
    private const KEYS = [
        'app_name', 'primary_color', 'logo_path', 'favicon_path', 'login_note',
        'panduan_pengguna_path', 'panduan_admin_path',
        'hero_title', 'hero_subtitle',
        'footer_lembaga', 'footer_deskripsi', 'footer_alamat', 'footer_email', 'footer_telepon', 'footer_copyright',
        'institusi_kode_pt', 'institusi_nama', 'institusi_klaster',
        // Lembaga Penelitian (pen_) & Pengabdian (pkm_)
        'pen_nama_lembaga', 'pen_sk_pendirian', 'pen_alamat', 'pen_telepon', 'pen_fax',
        'pen_email', 'pen_website', 'pen_jabatan_pimpinan', 'pen_pimpinan_nama', 'pen_pimpinan_nidn',
        'pkm_nama_lembaga', 'pkm_sk_pendirian', 'pkm_alamat', 'pkm_telepon', 'pkm_fax',
        'pkm_email', 'pkm_website', 'pkm_jabatan_pimpinan', 'pkm_pimpinan_nama', 'pkm_pimpinan_nidn',
    ];

    public static function canAccess(): bool
    {
        return auth()->user()?->isActingAs('super_admin') ?? false;
    }

    public function mount(): void
    {
        $state = [];
        foreach (self::KEYS as $key) {
            $state[$key] = Settings::get($key);
        }
        $this->form->fill($state);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Branding')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('app_name')
                            ->label('Nama aplikasi')->required()->maxLength(60),
                        Forms\Components\ColorPicker::make('primary_color')
                            ->label('Warna utama')->required(),
                        Forms\Components\FileUpload::make('logo_path')
                            ->label('Logo')
                            ->image()->disk('public')->directory('branding')
                            ->imagePreviewHeight('56')
                            ->helperText('Kosongkan untuk memakai logo bawaan. Disarankan PNG/SVG transparan.'),
                        Forms\Components\FileUpload::make('favicon_path')
                            ->label('Favicon')
                            ->image()->disk('public')->directory('branding')
                            ->imagePreviewHeight('40'),
                        Forms\Components\TextInput::make('login_note')
                            ->label('Catatan di halaman login')->maxLength(160)->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Buku Panduan')
                    ->description('PDF panduan lengkap yang bisa diunduh dari menu "Buku Panduan". Kosongkan untuk memakai panduan ringkas bawaan aplikasi.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\FileUpload::make('panduan_pengguna_path')
                            ->label('PDF Panduan Pengguna (Dosen)')
                            ->disk('public')->directory('panduan')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(20480)
                            ->downloadable()
                            ->previewable(false),
                        Forms\Components\FileUpload::make('panduan_admin_path')
                            ->label('PDF Panduan Admin LPPM')
                            ->disk('public')->directory('panduan')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(20480)
                            ->downloadable()
                            ->previewable(false),
                    ]),

                Forms\Components\Section::make('Halaman Publik')
                    ->columns(1)
                    ->schema([
                        Forms\Components\TextInput::make('hero_title')->label('Judul hero')->maxLength(160),
                        Forms\Components\Textarea::make('hero_subtitle')->label('Sub-judul hero')->rows(3)->maxLength(400),
                    ]),

                Forms\Components\Section::make('Footer Halaman Publik')
                    ->description('Tampil di kaki halaman depan (landing page).')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('footer_lembaga')
                            ->label('Nama lembaga (strip atas & footer)')->maxLength(160)->columnSpanFull(),
                        Forms\Components\Textarea::make('footer_deskripsi')
                            ->label('Deskripsi singkat')->rows(2)->maxLength(300)->columnSpanFull(),
                        Forms\Components\TextInput::make('footer_alamat')->label('Alamat kontak')->maxLength(200),
                        Forms\Components\TextInput::make('footer_email')->label('Email kontak')->email()->maxLength(120),
                        Forms\Components\TextInput::make('footer_telepon')->label('Telepon kontak')->maxLength(60),
                        Forms\Components\TextInput::make('footer_copyright')
                            ->label('Pemilik hak cipta')->maxLength(120)
                            ->helperText('Tahun berjalan ditambahkan otomatis, mis. "© 2026 <isian>".'),
                    ]),

                Forms\Components\Section::make('Identitas PT')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('institusi_kode_pt')->label('Kode PT')->maxLength(20),
                        Forms\Components\TextInput::make('institusi_nama')->label('Nama PT')->maxLength(255)->columnSpan(2),
                        Forms\Components\TextInput::make('institusi_klaster')->label('Klaster')->maxLength(100),
                    ]),

                Forms\Components\Section::make('Lembaga Penelitian')
                    ->description('Profil & pimpinan Lembaga Penelitian.')
                    ->collapsible()
                    ->schema(self::lembagaFields('pen')),

                Forms\Components\Section::make('Lembaga Pengabdian kepada Masyarakat')
                    ->description('Profil & pimpinan Lembaga Pengabdian. Kosongkan bila sama dengan Lembaga Penelitian.')
                    ->collapsible()
                    ->collapsed()
                    ->schema(self::lembagaFields('pkm')),
            ])
            ->statePath('data');
    }

    /** @return array<int, Component> */
    private static function lembagaFields(string $prefix): array
    {
        return [
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make("{$prefix}_nama_lembaga")->label('Nama lembaga')->maxLength(255)->columnSpanFull(),
                Forms\Components\TextInput::make("{$prefix}_sk_pendirian")->label('Nomor SK pendirian lembaga')->maxLength(100),
                Forms\Components\TextInput::make("{$prefix}_jabatan_pimpinan")->label('Nama jabatan pimpinan')->maxLength(100),
                Forms\Components\Textarea::make("{$prefix}_alamat")->label('Alamat lembaga')->rows(2)->maxLength(400)->columnSpanFull(),
                Forms\Components\TextInput::make("{$prefix}_telepon")->label('No. telepon')->maxLength(50),
                Forms\Components\TextInput::make("{$prefix}_fax")->label('No. fax')->maxLength(50),
                Forms\Components\TextInput::make("{$prefix}_email")->label('Email')->email()->maxLength(255),
                Forms\Components\TextInput::make("{$prefix}_website")->label('Website')->maxLength(255),
            ]),
            Forms\Components\Fieldset::make('Pimpinan Lembaga')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make("{$prefix}_pimpinan_nama")
                        ->label('Nama pimpinan')
                        ->maxLength(255)
                        ->helperText('Kosongkan untuk memakai akun pengguna berperan "Pimpinan".'),
                    Forms\Components\TextInput::make("{$prefix}_pimpinan_nidn")->label('NIDN pimpinan')->maxLength(30),
                ]),
        ];
    }

    public function simpan(): void
    {
        $data = $this->form->getState();

        Settings::setMany(array_intersect_key($data, array_flip(self::KEYS)));

        // Bersihkan cache view agar branding baru langsung terpakai.
        Artisan::call('view:clear');

        Notification::make()
            ->title('Pengaturan disimpan')
            ->body('Perubahan branding & profil lembaga sudah diterapkan.')
            ->success()
            ->send();
    }
}
