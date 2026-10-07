@extends('layouts.public')
@section('title', $berita->judul.' — '.$site['nama_sekolah'])
@section('content')
<article class="max-w-3xl mx-auto px-4 py-12">
    <a href="{{ route('berita.index') }}" class="inline-flex items-center gap-1 text-sm font-bold text-sky"><i data-lucide="arrow-left" class="w-4 h-4"></i> Semua berita</a>
    <p class="text-sm font-bold text-slate-400 mt-6">{{ $berita->published_at->translatedFormat('l, d F Y') }}</p>
    <h1 class="font-display text-4xl font-semibold text-navy mt-1 leading-tight">{{ $berita->judul }}</h1>
    @if ($berita->gambar)
        <img src="{{ Storage::disk('public')->url($berita->gambar) }}" alt="" class="w-full rounded-3xl mt-6">
    @endif
    <div class="mt-6 text-[16px] leading-[1.8] space-y-4">
        @foreach (preg_split("/\n\s*\n/", e($berita->isi)) as $par)
            <p>{!! nl2br($par) !!}</p>
        @endforeach
    </div>
</article>
@if ($lainnya->isNotEmpty())
<section class="max-w-7xl mx-auto px-4 lg:px-8">
    <h2 class="font-display text-2xl font-semibold text-navy">Berita lainnya</h2>
    <ul class="grid md:grid-cols-3 gap-5 mt-4">
        @foreach ($lainnya as $b)<li>@include('berita._card', ['b' => $b])</li>@endforeach
    </ul>
</section>
@endif
@endsection
