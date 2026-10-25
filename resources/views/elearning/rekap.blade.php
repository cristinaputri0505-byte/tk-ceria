@extends('layouts.dashboard')
@section('title', 'Rekap E-Learning')
@section('heading', 'Rekap E-Learning Anak')
@section('actions')
    <a href="{{ route('elearning.index', ['kelas_id' => $kelas?->id]) }}" class="inline-flex items-center gap-2 border border-slate-200 bg-white text-navy text-sm font-bold px-4 py-2.5 rounded-xl hover:bg-cloud">
        <i data-lucide="gamepad-2" class="w-4 h-4"></i> <span>Daftar tugas</span>
    </a>
@endsection
@section('content')
@if ($kelasList->isEmpty())
    <p class="text-slate-500">Belum ada kelas.</p>
@else
<div class="flex flex-wrap gap-2 mb-5">
    @foreach ($kelasList as $k)
        <a href="{{ route('elearning.rekap', ['kelas_id' => $k->id]) }}" class="text-sm font-bold px-4 py-2 rounded-full border {{ $kelas?->id === $k->id ? 'bg-navy text-white border-navy' : 'bg-white text-navy border-slate-200 hover:border-sky' }}">{{ $k->nama }}</a>
    @endforeach
</div>

<p class="text-sm text-slate-500 mb-4">{{ $tugas->count() }} tugas aktif untuk kelas {{ $kelas->nama }}. Bintang dihitung sekali per tugas.</p>

<ul class="grid md:grid-cols-2 gap-4">
    @forelse ($siswa as $s)
        @php
            $h = ($hasil[$s->id] ?? collect())->keyBy('tugas_id');
            $selesai = $h->filter(fn ($x) => $x->selesai_pada)->count();
            $bintang = $h->sum('bintang');
            $karya = $h->filter(fn ($x) => $x->gambar);
        @endphp
        <li class="bg-white rounded-3xl border border-slate-100 p-5">
            <div class="flex items-center gap-3">
                @include('partials.foto-anak', ['s' => $s, 'size' => 'w-12 h-12 !border-2', 'teks' => 'text-xl'])
                <div class="min-w-0 flex-1">
                    <p class="font-bold text-navy truncate">{{ $s->nama }}</p>
                    <p class="text-xs text-slate-500">Selesai {{ $selesai }}/{{ $tugas->count() }} tugas</p>
                </div>
                <span class="shrink-0 bg-amber-50 text-amber-700 font-extrabold text-sm px-3 py-1.5 rounded-full">⭐ {{ $bintang }}</span>
            </div>
            <div class="w-full h-2 bg-cloud rounded-full mt-3 overflow-hidden">
                <div class="h-full bg-leaf rounded-full" style="width: {{ $tugas->count() ? round($selesai / $tugas->count() * 100) : 0 }}%"></div>
            </div>
            <ul class="mt-3 flex flex-wrap gap-1.5">
                @foreach ($tugas as $t)
                    <li class="text-xs px-2 py-1 rounded-lg {{ optional($h->get($t->id))->selesai_pada ? 'bg-leaf-soft text-leaf font-bold' : 'bg-cloud text-slate-500' }}" title="{{ $t->judul }}">{{ $t->ikon }} {{ \Illuminate\Support\Str::limit($t->judul, 18) }}</li>
                @endforeach
            </ul>
            @if ($karya->isNotEmpty())
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($karya as $k)
                        <button type="button" data-foto="{{ $k->gambar_url }}" data-judul="{{ $s->nama_panggilan }}: {{ optional($tugas->firstWhere('id', $k->tugas_id))->judul }}" class="block">
                            <img src="{{ $k->gambar_url }}" alt="Karya {{ $s->nama_panggilan }}" class="w-24 h-16 object-cover rounded-xl border border-slate-200" loading="lazy">
                        </button>
                    @endforeach
                </div>
            @endif
        </li>
    @empty
        <li class="text-slate-500">Belum ada siswa di kelas ini.</li>
    @endforelse
</ul>
@include('partials.lightbox')
@endif
@endsection
