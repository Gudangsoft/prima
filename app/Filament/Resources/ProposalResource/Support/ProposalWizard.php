<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProposalResource\Support;

use App\Enums\BidangFokus;
use App\Enums\MemberType;
use App\Models\ProposalScheme;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Builder;

/**
 * Definisi wizard usulan gaya BIMA (Identitas, Anggota, Substansi, Target
 * Luaran, 8 Bidang Strategis, RAB, Mitra, Berkas). Dipakai oleh
 * ProposalResource::form() untuk create maupun edit.
 */
final class ProposalWizard
{
    /** @return array<int, Forms\Components\Wizard\Step> */
    public static function steps(): array
    {
        return [
            self::identitas(),
            self::anggota(),
            self::substansi(),
            self::targetLuaran(),
            self::bidangStrategis(),
            self::rab(),
            self::mitra(),
            self::berkas(),
        ];
    }

    private static function identitas(): Forms\Components\Wizard\Step
    {
        return Forms\Components\Wizard\Step::make('Identitas Usulan')
            ->icon('heroicon-o-identification')
            ->columns(2)
            ->schema([
                Forms\Components\TextInput::make('kelompok_skema')
                    ->label('Kelompok Skema')
                    ->maxLength(120)
                    ->placeholder('mis. Riset Dasar'),

                Forms\Components\Select::make('scheme_id')
                    ->label('Ruang Lingkup (Skema)')
                    ->required()
                    ->searchable()
                    ->native(false)
                    ->live()
                    ->default(fn ($livewire): ?int => property_exists($livewire, 'scheme') ? $livewire->scheme : null)
                    ->hintAction(
                        Forms\Components\Actions\Action::make('unduhTemplateSkema')
                            ->label('Unduh Template')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->url(fn (Get $get): ?string => ProposalScheme::find($get('scheme_id'))?->templateUrl())
                            ->openUrlInNewTab()
                            ->visible(fn (Get $get): bool => filled(ProposalScheme::find($get('scheme_id'))?->template_path)),
                    )
                    ->options(function ($livewire): array {
                        // Dari menu Penelitian/Pengabdian dosen: kunci kategori.
                        $kat = property_exists($livewire, 'kat') ? $livewire->kat : null;

                        return ProposalScheme::query()
                            ->aktif()
                            ->when(
                                in_array($kat, ['penelitian', 'pengabdian'], true),
                                fn (Builder $q) => $q->where('kategori', $kat),
                            )
                            ->orderBy('nama_skema')
                            ->get()
                            ->mapWithKeys(fn (ProposalScheme $s): array => [
                                $s->id => "{$s->nama_skema} — {$s->kategori->label()}",
                            ])
                            ->all();
                    }),

                Forms\Components\Select::make('bidang_fokus')
                    ->label('Bidang Fokus')
                    ->options(BidangFokus::options())
                    ->native(false)
                    ->searchable(),

                Forms\Components\TextInput::make('rumpun_ilmu')
                    ->label('Rumpun Ilmu (Level 3)')
                    ->maxLength(150),

                Forms\Components\TextInput::make('tema')
                    ->label('Tema Penelitian')
                    ->maxLength(200),

                Forms\Components\TextInput::make('topik')
                    ->label('Topik Penelitian')
                    ->maxLength(200),

                Forms\Components\TextInput::make('makro_riset')
                    ->label('Nama Makro Riset')
                    ->maxLength(200)
                    ->placeholder('mis. Kelompok Riset teknologi tinggi'),

                Forms\Components\Select::make('target_tkt')
                    ->label('Target TKT')
                    ->options(array_combine(range(1, 9), range(1, 9)))
                    ->native(false),

                Forms\Components\Select::make('lama_kegiatan')
                    ->label('Lama Kegiatan')
                    ->options([1 => '1 Tahun', 2 => '2 Tahun', 3 => '3 Tahun'])
                    ->default(1)
                    ->required()
                    ->native(false),

                Forms\Components\Select::make('tahun_usulan')
                    ->label('Tahun Usulan')
                    ->options(self::yearOptions())
                    ->default((int) now()->year)
                    ->native(false),

                Forms\Components\Select::make('tahun_anggaran')
                    ->label('Tahun Pelaksanaan')
                    ->options(self::yearOptions())
                    ->default((int) now()->year + 1)
                    ->required()
                    ->native(false),
            ]);
    }

