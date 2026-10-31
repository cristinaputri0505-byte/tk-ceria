@extends('layouts.dashboard')
@section('title', 'Rekap Siswa')
@section('heading', 'Rekap Siswa')
@section('actions')
    @if ($kelas)
        <a href="{{ route($rp.'rekap.index', ['kelas_id' => $kelas->id, 'mode' => $mode, 'tanggal' => $tanggal->toDateString(), 'unduh' => 'csv']) }}" class="hidden sm:inline-flex items-center gap-2 bg-daun text-navy text-sm font-extrabold px-4 py-2.5 rounded-xl">
            <i data-lucide="file-spreadsheet" class="w-4 h-4"></i> Unduh Excel
        </a>
        <button type="button" onclick="window.print()" class="hidden sm:inline-flex items-center gap-2 bg-white text-navy text-sm font-extrabold px-4 py-2.5 rounded-xl">
            <i data-lucide="printer" class="w-4 h-4"></i> Cetak
        </button>
    @endif
@endsection
@section('content')
@php
    $warnaNilai = ['BSB' => 'bg-daun', 'BSH' => 'bg-langit', 'MB' => 'bg-sun', 'BB' => 'bg-permen'];
    $singkatAspek = \App\Models\Perkembangan::ASPEK_MUDAH;
    $total = $baris->isNotEmpty() ? collect(\App\Models\Absensi::STATUS)->map(fn ($l, $k) => $baris->sum(fn ($b) => $b->absen[$k])) : collect();
    $tercatat = $total->sum();
@endphp

@if (! $kelas)
    <div class="bg-white rounded-3xl p-8 text-center">
        <p class="font-display text-2xl font-extrabold text-navy">Belum ada kelas</p>
        <p class="text-ink mt-1">Rekap tersedia setelah Anda ditetapkan sebagai wali kelas.</p>
    </div>
@else

{{-- Pilihan kelas & periode (tidak ikut dicetak) --}}
<form method="GET" class="print:hidden bg-white rounded-3xl p-5 mb-6 flex flex-wrap items-end gap-4">
    @if ($kelasList->count() > 1)
        <div>
            <label for="r_kelas" class="block text-sm font-bold text-navy mb-1.5">Kelas</label>
            <select id="r_kelas" name="kelas_id" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm">
                @foreach ($kelasList as $k)<option value="{{ $k->id }}" @selected($k->id === $kelas->id)>{{ $k->nama }}</option>@endforeach
            </select>
        </div>
    @else
        <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
    @endif
    <fieldset>
        <legend class="block text-sm font-bold text-navy mb-1.5">Jenis rekap</legend>
        <div class="flex flex-wrap gap-2">
            @foreach (\App\Http\Controllers\Guru\RekapController::MODE as $kode => $label)
                <label class="cursor-pointer inline-flex items-center min-h-[42px] px-4 rounded-xl border-2 border-navy text-sm font-extrabold text-navy bg-white has-[:checked]:bg-sun">
                    <input type="radio" name="mode" value="{{ $kode }}" class="sr-only" @checked($mode === $kode) onchange="this.form.submit()">{{ $label }}
                </label>
            @endforeach
        </div>
    </fieldset>
    <div>
        <label for="r_tgl" class="block text-sm font-bold text-navy mb-1.5">Tanggal</label>
        <input id="r_tgl" type="date" name="tanggal" value="{{ $tanggal->toDateString() }}" max="{{ today()->toDateString() }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm">
    </div>
    <button class="bg-navy text-white text-sm font-extrabold px-5 py-2.5 rounded-xl">Tampilkan</button>
    <div class="flex gap-2 sm:hidden w-full">
        <a href="{{ route($rp.'rekap.index', ['kelas_id' => $kelas->id, 'mode' => $mode, 'tanggal' => $tanggal->toDateString(), 'unduh' => 'csv']) }}" class="flex-1 inline-flex justify-center items-center gap-2 bg-daun text-navy text-sm font-extrabold px-4 py-2.5 rounded-xl"><i data-lucide="file-spreadsheet" class="w-4 h-4"></i> Excel</a>
        <button type="button" onclick="window.print()" class="flex-1 inline-flex justify-center items-center gap-2 bg-white text-navy text-sm font-extrabold px-4 py-2.5 rounded-xl"><i data-lucide="printer" class="w-4 h-4"></i> Cetak</button>
    </div>
</form>

{{-- Judul laporan --}}
<div class="mb-5">
    <p class="text-sm font-extrabold text-ink">Laporan {{ strtolower(\App\Http\Controllers\Guru\RekapController::MODE[$mode]) }} · {{ $site['nama_sekolah'] }}</p>
    <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-navy leading-tight">{{ $kelas->nama }} · {{ $judulPeriode }}</h2>
    <p class="text-ink font-semibold">Wali kelas: {{ $kelas->wali->name ?? '—' }} · {{ $baris->count() }} siswa</p>
</div>

