@extends('layouts.public')
@section('title', $kegiatan->judul.' — '.$site['nama_sekolah'])
@section('content')
<article class="max-w-3xl mx-auto px-4 py-12">
    <a href="{{ route('kegiatan.index') }}" class="inline-flex items-center gap-1 text-sm font-bold text-sky"><i data-lucide="arrow-left" class="w-4 h-4"></i> Semua kegiatan</a>
    <p class="text-sm font-bold text-slate-400 mt-6">{{ $kegiatan->tanggal->translatedFormat('l, d F Y') }}</p>
    <h1 class="font-display text-4xl font-semibold text-navy mt-1">{{ $kegiatan->judul }}</h1>
    @if ($kegiatan->gambar)
        <button type="button" data-foto="{{ $kegiatan->sampul_url }}" data-judul="{{ $kegiatan->judul }}" class="block w-full mt-6">
            <img src="{{ $kegiatan->sampul_url }}" alt="{{ $kegiatan->judul }}" class="w-full rounded-3xl">
        </button>
    @endif
    @if ($kegiatan->deskripsi)<div class="mt-6 text-[16px] leading-[1.8] space-y-4">{!! nl2br(e($kegiatan->deskripsi)) !!}</div>@endif

    @if (count($foto) > 1)
        <h2 class="font-display text-2xl font-semibold text-navy mt-10">Foto kegiatan <span class="text-base text-slate-400">({{ count($foto) }})</span></h2>
        <ul class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4">
            @foreach ($foto as $i => $url)
                <li>
                    <button type="button" data-foto="{{ $url }}" data-judul="{{ $kegiatan->judul }} ({{ $i + 1 }}/{{ count($foto) }})" class="block w-full">
                        <img src="{{ $url }}" alt="Foto {{ $i + 1 }} {{ $kegiatan->judul }}" class="w-full aspect-square object-cover rounded-2xl" loading="lazy">
                    </button>
                </li>
            @endforeach
        </ul>
    @endif
</article>
@include('partials.lightbox')
@if ($lainnya->isNotEmpty())
<section class="max-w-7xl mx-auto px-4 lg:px-8">
    <h2 class="font-display text-2xl font-semibold text-navy">Kegiatan lainnya</h2>
    <ul class="grid grid-cols-2 lg:grid-cols-4 gap-5 mt-4">
        @foreach ($lainnya as $k)<li>@include('partials.kegiatan-card', ['k' => $k, 'tanggal' => true])</li>@endforeach
    </ul>
</section>
@endif
@endsection
