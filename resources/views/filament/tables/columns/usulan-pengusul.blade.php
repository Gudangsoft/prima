@php
    /** @var \App\Models\Proposal $record */
    $record = $getRecord();
    $u = $record->submitter;
@endphp

<div style="line-height:1.65;font-size:13px;color:#374151;padding:4px 0;min-width:220px;">
    <div>Ketua : <span style="font-weight:700;color:#111827;">{{ strtoupper($u?->name ?? '—') }}</span></div>
    @if ($u?->nidn)
        <div>NIDN : {{ $u->nidn }}</div>
    @endif
    @if ($u?->programStudi)
        <div>Prodi : {{ $u->programStudi->nama }}</div>
    @endif
    <div>Tahun Pelaksanaan : <span style="font-weight:700;color:#111827;">{{ $record->tahun_anggaran }}</span></div>
    @if ($record->scheme?->kategori)
        <div>Kategori : <span style="font-weight:700;color:#111827;">{{ $record->scheme->kategori->label() }}</span></div>
    @endif
</div>
