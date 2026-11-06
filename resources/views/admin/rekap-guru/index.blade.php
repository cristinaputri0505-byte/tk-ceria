@extends('layouts.dashboard')
@section('title', 'Rekap Guru')
@section('heading', 'Rekap Kehadiran Guru')
@section('actions')
    <a href="{{ route('admin.rekap-guru.index', ['mode' => $mode, 'tanggal' => $tanggal->toDateString(), 'unduh' => 'csv']) }}" class="hidden sm:inline-flex items-center gap-2 bg-daun text-navy text-sm font-extrabold px-4 py-2.5 rounded-xl">
        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i> Unduh Excel
    </a>
    <button type="button" onclick="window.print()" class="hidden sm:inline-flex items-center gap-2 bg-white text-navy text-sm font-extrabold px-4 py-2.5 rounded-xl">
        <i data-lucide="printer" class="w-4 h-4"></i> Cetak
    </button>
@endsection
@section('content')
@php
    $S = \App\Models\GuruKehadiran::STATUS;
    $jam = fn ($j) => $j ? str_replace(':', '.', $j) : '—';
    $total = collect($S)->map(fn ($s, $k) => $baris->sum(fn ($b) => $b->hitung[$k]));
@endphp

{{-- Pilihan periode --}}
<form method="GET" class="print:hidden bg-white rounded-3xl p-5 mb-6 flex flex-wrap items-end gap-4">
    <fieldset>
        <legend class="block text-sm font-bold text-navy mb-1.5">Jenis rekap</legend>
        <div class="flex flex-wrap gap-2">
            @foreach (\App\Http\Controllers\Admin\RekapGuruController::MODE as $kode => $label)
                <label class="cursor-pointer inline-flex items-center min-h-[42px] px-4 rounded-xl border-2 border-navy text-sm font-extrabold text-navy bg-white has-[:checked]:bg-sun">
                    <input type="radio" name="mode" value="{{ $kode }}" class="sr-only" @checked($mode === $kode) onchange="this.form.submit()">{{ $label }}
                </label>
            @endforeach
        </div>
    </fieldset>
    <div>
        <label for="rg_tgl" class="block text-sm font-bold text-navy mb-1.5">Tanggal</label>
        <input id="rg_tgl" type="date" name="tanggal" value="{{ $tanggal->toDateString() }}" max="{{ today()->toDateString() }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm">
    </div>
    <button class="bg-navy text-white text-sm font-extrabold px-5 py-2.5 rounded-xl">Tampilkan</button>
    <div class="flex gap-2 sm:hidden w-full">
        <a href="{{ route('admin.rekap-guru.index', ['mode' => $mode, 'tanggal' => $tanggal->toDateString(), 'unduh' => 'csv']) }}" class="flex-1 inline-flex justify-center items-center gap-2 bg-daun text-navy text-sm font-extrabold px-4 py-2.5 rounded-xl"><i data-lucide="file-spreadsheet" class="w-4 h-4"></i> Excel</a>
        <button type="button" onclick="window.print()" class="flex-1 inline-flex justify-center items-center gap-2 bg-white text-navy text-sm font-extrabold px-4 py-2.5 rounded-xl"><i data-lucide="printer" class="w-4 h-4"></i> Cetak</button>
    </div>
</form>

<div class="mb-5">
    <p class="text-sm font-extrabold text-ink">Laporan {{ strtolower(\App\Http\Controllers\Admin\RekapGuruController::MODE[$mode]) }} kehadiran guru · {{ $site['nama_sekolah'] }}</p>
    <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-navy leading-tight">{{ $judulPeriode }}</h2>
    <p class="text-ink font-semibold">{{ $baris->count() }} guru · {{ $hariSekolah->count() }} hari sekolah (Senin–Jumat) · batas jam masuk {{ str_replace(':', '.', \App\Models\GuruKehadiran::batasMasuk()) }} WIB</p>
</div>

