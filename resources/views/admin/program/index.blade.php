@extends('layouts.dashboard')
@section('title', 'Program')
@section('heading', 'Program Pendidikan')
@section('actions') @include('partials.btn-tambah', ['href' => route('admin.program.create'), 'label' => 'Tambah program']) @endsection
@section('content')
@php $warna = ['rose' => 'bg-rose-50', 'amber' => 'bg-amber-50', 'violet' => 'bg-violet-50', 'emerald' => 'bg-emerald-50', 'sky' => 'bg-sky-50']; @endphp
<p class="text-sm text-slate-500 mb-5">Kartu program tampil di Home dan halaman Program, diurutkan dari nomor urut terkecil. Pilihan program juga muncul di formulir pendaftaran.</p>
<ul class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
    @forelse ($programs as $p)
        <li class="{{ $warna[$p->warna] ?? 'bg-white' }} rounded-3xl p-5">
            <div class="flex items-start justify-between">
                <span class="w-11 h-11 grid place-items-center rounded-full bg-white text-navy"><i data-lucide="{{ $p->ikon }}" class="w-5 h-5"></i></span>
                <div>
                    @include('partials.edit-link', ['href' => route('admin.program.edit', $p)])
                    @include('partials.hapus', ['action' => route('admin.program.destroy', $p)])
                </div>
            </div>
            <h2 class="font-extrabold text-navy mt-3">{{ $p->nama }}</h2>
            <p class="text-xs text-slate-500">{{ $p->keterangan }}</p>
            <p class="text-sm text-slate-600 mt-2">{{ $p->deskripsi }}</p>
            <p class="text-xs text-slate-400 mt-3">Urutan {{ $p->urutan }}</p>
        </li>
    @empty
        <li class="col-span-full text-slate-500">Belum ada program. Tambahkan program agar tampil di halaman depan.</li>
    @endforelse
</ul>
@endsection
