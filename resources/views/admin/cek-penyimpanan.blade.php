@extends('layouts.dashboard')
@section('title', 'Cek Penyimpanan')
@section('heading', 'Cek Penyimpanan')
@section('actions')
    <a href="{{ route('admin.cek-penyimpanan') }}" class="inline-flex items-center gap-2 bg-sky text-white text-sm font-bold px-4 py-2.5 rounded-xl hover:bg-sky-dark">
        <i data-lucide="refresh-cw" class="w-4 h-4"></i> <span>Periksa ulang</span>
    </a>
@endsection
@section('content')
@php $gagal = collect($hasil)->filter(fn ($h) => $h[1] === false)->count(); @endphp
<div class="max-w-4xl space-y-5">
    <div class="rounded-3xl p-5 border {{ $gagal ? 'bg-rose-50 border-rose-200 text-rose-700' : 'bg-emerald-50 border-slate-100 text-emerald-700' }}">
        <p class="font-extrabold flex items-center gap-2">
            <i data-lucide="{{ $gagal ? 'circle-alert' : 'circle-check' }}" class="w-5 h-5"></i>
            {{ $gagal ? "{$gagal} pemeriksaan gagal. Foto tidak bisa diunggah atau ditampilkan sampai ini diperbaiki." : 'Penyimpanan berjalan normal. Foto bisa diunggah dan ditampilkan.' }}
        </p>
        <p class="text-sm mt-1 text-slate-600">Halaman ini menguji penyimpanan langsung dari server website (menulis, membaca, lalu menghapus file uji kecil).</p>
    </div>

    <ul class="bg-white rounded-3xl border border-slate-100 divide-y divide-slate-100">
        @foreach ($hasil as [$nama, $lulus, $ket])
            <li class="p-5 flex gap-4">
                <span class="w-9 h-9 shrink-0 grid place-items-center rounded-full {{ $lulus === null ? 'bg-sky-soft text-sky' : ($lulus ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-600') }}">
                    <i data-lucide="{{ $lulus === null ? 'info' : ($lulus ? 'check' : 'x') }}" class="w-4 h-4"></i>
                </span>
                <div class="min-w-0">
                    <p class="font-bold text-navy">{{ $nama }}</p>
                    <p class="text-sm text-slate-500 break-words">{{ $ket }}</p>
                </div>
            </li>
        @endforeach
    </ul>

    @if ($gagal)
        <p class="text-sm text-slate-500">Kirim screenshot halaman ini ke pengembang website. Isi kunci rahasia tidak pernah ditampilkan di sini.</p>
    @endif
</div>
@endsection
