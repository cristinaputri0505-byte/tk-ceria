@extends('layouts.dashboard')
@section('title', 'Kegiatan')
@section('heading', 'Kegiatan & Galeri')
@section('actions') @include('partials.btn-tambah', ['href' => route('admin.kegiatan.create'), 'label' => 'Tambah kegiatan']) @endsection
@section('content')
<p class="text-sm text-slate-500 mb-5">Lima kegiatan terbaru tampil di halaman depan bagian "Kegiatan Kami". Klik kartu atau ikon pensil untuk mengunggah foto kegiatan (bisa banyak foto sekaligus).</p>
<ul class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
    @forelse ($kegiatan as $k)
        <li class="bg-white rounded-3xl border border-slate-100 overflow-hidden">
            <a href="{{ route('admin.kegiatan.edit', $k) }}" class="relative aspect-[4/3] bg-gradient-to-br from-amber-100 to-sky-100 grid place-items-center">
                @if ($k->gambar)
                    <img src="{{ $k->sampul_url }}" alt="" class="w-full h-full object-cover">
                @else
                    <span class="text-center text-xs font-bold text-navy/50"><i data-lucide="image" class="w-8 h-8 mx-auto mb-1"></i>Belum ada foto<br>klik untuk menambah</span>
                @endif
                @if (isset($k->foto_count) && $k->foto_count > 0)
                    <span class="absolute bottom-2 right-2 bg-navy-deep/60 text-white text-[11px] font-bold px-2 py-0.5 rounded-full inline-flex items-center gap-1"><i data-lucide="images" class="w-3 h-3"></i>{{ $k->foto_count }}</span>
                @endif
            </a>
            <div class="p-4 flex items-start justify-between gap-2">
                <div><p class="font-bold text-navy">{{ $k->judul }}</p><p class="text-xs text-slate-500">{{ $k->tanggal->translatedFormat('d F Y') }}</p></div>
                <div class="shrink-0">
                    @include('partials.edit-link', ['href' => route('admin.kegiatan.edit', $k)])
                    @include('partials.hapus', ['action' => route('admin.kegiatan.destroy', $k)])
                </div>
            </div>
        </li>
    @empty
        <li class="col-span-full text-slate-500">Belum ada kegiatan. Unggah foto kegiatan pertama agar galeri di halaman depan terisi.</li>
    @endforelse
</ul>
<div class="mt-5">{{ $kegiatan->links() }}</div>
@endsection
