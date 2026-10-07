@extends('layouts.dashboard')
@section('title', 'Pengumuman')
@section('heading', 'Pengumuman')
@section('content')
<ul class="space-y-4 max-w-3xl">
    @forelse ($pengumuman as $p)
        <li class="bg-white rounded-3xl border {{ $p->penting ? 'border-rose-200' : 'border-slate-100' }} p-6">
            <div class="flex flex-wrap items-center gap-2">
                @if ($p->penting)<span class="text-[11px] font-extrabold bg-berry text-white px-2 py-0.5 rounded-full">Penting</span>@endif
                <span class="text-xs font-bold bg-cloud text-navy px-2 py-0.5 rounded-full">{{ $p->kelas ? 'Kelas '.$p->kelas->nama : 'Semua kelas' }}</span>
                <span class="text-xs text-slate-400">{{ $p->created_at->translatedFormat('d F Y') }}</span>
            </div>
            <h2 class="font-display text-xl font-semibold text-navy mt-2">{{ $p->judul }}</h2>
            @if ($p->tanggal_acara)
                <p class="inline-flex items-center gap-1.5 text-sm font-bold text-amber-700 bg-sun-soft rounded-xl px-3 py-1.5 mt-2"><i data-lucide="calendar-days" class="w-4 h-4"></i> {{ $p->tanggal_acara->translatedFormat('l, d F Y') }}</p>
            @endif
            <p class="text-[15px] text-slate-600 mt-3 whitespace-pre-line leading-relaxed">{{ $p->isi }}</p>
        </li>
    @empty
        <li class="bg-white rounded-3xl border border-slate-100 p-8 text-center text-slate-500">Belum ada pengumuman.</li>
    @endforelse
</ul>
<div class="mt-5">{{ $pengumuman->links() }}</div>
@endsection
