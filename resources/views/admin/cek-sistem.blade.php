@extends('layouts.dashboard')
@section('title', 'Cek Sistem')
@section('heading', 'Cek Sistem')
@section('actions')
    <a href="{{ route('admin.cek-sistem') }}" class="inline-flex items-center gap-2 bg-sky text-white text-sm font-bold px-4 py-2.5 rounded-xl hover:bg-sky-dark">
        <i data-lucide="refresh-cw" class="w-4 h-4"></i> <span>Periksa ulang</span>
    </a>
@endsection
@section('content')
@php $gagal = collect($hasil)->filter(fn ($h) => $h[1] === false)->count(); @endphp
<div class="max-w-4xl space-y-5">

    {{-- ---------- Database ---------- --}}
    <h2 class="font-display text-xl font-semibold text-navy">Database</h2>
    @if (empty($tertunda))
        <div class="rounded-3xl p-5 border bg-emerald-50 border-slate-100 text-emerald-700">
            <p class="font-extrabold flex items-center gap-2"><i data-lucide="circle-check" class="w-5 h-5"></i> Database sudah versi terbaru.</p>
        </div>
    @else
        <div class="rounded-3xl p-5 border bg-amber-50 border-amber-100 text-amber-700">
            <p class="font-extrabold flex items-center gap-2"><i data-lucide="circle-alert" class="w-5 h-5"></i> Ada {{ count($tertunda) }} pembaruan database yang belum dijalankan.</p>
            <p class="text-sm mt-1 text-slate-600">Fitur baru (misalnya banyak foto per kegiatan) baru aktif setelah database diperbarui. Data yang sudah ada tidak dihapus.</p>
            <ul class="text-xs mt-2 text-slate-600 list-disc pl-5">
                @foreach ($tertunda as $m)<li>{{ $m }}</li>@endforeach
            </ul>
            <form method="POST" action="{{ route('admin.cek-sistem.database') }}" class="mt-4" onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').textContent='Memperbarui…'">
                @csrf
                <button class="bg-sky text-white text-sm font-extrabold px-5 py-2.5 rounded-xl hover:bg-sky-dark">Perbarui database sekarang</button>
            </form>
        </div>
    @endif

    {{-- ---------- Penyimpanan file ---------- --}}
    <h2 class="font-display text-xl font-semibold text-navy pt-4">Penyimpanan foto</h2>
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