{{-- Ringkasan --}}
<ul class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    @foreach ([['hadir', 'Hadir', 'bg-daun-soft'], ['izin', 'Izin', 'bg-langit-soft'], ['sakit', 'Sakit', 'bg-sun-soft'], ['alpa', 'Alpa', 'bg-permen-soft']] as [$k, $l, $bg])
        <li class="rounded-2xl p-4 border-[2.5px] border-navy {{ $bg }}">
            <p class="font-display text-3xl font-extrabold text-navy leading-none">{{ $total[$k] ?? 0 }}</p>
            <p class="text-sm font-extrabold text-navy mt-1">{{ $l }}</p>
        </li>
    @endforeach
    <li class="rounded-2xl p-4 border-[2.5px] border-navy bg-anggur-soft">
        <p class="font-display text-3xl font-extrabold text-navy leading-none">{{ $tercatat ? round(($total['hadir'] ?? 0) / $tercatat * 100) . '%' : '—' }}</p>
        <p class="text-sm font-extrabold text-navy mt-1">Kehadiran</p>
    </li>
    <li class="rounded-2xl p-4 border-[2.5px] border-navy bg-jeruk-soft">
        <p class="font-display text-3xl font-extrabold text-navy leading-none">{{ $baris->sum('tugas') }}</p>
        <p class="text-sm font-extrabold text-navy mt-1">Tugas belajar selesai</p>
    </li>
</ul>
@if ($mode === 'harian' && ! $tercatat)
    <p class="print:hidden mb-5 rounded-2xl bg-sun-soft px-4 py-3 text-sm font-bold text-navy">Absensi tanggal ini belum diisi. <a href="{{ route($rp.'absensi.index', ['kelas_id' => $kelas->id, 'tanggal' => $tanggal->toDateString()]) }}" class="underline">Isi absensi</a></p>
@endif

