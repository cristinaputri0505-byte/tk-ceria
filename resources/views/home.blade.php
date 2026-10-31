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
<section class="relative overflow-hidden">
    @if ($heroLatar)
        <img src="{{ $heroLatar }}" alt="" class="absolute inset-0 w-full h-full object-cover opacity-20">
    @endif
    <div class="relative max-w-7xl mx-auto px-4 lg:px-8 pt-10 pb-16 lg:py-16 grid lg:grid-cols-2 items-center gap-10 lg:gap-14">
        <div>
            <p class="inline-flex items-center gap-2 bg-white text-navy text-sm font-extrabold px-4 py-2 rounded-full border-2 border-navy">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="#ffc53d" stroke="#2b2140" stroke-width="2" aria-hidden="true"><path d="M12 2l3 6.5 7 .8-5.2 4.8 1.5 7L12 17.6 5.7 21.1l1.5-7L2 9.3l7-.8z"/></svg>
                {{ $site['hero_badge'] }}
            </p>
            <h1 class="font-display font-extrabold text-[2.8rem] sm:text-6xl leading-[1.02] mt-5 text-navy">
                {{ $site['hero_judul_1'] }}<br>
                <span class="coret">{{ $site['hero_kata_kuning'] }}</span> {{ $site['hero_kata_sambung'] }}<br>
                <span class="text-leaf">{{ $site['hero_kata_hijau'] }}</span>
            </h1>
            <p class="mt-6 text-lg leading-relaxed max-w-lg text-ink">{{ $site['hero_deskripsi'] }}</p>
            <div class="mt-8 flex flex-wrap gap-4">
                <a href="{{ route('pendaftaran.create') }}" class="inline-flex items-center gap-2 bg-daun text-navy font-display font-bold text-lg px-6 py-3 rounded-2xl">
                    Daftar Murid Baru <i data-lucide="arrow-right" class="w-5 h-5"></i>
                </a>
                <a href="{{ route('tentang') }}" class="inline-flex items-center gap-2 bg-white text-navy font-display font-bold text-lg px-6 py-3 rounded-xl">
                    {{ $site['hero_tombol'] }}
                </a>
            </div>
        </div>

        <div class="relative px-2 sm:px-6 pb-8">
            {{-- matahari --}}
            <svg class="absolute -top-6 right-0 sm:right-2 w-20 h-20 sm:w-24 sm:h-24 z-10" viewBox="0 0 100 100" aria-hidden="true">
                <g stroke="#2b2140" stroke-width="4" stroke-linecap="round"><path d="M50 6v12M50 82v12M6 50h12M82 50h12M19 19l8 8M73 73l8 8M19 81l8-8M73 27l8-8"/></g>
                <circle cx="50" cy="50" r="22" fill="#ffc53d" stroke="#2b2140" stroke-width="3"/>
            </svg>
            <figure class="bg-white border-[2.5px] border-navy rounded-3xl p-3 pb-4 shadow-[6px_6px_0_#2b2140] -rotate-2">
                <div class="relative aspect-[4/3] rounded-2xl overflow-hidden bg-langit-soft">
                    @if ($hero)
                        <img src="{{ $hero }}" alt="Anak-anak {{ $site['nama_sekolah'] }}" class="absolute inset-0 w-full h-full object-cover">
                    @else
                        {{-- Ilustrasi pengganti: unggah foto di Pengaturan Halaman --}}
                        <svg viewBox="0 0 600 450" class="w-full h-full" preserveAspectRatio="xMidYMax slice" aria-label="Ilustrasi anak-anak bermain">
                            <path d="M0 330c120-40 220-40 320 0s200 40 280 0v120H0z" fill="#3dbe72" stroke="#2b2140" stroke-width="4"/>
                            <rect x="390" y="170" width="120" height="120" rx="10" fill="#fff" stroke="#2b2140" stroke-width="4"/>
                            <path d="M380 180l70-60 70 60z" fill="#ff8fb1" stroke="#2b2140" stroke-width="4" stroke-linejoin="round"/>
                            <circle cx="450" cy="220" r="16" fill="#6ec3ff" stroke="#2b2140" stroke-width="3"/>
                            @foreach ([[110,'#ffc53d'],[200,'#ff8fb1'],[290,'#b9a4ff']] as [$x,$baju])
                                <circle cx="{{ $x }}" cy="265" r="30" fill="#fcd9b6" stroke="#2b2140" stroke-width="3"/>
                                <path d="M{{ $x-30 }} 260a30 30 0 0 1 60 0c-10-14-50-14-60 0z" fill="#4a3020"/>
                                <circle cx="{{ $x-10 }}" cy="268" r="3" fill="#2b2140"/><circle cx="{{ $x+10 }}" cy="268" r="3" fill="#2b2140"/>
                                <path d="M{{ $x-9 }} 280q9 8 18 0" stroke="#2b2140" stroke-width="3" fill="none" stroke-linecap="round"/>
                                <rect x="{{ $x-32 }}" y="296" width="64" height="70" rx="22" fill="{{ $baju }}" stroke="#2b2140" stroke-width="3"/>
                            @endforeach
                        </svg>
                    @endif
                </div>
            </figure>
            @if ($gelembung)
                <div class="absolute -left-1 sm:left-0 bottom-0 bg-permen border-[2.5px] border-navy rounded-2xl px-4 py-2 shadow-[4px_4px_0_#2b2140] rotate-3 max-w-[16rem]">
                    <p class="font-display text-navy text-lg leading-tight font-extrabold">{{ implode(' ', $gelembung) }}</p>
                </div>
            @endif
        </div>
    </div>
