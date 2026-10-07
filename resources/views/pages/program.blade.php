@extends('layouts.public')
@section('title', $site['program_banner_judul'].' — '.$site['nama_sekolah'])
@section('content')
@include('partials.page-banner', ['p' => 'program', 'crumb' => 'Program'])
<div class="max-w-7xl mx-auto px-4 lg:px-8 py-12 space-y-6">
    @forelse ($programs as $i => $p)
        @php [$bg, $ic] = $p->kelas_warna; @endphp
        <article class="{{ $bg }} rounded-[2rem] p-6 sm:p-8 grid {{ $p->gambar ? 'md:grid-cols-[1fr_1.4fr]' : '' }} gap-8 items-center">
            @if ($p->gambar)
                <img src="{{ asset('storage/'.$p->gambar) }}" alt="{{ $p->nama }}" class="w-full aspect-[4/3] object-cover rounded-3xl {{ $i % 2 ? 'md:order-2' : '' }}">
            @endif
            <div>
                <span class="w-14 h-14 grid place-items-center rounded-full {{ $ic }}"><i data-lucide="{{ $p->ikon }}" class="w-7 h-7"></i></span>
                <h2 class="font-display text-3xl font-semibold text-navy mt-4">{{ $p->nama }}</h2>
                @if ($p->keterangan)<p class="font-bold text-slate-500">{{ $p->keterangan }}</p>@endif
                <p class="mt-3 text-[15px] font-semibold">{{ $p->deskripsi }}</p>
                @if ($p->detail)<div class="mt-3 text-[15px] leading-relaxed space-y-3 text-slate-600">{!! nl2br(e($p->detail)) !!}</div>@endif
                <a href="{{ route('pendaftaran.create') }}" class="inline-flex items-center gap-2 mt-5 bg-sky text-white font-extrabold px-5 py-2.5 rounded-xl hover:bg-sky-dark">Daftar program ini <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
            </div>
        </article>
    @empty
        <p class="text-slate-500">Program belum tersedia.</p>
    @endforelse
</div>
@endsection
