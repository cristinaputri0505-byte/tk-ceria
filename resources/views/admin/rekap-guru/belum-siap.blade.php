@extends('layouts.dashboard')
@section('title', 'Rekap Guru')
@section('heading', 'Rekap Guru')
@section('content')
<div class="max-w-2xl bg-white rounded-3xl p-6">
    <p class="font-display text-2xl font-extrabold text-navy">Rekap Guru belum aktif</p>
    <p class="text-ink mt-2">Database perlu diperbarui satu kali agar tabel kehadiran guru dibuat. Data lain tidak berubah.</p>
    <a href="{{ route('admin.cek-sistem') }}" class="inline-flex items-center gap-2 bg-sky text-white text-sm font-extrabold px-4 py-2.5 rounded-xl mt-4"><i data-lucide="database" class="w-4 h-4"></i> Buka Cek Sistem</a>
</div>
@endsection