</section>

{{-- ================= KEUNGGULAN ================= --}}
<svg class="block w-full h-10 text-sun-soft" viewBox="0 0 1440 60" preserveAspectRatio="none" aria-hidden="true"><path d="M0 30 C 120 0, 240 60, 360 30 S 600 0, 720 30 S 960 60, 1080 30 S 1320 0, 1440 30 V60 H0Z" fill="currentColor" stroke="#2b2140" stroke-width="3"/></svg>
<section class="bg-sun-soft pt-6 pb-14">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">
        <ul class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-5">
            @foreach ($keunggulan as $i => $k)
                <li>@include('partials.keunggulan-card', ['k' => $k, 'i' => $i])</li>
            @endforeach
        </ul>
    </div>
</section>
<div class="h-[2.5px] bg-navy" aria-hidden="true"></div>

{{-- ================= TENTANG + VISI MISI ================= --}}
<section class="max-w-7xl mx-auto px-4 lg:px-8 mt-14 grid lg:grid-cols-[1fr_1.2fr_1fr] gap-6 items-stretch">
    <div class="bg-white border-[2.5px] border-navy rounded-3xl p-3 shadow-[5px_5px_0_#2b2140] rotate-1 min-h-[240px]">
        <div class="relative h-full min-h-[210px] rounded-2xl overflow-hidden bg-daun-soft">
            @if ($gedung)
                <img src="{{ $gedung }}" alt="Gedung {{ $site['nama_sekolah'] }}" class="absolute inset-0 w-full h-full object-cover">
            @else
                <div class="absolute inset-0 grid place-items-center">@include('partials.logo', ['size' => 120])</div>
            @endif
        </div>
    </div>
    <div class="bg-white rounded-3xl p-6 sm:p-7">
        <p class="inline-block text-xs font-extrabold bg-langit border-2 border-navy rounded-full px-3 py-0.5 text-navy">{{ $site['tentang_label'] }}</p>
        <h2 class="font-display text-4xl font-extrabold text-navy mt-3 leading-none">{{ $site['tentang_judul'] }}</h2>
        <p class="font-bold text-navy mt-1">{{ $site['tentang_subjudul'] }}</p>
        <div class="text-[15px] leading-relaxed mt-3 text-ink space-y-2 line-clamp-[8]">{!! nl2br(e($site['tentang_isi'])) !!}</div>
        <a href="{{ route('tentang') }}" class="inline-flex items-center gap-1.5 mt-5 text-sm font-extrabold text-navy bg-white px-4 py-2 rounded-full">Pelajari lebih banyak <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
    </div>
    <div class="bg-leaf-soft rounded-3xl p-6 sm:p-7">
        <h3 class="flex items-center gap-2 font-display text-2xl font-extrabold text-navy"><i data-lucide="eye" class="w-6 h-6"></i> Visi</h3>
        <p class="text-[14px] text-ink mt-1.5">{{ $site['visi'] }}</p>
        <h3 class="flex items-center gap-2 font-display text-2xl font-extrabold text-navy mt-5"><i data-lucide="target" class="w-6 h-6"></i> Misi</h3>
        <ul class="mt-2 space-y-2 text-[14px] text-ink">
            @foreach ($misi as $m)
                <li class="flex gap-2"><i data-lucide="circle-check" class="w-5 h-5 text-leaf shrink-0"></i>{{ $m }}</li>
            @endforeach
        </ul>
    </div>
