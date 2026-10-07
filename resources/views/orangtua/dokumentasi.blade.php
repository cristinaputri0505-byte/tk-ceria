@extends('layouts.dashboard')
@section('title', 'Dokumentasi')
@section('heading', 'Dokumentasi Kegiatan')
@section('content')
<p class="text-slate-500 mb-4">{{ $site['portal_dokumentasi_info'] }}</p>
@if ($anak->count() > 1)
    <nav class="flex flex-wrap gap-2 mb-5" aria-label="Pilih anak">
        <a href="{{ route('orangtua.dokumentasi') }}" class="px-4 py-2 rounded-full text-sm font-bold border {{ ! $pilih ? 'bg-navy text-white border-navy' : 'bg-white text-navy border-slate-200' }}">Semua anak</a>
        @foreach ($anak as $a)
            <a href="{{ route('orangtua.dokumentasi', ['anak' => $a->id]) }}" class="px-4 py-2 rounded-full text-sm font-bold border {{ $pilih?->id === $a->id ? 'bg-navy text-white border-navy' : 'bg-white text-navy border-slate-200' }}">{{ $a->nama_panggilan }}</a>
        @endforeach
    </nav>
@endif
<ul class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
    @forelse ($foto as $d)
        <li class="bg-white rounded-3xl border border-slate-100 p-2">
            <button type="button" data-foto="{{ $d->url }}" data-judul="{{ $d->keterangan }} · {{ $d->tanggal->translatedFormat('d M Y') }}" class="block w-full text-left">
                <img src="{{ $d->url }}" alt="{{ $d->keterangan ?? 'Dokumentasi' }}" class="w-full aspect-square object-cover rounded-2xl" loading="lazy">
                <span class="block px-2 pt-2 text-sm font-bold text-navy truncate">{{ $d->keterangan ?? 'Kegiatan kelas' }}</span>
                <span class="block px-2 pb-1 text-xs text-slate-400">{{ $d->tanggal->translatedFormat('d F Y') }}{{ $d->siswa ? ' · '.$d->siswa->nama_panggilan : '' }}</span>
            </button>
        </li>
    @empty
        <li class="col-span-full bg-white rounded-3xl border border-slate-100 p-8 text-center text-slate-500">Belum ada foto dokumentasi.</li>
    @endforelse
</ul>
@if ($foto instanceof \Illuminate\Contracts\Pagination\Paginator)<div class="mt-5">{{ $foto->links() }}</div>@endif
@include('partials.lightbox')
@endsection
