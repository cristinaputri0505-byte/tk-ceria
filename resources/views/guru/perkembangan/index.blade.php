@extends('layouts.dashboard')
@section('title', 'Laporan Perkembangan')
@section('heading', 'Laporan Perkembangan')
@section('content')
@php $rp = auth()->user()->role === 'admin' ? 'admin.' : 'guru.'; $isAdmin = $rp === 'admin.'; @endphp
<form class="flex items-end gap-3 mb-6">
    <div>
        <label for="periode" class="block text-sm font-bold text-navy mb-1">Periode</label>
        <input id="periode" name="periode" value="{{ $periode }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm w-64">
    </div>
    <button class="bg-navy text-white text-sm font-bold px-5 py-2.5 rounded-xl">Tampilkan</button>
</form>
@php $total = count(\App\Models\Perkembangan::ASPEK); @endphp
@forelse ($kelas as $k)
    <section class="mb-6">
        <h2 class="font-display text-xl font-semibold text-navy mb-3">{{ $k->nama }}</h2>
        <ul class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @forelse ($k->siswa as $s)
                <li>
                    <a href="{{ route($rp.'perkembangan.edit', ['siswa' => $s, 'periode' => $periode]) }}" class="flex items-center justify-between gap-3 bg-white rounded-2xl border border-slate-100 p-4 hover:border-sky/40">
                        <span>
                            <span class="block font-bold text-navy">{{ $s->nama }}</span>
                            <span class="block text-xs {{ $s->terisi >= $total ? 'text-leaf' : 'text-amber-600' }}">{{ $s->terisi }} dari {{ $total }} aspek terisi</span>
                        </span>
                        <span class="w-10 h-10 rounded-full grid place-items-center {{ $s->terisi >= $total ? 'bg-leaf-soft text-leaf' : 'bg-amber-50 text-amber-600' }}">
                            <i data-lucide="{{ $s->terisi >= $total ? 'circle-check' : 'pencil' }}" class="w-5 h-5"></i>
                        </span>
                    </a>
                </li>
            @empty
                <li class="text-sm text-slate-500">Belum ada siswa.</li>
            @endforelse
        </ul>
    </section>
@empty
    <p class="text-slate-500">{{ $isAdmin ? 'Belum ada kelas. Buat kelas di menu Kelas.' : 'Anda belum menjadi wali kelas. Hubungi admin.' }}</p>
@endforelse
@endsection