{{-- Ringkasan --}}
<ul class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    @foreach ([['hadir', 'bg-daun-soft'], ['izin', 'bg-langit-soft'], ['sakit', 'bg-sun-soft'], ['dinas', 'bg-anggur-soft'], ['alpa', 'bg-permen-soft']] as [$k, $bg])
        <li class="rounded-2xl p-4 border-[2.5px] border-navy {{ $bg }}">
            <p class="font-display text-3xl font-extrabold text-navy leading-none">{{ $total[$k] }}</p>
            <p class="text-sm font-extrabold text-navy mt-1">{{ $S[$k][0] }}</p>
        </li>
    @endforeach
    <li class="rounded-2xl p-4 border-[2.5px] border-navy bg-jeruk-soft">
        <p class="font-display text-3xl font-extrabold text-navy leading-none">{{ $baris->sum('terlambat') }}</p>
        <p class="text-sm font-extrabold text-navy mt-1">Terlambat</p>
    </li>
</ul>

<div class="bg-white rounded-3xl border border-slate-100 overflow-hidden">
<div class="overflow-x-auto">
<table class="w-full text-sm">
    <thead>
        <tr class="text-left text-navy">
            <th class="px-3 py-3 font-extrabold">No</th>
            <th class="px-3 py-3 font-extrabold">Guru</th>
            @if ($mode === 'harian')
                <th class="px-3 py-3 font-extrabold">Status</th>
                <th class="px-3 py-3 font-extrabold">Masuk</th>
                <th class="px-3 py-3 font-extrabold">Pulang</th>
                <th class="px-3 py-3 font-extrabold" title="Apakah absensi siswa kelasnya sudah diisi pada tanggal ini">Absensi kelas</th>
                <th class="px-3 py-3 font-extrabold">Keterangan</th>
                <th class="px-3 py-3 font-extrabold print:hidden">Ubah</th>
            @else
                @foreach ($S as $k => [$l])<th class="px-2 py-3 font-extrabold text-center">{{ $l }}</th>@endforeach
                <th class="px-2 py-3 font-extrabold text-center" title="Hari sekolah tanpa catatan kehadiran sama sekali">Belum ada catatan</th>
                <th class="px-2 py-3 font-extrabold text-center">Terlambat</th>
                <th class="px-2 py-3 font-extrabold text-center" title="Jumlah hari absensi siswa kelasnya diisi">Hari absensi kelas diisi</th>
                <th class="px-3 py-3 font-extrabold">Keterangan</th>
            @endif
        </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
        @forelse ($baris as $i => $b)
            @php $h = $b->hari; @endphp
            <tr class="align-top">
                <td class="px-3 py-3 text-ink">{{ $i + 1 }}</td>
                <td class="px-3 py-3 min-w-[11rem]">
                    <p class="font-extrabold text-navy">{{ $b->guru->name }}</p>
                    <p class="text-xs text-ink">{{ $b->guru->jabatan ?: 'Guru' }}{{ $b->kelas ? ' · ' . $b->kelas : '' }}</p>
                </td>
                @if ($mode === 'harian')
                    <td class="px-3 py-3">
                        @if ($h)
                            <span class="inline-block whitespace-nowrap text-xs font-extrabold text-navy px-2.5 py-1 rounded-full border-2 border-navy {{ $S[$h->status][1] }}">{{ $S[$h->status][0] }}</span>
                            @if ($h->terlambat)<span class="inline-block mt-1 text-[11px] font-extrabold text-navy bg-jeruk border-2 border-navy rounded-full px-2 py-0.5">Terlambat</span>@endif
                        @else
                            <span class="text-xs font-bold text-ink">{{ $tanggal->isWeekend() ? 'Libur (akhir pekan)' : 'Belum ada catatan' }}</span>
                        @endif
                    </td>
                    <td class="px-3 py-3 font-bold text-navy whitespace-nowrap">{{ $jam($h->jam_masuk ?? null) }}</td>
                    <td class="px-3 py-3 font-bold text-navy whitespace-nowrap">{{ $jam($h->jam_pulang ?? null) }}</td>
                    <td class="px-3 py-3 whitespace-nowrap">
                        @if (! $b->kelas)<span class="text-xs text-ink">Bukan wali kelas</span>
                        @elseif ($b->absenKelasHariIni)<span class="text-xs font-extrabold text-leaf">✓ Sudah diisi</span>
                        @else<span class="text-xs font-bold text-ink">Belum diisi</span>@endif
                    </td>
                    <td class="px-3 py-3 text-xs text-ink min-w-[8rem]">{{ $h->keterangan ?? '—' }}@if ($h && $h->dicatat_oleh && (int) $h->dicatat_oleh !== (int) $h->user_id)<span class="block mt-0.5">dicatat admin</span>@endif</td>
                    <td class="px-3 py-3 print:hidden">
                        <details>
                            <summary class="cursor-pointer text-xs font-extrabold text-sky whitespace-nowrap">Ubah</summary>
                            <form method="POST" action="{{ route('admin.rekap-guru.simpan') }}" class="mt-2 grid gap-2 w-56">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $b->guru->id }}">
                                <input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">
                                <label class="sr-only" for="st_{{ $b->guru->id }}">Status</label>
                                <select id="st_{{ $b->guru->id }}" name="status" class="rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs">
                                    @foreach ($S as $k => [$l])<option value="{{ $k }}" @selected(($h->status ?? 'hadir') === $k)>{{ $l }}</option>@endforeach
                                    @if ($h)<option value="hapus">Kosongkan catatan</option>@endif
                                </select>
                                <div class="flex gap-2">
                                    <label class="text-[11px] font-bold text-navy">Masuk <input type="time" name="jam_masuk" value="{{ $h->jam_masuk ?? '' }}" class="w-full rounded-lg border border-slate-200 bg-white px-1 py-1 text-xs"></label>
                                    <label class="text-[11px] font-bold text-navy">Pulang <input type="time" name="jam_pulang" value="{{ $h->jam_pulang ?? '' }}" class="w-full rounded-lg border border-slate-200 bg-white px-1 py-1 text-xs"></label>
                                </div>
                                <label class="sr-only" for="kt_{{ $b->guru->id }}">Keterangan</label>
                                <input id="kt_{{ $b->guru->id }}" name="keterangan" maxlength="255" value="{{ $h->keterangan ?? '' }}" placeholder="Keterangan" class="rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs">
                                <button class="bg-navy text-white text-xs font-extrabold px-3 py-1.5 rounded-lg">Simpan</button>
                            </form>
                        </details>
                    </td>
                @else
                    @foreach ($S as $k => $s)<td class="px-2 py-3 text-center font-bold {{ $b->hitung[$k] ? 'text-navy' : 'text-ink' }}">{{ $b->hitung[$k] }}</td>@endforeach
                    <td class="px-2 py-3 text-center font-bold {{ $b->kosong ? 'text-berry' : 'text-ink' }}">{{ $b->kosong }}</td>
                    <td class="px-2 py-3 text-center font-bold">{{ $b->terlambat }}</td>
                    <td class="px-2 py-3 text-center font-bold">{{ $b->kelas ? $b->absenKelas . ' / ' . $hariSekolah->count() : '—' }}</td>
                    <td class="px-3 py-3 text-xs text-ink min-w-[10rem]">{{ $b->keterangan->join(', ') ?: '—' }}</td>
                @endif
            </tr>
        @empty
            <tr><td colspan="12" class="px-3 py-8 text-center text-ink">Belum ada akun guru.</td></tr>
        @endforelse
    </tbody>
</table>
</div>
</div>
<p class="text-xs text-ink mt-3">
    Guru mencatat sendiri lewat tombol <b>Absen masuk</b> / <b>Absen pulang</b> di Ringkasan panel guru (jam dicatat otomatis dari server). Bila guru lupa atau izin lewat telepon, admin bisa mengubahnya dengan tombol <b>Ubah</b> pada rekap harian.
    "Absensi kelas" menunjukkan apakah guru sudah mengisi absensi siswa kelasnya pada hari itu.
</p>

<div class="hidden print:flex justify-end mt-10 text-sm">
    <div class="text-center w-64">
        <p>{{ now()->translatedFormat('d F Y') }}</p>
        <p>Kepala Sekolah</p>
        <div class="h-20"></div>
        <p>........................................</p>
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
@endsection