</section>

{{-- ================= PROGRAM ================= --}}
<section class="max-w-7xl mx-auto px-4 lg:px-8 mt-16">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 class="font-display text-4xl sm:text-5xl font-extrabold text-navy leading-none">{{ $site['program_judul'] }}</h2>
            <p class="text-lg text-ink mt-2 max-w-xl">{{ $site['program_deskripsi'] }}</p>
        </div>
        <a href="{{ route('program') }}" class="inline-flex items-center gap-2 text-sm font-extrabold text-navy bg-white px-5 py-2.5 rounded-xl">Lihat semua program</a>
    </div>
    <ul class="grid sm:grid-cols-2 xl:grid-cols-4 gap-6 mt-8">
        @forelse ($programs as $i => $p)
            @php [$bg, $ic] = $p->kelas_warna; @endphp
            <li class="{{ $bg }} rounded-3xl p-6 {{ $i % 2 ? 'miring-kanan' : 'miring-kiri' }}">
                <span class="w-14 h-14 grid place-items-center rounded-2xl {{ $ic }}"><i data-lucide="{{ $p->ikon }}" class="w-7 h-7"></i></span>
                @if ($p->keterangan)<p class="inline-block mt-4 text-xs font-extrabold bg-white border-2 border-navy rounded-full px-3 py-0.5 text-navy">{{ $p->keterangan }}</p>@endif
                <h3 class="font-display text-2xl font-extrabold text-navy mt-2 leading-tight">{{ $p->nama }}</h3>
                <p class="text-[14px] text-ink mt-2 leading-relaxed">{{ $p->deskripsi }}</p>
            </li>
        @empty
            <li class="text-ink">Program belum tersedia.</li>
        @endforelse
    </ul>
</section>

{{-- ================= KEGIATAN ================= --}}
<section class="max-w-7xl mx-auto px-4 lg:px-8 mt-16">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 class="font-display text-4xl sm:text-5xl font-extrabold text-navy leading-none">{{ $site['kegiatan_judul'] }}</h2>
            <p class="text-lg text-ink mt-2 max-w-xl">{{ $site['kegiatan_deskripsi'] }}</p>
        </div>
        <a href="{{ route('kegiatan.index') }}" class="inline-flex items-center gap-2 text-sm font-extrabold text-navy bg-white px-5 py-2.5 rounded-xl">Lihat semua kegiatan</a>
    </div>
    <ul class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-5 sm:gap-6 mt-8">
        @forelse ($kegiatan as $k)
            <li>@include('partials.kegiatan-card', ['k' => $k])</li>
        @empty
            <li class="text-ink col-span-full">Dokumentasi kegiatan akan tampil di sini.</li>
        @endforelse
    </ul>
</section>

