@extends('layouts.dashboard')
@section('title', 'E-Learning Anak')
@section('heading', 'E-Learning Anak')
@section('actions')
    <a href="{{ route('elearning.rekap', ['kelas_id' => $kelasId]) }}" class="inline-flex items-center gap-2 border border-slate-200 bg-white text-navy text-sm font-bold px-4 py-2.5 rounded-xl hover:bg-cloud">
        <i data-lucide="trophy" class="w-4 h-4"></i> <span class="hidden sm:inline">Rekap anak</span>
    </a>
    @include('partials.btn-tambah', ['href' => route('elearning.create', ['kelas_id' => $kelasId]), 'label' => 'Tambah tugas'])
@endsection
@section('content')
@php
    $isAdmin = auth()->user()->role === 'admin';
    // Warna kartu ditampilkan sebagai garis tepi kiri agar tetap terbaca di tema gelap
    $warna = ['pink' => '#f472b6', 'biru' => '#38bdf8', 'hijau' => '#34d399', 'kuning' => '#fbbf24', 'ungu' => '#a78bfa', 'oranye' => '#fb923c'];
@endphp
<p class="text-sm text-slate-500 mb-4 max-w-3xl">Tugas di sini dimainkan anak lewat akun orang tua (menu <b>Ruang Belajar Anak</b>). Ada 4 jenis permainan: tebak gambar, berhitung, mencocokkan, dan menggambar. Soal dibacakan dengan suara, dan anak mendapat bintang saat berhasil.</p>

@if ($kelasList->isNotEmpty())
<div class="flex flex-wrap gap-2 mb-5">
    <a href="{{ route('elearning.index') }}" class="text-sm font-bold px-4 py-2 rounded-full border {{ ! $kelasId ? 'bg-navy text-white border-navy' : 'bg-white text-navy border-slate-200 hover:border-sky' }}">Semua</a>
    @foreach ($kelasList as $k)
        <a href="{{ route('elearning.index', ['kelas_id' => $k->id]) }}" class="text-sm font-bold px-4 py-2 rounded-full border {{ $kelasId === $k->id ? 'bg-navy text-white border-navy' : 'bg-white text-navy border-slate-200 hover:border-sky' }}">{{ $k->nama }}</a>
    @endforeach
</div>
@elseif (! $isAdmin)
    <p class="text-amber-700 bg-amber-50 rounded-xl px-4 py-3 mb-5 text-sm">Anda belum menjadi wali kelas, jadi belum bisa membuat tugas. Hubungi admin.</p>
@endif

<ul class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
    @forelse ($tugas as $t)
        @php $siswaKelas = $t->kelas_id ? ($jumlahSiswa[$t->kelas_id] ?? 0) : ($lingkup !== null ? $jumlahSiswa->only($lingkup)->sum() : $jumlahSiswa->sum()); @endphp
        <li class="bg-white rounded-3xl border border-slate-100 border-l-8 p-5 flex flex-col gap-3 {{ $t->aktif ? '' : 'opacity-60' }}" style="border-left-color: {{ $warna[$t->warna] ?? $warna['pink'] }}">
            <div class="flex items-start gap-3">
                <span class="w-14 h-14 shrink-0 rounded-2xl bg-cloud grid place-items-center text-3xl">{{ $t->ikon }}</span>
                <div class="min-w-0">
                    <div class="flex flex-wrap gap-1.5 mb-1">
                        <span class="text-[11px] font-bold bg-sky-soft text-sky px-2 py-0.5 rounded-full">{{ $t->label_tipe }}</span>
                        <span class="text-[11px] font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">⭐ +{{ $t->bintang }}</span>
                        @unless ($t->aktif)<span class="text-[11px] font-bold bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full">Disembunyikan</span>@endunless
                    </div>
                    <p class="font-bold text-navy leading-snug">{{ $t->judul }}</p>
                    <p class="text-xs text-slate-500 line-clamp-2">{{ $t->instruksi }}</p>
                </div>
            </div>
            <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-100">
                <span class="text-xs text-slate-500">{{ $t->kelas?->nama ?? 'Semua kelas' }} · selesai <b class="text-navy">{{ $t->selesai_count }}/{{ $siswaKelas }}</b> anak</span>
                @if ($t->bolehDiubah(auth()->user()))
                    <span class="shrink-0">
                        @include('partials.edit-link', ['href' => route('elearning.edit', $t)])
                        @include('partials.hapus', ['action' => route('elearning.destroy', $t), 'confirm' => 'Hapus tugas ini? Bintang dan karya anak untuk tugas ini ikut terhapus.'])
                    </span>
                @endif
            </div>
            <a href="{{ route('elearning.hasil', [$t, 'kelas_id' => $kelasId]) }}" class="inline-flex items-center justify-center gap-2 w-full rounded-xl border border-slate-200 bg-cloud hover:border-sky hover:text-sky text-navy text-sm font-bold px-4 py-2">
                <i data-lucide="users" class="w-4 h-4"></i> <span>Lihat siswa yang mengerjakan</span>
            </a>
        </li>
    @empty
        <li class="col-span-full bg-white rounded-3xl border border-slate-100 p-8 text-center">
            <p class="text-5xl">🎈</p>
            <p class="font-bold text-navy mt-2">Belum ada tugas</p>
            <p class="text-sm text-slate-500">Klik <b>Tambah tugas</b> untuk membuat permainan belajar pertama.</p>
        </li>
    @endforelse
</ul>
@endsection
