@extends('layouts.dashboard')
@section('title', 'Kegiatan')
@section('heading', 'Kegiatan & Galeri')
@section('actions') @include('partials.btn-tambah', ['href' => route('admin.kegiatan.create'), 'label' => 'Tambah kegiatan']) @endsection
@section('content')
<p class="text-sm text-slate-500 mb-5">Lima kegiatan terbaru tampil di halaman depan bagian "Kegiatan Kami".</p>
<ul class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
    @forelse ($kegiatan as $k)
        <li class="bg-white rounded-3xl border border-slate-100 overflow-hidden">
            <div class="aspect-[4/3] bg-gradient-to-br from-amber-100 to-sky-100 grid place-items-center">
                @if ($k->gambar)<img src="{{ Storage::disk('public')->url($k->gambar) }}" alt="" class="w-full h-full object-cover">@else<i data-lucide="image" class="w-8 h-8 text-navy/30"></i>@endif
            </div>
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
