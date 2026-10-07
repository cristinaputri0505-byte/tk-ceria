@extends('layouts.public')
@section('title', $site['galeri_banner_judul'].' — '.$site['nama_sekolah'])
@section('content')
@include('partials.page-banner', ['p' => 'galeri', 'crumb' => 'Galeri'])
<div class="max-w-7xl mx-auto px-4 lg:px-8 py-12">
    @if ($albums->isNotEmpty())
        <nav class="flex flex-wrap gap-2 mb-8" aria-label="Album">
            <a href="{{ route('galeri') }}" class="px-4 py-2 rounded-full text-sm font-bold {{ ! $album ? 'bg-navy text-white' : 'bg-white text-navy border border-slate-200' }}">Semua</a>
            @foreach ($albums as $a)
                <a href="{{ route('galeri', ['album' => $a]) }}" class="px-4 py-2 rounded-full text-sm font-bold {{ $album === $a ? 'bg-navy text-white' : 'bg-white text-navy border border-slate-200' }}">{{ $a }}</a>
            @endforeach
        </nav>
    @endif

    <ul class="columns-2 sm:columns-3 lg:columns-4 gap-4 [&>li]:mb-4">
        @forelse ($foto as $g)
            <li class="break-inside-avoid">
                <button type="button" class="block w-full text-left group" data-foto="{{ Storage::disk('public')->url($g->gambar) }}" data-judul="{{ $g->judul }}">
                    <img src="{{ Storage::disk('public')->url($g->gambar) }}" alt="{{ $g->judul ?? 'Foto kegiatan' }}" class="w-full rounded-2xl" loading="lazy">
                    @if ($g->judul)<span class="block text-[13px] font-bold text-navy mt-1.5">{{ $g->judul }}</span>@endif
                </button>
            </li>
        @empty
            <li class="text-slate-500">Belum ada foto di galeri.</li>
        @endforelse
    </ul>
    <div class="mt-8">{{ $foto->links() }}</div>
</div>

<dialog id="lightbox" class="backdrop:bg-navy-deep/80 bg-transparent p-0 max-w-5xl w-[92vw]">
    <figure class="relative">
        <img id="lbImg" src="" alt="" class="w-full max-h-[80vh] object-contain rounded-2xl">
        <figcaption id="lbCap" class="text-white font-bold text-center mt-3"></figcaption>
        <button type="button" onclick="lightbox.close()" class="absolute -top-3 -right-3 w-10 h-10 grid place-items-center rounded-full bg-white text-navy" aria-label="Tutup"><i data-lucide="x" class="w-5 h-5"></i></button>
    </figure>
</dialog>
@endsection
@push('scripts')
<script>
    const lightbox = document.getElementById('lightbox');
    document.querySelectorAll('[data-foto]').forEach(b => b.addEventListener('click', () => {
        document.getElementById('lbImg').src = b.dataset.foto;
        document.getElementById('lbImg').alt = b.dataset.judul || '';
        document.getElementById('lbCap').textContent = b.dataset.judul || '';
        lightbox.showModal();
    }));
    lightbox.addEventListener('click', e => { if (e.target === lightbox) lightbox.close(); });
</script>
@endpush
