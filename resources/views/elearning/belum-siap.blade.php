@extends('layouts.dashboard')
@section('title', 'E-Learning Anak')
@section('heading', 'E-Learning Anak')
@section('content')
<div class="max-w-2xl bg-white rounded-3xl border border-slate-100 p-6">
    <p class="font-display text-xl font-semibold text-navy">Fitur E-Learning belum aktif</p>
    <p class="text-sm text-slate-500 mt-2">Database perlu diperbarui satu kali agar tabel tugas dan hasil belajar anak dibuat. Data lain tidak berubah.</p>
    @if (auth()->user()->role === 'admin')
        <a href="{{ route('admin.cek-sistem') }}" class="inline-flex items-center gap-2 bg-sky text-white text-sm font-extrabold px-4 py-2.5 rounded-xl hover:bg-sky-dark mt-4">
            <i data-lucide="database" class="w-4 h-4"></i> Buka Cek Sistem
        </a>
    @else
        <p class="text-sm text-amber-700 mt-3">Minta admin membuka menu <b>Cek Sistem</b> lalu klik <b>Perbarui database sekarang</b>.</p>
    @endif
</div>
@endsection
