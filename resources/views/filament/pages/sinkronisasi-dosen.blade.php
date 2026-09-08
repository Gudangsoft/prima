<x-filament-panels::page>
    <x-filament::section icon="heroicon-o-information-circle" heading="Cara pakai">
        <ol style="margin:0;padding-left:18px;line-height:1.9;font-size:13px;color:#374151;">
            <li>Unduh <strong>template CSV</strong> (tombol di atas) atau ekspor data dosen dari SIAKAD/PDDIKTI.</li>
            <li>Kolom: <code>nidn</code>, <code>name</code>, <code>gelar_depan</code>, <code>gelar_belakang</code>,
                <code>sinta_id</code>, <code>pendidikan_terakhir</code>,
                <code>sinta_score_overall_v2</code>, <code>sinta_score_3yr_v2</code>,
                <code>sinta_score_overall_v3</code>, <code>sinta_score_3yr_v3</code>,
                <code>phone_number</code>, <code>jabatan</code>, <code>kompetensi</code>, <code>kode_prodi</code>.
                Hanya <code>nidn</code> dan <code>name</code> yang wajib — email tidak diminta lagi,
                akun baru otomatis diberi email placeholder karena dosen login memakai <strong>NIDN</strong>.
                <code>gelar_depan</code>/<code>gelar_belakang</code> otomatis digabung ke nama lengkap.</li>
            <li>Klik <strong>Impor CSV Dosen</strong>, unggah berkas, dan petakan kolom bila perlu.</li>
            <li>Baris dicocokkan berdasarkan <strong>NIDN</strong>: akun yang sudah ada
                <em>diperbarui</em>, yang belum ada <em>dibuat</em> dengan peran <strong>dosen</strong>
                dan kata sandi acak (dosen login pakai NIDN, ganti kata sandi lewat profil setelah masuk pertama kali).</li>
            <li><code>kode_prodi</code> ditautkan ke Program Studi bila kodenya sudah terdaftar
                (lihat menu <strong>Sinkronisasi Prodi</strong>).</li>
        </ol>
    </x-filament::section>

    <x-filament::section icon="heroicon-o-clock" heading="Riwayat impor terakhir" collapsible collapsed>
        @php($recent = \Filament\Actions\Imports\Models\Import::query()->latest()->limit(10)->get())
        @if ($recent->isEmpty())
            <p style="font-size:13px;color:#6b7280;">Belum ada impor.</p>
        @else
            <table style="width:100%;font-size:13px;border-collapse:collapse;">
                <thead>
                    <tr style="text-align:left;color:#9ca3af;">
                        <th style="padding:6px 8px;">Waktu</th>
                        <th style="padding:6px 8px;">Berkas</th>
                        <th style="padding:6px 8px;text-align:right;">Berhasil</th>
                        <th style="padding:6px 8px;text-align:right;">Total baris</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recent as $imp)
                        <tr style="border-top:1px solid #f1f2f4;">
                            <td style="padding:6px 8px;">{{ $imp->created_at?->format('d M Y H:i') }}</td>
                            <td style="padding:6px 8px;">{{ $imp->file_name }}</td>
                            <td style="padding:6px 8px;text-align:right;font-weight:700;">{{ number_format($imp->successful_rows) }}</td>
                            <td style="padding:6px 8px;text-align:right;">{{ number_format($imp->total_rows) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-filament::section>
</x-filament-panels::page>
