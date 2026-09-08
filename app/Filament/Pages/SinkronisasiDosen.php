<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\Role as RoleEnum;
use App\Filament\Imports\DosenImporter;
use App\Filament\Resources\UserResource;
use App\Models\ProgramStudi;
use App\Models\User;
use App\Support\Settings;
use Filament\Actions\Action;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
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

    #[Url]
    public ?int $prodi = null;

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

    public function getProdiAktifProperty(): ?ProgramStudi
    {
        return $this->prodi ? ProgramStudi::find($this->prodi) : null;
    }

    public function hapusFilterProdi(): void
    {
        $this->prodi = null;
        $this->resetPage();
    }

    /** @return LengthAwarePaginator<int, User> */
    public function getDosenProperty(): LengthAwarePaginator
    {
        $kolom = match ($this->berdasarkan) {
            'nidn' => 'nidn',
            'nuptk' => 'nuptk',
            default => 'name',
        };

        return User::query()
            ->role(RoleEnum::Dosen->value)
            ->when($this->cari !== '', fn ($q) => $q->where($kolom, 'like', '%'.$this->cari.'%'))
            ->when($this->prodi, fn ($q) => $q->where('program_studi_id', $this->prodi))
            ->with('programStudi')
            ->orderBy('name')
            ->paginate($this->perHalaman);
    }

    public static function urlUntukProdi(int $prodiId): string
    {
        return static::getUrl(['prodi' => $prodiId]);
    }

    public function editUrl(User $dosen): string
    {
        return UserResource::getUrl('edit', ['record' => $dosen]);
    }

    /**
     * Modal "Detail Profil" gaya BIMA: ringkasan data PDDIKTI & SINTA (baca
     * saja, dari data lokal — aplikasi ini tidak terhubung ke API PDDIKTI/
     * SINTA nasional, jadi tak ada tombol sinkron langsung) plus form ringkas
     * untuk memutakhirkan kontak dosen.
     */
    public function detailDosenAction(): Action
    {
        return Action::make('detailDosen')
            ->label('Detail')
            ->modalHeading('Detail Profil')
            ->modalWidth('3xl')
            ->modalSubmitActionLabel('Submit form')
            ->modalContent(fn (array $arguments) => view('filament.pages.partials.detail-dosen', [
                'dosen' => User::findOrFail($arguments['dosen']),
                'institusi' => $this->institusi,
            ]))
            ->fillForm(fn (array $arguments): array => User::findOrFail($arguments['dosen'])
                ->only(['email', 'phone_number', 'telepon']))
            ->form([
                TextInput::make('email')
                    ->label('Alamat Surel')
                    ->email()
                    ->required(),
                TextInput::make('phone_number')
                    ->label('Nomor Hp')
                    ->tel()
                    ->maxLength(30),
                TextInput::make('telepon')
                    ->label('Nomor Telepon')
                    ->tel()
                    ->maxLength(30),
            ])
            ->action(function (array $arguments, array $data): void {
                $dosen = User::findOrFail($arguments['dosen']);

                if (User::where('email', $data['email'])->where('id', '!=', $dosen->id)->exists()) {
                    Notification::make()->title('Alamat surel sudah dipakai akun lain.')->danger()->send();

                    throw new Halt();
                }

                $dosen->update($data);

                Notification::make()->title('Kontak dosen diperbarui')->success()->send();
            });
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
                    fn () => print ("nuptk,nidn,name,gelar_depan,gelar_belakang,sinta_id,pendidikan_terakhir,sinta_score_overall_v2,sinta_score_3yr_v2,sinta_score_overall_v3,sinta_score_3yr_v3,phone_number,jabatan,kompetensi,kode_prodi\n"
                        .",0401019001,BUDI SANTOSO,Dr,\"S.Kom, M.Kom\",257669,S2,771.5,391.5,1123.87,620.2,081234567890,Lektor,\"Rekayasa Perangkat Lunak\",55201\n"),
                    'template-dosen.csv',
                    ['Content-Type' => 'text/csv'],
                )),
        ];
    }
}