    private static function anggota(): Forms\Components\Wizard\Step
    {
        return Forms\Components\Wizard\Step::make('Anggota Tim')
            ->icon('heroicon-o-user-group')
            ->schema([
                Forms\Components\Repeater::make('members')
                    ->relationship()
                    ->label('Anggota')
                    ->addActionLabel('Tambah anggota')
                    ->itemLabel(fn (array $state): ?string => $state['nama'] ?? null)
                    ->columns(2)
                    ->defaultItems(0)
                    ->collapsible()
                    ->schema([
                        Forms\Components\Select::make('jenis')
                            ->label('Jenis anggota')
                            ->options(MemberType::options())
                            ->default(MemberType::Dosen->value)
                            ->required()
                            ->live()
                            ->native(false),

                        Forms\Components\Select::make('user_id')
                            ->label('Dosen (akun terdaftar)')
                            ->visible(fn (Get $get): bool => $get('jenis') === MemberType::Dosen->value)
                            ->required(fn (Get $get): bool => $get('jenis') === MemberType::Dosen->value)
                            ->searchable()
                            ->native(false)
                            ->options(fn ($livewire): array => User::query()
                                ->role('dosen')
                                ->where('id', '!=', $livewire->record?->user_id ?? auth()->id())
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->live()
                            ->afterStateUpdated(function (?string $state, Forms\Set $set): void {
                                $u = $state ? User::with('programStudi')->find($state) : null;
                                $set('nama', $u?->name);
                                $set('identitas_no', $u?->nidn);
                                $set('institusi', $u?->unit_kerja ?: config('sip2m.institution.penelitian.nama_lembaga'));
                                $set('prodi', $u?->programStudi?->nama);
                            }),

                        Forms\Components\TextInput::make('nama')
                            ->label('Nama')
                            ->required()
                            ->maxLength(150),

                        Forms\Components\TextInput::make('identitas_no')
                            ->label('NIDN / NIM / No. Identitas')
                            ->maxLength(50),

                        Forms\Components\TextInput::make('institusi')
                            ->label('Institusi')
                            ->maxLength(150),

                        Forms\Components\TextInput::make('prodi')
                            ->label('Program Studi')
                            ->maxLength(150),

                        Forms\Components\Select::make('jenjang')
                            ->label('Jenjang Pendidikan')
                            ->options(['S1' => 'S1', 'S2' => 'S2', 'S3' => 'S3'])
                            ->visible(fn (Get $get): bool => $get('jenis') !== MemberType::Dosen->value)
                            ->native(false),

                        Forms\Components\Textarea::make('tugas')
                            ->label('Uraian Tugas')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function substansi(): Forms\Components\Wizard\Step
    {
        return Forms\Components\Wizard\Step::make('Substansi')
            ->icon('heroicon-o-pencil-square')
            ->schema([
                Forms\Components\TextInput::make('judul')
                    ->label('Judul usulan')
                    ->required()
                    ->maxLength(500)
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('abstrak')
                    ->label('Abstrak')
                    ->required()
                    ->minLength(100)
                    ->maxLength(5000)
                    ->rows(8)
                    ->columnSpanFull()
                    ->helperText('Minimal 100 karakter.'),

                Forms\Components\FileUpload::make('substansi_file')
                    ->label('Berkas Substansi (PDF)')
                    ->disk('local')->directory('proposals/substansi')->visibility('private')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize((int) config('sip2m.uploads.proposal_max_kb'))
                    ->downloadable()->previewable(false)
                    ->columnSpanFull(),
            ]);
    }

    private static function targetLuaran(): Forms\Components\Wizard\Step
    {
        return Forms\Components\Wizard\Step::make('Target Luaran')
            ->icon('heroicon-o-flag')
            ->schema([
                Forms\Components\Repeater::make('outputTargets')
                    ->relationship()
                    ->label('Target luaran per tahun')
                    ->addActionLabel('Tambah target luaran')
                    ->columns(2)
                    ->defaultItems(0)
                    ->schema([
                        Forms\Components\Select::make('tahun_ke')
                            ->label('Urutan Tahun')
                            ->options([1 => 'Tahun ke-1', 2 => 'Tahun ke-2', 3 => 'Tahun ke-3'])
                            ->default(1)->required()->native(false),

                        Forms\Components\TextInput::make('kelompok_luaran')
                            ->label('Kelompok Luaran')->required()->maxLength(150)
                            ->placeholder('mis. Artikel di Jurnal'),

                        Forms\Components\TextInput::make('jenis_luaran')
                            ->label('Jenis Luaran')->required()->maxLength(200)
                            ->placeholder('mis. Artikel di Jurnal Bereputasi Internasional'),

                        Forms\Components\TextInput::make('target')
                            ->label('Target')->maxLength(150)
                            ->placeholder('mis. Accepted/Published'),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')->rows(2)->columnSpanFull(),
                    ]),
            ]);
    }

    private static function bidangStrategis(): Forms\Components\Wizard\Step
    {
        return Forms\Components\Wizard\Step::make('8 Bidang Strategis')
            ->icon('heroicon-o-squares-2x2')
            ->schema([
                Forms\Components\Repeater::make('strategicFields')
                    ->relationship()
                    ->label('Bidang strategis')
                    ->addActionLabel('Tambah bidang strategis')
                    ->defaultItems(0)
                    ->schema([
                        Forms\Components\TextInput::make('bidang')
                            ->label('Bidang')->required()->maxLength(200),
                        Forms\Components\Textarea::make('rumusan_masalah')
                            ->label('Rumusan Masalah')->rows(3),
                        Forms\Components\Textarea::make('uraian_kegiatan')
                            ->label('Uraian Kegiatan')->rows(3),
                    ]),
            ]);
    }

    private static function rab(): Forms\Components\Wizard\Step
    {
        return Forms\Components\Wizard\Step::make('RAB')
            ->icon('heroicon-o-calculator')
            ->schema([
                Forms\Components\Placeholder::make('rab_total')
                    ->label('Total Anggaran yang diajukan')
                    ->content(fn (Get $get): string => 'Rp '.number_format(
                        collect($get('rabItems'))->sum(
                            fn ($i): float => (float) ($i['harga_satuan'] ?? 0) * (float) ($i['volume'] ?? 0),
                        ),
                        0, ',', '.',
                    )),

                Forms\Components\Repeater::make('rabItems')
                    ->relationship()
                    ->label('Rincian anggaran')
                    ->addActionLabel('Tambah item RAB')
                    ->columns(3)
                    ->defaultItems(0)
                    ->itemLabel(fn (array $state): ?string => $state['item'] ?? null)
                    ->collapsible()
                    ->schema([
                        Forms\Components\Select::make('tahun_ke')
                            ->label('Tahun Ke')
                            ->options([1 => 'Tahun ke-1', 2 => 'Tahun ke-2', 3 => 'Tahun ke-3'])
                            ->default(1)->required()->native(false),
                        Forms\Components\TextInput::make('kelompok')
                            ->label('Kelompok')->required()->maxLength(120)
                            ->placeholder('mis. Bahan'),
                        Forms\Components\TextInput::make('komponen')
                            ->label('Komponen')->required()->maxLength(150),
                        Forms\Components\TextInput::make('item')
                            ->label('Item')->required()->maxLength(200)->columnSpan(2),
                        Forms\Components\TextInput::make('satuan')
                            ->label('Satuan')->required()->maxLength(40)
                            ->placeholder('mis. Unit, OJ, OH, Paket'),
                        Forms\Components\TextInput::make('harga_satuan')
                            ->label('Harga Satuan')->numeric()->minValue(0)->required()
                            ->prefix('Rp')->live(onBlur: true),
                        Forms\Components\TextInput::make('volume')
                            ->label('Volume')->numeric()->minValue(0)->required()
                            ->live(onBlur: true),
                        Forms\Components\Placeholder::make('subtotal')
                            ->label('Total')
                            ->content(fn (Get $get): string => 'Rp '.number_format(
                                (float) ($get('harga_satuan') ?? 0) * (float) ($get('volume') ?? 0),
                                0, ',', '.',
                            )),
                    ]),
            ]);
    }

    private static function mitra(): Forms\Components\Wizard\Step
    {
        return Forms\Components\Wizard\Step::make('Mitra')
            ->icon('heroicon-o-building-office-2')
            ->schema([
                Forms\Components\Repeater::make('partners')
                    ->relationship()
                    ->label('Mitra (opsional)')
                    ->addActionLabel('Tambah mitra')
                    ->columns(2)
                    ->defaultItems(0)
                    ->itemLabel(fn (array $state): ?string => $state['nama_mitra'] ?? null)
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('nama_mitra')->label('Nama Mitra')->required()->maxLength(150),
                        Forms\Components\TextInput::make('institusi')->label('Institusi')->maxLength(150),
                        Forms\Components\Textarea::make('alamat')->label('Alamat Institusi')->rows(2)->columnSpanFull(),
                        Forms\Components\TextInput::make('negara')->label('Negara')->default('Indonesia')->maxLength(80),
                        Forms\Components\TextInput::make('surel')->label('Surel')->email()->maxLength(150),
                        Forms\Components\TextInput::make('dana')->label('Kontribusi Dana')->numeric()->minValue(0)->prefix('Rp')->default(0),
                        Forms\Components\FileUpload::make('surat_kesanggupan')
                            ->label('Surat Kesanggupan (PDF)')
                            ->disk('local')->directory('proposals/mitra')->visibility('private')
                            ->acceptedFileTypes(['application/pdf'])->maxSize(10240)
                            ->downloadable()->previewable(false)->columnSpanFull(),
                    ]),
            ]);
    }

    private static function berkas(): Forms\Components\Wizard\Step
    {
        return Forms\Components\Wizard\Step::make('Berkas & Kirim')
            ->icon('heroicon-o-paper-clip')
            ->schema([
                Forms\Components\FileUpload::make('file_proposal')
                    ->label('File Proposal Lengkap (PDF)')
                    ->disk('local')->directory('proposals')->visibility('private')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize((int) config('sip2m.uploads.proposal_max_kb'))
                    ->downloadable()->previewable(false)
                    ->helperText('Format PDF, maksimal '.number_format((int) config('sip2m.uploads.proposal_max_kb') / 1024, 0).' MB. '
                        .'Boleh dikosongkan saat menyimpan draf; wajib diisi sebelum mengirim usulan.'),
            ]);
    }

    /** @return array<int, string> */
    private static function yearOptions(): array
    {
        return collect(range((int) now()->year - 1, (int) now()->year + 3))
            ->mapWithKeys(fn (int $y): array => [$y => (string) $y])
            ->all();
    }
}