{{-- ================= PENDAFTARAN + KUTIPAN ================= --}}
<section class="max-w-7xl mx-auto px-4 lg:px-8 mt-16 grid lg:grid-cols-[1.6fr_1fr] gap-6">
    <div class="relative overflow-hidden bg-daun border-[2.5px] border-navy rounded-3xl p-7 sm:p-9 shadow-[6px_6px_0_#2b2140]">
        <span class="inline-block bg-sun text-navy font-display font-bold px-3 py-0.5 rounded-full border-2 border-navy -rotate-2">{{ $site['daftar_label'] }}</span>
        <h2 class="font-display text-4xl sm:text-5xl font-extrabold text-navy mt-3 leading-none">{{ $site['daftar_judul'] }}</h2>
        @php $th = now()->month >= 7 ? now()->year + 1 : now()->year; @endphp
        <p class="font-display text-xl font-bold text-navy mt-1">Tahun Ajaran {{ $site['daftar_tahun'] ?: $th.'/'.($th + 1) }}</p>
        <p class="text-[15px] font-semibold text-navy mt-3 max-w-md">{{ $site['daftar_deskripsi'] }}</p>
        <a href="{{ route('pendaftaran.create') }}" class="inline-flex items-center gap-2 bg-sun text-navy font-display font-bold text-lg px-6 py-3 rounded-2xl mt-6">
            {{ $site['daftar_tombol'] }} <i data-lucide="arrow-right" class="w-5 h-5"></i>
        </a>
        <ul class="flex flex-wrap gap-2 mt-6 text-[13px] font-extrabold text-navy">
            @foreach ([['file-check', $site['daftar_poin_1']], ['info', $site['daftar_poin_2']], ['headset', $site['daftar_poin_3']]] as [$i, $t])
                <li class="bg-white border-2 border-navy rounded-full px-3 py-1.5 flex items-center gap-1.5"><i data-lucide="{{ $i }}" class="w-4 h-4"></i>{{ $t }}</li>
            @endforeach
        </ul>
        @if ($gambarDaftar)
            <img src="{{ $gambarDaftar }}" alt="" class="hidden sm:block absolute right-5 top-6 w-32 h-40 object-contain">
        @else
            <svg class="hidden sm:block absolute right-6 top-8 w-28 h-36" viewBox="0 0 110 140" aria-hidden="true">
                <path d="M92 8l-24 8 18 8z" fill="#6ec3ff" stroke="#2b2140" stroke-width="2.5"/>
                <circle cx="55" cy="45" r="26" fill="#fcd9b6" stroke="#2b2140" stroke-width="3"/>
                <path d="M29 42a26 26 0 0 1 52 0c-8-12-44-12-52 0z" fill="#4a3020"/>
                <circle cx="46" cy="47" r="3" fill="#2b2140"/><circle cx="64" cy="47" r="3" fill="#2b2140"/>
                <path d="M45 57q10 9 20 0" stroke="#2b2140" stroke-width="3.5" fill="none" stroke-linecap="round"/>
                <rect x="28" y="72" width="54" height="60" rx="20" fill="#ffc53d" stroke="#2b2140" stroke-width="3"/>
                <path d="M14 30l3 7 7 1-5 5 1 7-6-4-6 4 1-7-5-5 7-1z" fill="#ffc53d" stroke="#2b2140" stroke-width="2"/>
            </svg>
        @endif
    </div>
    <div class="relative overflow-hidden bg-anggur-soft rounded-3xl p-7 min-h-[260px]">
        @if ($gambarKutipan)
            <img src="{{ $gambarKutipan }}" alt="" class="absolute inset-0 w-full h-full object-cover">
        @endif
        <blockquote class="font-display text-2xl font-extrabold text-navy leading-snug relative z-10 {{ $gambarKutipan ? 'bg-white/90 border-2 border-navy rounded-2xl p-4' : '' }}">“{{ $site['kutipan_teks'] }}”</blockquote>
        @unless ($gambarKutipan)
            <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 400 120" aria-hidden="true">
                <path d="M0 80c80-30 160-30 240 0s120 20 160 0v40H0z" fill="#3dbe72" stroke="#2b2140" stroke-width="3"/>
                <rect x="250" y="40" width="80" height="55" rx="6" fill="#fff" stroke="#2b2140" stroke-width="3"/>
                <path d="M244 44l46-34 46 34z" fill="#ff8fb1" stroke="#2b2140" stroke-width="3" stroke-linejoin="round"/>
                <circle cx="290" cy="62" r="9" fill="#ffc53d" stroke="#2b2140" stroke-width="2.5"/>
                <circle cx="80" cy="60" r="20" fill="#1e8a4c" stroke="#2b2140" stroke-width="3"/><rect x="77" y="78" width="6" height="16" fill="#92400e"/>
            </svg>
        @endunless
    </div>
</section>

{{-- ================= GURU & STAFF ================= --}}
<section class="max-w-7xl mx-auto px-4 lg:px-8 mt-16">
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="font-display text-4xl font-extrabold text-navy leading-none">{{ $site['guru_judul'] }}</h2>
                <p class="text-lg text-ink mt-2">{{ $site['guru_deskripsi'] }}</p>
            </div>
            <a href="{{ route('guru') }}" class="inline-flex items-center gap-2 text-sm font-extrabold text-navy bg-white px-5 py-2.5 rounded-xl">Lihat semua</a>
        </div>
        <ul class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-6 mt-7">
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
<section class="max-w-7xl mx-auto px-4 lg:px-8 mt-16">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h2 class="font-display text-4xl font-extrabold text-navy leading-none">{{ $site['berita_judul'] }}</h2>
        <a href="{{ route('berita.index') }}" class="inline-flex items-center gap-2 text-sm font-extrabold text-navy bg-white px-5 py-2.5 rounded-xl">Semua berita</a>
    </div>
    <ul class="grid md:grid-cols-3 gap-6 mt-7">
        @foreach ($berita as $b)
            <li>@include('berita._card', ['b' => $b])</li>
        @endforeach
    </ul>
</section>
@endif
@endsection
