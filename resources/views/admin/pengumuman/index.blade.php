@extends('layouts.dashboard')
@section('title', 'Pengumuman')
@section('heading', 'Pengumuman untuk Orang Tua')
@section('actions') @include('partials.btn-tambah', ['href' => route('admin.pengumuman.create'), 'label' => 'Tulis pengumuman']) @endsection
@section('content')
<p class="text-sm text-slate-500 mb-5">Pengumuman tampil di panel orang tua. Isi tanggal acara agar muncul juga di kolom Agenda.</p>
<ul class="space-y-3">
    @forelse ($pengumuman as $p)
        <li class="bg-white rounded-3xl border border-slate-100 p-5 flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    @if ($p->penting)<span class="text-[11px] font-extrabold bg-berry text-white px-2 py-0.5 rounded-full">Penting</span>@endif
                    <span class="text-xs font-bold bg-cloud text-navy px-2 py-0.5 rounded-full">{{ $p->kelas ? 'Kelas '.$p->kelas->nama : 'Semua kelas' }}</span>
                    @if ($p->tanggal_acara)<span class="text-xs font-bold bg-sun-soft text-amber-700 px-2 py-0.5 rounded-full">Acara {{ $p->tanggal_acara->translatedFormat('d M Y') }}</span>@endif
                </div>
                <p class="font-extrabold text-navy mt-1">{{ $p->judul }}</p>
                <p class="text-sm text-slate-500 line-clamp-2">{{ $p->isi }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ $p->created_at->translatedFormat('d M Y, H.i') }} · {{ $p->penulis->name ?? 'Admin' }}</p>
            </div>
            <div class="shrink-0">
                @include('partials.edit-link', ['href' => route('admin.pengumuman.edit', $p)])
                @include('partials.hapus', ['action' => route('admin.pengumuman.destroy', $p)])
            </div>
        </li>
    @empty
        <li class="bg-white rounded-3xl border border-slate-100 p-8 text-center text-slate-500">Belum ada pengumuman.</li>
    @endforelse
</ul>
<div class="mt-5">{{ $pengumuman->links() }}</div>
@endsection
