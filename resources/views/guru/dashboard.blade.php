@extends('layouts.dashboard')
@section('title', 'Ringkasan')
@section('heading', 'Halo, '.auth()->user()->name)
@section('content')
@php
    $warnaStatus = ['hadir' => ['bg-daun-soft', 'has-[:checked]:bg-daun'], 'izin' => ['bg-langit-soft', 'has-[:checked]:bg-langit'], 'sakit' => ['bg-sun-soft', 'has-[:checked]:bg-sun'], 'alpa' => ['bg-permen-soft', 'has-[:checked]:bg-permen']];
    $warnaAnak = ['bg-permen', 'bg-langit', 'bg-anggur', 'bg-sun', 'bg-daun', 'bg-jeruk'];
@endphp
<p class="text-ink font-bold mb-5" data-tgl-wib>{{ now()->timezone('Asia/Jakarta')->translatedFormat('l, d F Y') }}</p>

@forelse ($kelas as $k)
    @php $rekap = ($absenHariIni[$k->id] ?? collect())->pluck('total', 'status'); $tercatat = $rekap->sum(); @endphp

    {{-- Kartu kelas --}}
    <section class="relative overflow-hidden bg-jeruk border-[2.5px] border-navy rounded-[2rem] shadow-[5px_5px_0_#2b2140] p-6 sm:p-8 mb-6">
        <svg class="hidden sm:block absolute right-8 top-1/2 -translate-y-1/2 w-32 h-32" viewBox="0 0 100 100" aria-hidden="true"><circle cx="50" cy="50" r="24" fill="#ffc53d" stroke="#2b2140" stroke-width="3"/><circle cx="42" cy="46" r="3" fill="#2b2140"/><circle cx="58" cy="46" r="3" fill="#2b2140"/><path d="M40 57c5 5 15 5 20 0" fill="none" stroke="#2b2140" stroke-width="3" stroke-linecap="round"/><g stroke="#2b2140" stroke-width="4" stroke-linecap="round"><path d="M50 6v12M50 82v12M6 50h12M82 50h12M19 19l8 8M73 73l8 8M81 19l-8 8M27 73l-8 8"/></g></svg>
        <div class="relative sm:pr-40">
            <p class="text-sm font-extrabold text-navy">Kelas Anda</p>
            <h2 class="font-display text-4xl sm:text-5xl font-extrabold text-navy leading-none mt-1">{{ $k->nama }}</h2>
            <p class="font-bold text-navy mt-2">Kelompok {{ $k->kelompok }} · {{ $k->siswa_count }} siswa · {{ $k->tahun_ajaran }}</p>
            <div class="flex flex-wrap gap-3 mt-5">
                <a href="{{ route('guru.absensi.index', ['kelas_id' => $k->id]) }}" class="inline-flex items-center gap-2 bg-sun text-navy text-sm font-extrabold px-4 py-2.5 rounded-xl">
                    <i data-lucide="calendar-check" class="w-4 h-4"></i> {{ $tercatat ? 'Ubah absensi hari ini' : 'Isi absensi hari ini' }}
                </a>
                <a href="{{ route('guru.perkembangan.index') }}" class="inline-flex items-center gap-2 bg-white text-navy text-sm font-extrabold px-4 py-2.5 rounded-xl">
                    <i data-lucide="sprout" class="w-4 h-4"></i> Laporan perkembangan
                </a>
            </div>
        </div>
    </section>

    {{-- Rekap absensi hari ini --}}
    <ul class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @foreach (\App\Models\Absensi::STATUS as $kode => $label)
            <li class="rounded-2xl p-4 border-[2.5px] border-navy shadow-[4px_4px_0_#2b2140] {{ $warnaStatus[$kode][0] }}">
                <p class="font-display text-4xl font-extrabold text-navy leading-none">{{ $rekap[$kode] ?? 0 }}</p>
                <p class="font-extrabold text-navy mt-1">{{ $label }}</p>
            </li>
        @endforeach
    </ul>

    {{-- Absen cepat --}}
    @if ($k->siswa->isNotEmpty())
    <section class="bg-white rounded-3xl p-6 mb-8">
        <div class="flex flex-wrap items-end justify-between gap-2">
            <div>
                <h2 class="font-display text-2xl font-extrabold text-navy">Absen cepat · {{ $k->nama }}</h2>
                <p class="text-sm font-semibold text-ink">{{ $tercatat ? 'Absensi hari ini sudah diisi. Ubah bila perlu, lalu simpan.' : 'Absensi hari ini belum diisi. Ketuk status tiap anak, lalu simpan.' }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('guru.absensi.store') }}" class="mt-4">
            @csrf
            <input type="hidden" name="kelas_id" value="{{ $k->id }}">
            <input type="hidden" name="tanggal" value="{{ today()->toDateString() }}">
            <ul class="divide-y divide-slate-100">
                @foreach ($k->siswa as $s)
                    @php $pilih = $statusHariIni[$s->id] ?? 'hadir'; @endphp
                    <li class="py-3 flex flex-wrap items-center gap-3">
                        <span class="w-10 h-10 shrink-0 rounded-full border-2 border-navy grid place-items-center font-display font-extrabold text-navy {{ $warnaAnak[$s->id % 6] }}" aria-hidden="true">{{ mb_substr($s->nama_panggilan ?: $s->nama, 0, 1) }}</span>
                        <span class="flex-1 min-w-[8rem] font-extrabold text-navy">{{ $s->nama }}</span>
                        <fieldset class="flex flex-wrap gap-1.5">
                            <legend class="sr-only">Status {{ $s->nama }}</legend>
                            @foreach (\App\Models\Absensi::STATUS as $kode => $label)
                                <label class="cursor-pointer inline-flex items-center min-h-[36px] px-3 rounded-full border-2 border-navy text-xs font-extrabold text-navy bg-white {{ $warnaStatus[$kode][1] }} has-[:focus-visible]:ring-4 has-[:focus-visible]:ring-sun">
                                    <input type="radio" class="sr-only" name="status[{{ $s->id }}]" value="{{ $kode }}" @checked($pilih === $kode)>{{ $label }}
                                </label>
                            @endforeach
                        </fieldset>
                    </li>
                @endforeach
            </ul>
            <button class="mt-4 inline-flex items-center gap-2 bg-daun text-navy font-extrabold px-5 py-2.5 rounded-xl"><i data-lucide="save" class="w-4 h-4"></i> Simpan absensi</button>
        </form>
    </section>
    @endif
@empty
    <div class="bg-white rounded-3xl p-8 text-center">
        <p class="font-display text-2xl font-extrabold text-navy">Anda belum menjadi wali kelas.</p>
        <p class="text-ink mt-1">Minta admin menetapkan Anda sebagai wali kelas agar bisa mengisi absensi dan laporan perkembangan.</p>
    </div>
@endforelse
@endsection
