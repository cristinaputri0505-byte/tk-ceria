@extends('layouts.dashboard')
@section('title', 'Ringkasan')
@section('heading', 'Selamat datang, '.auth()->user()->name)
@section('content')
<ul class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach ([
        ['Siswa aktif', $stat['siswa'], 'baby', 'bg-rose-100 text-rose-500', route('admin.siswa.index')],
        ['Guru', $stat['guru'], 'users', 'bg-amber-100 text-amber-600', route('admin.pengguna.index', ['role' => 'guru'])],
        ['Kelas', $stat['kelas'], 'school', 'bg-emerald-100 text-emerald-600', route('admin.kelas.index')],
        ['Pendaftar baru', $stat['pendaftar_baru'], 'clipboard-list', 'bg-sky-100 text-sky-600', route('admin.pendaftaran.index', ['status' => 'baru'])],
    ] as [$label, $nilai, $ikon, $cls, $url])
        <li>
            <a href="{{ $url }}" class="block bg-white rounded-3xl p-5 border border-slate-100 hover:border-sky/40">
                <span class="w-10 h-10 grid place-items-center rounded-full {{ $cls }}"><i data-lucide="{{ $ikon }}" class="w-5 h-5"></i></span>
                <p class="font-display text-3xl font-semibold text-navy mt-3">{{ $nilai }}</p>
                <p class="text-sm text-slate-500">{{ $label }}</p>
            </a>
        </li>
    @endforeach
</ul>

<div class="grid lg:grid-cols-5 gap-6 mt-6">
    <section class="lg:col-span-3 bg-white rounded-3xl border border-slate-100 p-6">
        <div class="flex items-center justify-between">
            <h2 class="font-display text-lg font-semibold text-navy">Pendaftaran terbaru</h2>
            <a href="{{ route('admin.pendaftaran.index') }}" class="text-sm font-bold text-sky">Lihat semua</a>
        </div>
        <ul class="divide-y divide-slate-100 mt-3">
            @forelse ($pendaftaranTerbaru as $p)
                <li class="py-3 flex items-center justify-between gap-3">
                    <div>
                        <p class="font-bold text-navy">{{ $p->nama_anak }}</p>
                        <p class="text-xs text-slate-500">{{ $p->program }} · {{ $p->nama_orang_tua }} · {{ $p->telepon }}</p>
                    </div>
                    @include('partials.status-pendaftaran', ['status' => $p->status])
                </li>
            @empty
                <li class="py-6 text-sm text-slate-500">Belum ada pendaftar. Formulir online ada di halaman <a class="text-sky font-bold" href="{{ route('pendaftaran.create') }}">Pendaftaran</a>.</li>
            @endforelse
        </ul>
    </section>

    <section class="lg:col-span-2 bg-white rounded-3xl border border-slate-100 p-6">
        <h2 class="font-display text-lg font-semibold text-navy">Kehadiran hari ini</h2>
        <p class="font-display text-4xl font-semibold text-leaf mt-2">{{ $hadirHariIni }}<span class="text-lg text-slate-400"> / {{ $stat['siswa'] }}</span></p>
        <p class="text-sm text-slate-500">siswa hadir · {{ $absenTercatat }} sudah diabsen guru</p>
        <h3 class="font-bold text-navy mt-6 text-sm">Kelas</h3>
        <ul class="mt-2 space-y-2">
            @foreach ($kelas as $k)
                <li class="flex justify-between text-sm"><span>{{ $k->nama }} <span class="text-slate-400">· {{ $k->wali->name ?? 'Belum ada wali' }}</span></span><span class="font-bold text-navy">{{ $k->siswa_count }} anak</span></li>
            @endforeach
        </ul>
    </section>
</div>
@endsection
