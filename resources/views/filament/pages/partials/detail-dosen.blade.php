<div class="dp-wrap">
    <style>
        .dp-wrap { display:grid; grid-template-columns:1fr; gap:14px; margin-bottom:18px; }
        @media (min-width:768px) { .dp-wrap { grid-template-columns:1fr 1fr; } }
        .dp-card { border:1px solid #e5e9f5; border-radius:10px; overflow:hidden; background:#fff; }
        .dp-card-head { background:#dbe3fd; padding:12px 16px; }
        .dp-card-head strong { display:block; font-size:14px; font-weight:800; color:#1f2937; }
        .dp-card-head span { font-size:11.5px; color:#4b5563; }
        .dp-card-body { padding:14px 16px; display:grid; grid-template-columns:repeat(2,1fr); gap:12px 16px; }
        .dp-item dt { font-size:11px; color:#6b7280; text-transform:uppercase; letter-spacing:.02em; }
        .dp-item dd { margin:2px 0 0; font-size:13px; font-weight:700; color:#2f43b8; }
        .dp-item dd.dp-plain { color:#1f2937; }
        .dp-badge { display:inline-block; padding:2px 9px; border-radius:9999px; background:#dcfce7; color:#15803d; font-size:11px; font-weight:700; }
        .dp-card-foot { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:10px 16px; border-top:1px solid #eef0f3; background:#fafbfd; font-size:11.5px; color:#6b7280; }
    </style>

    <div class="dp-card">
        <div class="dp-card-head">
            <strong>Data PDDIKTI</strong>
            <span>Data dosen &amp; program studi tersimpan di sistem ini</span>
        </div>
        <div class="dp-card-body">
            <div class="dp-item">
                <dt>Nama</dt>
                <dd>{{ $dosen->name }}</dd>
            </div>
            <div class="dp-item">
                <dt>NIDN / NUPTK</dt>
                <dd>{{ $dosen->nidn ?: '—' }} / {{ $dosen->nuptk ?: '—' }}</dd>
            </div>
            <div class="dp-item">
                <dt>Institusi</dt>
                <dd>{{ $institusi }}</dd>
            </div>
            <div class="dp-item">
                <dt>Program Studi</dt>
                <dd>{{ $dosen->programStudi?->nama ?: '—' }}</dd>
            </div>
            <div class="dp-item">
                <dt>Jenjang Pendidikan</dt>
                <dd>{{ $dosen->pendidikan_terakhir ?: '—' }}</dd>
            </div>
            <div class="dp-item">
                <dt>Jabatan Akademik</dt>
                <dd>{{ $dosen->jabatan ?: '—' }}</dd>
            </div>
            <div class="dp-item">
                <dt>Status</dt>
                <dd><span class="dp-badge">Aktif Mengajar</span></dd>
            </div>
        </div>
        <div class="dp-card-foot">
            <span>Terakhir diperbarui: <strong>{{ $dosen->updated_at?->format('d M Y H:i') ?: '—' }}</strong></span>
        </div>
    </div>

    <div class="dp-card">
        <div class="dp-card-head">
            <strong>Data SINTA</strong>
            <span>Sinta (Indeks Sains dan Teknologi)</span>
        </div>
        <div class="dp-card-body">
            <div class="dp-item">
                <dt>Sinta ID</dt>
                <dd>{{ $dosen->sinta_id ?: '—' }}</dd>
            </div>
            <div class="dp-item">
                <dt>Jabatan Fungsional</dt>
                <dd>{{ $dosen->jabatan ?: '—' }}</dd>
            </div>
            <div class="dp-item">
                <dt>Skor Overall (v3)</dt>
                <dd>{{ $dosen->sinta_score_overall_v3 !== null ? number_format($dosen->sinta_score_overall_v3, 2) : '—' }}</dd>
            </div>
            <div class="dp-item">
                <dt>Skor 3 Tahun (v3)</dt>
                <dd>{{ $dosen->sinta_score_3yr_v3 !== null ? number_format($dosen->sinta_score_3yr_v3, 2) : '—' }}</dd>
            </div>
            <div class="dp-item">
                <dt>Skor Overall (v2)</dt>
                <dd class="dp-plain">{{ $dosen->sinta_score_overall_v2 !== null ? number_format($dosen->sinta_score_overall_v2, 2) : '—' }}</dd>
            </div>
            <div class="dp-item">
                <dt>Skor 3 Tahun (v2)</dt>
                <dd class="dp-plain">{{ $dosen->sinta_score_3yr_v2 !== null ? number_format($dosen->sinta_score_3yr_v2, 2) : '—' }}</dd>
            </div>
        </div>

        <div class="dp-card-body" style="border-top:1px solid #eef0f3;">
            <div class="dp-item" style="grid-column:1 / -1;">
                <dt>Scopus</dt>
            </div>
            <div class="dp-item">
                <dt>Scopus ID</dt>
                <dd>{{ $dosen->scopus_id ?: '—' }}</dd>
            </div>
            <div class="dp-item">
                <dt>H-Index</dt>
                <dd>{{ $dosen->scopus_h_index ?? '—' }}</dd>
            </div>
            <div class="dp-item">
                <dt>Articles</dt>
                <dd>{{ $dosen->scopus_articles ?? '—' }}</dd>
            </div>
            <div class="dp-item">
                <dt>Citation</dt>
                <dd>{{ $dosen->scopus_citation ?? '—' }}</dd>
            </div>
            <div class="dp-item" style="grid-column:1 / -1; margin-top:6px;">
                <dt>WOS</dt>
            </div>
            <div class="dp-item">
                <dt>WOS</dt>
                <dd>{{ $dosen->wos_score ?? '—' }}</dd>
            </div>
        </div>

        <div class="dp-card-foot">
            <span>Terakhir diperbarui: <strong>{{ $dosen->updated_at?->format('d M Y H:i') ?: '—' }}</strong></span>
        </div>
    </div>
</div>
