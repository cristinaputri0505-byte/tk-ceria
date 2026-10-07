@extends('layouts.dashboard')
@section('title', 'Kelas')
@section('heading', 'Kelas')
@section('actions') @include('partials.btn-tambah', ['href' => route('admin.kelas.create'), 'label' => 'Tambah kelas']) @endsection
@section('content')
<ul class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse ($kelas as $k)
        <li class="bg-white rounded-3xl border border-slate-100 p-5">
            <div class="flex items-start justify-between">
                <span class="text-xs font-extrabold px-2.5 py-1 rounded-full {{ $k->kelompok === 'A' ? 'bg-rose-100 text-rose-600' : 'bg-amber-100 text-amber-700' }}">Kelompok {{ $k->kelompok }}</span>
                <div class="-mt-1 -mr-1">
                    @include('partials.edit-link', ['href' => route('admin.kelas.edit', $k)])
                    @include('partials.hapus', ['action' => route('admin.kelas.destroy', $k)])
                </div>
            </div>
            <h2 class="font-display text-xl font-semibold text-navy mt-2">{{ $k->nama }}</h2>
            <p class="text-sm text-slate-500">Tahun ajaran {{ $k->tahun_ajaran }}</p>
            <p class="text-sm mt-3"><span class="text-slate-500">Wali kelas:</span> <span class="font-bold text-navy">{{ $k->wali->name ?? 'Belum ditentukan' }}</span></p>
            <a href="{{ route('admin.siswa.index', ['kelas_id' => $k->id]) }}" class="inline-block text-sm font-bold text-sky mt-3">{{ $k->siswa_count }} siswa</a>
        </li>
    @empty
        <li class="col-span-full text-slate-500">Belum ada kelas. Buat kelas agar siswa bisa ditempatkan dan guru bisa mengabsen.</li>
    @endforelse
</ul>
@endsection
