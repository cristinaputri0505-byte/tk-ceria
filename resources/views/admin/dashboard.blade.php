@extends('layouts.dashboard')
@section('title', 'Ringkasan')
@section('heading', 'Ringkasan')
@section('content')
@php $menunggu = collect($perlu)->sum(1); @endphp

{{-- Sapaan --}}
<section class="relative overflow-hidden bg-daun border-[2.5px] border-navy rounded-[2rem] shadow-[5px_5px_0_#2b2140] p-6 sm:p-8">
    <svg class="hidden sm:block absolute right-6 top-1/2 -translate-y-1/2 w-40 h-32" viewBox="0 0 150 120" aria-hidden="true"><circle cx="112" cy="36" r="22" fill="#ffc53d" stroke="#2b2140" stroke-width="3"/><path d="M10 110 L45 62 L70 92 L92 70 L140 110 Z" fill="#d6f2e0" stroke="#2b2140" stroke-width="3" stroke-linejoin="round"/><path d="M30 34c6-8 18-8 22 0 8-2 14 6 10 12H24c-6-4-2-12 6-12z" fill="#fff" stroke="#2b2140" stroke-width="3"/></svg>
    <div class="relative sm:pr-48">
        <p class="text-sm font-extrabold text-navy" data-tgl-wib>{{ now()->timezone('Asia/Jakarta')->translatedFormat('l, d F Y') }}</p>
        <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-navy leading-tight mt-1"><span data-salam>Selamat datang</span>, {{ explode(' ', auth()->user()->name)[0] }}!</h2>
        <p class="font-semibold text-navy mt-1">
            @if ($menunggu)
                Ada {{ $menunggu }} hal yang menunggu tindakan Anda hari ini.
            @else
                Semua beres. Tidak ada yang menunggu tindakan Anda.
            @endif
        </p>
        <div class="flex flex-wrap gap-3 mt-5">
            <a href="{{ route('admin.pendaftaran.index', ['status' => 'baru']) }}" class="inline-flex items-center gap-2 bg-sun text-navy text-sm font-extrabold px-4 py-2.5 rounded-xl"><i data-lucide="clipboard-list" class="w-4 h-4"></i> Cek pendaftaran</a>
            <a href="{{ route('admin.tagihan.index') }}" class="inline-flex items-center gap-2 bg-white text-navy text-sm font-extrabold px-4 py-2.5 rounded-xl"><i data-lucide="wallet" class="w-4 h-4"></i> Cek pembayaran</a>
        </div>
    </div>
</section>

{{-- Angka --}}
<ul class="grid grid-cols-2 lg:grid-cols-4 gap-5 mt-6">
    @foreach ([
        ['Siswa aktif', $stat['siswa'], 'baby', 'bg-permen-soft', 'bg-permen', route('admin.siswa.index')],
        ['Guru', $stat['guru'], 'users', 'bg-sun-soft', 'bg-sun', route('admin.pengguna.index', ['role' => 'guru'])],
        ['Kelas', $stat['kelas'], 'school', 'bg-daun-soft', 'bg-daun', route('admin.kelas.index')],
        ['Pendaftar baru', $stat['pendaftar_baru'], 'clipboard-list', 'bg-langit-soft', 'bg-langit', route('admin.pendaftaran.index', ['status' => 'baru'])],
    ] as $i => [$label, $nilai, $ikon, $latar, $warnaIkon, $url])
        <li>
            <a href="{{ $url }}" class="block {{ $latar }} rounded-3xl p-5 {{ $i % 2 ? 'miring-kanan' : 'miring-kiri' }}">
                <span class="w-11 h-11 grid place-items-center rounded-xl border-2 border-navy text-navy {{ $warnaIkon }}"><i data-lucide="{{ $ikon }}" class="w-5 h-5"></i></span>
                <p class="font-display text-4xl font-extrabold text-navy mt-3 leading-none">{{ $nilai }}</p>
                <p class="text-sm font-extrabold text-navy mt-1">{{ $label }}</p>
            </a>
        </li>
    @endforeach
