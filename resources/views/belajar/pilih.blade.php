@extends('layouts.dashboard')
@section('title', 'Ruang Belajar Anak')
@section('heading', 'Ruang Belajar Anak')
@section('content')
<p class="text-slate-500 mb-5 max-w-2xl">Pilih anak yang akan bermain. Soal dibacakan dengan suara, jadi nyalakan suara HP atau laptop. Bintang dan hasil gambar tersimpan dan bisa dilihat guru.</p>
<ul class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse ($anak as $a)
        <li>
            <a href="{{ route('belajar.main', $a) }}" class="flex items-center gap-4 bg-white rounded-3xl border-2 border-slate-100 p-5 hover:border-sky/40 hover:shadow-md transition">
                @include('partials.foto-anak', ['s' => $a, 'size' => 'w-16 h-16', 'teks' => 'text-2xl'])
                <span class="min-w-0 flex-1">
                    <span class="block font-display text-xl font-semibold text-navy">{{ $a->nama_panggilan }}</span>
                    <span class="block text-sm text-slate-500">{{ $a->kelas?->nama ?? 'Belum ada kelas' }} · ⭐ {{ (int) ($bintang[$a->id] ?? 0) }} bintang</span>
                </span>
                <span class="shrink-0 bg-sky text-white text-sm font-extrabold px-4 py-2 rounded-xl inline-flex items-center gap-1"><i data-lucide="play" class="w-4 h-4"></i> Main</span>
            </a>
        </li>
    @empty
        <li class="text-slate-500">Belum ada data anak di akun ini. Hubungi admin sekolah.</li>
    @endforelse
</ul>
@endsection