{{-- Tabel rekap --}}
<div class="bg-white rounded-3xl border border-slate-100 overflow-hidden print:shadow-none">
<div class="overflow-x-auto">
<table class="w-full text-sm">
    <thead>
        <tr class="text-left text-navy">
            <th class="px-3 py-3 font-extrabold" rowspan="2">No</th>
            <th class="px-3 py-3 font-extrabold" rowspan="2">Nama siswa</th>
            <th class="px-2 py-2 font-extrabold text-center border-l border-navy/20" colspan="{{ $mode === 'harian' ? 2 : 6 }}">Kehadiran</th>
            <th class="px-2 py-2 font-extrabold text-center border-l border-navy/20" colspan="2">Ruang Belajar</th>
            <th class="px-2 py-2 font-extrabold text-center border-l border-navy/20" colspan="6">Perkembangan · {{ $periode }}</th>
        </tr>
        <tr class="text-navy text-xs">
            @if ($mode === 'harian')
                <th class="px-2 py-2 font-extrabold text-left border-l border-navy/20">Status</th>
                <th class="px-2 py-2 font-extrabold text-left">Keterangan</th>
            @else
                <th class="px-2 py-2 font-extrabold border-l border-navy/20">H</th><th class="px-2 py-2 font-extrabold">I</th><th class="px-2 py-2 font-extrabold">S</th><th class="px-2 py-2 font-extrabold">A</th><th class="px-2 py-2 font-extrabold">%</th>
                <th class="px-2 py-2 font-extrabold text-left">Keterangan</th>
            @endif
            <th class="px-2 py-2 font-extrabold border-l border-navy/20" title="Tugas guru + Soal Hari Ini yang selesai">Tugas</th>
            <th class="px-2 py-2 font-extrabold">Bintang</th>
            @foreach (\App\Models\Perkembangan::ASPEK as $kode => $label)
                <th class="px-2 py-2 font-extrabold leading-tight {{ $loop->first ? 'border-l border-navy/20' : '' }}" title="{{ $label }}">{{ $singkatAspek[$kode] }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
        @forelse ($baris as $i => $b)
            <tr class="align-top">
                <td class="px-3 py-3 text-ink">{{ $i + 1 }}</td>
                <td class="px-3 py-3 min-w-[10rem]">
                    <p class="font-extrabold text-navy">{{ $b->siswa->nama }}</p>
                    <p class="text-xs text-ink">NIS {{ $b->siswa->nis }}</p>
                </td>
                @if ($mode === 'harian')
                    @php $st = collect($b->absen)->filter()->keys()->first(); @endphp
                    <td class="px-2 py-3 border-l border-navy/10">
                        @if ($st)
                            <span class="inline-block text-xs font-extrabold text-navy px-2.5 py-1 rounded-full border-2 border-navy {{ ['hadir' => 'bg-daun', 'izin' => 'bg-langit', 'sakit' => 'bg-sun', 'alpa' => 'bg-permen'][$st] }}">{{ \App\Models\Absensi::STATUS[$st] }}</span>
                        @else
                            <span class="text-xs text-ink">Belum diabsen</span>
                        @endif
                    </td>
                    <td class="px-2 py-3 text-xs text-ink min-w-[8rem]">{{ $b->catatan->join(', ') ?: '—' }}</td>
                @else
                    <td class="px-2 py-3 text-center font-bold border-l border-navy/10">{{ $b->absen['hadir'] }}</td>
                    <td class="px-2 py-3 text-center font-bold">{{ $b->absen['izin'] }}</td>
                    <td class="px-2 py-3 text-center font-bold">{{ $b->absen['sakit'] }}</td>
                    <td class="px-2 py-3 text-center font-bold">{{ $b->absen['alpa'] }}</td>
                    <td class="px-2 py-3 text-center font-extrabold text-navy">{{ $b->persen === null ? '—' : $b->persen.'%' }}</td>
                    <td class="px-2 py-3 text-xs text-ink min-w-[9rem]">{{ $b->keterangan->join(', ') ?: '—' }}</td>
                @endif
                <td class="px-2 py-3 text-center font-bold border-l border-navy/10">{{ $b->tugas }}</td>
                <td class="px-2 py-3 text-center font-bold whitespace-nowrap">⭐ {{ $b->bintang }}</td>
                @foreach (array_keys(\App\Models\Perkembangan::ASPEK) as $kode)
                    @php $n = optional($b->rapor->get($kode))->nilai; @endphp
                    <td class="px-1.5 py-3 text-center {{ $loop->first ? 'border-l border-navy/10' : '' }}">
                        @if ($n)<span class="inline-block whitespace-nowrap text-[11px] font-extrabold text-navy px-2 py-0.5 rounded-full border-2 border-navy {{ $warnaNilai[$n] }}" title="{{ $n }} · {{ \App\Models\Perkembangan::NILAI[$n] }}">{{ \App\Models\Perkembangan::NILAI_MUDAH[$n] }}</span>@else<span class="text-ink">—</span>@endif
                    </td>
                @endforeach
            </tr>
        @empty
            <tr><td colspan="16" class="px-3 py-8 text-center text-ink">Belum ada siswa di kelas ini.</td></tr>
        @endforelse
    </tbody>
</table>
</div>
</div>
<div class="mt-4 bg-white rounded-2xl border-2 border-navy p-4">
    <p class="text-sm font-extrabold text-navy">Arti nilai perkembangan</p>
    <ul class="mt-2 grid sm:grid-cols-2 lg:grid-cols-4 gap-2 text-xs">
        @foreach (\App\Models\Perkembangan::NILAI_MUDAH as $k => $l)
            <li class="flex items-start gap-2">
                <span class="shrink-0 whitespace-nowrap font-extrabold text-navy px-2 py-0.5 rounded-full border-2 border-navy {{ $warnaNilai[$k] }}">{{ $l }}</span>
                <span class="text-ink">{{ \App\Models\Perkembangan::NILAI_PENJELASAN[$k] }} <span class="whitespace-nowrap">(rapor: {{ $k }})</span></span>
            </li>
        @endforeach
    </ul>
    <a href="{{ route($rp.'perkembangan.index') }}" class="print:hidden inline-block mt-2 text-xs font-bold text-sky">Isi perkembangan</a>
</div>

{{-- Kegiatan (dokumentasi) pada periode ini --}}
<section class="bg-white rounded-3xl p-6 mt-6">
    <h2 class="font-display text-2xl font-extrabold text-navy">Kegiatan yang didokumentasikan</h2>
    @if ($dokumentasi->isEmpty())
        <p class="text-ink mt-2">Belum ada foto kegiatan pada periode ini. <a href="{{ route($rp.'dokumentasi.index') }}" class="print:hidden font-bold text-sky">Bagikan foto kegiatan</a></p>
    @else
        <ul class="mt-3 space-y-1.5 text-sm">
            @foreach ($dokumentasi->groupBy(fn ($d) => $d->tanggal->toDateString()) as $tgl => $isi)
                <li><span class="font-extrabold text-navy">{{ \Carbon\Carbon::parse($tgl)->translatedFormat('l, d M') }}:</span> <span class="text-ink">{{ $isi->pluck('keterangan')->filter()->unique()->join('; ') ?: $isi->count().' foto kegiatan' }}</span></li>
            @endforeach
        </ul>
    @endif
</section>

{{-- Tanda tangan (hanya saat dicetak) --}}
<div class="hidden print:flex justify-end mt-10 text-sm">
    <div class="text-center w-64">
        <p>{{ now()->translatedFormat('d F Y') }}</p>
        <p>Wali Kelas {{ $kelas->nama }}</p>
        <div class="h-20"></div>
        <p class="font-bold underline">{{ $kelas->wali->name ?? '........................' }}</p>
    </div>
</div>

<style>
    @media print {
        @page { size: A4 landscape; margin: 12mm; }
        body { background: #fff !important; }
        .dash main { padding: 0 !important; max-width: none !important; }
        .dash .bg-white.rounded-3xl { box-shadow: none !important; }
    }
</style>
@endif
@endsection