</ul>

<div class="grid lg:grid-cols-2 gap-6 mt-7">
    {{-- Perlu ditindaklanjuti --}}
    <section class="bg-white rounded-3xl p-6">
        <h2 class="font-display text-2xl font-extrabold text-navy">Perlu ditindaklanjuti</h2>
        <ul class="mt-4 space-y-3">
            @foreach ($perlu as [$label, $n, $url, $ikon, $latar])
                <li>
                    <a href="{{ $url }}" class="flex items-center gap-3 rounded-2xl {{ $n ? $latar : 'bg-white' }} px-4 py-3 border-2 border-navy hover:-translate-y-0.5 transition-transform">
                        <i data-lucide="{{ $ikon }}" class="w-5 h-5 text-navy shrink-0"></i>
                        <span class="flex-1 font-extrabold text-navy text-sm">{{ $label }}</span>
                        <span class="min-w-[2rem] text-center font-display text-lg font-extrabold {{ $n ? 'text-navy' : 'text-ink' }}">{{ $n }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Kehadiran --}}
    <section class="bg-white rounded-3xl p-6">
        <h2 class="font-display text-2xl font-extrabold text-navy">Kehadiran hari ini</h2>
        <p class="mt-1 text-ink"><span class="font-display text-3xl font-extrabold text-leaf">{{ $hadirHariIni }}</span> <span class="font-bold">/ {{ $stat['siswa'] }} siswa hadir</span> · {{ $absenTercatat }} sudah diabsen guru</p>
        <ul class="mt-5 space-y-4">
            @foreach ($kelas as $k)
                @php $h = (int) ($hadirPerKelas[$k->id] ?? 0); $persen = $k->siswa_count ? round($h / $k->siswa_count * 100) : 0; @endphp
                <li>
                    <div class="flex justify-between gap-3 text-sm">
                        <span class="font-extrabold text-navy">{{ $k->nama }} <span class="font-semibold text-ink">· {{ $k->wali->name ?? 'Belum ada wali' }}</span></span>
                        <span class="font-extrabold text-navy shrink-0">{{ $h }} / {{ $k->siswa_count }} hadir</span>
                    </div>
                    <div class="h-4 mt-1.5 rounded-full border-2 border-navy bg-cloud overflow-hidden" role="img" aria-label="{{ $persen }}% hadir">
                        <div class="h-full bg-sun {{ $persen ? 'border-r-2 border-navy' : '' }}" style="width: {{ $persen }}%"></div>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>
</div>

{{-- Pendaftaran terbaru --}}
<section class="bg-white rounded-3xl p-6 mt-7">
    <div class="flex items-center justify-between gap-3">
        <h2 class="font-display text-2xl font-extrabold text-navy">Pendaftaran terbaru</h2>
        <a href="{{ route('admin.pendaftaran.index') }}" class="inline-flex items-center gap-1.5 shrink-0 whitespace-nowrap bg-white text-navy text-sm font-extrabold px-4 py-2 rounded-xl">Lihat semua</a>
    </div>
    <ul class="divide-y divide-slate-100 mt-3">
        @forelse ($pendaftaranTerbaru as $p)
            <li class="py-3 flex items-center justify-between gap-3">
                <div>
                    <p class="font-extrabold text-navy">{{ $p->nama_anak }}</p>
                    <p class="text-xs text-ink">{{ $p->program }} · {{ $p->nama_orang_tua }} · {{ $p->telepon }}</p>
                </div>
                @include('partials.status-pendaftaran', ['status' => $p->status])
            </li>
        @empty
            <li class="py-6 text-sm text-ink">Belum ada pendaftar. Formulir online ada di halaman <a class="text-sky font-bold" href="{{ route('pendaftaran.create') }}">Pendaftaran</a>.</li>
        @endforelse
    </ul>
</section>
@endsection
