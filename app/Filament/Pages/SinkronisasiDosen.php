<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\Role as RoleEnum;
use App\Filament\Imports\DosenImporter;
use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Support\Settings;
use Filament\Actions\Action;
use Filament\Actions\ImportAction;
use Filament\Pages\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

/**
 * "Sinkronisasi Dosen" (grup Data Pendukung) — gaya BIMA: daftar dosen yang
 * bisa dicari, plus impor / pemutakhiran akun dari berkas CSV (pengganti
 * tarik data PDDIKTI langsung, karena aplikasi ini tidak terhubung ke PDDIKTI
 * nasional). Baris dicocokkan berdasarkan NIDN; akun baru otomatis berperan
 * "dosen".
 */
class SinkronisasiDosen extends Page
{
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-on-square-stack';

    protected static ?string $navigationGroup = 'Data Pendukung';

    protected static ?string $navigationLabel = 'Sinkronisasi Dosen';

    protected static ?int $navigationSort = 50;

    protected static ?string $title = 'Sinkronisasi Dosen';

    protected static string $view = 'filament.pages.sinkronisasi-dosen';

    #[Url]
    public string $cari = '';

    #[Url]
    public string $berdasarkan = 'nama';

    #[Url]
    public int $perHalaman = 10;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('create', User::class) ?? false;
    }

    public function updatingCari(): void
    {
        $this->resetPage();
    }

    public function getInstitusiProperty(): string
    {
        return Settings::institution()['nama'] ?: (string) Settings::get('app_name', 'SIP2M');
    }

    /** @return LengthAwarePaginator<int, User> */
    public function getDosenProperty(): LengthAwarePaginator
    {
        $kolom = $this->berdasarkan === 'nidn' ? 'nidn' : 'name';

        return User::query()
            ->role(RoleEnum::Dosen->value)
            ->when($this->cari !== '', fn ($q) => $q->where($kolom, 'like', '%'.$this->cari.'%'))
            ->with('programStudi')
            ->orderBy('name')
            ->paginate($this->perHalaman);
    }

    public function editUrl(User $dosen): string
    {
        return UserResource::getUrl('edit', ['record' => $dosen]);
    }

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make('imporDosen')
                ->label('Impor CSV Dosen')
                ->icon('heroicon-o-arrow-up-tray')
                ->importer(DosenImporter::class),

            Action::make('unduhTemplate')
                ->label('Unduh template')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(fn () => response()->streamDownload(
                    fn () => print ("nidn,name,gelar_depan,gelar_belakang,sinta_id,pendidikan_terakhir,sinta_score_overall_v2,sinta_score_3yr_v2,sinta_score_overall_v3,sinta_score_3yr_v3,phone_number,jabatan,kompetensi,kode_prodi\n"
                        ."0401019001,BUDI SANTOSO,Dr,\"S.Kom, M.Kom\",257669,S2,771.5,391.5,1123.87,620.2,081234567890,Lektor,\"Rekayasa Perangkat Lunak\",55201\n"),
                    'template-dosen.csv',
                    ['Content-Type' => 'text/csv'],
                )),
        ];
    }
}
