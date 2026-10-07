@extends('layouts.dashboard')
@section('title', $m['judul'])
@section('heading', $m['judul'])
@section('actions') @include('partials.btn-tambah', ['href' => route($m['route'].'.create'), 'label' => 'Tambah '.$m['satuan']]) @endsection
@section('content')
@php
    $f = collect($m['fields']);
    $kolomGambar = $f->filter(fn ($x) => $x[1] === 'image')->keys()->first();
    $kolomIkon = $f->filter(fn ($x) => $x[1] === 'ikon')->keys()->first();
    $kolomJudul = $f->filter(fn ($x) => $x[3]['judul'] ?? false)->keys()->first();
    $kolomSub = $f->filter(fn ($x) => $x[3]['sub'] ?? false)->keys()->first();
@endphp
<p class="text-sm text-slate-500 mb-5">{{ $m['keterangan'] }}</p>

<ul class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
    @forelse ($items as $item)
        <li class="bg-white rounded-3xl border border-slate-100 overflow-hidden flex flex-col">
            @if ($kolomGambar)
                <div class="aspect-[4/3] bg-gradient-to-br from-sky-100 to-amber-50 grid place-items-center">
                    @if ($item->$kolomGambar)
                        <img src="{{ Storage::disk('public')->url($item->$kolomGambar) }}" alt="" class="w-full h-full object-cover" loading="lazy">
                    @else
                        <span class="font-display text-3xl text-sky">{{ mb_substr($item->$kolomJudul ?? '?', 0, 1) }}</span>
                    @endif
                </div>
            @endif
            <div class="p-4 flex-1 flex flex-col">
                @if ($kolomIkon)
                    <span class="w-10 h-10 grid place-items-center rounded-full bg-sky-soft text-sky mb-2"><i data-lucide="{{ $item->$kolomIkon }}" class="w-5 h-5"></i></span>
                @endif
                <p class="font-bold text-navy">{{ $item->$kolomJudul ?: 'Tanpa keterangan' }}</p>
                @if ($kolomSub && $item->$kolomSub)<p class="text-xs text-slate-500 mt-0.5">{{ $item->$kolomSub }}</p>@endif
                <div class="mt-auto pt-3 flex items-center justify-between">
                    <span class="text-xs text-slate-400">Urutan {{ $item->urutan }}</span>
                    <span>
                        @include('partials.edit-link', ['href' => route($m['route'].'.edit', $item->id)])
                        @include('partials.hapus', ['action' => route($m['route'].'.destroy', $item->id)])
                    </span>
                </div>
            </div>
        </li>
    @empty
        <li class="col-span-full bg-white rounded-3xl border border-slate-100 p-8 text-center text-slate-500">
            Belum ada {{ $m['satuan'] }}. <a href="{{ route($m['route'].'.create') }}" class="font-bold text-sky">Tambah {{ $m['satuan'] }} pertama</a>.
        </li>
    @endforelse
</ul>
<div class="mt-5">{{ $items->links() }}</div>
@endsection
