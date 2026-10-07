@extends('layouts.public')
@section('title', $site['tentang_banner_judul'].' — '.$site['nama_sekolah'])
@section('content')
@include('partials.page-banner', ['p' => 'tentang', 'crumb' => 'Tentang Kami'])
@php
    $gedung = \App\Support\Pengaturan::url('tentang_gambar', 'images/gedung.jpg');
    $kepsekFoto = \App\Support\Pengaturan::url('kepsek_foto');
    $misi = \App\Support\Pengaturan::baris('misi');
    $fasilitas = \App\Support\Pengaturan::baris('fasilitas');
@endphp

<div class="max-w-7xl mx-auto px-4 lg:px-8 py-12 space-y-16">
    {{-- Profil --}}
    <section class="grid lg:grid-cols-2 gap-10 items-center">
        <div class="rounded-[2.5rem] overflow-hidden aspect-[4/3] bg-gradient-to-br from-sky-100 to-amber-50 grid place-items-center">
            @if ($gedung)<img src="{{ $gedung }}" alt="Gedung {{ $site['nama_sekolah'] }}" class="w-full h-full object-cover">@else @include('partials.logo', ['size' => 140]) @endif
        </div>
        <div>
            <p class="text-sm font-extrabold text-leaf">{{ $site['tentang_label'] }}</p>
            <h2 class="font-display text-4xl font-semibold text-navy mt-1">{{ $site['tentang_judul'] }}</h2>
            <p class="font-bold text-navy mt-1">{{ $site['tentang_subjudul'] }}</p>
            <div class="mt-4 text-[15px] leading-relaxed space-y-3">{!! nl2br(e($site['tentang_isi'])) !!}</div>
            <dl class="grid grid-cols-3 gap-3 mt-6">
                <div class="bg-white rounded-2xl p-4 border border-slate-100"><dt class="text-xs text-slate-500">Program</dt><dd class="font-display text-3xl font-semibold text-navy">{{ $jumlahProgram }}</dd></div>
                <div class="bg-white rounded-2xl p-4 border border-slate-100"><dt class="text-xs text-slate-500">Guru & staff</dt><dd class="font-display text-3xl font-semibold text-navy">{{ $jumlahGuru }}</dd></div>
                <div class="bg-white rounded-2xl p-4 border border-slate-100"><dt class="text-xs text-slate-500">Fasilitas</dt><dd class="font-display text-3xl font-semibold text-navy">{{ count($fasilitas) }}</dd></div>
            </dl>
        </div>
    </section>

    {{-- Sambutan kepala sekolah --}}
    @if ($site['kepsek_sambutan'])
    <section class="bg-sun-soft rounded-[2.5rem] p-8 sm:p-12 grid md:grid-cols-[200px_1fr] gap-8 items-center">
        <div class="text-center">
            @if ($kepsekFoto)
                <img src="{{ $kepsekFoto }}" alt="{{ $site['kepsek_nama'] }}" class="w-44 h-44 mx-auto rounded-full object-cover border-8 border-white">
            @else
                <span class="w-44 h-44 mx-auto rounded-full grid place-items-center bg-white"><i data-lucide="user-round" class="w-16 h-16 text-sun"></i></span>
            @endif
        </div>
        <div>
            <h2 class="font-display text-2xl font-semibold text-navy">Sambutan {{ $site['kepsek_jabatan'] }}</h2>
            <div class="mt-3 text-[15px] leading-relaxed space-y-3">{!! nl2br(e($site['kepsek_sambutan'])) !!}</div>
            <p class="mt-4 font-extrabold text-navy">{{ $site['kepsek_nama'] }}</p>
            <p class="text-sm text-slate-500">{{ $site['kepsek_jabatan'] }}</p>
        </div>
    </section>
    @endif

    {{-- Visi Misi + Sejarah --}}
    <section class="grid lg:grid-cols-2 gap-6">
        <div class="bg-sky-soft rounded-3xl p-8">
            <h2 class="flex items-center gap-2 font-display text-2xl font-semibold text-navy"><i data-lucide="eye" class="w-6 h-6 text-leaf"></i> Visi</h2>
            <p class="mt-2 text-[15px]">{{ $site['visi'] }}</p>
            <h2 class="flex items-center gap-2 font-display text-2xl font-semibold text-navy mt-6"><i data-lucide="target" class="w-6 h-6 text-sky"></i> Misi</h2>
            <ul class="mt-3 space-y-2 text-[15px]">
                @foreach ($misi as $m)<li class="flex gap-2"><i data-lucide="circle-check" class="w-5 h-5 text-leaf shrink-0 mt-0.5"></i>{{ $m }}</li>@endforeach
            </ul>
        </div>
        <div class="bg-white rounded-3xl p-8 border border-slate-100">
            <h2 class="flex items-center gap-2 font-display text-2xl font-semibold text-navy"><i data-lucide="history" class="w-6 h-6 text-berry"></i> {{ $site['sejarah_judul'] }}</h2>
            <div class="mt-3 text-[15px] leading-relaxed space-y-3">{!! nl2br(e($site['sejarah_isi'])) !!}</div>
            @if ($fasilitas)
                <h2 class="flex items-center gap-2 font-display text-2xl font-semibold text-navy mt-6"><i data-lucide="building-2" class="w-6 h-6 text-amber-500"></i> Fasilitas</h2>
                <ul class="mt-3 grid sm:grid-cols-2 gap-2 text-[14px]">
                    @foreach ($fasilitas as $fs)<li class="flex gap-2 bg-cloud rounded-xl px-3 py-2"><i data-lucide="check" class="w-4 h-4 text-leaf shrink-0 mt-0.5"></i>{{ $fs }}</li>@endforeach
                </ul>
            @endif
        </div>
    </section>

    {{-- Keunggulan --}}
    @if ($keunggulan->isNotEmpty())
    <section>
        <h2 class="font-display text-3xl font-semibold text-navy text-center">Mengapa memilih {{ $site['nama_sekolah'] }}?</h2>
        <ul class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-8">
            @foreach ($keunggulan as $i => $k)<li>@include('partials.keunggulan-card', ['k' => $k, 'i' => $i])</li>@endforeach
        </ul>
    </section>
    @endif
</div>
@endsection
