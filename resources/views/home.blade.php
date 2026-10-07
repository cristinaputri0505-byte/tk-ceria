@extends('layouts.public')

@section('content')
@php
    $hero = \App\Support\Pengaturan::url('hero_gambar', 'images/hero.jpg');
    $gedung = \App\Support\Pengaturan::url('tentang_gambar', 'images/gedung.jpg');
    $gambarDaftar = \App\Support\Pengaturan::url('daftar_gambar');
    $gambarKutipan = \App\Support\Pengaturan::url('kutipan_gambar');
    $gelembung = \App\Support\Pengaturan::baris('hero_gelembung');
    $misi = \App\Support\Pengaturan::baris('misi');
@endphp

{{-- ================= HERO ================= --}}
@php $heroLatar = \App\Support\Pengaturan::url('hero_latar'); @endphp
<section class="relative overflow-hidden bg-gradient-to-b from-sky-soft to-cloud">
    @if ($heroLatar)
        <img src="{{ $heroLatar }}" alt="" class="absolute inset-0 w-full h-full object-cover opacity-25">
    @endif
    {{-- matahari --}}
    <svg class="absolute left-6 top-8 w-24 h-24 hidden md:block" viewBox="0 0 100 100" aria-hidden="true">
        <g stroke="#fbbf24" stroke-width="5" stroke-linecap="round">
            <path d="M50 6v12M50 82v12M6 50h12M82 50h12M19 19l8 8M73 73l8 8M19 81l8-8M73 27l8-8"/>
        </g>
        <circle cx="50" cy="50" r="22" fill="#fbbf24"/>
    </svg>

    <div class="max-w-7xl mx-auto px-4 lg:px-8 grid lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)] items-center gap-8 pt-12 lg:pt-0">
        <div class="relative z-10 lg:py-20 lg:pl-16">
            <p class="inline-flex items-center gap-2 bg-white/80 text-navy text-xs font-extrabold px-3 py-1.5 rounded-full">
                <i data-lucide="heart" class="w-3.5 h-3.5 fill-sun text-sun"></i> {{ $site['hero_badge'] }}
            </p>
            <h1 class="font-display font-semibold text-[2.6rem] sm:text-5xl leading-[1.08] mt-4 text-navy">
                {{ $site['hero_judul_1'] }}<br>
                <span class="text-sun">{{ $site['hero_kata_kuning'] }}</span> {{ $site['hero_kata_sambung'] }}<br>
                <span class="text-leaf">{{ $site['hero_kata_hijau'] }}</span>
                <svg class="inline w-16 h-5 -mb-1" viewBox="0 0 64 20" aria-hidden="true"><path d="M2 12c10 8 22 8 30 0s20-8 30 0" fill="none" stroke="#fbbf24" stroke-width="4" stroke-linecap="round"/></svg>
            </h1>
            <p class="mt-5 text-[15px] leading-relaxed max-w-md">{{ $site['hero_deskripsi'] }}</p>
            <div class="mt-7 flex flex-wrap gap-3">
                <a href="{{ route('tentang') }}" class="inline-flex items-center gap-2 bg-sun text-navy font-extrabold px-6 py-3 rounded-full shadow-[0_6px_0_#e0a40c] hover:translate-y-0.5 hover:shadow-[0_4px_0_#e0a40c] transition">
                    {{ $site['hero_tombol'] }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
                <a href="{{ route('pendaftaran.create') }}" class="inline-flex items-center gap-2 bg-white text-navy font-extrabold px-6 py-3 rounded-full border-2 border-navy/10 hover:border-sky">
                    Daftar Murid Baru
                </a>
            </div>
        </div>

        <div class="relative h-72 sm:h-96 lg:h-[460px] -mx-4 lg:mx-0 lg:-mr-8">
            @if ($hero)
                <img src="{{ $hero }}" alt="Anak-anak {{ $site['nama_sekolah'] }}" class="absolute inset-0 w-full h-full object-cover lg:rounded-bl-[80px]">
            @else
                {{-- Ilustrasi pengganti: taruh foto di public/images/hero.jpg --}}
                <div class="absolute inset-0 bg-gradient-to-br from-sky-200 via-emerald-100 to-amber-100 lg:rounded-bl-[80px] overflow-hidden">
                    <svg viewBox="0 0 600 400" class="w-full h-full" preserveAspectRatio="xMidYMax slice" aria-label="Ilustrasi anak-anak bermain">
                        <path d="M0 300c120-40 220-40 320 0s200 40 280 0v100H0z" fill="#86efac"/>
                        <rect x="380" y="140" width="120" height="120" rx="10" fill="#fff" opacity=".9"/>
                        <path d="M370 150l70-60 70 60z" fill="#e5486b"/>
                        <circle cx="440" cy="190" r="16" fill="#1f6fe5"/>
                        @foreach ([[110,'#fbbf24','#1f6fe5'],[200,'#f472b6','#fbbf24'],[290,'#fbbf24','#1f9d55']] as [$x,$baju,$rambut])
                            <circle cx="{{ $x }}" cy="235" r="30" fill="#fcd9b6"/>
                            <path d="M{{ $x-30 }} 230a30 30 0 0 1 60 0c-10-14-50-14-60 0z" fill="{{ $rambut === '#1f6fe5' ? '#3b2a1a' : '#4a3020' }}"/>
                            <circle cx="{{ $x-10 }}" cy="238" r="3" fill="#2b3a55"/><circle cx="{{ $x+10 }}" cy="238" r="3" fill="#2b3a55"/>
                            <path d="M{{ $x-9 }} 250q9 8 18 0" stroke="#2b3a55" stroke-width="3" fill="none" stroke-linecap="round"/>
                            <rect x="{{ $x-32 }}" y="266" width="64" height="70" rx="22" fill="{{ $baju }}"/>
                        @endforeach
                    </svg>
                </div>
            @endif
            @if ($gelembung)
            <div class="absolute right-6 top-6 bg-white rounded-[40%] px-5 py-3 shadow-lg rotate-3 text-center">
                <p class="font-display text-navy text-lg leading-tight font-semibold">{!! implode('<br>', array_map('e', $gelembung)) !!}</p>
            </div>
            @endif
            <svg class="absolute -bottom-px left-0 w-full h-12 text-cloud" viewBox="0 0 1200 60" preserveAspectRatio="none" aria-hidden="true"><path d="M0 40c200-30 400 20 600 10S1000-10 1200 30v30H0z" fill="currentColor"/></svg>
        </div>
    </div>
</section>

{{-- ================= KEUNGGULAN ================= --}}
<section class="max-w-7xl mx-auto px-4 lg:px-8 -mt-2 relative z-10">
    <ul class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        @foreach ($keunggulan as $i => $k)
            <li>@include('partials.keunggulan-card', ['k' => $k, 'i' => $i])</li>
        @endforeach
    </ul>
</section>

{{-- ================= ISI UTAMA ================= --}}
<div class="max-w-7xl mx-auto px-4 lg:px-8 mt-10 grid lg:grid-cols-12 gap-6">
    <div class="lg:col-span-8 space-y-6">

        {{-- Tentang + Visi Misi --}}
        <section class="grid md:grid-cols-[1fr_1.1fr_1fr] gap-5 items-stretch">
            <div class="rounded-[2rem] overflow-hidden min-h-[220px] bg-gradient-to-br from-sky-100 to-amber-50 relative">
                @if ($gedung)
                    <img src="{{ $gedung }}" alt="Gedung {{ $site['nama_sekolah'] }}" class="absolute inset-0 w-full h-full object-cover">
                @else
                    <div class="absolute inset-0 grid place-items-center">@include('partials.logo', ['size' => 120])</div>
                @endif
            </div>
            <div class="bg-white rounded-3xl p-6 border border-slate-100">
                <p class="text-xs font-extrabold text-navy/70">{{ $site['tentang_label'] }}</p>
                <h2 class="font-display text-3xl font-semibold text-navy mt-1">{{ $site['tentang_judul'] }}</h2>
                <p class="font-bold text-navy text-sm">{{ $site['tentang_subjudul'] }}</p>
                <div class="text-[13.5px] leading-relaxed mt-3 text-slate-600 space-y-2 line-clamp-[8]">{!! nl2br(e($site['tentang_isi'])) !!}</div>
                <a href="{{ route('tentang') }}" class="inline-flex items-center gap-1.5 mt-4 text-xs font-bold text-sky border border-sky/30 px-4 py-2 rounded-full hover:bg-sky-soft">Pelajari lebih banyak <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></a>
            </div>
            <div class="bg-sky-soft rounded-3xl p-6">
                <h3 class="flex items-center gap-2 font-extrabold text-navy"><i data-lucide="eye" class="w-5 h-5 text-leaf"></i> Visi</h3>
                <p class="text-[13px] text-slate-600 mt-1.5">{{ $site['visi'] }}</p>
                <h3 class="flex items-center gap-2 font-extrabold text-navy mt-5"><i data-lucide="target" class="w-5 h-5 text-sky"></i> Misi</h3>
                <ul class="mt-2 space-y-2 text-[12.5px] text-slate-600">
                    @foreach ($misi as $m)
                        <li class="flex gap-2"><i data-lucide="circle-check" class="w-4 h-4 text-leaf shrink-0 mt-px"></i>{{ $m }}</li>
                    @endforeach
                </ul>
            </div>
        </section>

        {{-- Program --}}
        <section class="bg-white rounded-3xl p-6 border border-slate-100">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="font-display text-2xl font-semibold text-navy">{{ $site['program_judul'] }}</h2>
                    <p class="text-[13px] text-slate-500 mt-1 max-w-md">{{ $site['program_deskripsi'] }}</p>
                </div>
                <a href="{{ route('program') }}" class="text-xs font-bold text-sky border border-sky/30 px-4 py-2 rounded-full hover:bg-sky-soft">Lihat semua program</a>
            </div>
            <ul class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 mt-5">
                @forelse ($programs as $p)
                    @php [$bg, $ic] = $p->kelas_warna; @endphp
                    <li class="{{ $bg }} rounded-2xl p-5">
                        <span class="w-11 h-11 grid place-items-center rounded-full {{ $ic }}"><i data-lucide="{{ $p->ikon }}" class="w-5 h-5"></i></span>
                        <h3 class="font-extrabold text-navy mt-3">{{ $p->nama }}</h3>
                        <p class="text-xs text-slate-500">{{ $p->keterangan }}</p>
                        <p class="text-[13px] text-slate-600 mt-3">{{ $p->deskripsi }}</p>
                    </li>
                @empty
                    <li class="text-sm text-slate-500">Program belum tersedia.</li>
                @endforelse
            </ul>
        </section>

        {{-- Kegiatan / Galeri --}}
        <section class="bg-white rounded-3xl p-6 border border-slate-100">
                        <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="font-display text-2xl font-semibold text-navy">{{ $site['kegiatan_judul'] }}</h2>
                    <p class="text-[13px] text-slate-500 mt-1 max-w-md">{{ $site['kegiatan_deskripsi'] }}</p>
                </div>
                <a href="{{ route('kegiatan.index') }}" class="text-xs font-bold text-sky border border-sky/30 px-4 py-2 rounded-full hover:bg-sky-soft">Lihat semua kegiatan</a>
            </div>
            <ul class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mt-5">
                @forelse ($kegiatan as $k)
                    <li>@include('partials.kegiatan-card', ['k' => $k])</li>
                @empty
                    <li class="text-sm text-slate-500 col-span-full">Dokumentasi kegiatan akan tampil di sini.</li>
                @endforelse
            </ul>
        </section>
    </div>

    {{-- Kolom kanan --}}
    <aside class="lg:col-span-4 space-y-6">
        <section class="relative overflow-hidden bg-sun-soft border-2 border-sun/40 rounded-3xl p-6">
            <span class="inline-block bg-sun text-navy font-display font-semibold px-3 py-1 rounded-full -rotate-2">{{ $site['daftar_label'] }}</span>
            <h2 class="font-display text-4xl font-semibold text-navy mt-2 leading-none">{{ $site['daftar_judul'] }}</h2>
            @php $th = now()->month >= 7 ? now()->year + 1 : now()->year; @endphp
            <p class="font-display text-lg text-navy mt-1">Tahun Ajaran {{ $site['daftar_tahun'] ?: $th.'/'.($th + 1) }}</p>
            <p class="text-[13px] text-slate-600 mt-3 max-w-[15rem]">{{ $site['daftar_deskripsi'] }}</p>
            <a href="{{ route('pendaftaran.create') }}" class="inline-flex items-center gap-2 bg-sky text-white font-extrabold px-6 py-3 rounded-xl mt-5 hover:bg-sky-dark">
                {{ $site['daftar_tombol'] }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
            @if ($gambarDaftar)
                <img src="{{ $gambarDaftar }}" alt="" class="absolute right-3 top-8 w-32 h-40 object-contain">
            @else
            <svg class="absolute right-3 top-10 w-28 h-36" viewBox="0 0 110 140" aria-hidden="true">
                <path d="M92 8l-24 8 18 8z" fill="#1f6fe5"/>
                <circle cx="55" cy="45" r="26" fill="#fcd9b6"/>
                <path d="M29 42a26 26 0 0 1 52 0c-8-12-44-12-52 0z" fill="#4a3020"/>
                <circle cx="46" cy="47" r="3" fill="#2b3a55"/><circle cx="64" cy="47" r="3" fill="#2b3a55"/>
                <path d="M45 57q10 9 20 0" stroke="#e5486b" stroke-width="4" fill="none" stroke-linecap="round"/>
                <rect x="28" y="72" width="54" height="60" rx="20" fill="#fbbf24"/>
                <path d="M82 84l18-22" stroke="#fcd9b6" stroke-width="10" stroke-linecap="round"/>
                <path d="M14 30l3 7 7 1-5 5 1 7-6-4-6 4 1-7-5-5 7-1z" fill="#fbbf24"/>
            </svg>
            @endif
            <ul class="grid grid-cols-3 gap-2 mt-6 text-[11.5px] font-bold text-navy">
                @foreach ([['file-check', $site['daftar_poin_1'], 'text-leaf'], ['info', $site['daftar_poin_2'], 'text-violet-500'], ['headset', $site['daftar_poin_3'], 'text-berry']] as [$i, $t, $c])
                    <li class="bg-white/70 rounded-xl p-2.5 flex flex-col gap-1"><i data-lucide="{{ $i }}" class="w-5 h-5 {{ $c }}"></i>{{ $t }}</li>
                @endforeach
            </ul>
        </section>

        <section class="rounded-3xl overflow-hidden bg-gradient-to-b from-sky-100 to-emerald-100 p-7 relative min-h-[260px]">
            @if ($gambarKutipan)
                <img src="{{ $gambarKutipan }}" alt="" class="absolute inset-0 w-full h-full object-cover">
            @endif
            <blockquote class="font-display text-xl font-semibold text-navy leading-snug relative z-10 {{ $gambarKutipan ? 'bg-white/80 rounded-2xl p-4' : '' }}">“{{ $site['kutipan_teks'] }}”</blockquote>
            @unless ($gambarKutipan)
            <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 400 120" aria-hidden="true">
                <path d="M0 80c80-30 160-30 240 0s120 20 160 0v40H0z" fill="#86efac"/>
                <rect x="250" y="40" width="80" height="55" rx="6" fill="#fff"/>
                <path d="M244 44l46-34 46 34z" fill="#e5486b"/>
                <circle cx="290" cy="62" r="9" fill="#fbbf24"/>
                <circle cx="80" cy="60" r="20" fill="#16a34a"/><rect x="77" y="70" width="6" height="22" fill="#92400e"/>
            </svg>
            @endunless
        </section>
    </aside>
</div>

{{-- ================= GURU & STAFF ================= --}}
<section class="max-w-7xl mx-auto px-4 lg:px-8 mt-10">
    <div class="bg-white rounded-3xl p-6 border border-slate-100">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="font-display text-2xl font-semibold text-navy">{{ $site['guru_judul'] }}</h2>
                <p class="text-[13px] text-slate-500 mt-1">{{ $site['guru_deskripsi'] }}</p>
            </div>
            <a href="{{ route('guru') }}" class="text-xs font-bold text-sky border border-sky/30 px-4 py-2 rounded-full hover:bg-sky-soft">Lihat semua</a>
        </div>
        <ul class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-5 mt-5">
            @forelse ($guru as $g)
                <li>@include('partials.staff-card', ['s' => $g])</li>
            @empty
                <li class="col-span-full text-sm text-slate-500">Data guru & staff belum ditambahkan.</li>
            @endforelse
        </ul>
    </div>
</section>

{{-- ================= BERITA ================= --}}
@if ($berita->isNotEmpty())
<section class="max-w-7xl mx-auto px-4 lg:px-8 mt-10">
    <div class="flex items-end justify-between">
        <h2 class="font-display text-2xl font-semibold text-navy">{{ $site['berita_judul'] }}</h2>
        <a href="{{ route('berita.index') }}" class="text-sm font-bold text-sky hover:underline">Semua berita</a>
    </div>
    <ul class="grid md:grid-cols-3 gap-5 mt-4">
        @foreach ($berita as $b)
            <li>@include('berita._card', ['b' => $b])</li>
        @endforeach
    </ul>
</section>
@endif
@endsection
