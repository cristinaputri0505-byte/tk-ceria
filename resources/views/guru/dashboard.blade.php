@extends('layouts.dashboard')
@section('title', 'Ringkasan')
@section('heading', 'Halo, '.auth()->user()->name)
@section('content')
<p class="text-slate-500 mb-6">{{ now()->translatedFormat('l, d F Y') }}</p>

@forelse ($kelas as $k)
    @php $rekap = ($absenHariIni[$k->id] ?? collect())->pluck('total', 'status'); $tercatat = $rekap->sum(); @endphp
    <section class="bg-white rounded-3xl border border-slate-100 p-6 mb-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="font-display text-2xl font-semibold text-navy">{{ $k->nama }}</h2>
                <p class="text-sm text-slate-500">Kelompok {{ $k->kelompok }} · {{ $k->siswa_count }} siswa · {{ $k->tahun_ajaran }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('guru.absensi.index', ['kelas_id' => $k->id]) }}" class="inline-flex items-center gap-2 bg-sky text-white text-sm font-extrabold px-4 py-2.5 rounded-xl">
                    <i data-lucide="calendar-check" class="w-4 h-4"></i> {{ $tercatat ? 'Ubah absensi hari ini' : 'Isi absensi hari ini' }}
                </a>
                <a href="{{ route('guru.perkembangan.index') }}" class="inline-flex items-center gap-2 bg-leaf-soft text-leaf text-sm font-extrabold px-4 py-2.5 rounded-xl">
                    <i data-lucide="sprout" class="w-4 h-4"></i> Laporan perkembangan
                </a>
            </div>
        </div>
        <ul class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-5">
            @foreach (\App\Models\Absensi::STATUS as $kode => $label)
                <li class="rounded-2xl p-4 {{ ['hadir' => 'bg-emerald-50', 'izin' => 'bg-sky-50', 'sakit' => 'bg-amber-50', 'alpa' => 'bg-rose-50'][$kode] }}">
                    <p class="font-display text-3xl font-semibold text-navy">{{ $rekap[$kode] ?? 0 }}</p>
                    <p class="text-sm text-slate-600">{{ $label }}</p>
                </li>
            @endforeach
        </ul>
        @if (! $tercatat)
            <p class="text-sm text-amber-700 mt-3">Absensi hari ini belum diisi.</p>
        @endif
    </section>
@empty
    <div class="bg-white rounded-3xl border border-slate-100 p-8 text-center">
        <p class="font-bold text-navy">Anda belum menjadi wali kelas.</p>
        <p class="text-sm text-slate-500 mt-1">Minta admin menetapkan Anda sebagai wali kelas agar bisa mengisi absensi dan laporan perkembangan.</p>
    </div>
@endforelse
@endsection
