@extends('layouts.dashboard')
@section('title', 'Siswa: ' . $tugas->judul)
@section('heading', 'Siapa yang Sudah Mengerjakan?')
@section('actions')
    <a href="{{ route('elearning.index', ['kelas_id' => $kelasId]) }}" class="inline-flex items-center gap-2 border border-slate-200 bg-white text-navy text-sm font-bold px-4 py-2.5 rounded-xl hover:bg-cloud">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> <span>Daftar tugas</span>
    </a>
@endsection
@section('content')
@php $total = $sudah->count() + $belum->count(); @endphp

{{-- Ringkasan tugas --}}
<div class="bg-white rounded-3xl border border-slate-100 p-5 mb-5 flex flex-col md:flex-row md:items-center gap-4">
    <div class="flex items-start gap-3 flex-1 min-w-0">
        <span class="w-14 h-14 shrink-0 rounded-2xl bg-cloud grid place-items-center text-3xl">{{ $tugas->ikon }}</span>
        <div class="min-w-0">
            <div class="flex flex-wrap gap-1.5 mb-1">
                <span class="text-[11px] font-bold bg-sky-soft text-sky px-2 py-0.5 rounded-full">{{ $tugas->label_tipe }}</span>
                <span class="text-[11px] font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">⭐ +{{ $tugas->bintang }}</span>
                <span class="text-[11px] font-bold bg-cloud text-slate-600 px-2 py-0.5 rounded-full">{{ $tugas->kelas?->nama ?? 'Semua kelas' }}</span>
            </div>
            <p class="font-bold text-navy leading-snug">{{ $tugas->judul }}</p>
            <p class="text-xs text-slate-500 line-clamp-2">{{ $tugas->instruksi }}</p>
        </div>
    </div>
    <div class="flex gap-3 shrink-0">
        <div class="bg-leaf-soft rounded-2xl px-4 py-2 text-center">
            <p class="text-2xl font-extrabold text-leaf">{{ $sudah->count() }}</p>
            <p class="text-xs font-bold text-leaf">Sudah</p>
        </div>
        <div class="bg-cloud rounded-2xl px-4 py-2 text-center">
            <p class="text-2xl font-extrabold text-slate-600">{{ $belum->count() }}</p>
            <p class="text-xs font-bold text-slate-500">Belum</p>
        </div>
    </div>
</div>

@if ($kelasList->count() > 1)
<div class="flex flex-wrap gap-2 mb-5">
    <a href="{{ route('elearning.hasil', $tugas) }}" class="text-sm font-bold px-4 py-2 rounded-full border {{ ! $kelasId ? 'bg-navy text-white border-navy' : 'bg-white text-navy border-slate-200 hover:border-sky' }}">Semua kelas</a>
    @foreach ($kelasList as $k)
        <a href="{{ route('elearning.hasil', [$tugas, 'kelas_id' => $k->id]) }}" class="text-sm font-bold px-4 py-2 rounded-full border {{ $kelasId === $k->id ? 'bg-navy text-white border-navy' : 'bg-white text-navy border-slate-200 hover:border-sky' }}">{{ $k->nama }}</a>
    @endforeach
</div>
@endif

@if ($total === 0)
    <p class="bg-white rounded-3xl border border-slate-100 p-8 text-center text-slate-500">Belum ada siswa di kelas ini.</p>
@else
<div class="grid lg:grid-cols-2 gap-5">
    {{-- Sudah mengerjakan --}}
    <section class="bg-white rounded-3xl border border-slate-100 p-5">
        <h2 class="font-bold text-navy flex items-center gap-2 mb-3"><i data-lucide="circle-check" class="w-5 h-5 text-leaf"></i> Sudah mengerjakan ({{ $sudah->count() }})</h2>
        <ul class="divide-y divide-slate-100">
            @forelse ($sudah as $s)
                @php $h = $hasil[$s->id]; @endphp
                <li class="py-3 flex items-center gap-3">
                    @include('partials.foto-anak', ['s' => $s, 'size' => 'w-11 h-11 !border-2', 'teks' => 'text-lg'])
                    <div class="min-w-0 flex-1">
                        <p class="font-bold text-navy truncate">{{ $s->nama }}</p>
                        <p class="text-xs text-slate-500">
                            {{ $s->kelas?->nama }} · {{ $h->selesai_pada->translatedFormat('j M Y, H:i') }}
                            · dimainkan {{ $h->dimainkan }}×
                        </p>
                    </div>
                    @if ($h->gambar)
                        <button type="button" data-foto="{{ $h->gambar_url }}" data-judul="{{ $s->nama_panggilan }}: {{ $tugas->judul }}" class="shrink-0" title="Lihat karya">
                            <img src="{{ $h->gambar_url }}" alt="Karya {{ $s->nama_panggilan }}" class="w-16 h-11 object-cover rounded-lg border border-slate-200" loading="lazy">
                        </button>
                    @endif
                    <span class="shrink-0 bg-amber-50 text-amber-700 font-extrabold text-xs px-3 py-1 rounded-full">⭐ {{ $h->bintang }}</span>
                </li>
            @empty
                <li class="py-6 text-center text-sm text-slate-500">Belum ada siswa yang mengerjakan.</li>
            @endforelse
        </ul>
    </section>

    {{-- Belum mengerjakan --}}
    <section class="bg-white rounded-3xl border border-slate-100 p-5">
        <h2 class="font-bold text-navy flex items-center gap-2 mb-3"><i data-lucide="clock" class="w-5 h-5 text-slate-400"></i> Belum mengerjakan ({{ $belum->count() }})</h2>
        <ul class="divide-y divide-slate-100">
            @forelse ($belum as $s)
                <li class="py-3 flex items-center gap-3">
                    @include('partials.foto-anak', ['s' => $s, 'size' => 'w-11 h-11 !border-2', 'teks' => 'text-lg'])
                    <div class="min-w-0 flex-1">
                        <p class="font-bold text-navy truncate">{{ $s->nama }}</p>
                        <p class="text-xs text-slate-500">{{ $s->kelas?->nama }}{{ optional($hasil->get($s->id))->dimainkan ? ' · sudah mencoba, belum selesai' : '' }}</p>
                    </div>
                </li>
            @empty
                <li class="py-6 text-center text-sm text-slate-500">🎉 Semua siswa sudah mengerjakan!</li>
            @endforelse
        </ul>
    </section>
</div>
@include('partials.lightbox')
@endif
@endsection
