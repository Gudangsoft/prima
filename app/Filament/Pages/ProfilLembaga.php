<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\Role as RoleEnum;
use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Support\Settings;
use Filament\Pages\Page;

/**
 * "Profil Lembaga" (grup Data Pendukung) — identitas lembaga & pimpinan,
 * dipisah per kategori (Penelitian / Pengabdian) karena keduanya bisa punya
 * lembaga dan pimpinan yang berbeda. Data dikelola di Pengaturan > Pengaturan Web.
 */
class ProfilLembaga extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationGroup = 'Data Pendukung';

    protected static ?string $navigationLabel = 'Profil Lembaga';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Profil Lembaga Penelitian / Pengabdian kepada Masyarakat';

    protected static string $view = 'filament.pages.profil-lembaga';

    /** Tab aktif: penelitian | pengabdian. */
    public string $tab = 'penelitian';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['admin_lppm', 'pimpinan', 'super_admin']) ?? false;
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, Settings::KATEGORI, true) ? $tab : 'penelitian';
    }

    public function getViewData(): array
    {
        $kategori = in_array($this->tab, Settings::KATEGORI, true) ? $this->tab : 'penelitian';
        $i = Settings::institution($kategori);
        $pimpinan = Settings::pimpinanFor($kategori);
        $user = auth()->user();

        $labelKategori = $kategori === 'pengabdian' ? 'Pengabdian kepada Masyarakat' : 'Penelitian';

        $pimpinanUser = User::query()->role(RoleEnum::Pimpinan->value)->orderBy('id')->first();

        return [
            'tab' => $kategori,
            'labelKategori' => $labelKategori,
            'lembaga' => [
                'Kode PT' => $i['kode_pt'] ?: '-',
                'Nama PT' => $i['nama'] ?: '-',
                'Klaster' => $i['klaster'] ?: '-',
                'Nomor SK Pendirian Lembaga' => $i['sk_pendirian'] ?: '-',
                'Nama Lembaga' => $i['nama_lembaga'] ?: '-',
                'Alamat Lembaga' => $i['alamat'] ?: '-',
                'No Telepon' => $i['telepon'] ?: '-',
                'No Fax' => $i['fax'] ?: '-',
                'Email' => $i['email'] ?: '-',
                'Website' => $i['website'] ?: '-',
                'Nama Jabatan Pimpinan' => $i['jabatan_pimpinan'] ?: '-',
            ],
            'pimpinan' => [
                'Nama Jabatan' => $pimpinan['jabatan'],
                'NIDN Pimpinan' => $pimpinan['nidn'],
                'Nama' => mb_strtoupper($pimpinan['nama']),
            ],
            'canEditLembaga' => $user->hasRole(RoleEnum::SuperAdmin->value),
            'editLembagaUrl' => PengaturanWeb::getUrl(),
            'canEditPimpinan' => $pimpinanUser !== null && $user->can('update', $pimpinanUser),
            'editPimpinanUrl' => $pimpinanUser
                ? UserResource::getUrl('edit', ['record' => $pimpinanUser])
                : PengaturanWeb::getUrl(),
        ];
    }
}
