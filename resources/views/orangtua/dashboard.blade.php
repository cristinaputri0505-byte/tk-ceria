@extends('layouts.dashboard')
@section('title', 'Beranda')
@section('heading', 'Beranda')
@section('content')
@php
    // Salam mengikuti waktu WIB (bukan zona waktu server). Teksnya juga diperbarui otomatis oleh JavaScript.
    $jam = now()->timezone('Asia/Jakarta')->hour;
    $salam = ($jam >= 18 || $jam < 4) ? 'Selamat malam' : ($jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : 'Selamat sore'));
    $totalBelum = $tagihanBelum->where('status', 'belum')->sum('jumlah');
    $nBelum = $tagihanBelum->where('status', 'belum')->count();
    $nMenunggu = $tagihanBelum->where('status', 'menunggu')->count();
    $f = fn ($n) => \App\Support\Pengaturan::fitur($n);
    $kode = ['{salam}' => '@@SALAM@@', '{nama}' => explode(' ', auth()->user()->name)[0], '{sekolah}' => $site['nama_sekolah'], '{anak}' => $anak->pluck('nama_panggilan')->join(', ', ' dan ') ?: 'si kecil'];
    $salamHtml = fn ($teks) => str_replace('@@SALAM@@', '<span data-salam>'.e($salam).'</span>', e($teks));
    $bannerSapa = \App\Support\Pengaturan::url('portal_banner_gambar');
@endphp

{{-- Sapaan --}}
<section class="relative overflow-hidden rounded-[2rem] bg-navy text-white p-6 sm:p-8">
    @if ($bannerSapa)
        <img src="{{ $bannerSapa }}" alt="" class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-r from-navy-deep/90 via-navy-deep/60 to-transparent"></div>
    @else
        {{-- Matahari di siang hari --}}
        <svg class="ilu-siang absolute -right-6 -top-6 w-40 h-40 opacity-90" viewBox="0 0 100 100" aria-hidden="true">
            <g stroke="#fbbf24" stroke-width="5" stroke-linecap="round"><path d="M50 6v12M50 82v12M6 50h12M82 50h12M19 19l8 8M73 73l8 8M19 81l8-8M73 27l8-8"/></g>
            <circle cx="50" cy="50" r="22" fill="#fbbf24"/>
        </svg>
        {{-- Bulan sabit dan bintang di malam hari --}}
        <svg class="ilu-malam absolute -right-2 -top-2 w-40 h-40" viewBox="0 0 100 100" aria-hidden="true">
            <defs><mask id="potong-bulan"><rect width="100" height="100" fill="#fff"/><circle cx="66" cy="38" r="29" fill="#000"/></mask></defs>
            <circle cx="46" cy="54" r="32" fill="#fde68a" mask="url(#potong-bulan)"/>
            <g fill="#fde68a"><circle cx="80" cy="74" r="2.4"/><circle cx="62" cy="88" r="1.6"/><circle cx="24" cy="18" r="1.8"/><circle cx="90" cy="48" r="1.5"/></g>
        </svg>
    @endif
    <div class="relative">
        <p class="text-white/70 text-sm font-bold flex items-center gap-2">
            <svg class="i-malam w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
            <svg class="i-siang w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
            <span data-tgl-wib>{{ now()->timezone('Asia/Jakarta')->translatedFormat('l, d F Y') }}</span>
        </p>
        <h2 class="font-display text-3xl font-semibold mt-1">{!! $salamHtml(\App\Support\Pengaturan::isi('portal_salam_judul', $kode)) !!}</h2>
        <p class="text-white/80 mt-1 max-w-lg">{!! $salamHtml(\App\Support\Pengaturan::isi('portal_salam_sub', $kode)) !!}</p>
    </div>
</section>

{{-- Peringatan tagihan --}}
@if ($f('pembayaran') && ($nBelum || $nMenunggu))
    <section class="mt-5 flex flex-wrap items-center justify-between gap-4 rounded-3xl p-5 {{ $nBelum ? 'bg-rose-50 border border-rose-100' : 'bg-amber-50 border border-amber-100' }}">
        <div class="flex items-start gap-3">
            <span class="w-11 h-11 rounded-2xl grid place-items-center shrink-0 {{ $nBelum ? 'bg-rose-100 text-rose-600' : 'bg-amber-100 text-amber-600' }}"><i data-lucide="wallet" class="w-5 h-5"></i></span>
            <div>
                @if ($nBelum)
                    <p class="font-extrabold text-navy">{{ $nBelum }} tagihan belum dibayar · {{ \App\Models\Tagihan::rupiah($totalBelum) }}</p>
                    <p class="text-sm text-slate-600">{{ $tagihanBelum->where('status', 'belum')->map(fn ($t) => $t->jenis.' '.$t->periode.' ('.$t->siswa->nama_panggilan.')')->join(', ') }}</p>
                @endif
                @if ($nMenunggu)
                    <p class="text-sm {{ $nBelum ? 'text-slate-500 mt-1' : 'font-extrabold text-navy' }}">{{ $nMenunggu }} bukti transfer sedang diverifikasi admin.</p>
                @endif
            </div>
        </div>
        @if ($nBelum)
            <a href="{{ route('orangtua.pembayaran') }}" class="bg-navy text-white text-sm font-extrabold px-5 py-2.5 rounded-xl">Bayar sekarang</a>
        @endif
    </section>
@endif

{{-- Kartu anak --}}
<div class="grid xl:grid-cols-2 gap-5 mt-5">
    @forelse ($anak as $a)
        <article class="bg-white rounded-[2rem] border border-slate-100 p-6">
            <div class="flex items-start gap-5">
                @include('partials.foto-anak', ['s' => $a, 'size' => 'w-24 h-24 sm:w-28 sm:h-28'])
                <div class="min-w-0">
                    <h2 class="font-display text-2xl font-semibold text-navy leading-tight">{{ $a->nama }}</h2>
                    <p class="text-sm text-slate-500">Panggilan: <b class="text-navy">{{ $a->nama_panggilan }}</b> · NIS {{ $a->nis }}</p>
                    <div class="flex flex-wrap gap-1.5 mt-2">
                        <span class="text-xs font-bold bg-sky-soft text-sky px-2.5 py-1 rounded-full">{{ $a->kelas->nama ?? 'Belum ada kelas' }}</span>
                        <span class="text-xs font-bold bg-cloud text-navy px-2.5 py-1 rounded-full">{{ $a->usia }}</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Wali kelas: <b class="text-navy">{{ $a->kelas?->wali?->name ?? '—' }}</b></p>
                </div>
            </div>

            <div class="grid sm:grid-cols-3 gap-3 mt-5">
                @if ($f('kehadiran'))
                <div class="rounded-2xl bg-emerald-50 p-4">
                    <p class="text-xs font-bold text-slate-600">Kehadiran {{ now()->translatedFormat('F') }}</p>
                    <p class="font-display text-3xl font-semibold text-navy mt-1">{{ $a->persenHadir !== null ? $a->persenHadir.'%' : '—' }}</p>
                    <div class="h-1.5 rounded-full bg-white mt-2 overflow-hidden" aria-hidden="true"><div class="h-full bg-leaf rounded-full" style="width: {{ $a->persenHadir ?? 0 }}%"></div></div>
                    <p class="text-[11px] text-slate-500 mt-1.5">Hadir {{ $a->rekap['hadir'] ?? 0 }} · Izin {{ $a->rekap['izin'] ?? 0 }} · Sakit {{ $a->rekap['sakit'] ?? 0 }} · Alpa {{ $a->rekap['alpa'] ?? 0 }}</p>
                </div>
                @endif
                @if ($f('pembayaran'))
                <div class="rounded-2xl bg-sun-soft p-4">
                    <p class="text-xs font-bold text-slate-600">SPP {{ now()->translatedFormat('F') }}</p>
                    @if ($a->sppBulanIni)
                        <p class="font-display text-xl font-semibold text-navy mt-1">{{ $a->sppBulanIni->rupiah }}</p>
                        <div class="mt-1.5">@include('partials.status-tagihan', ['t' => $a->sppBulanIni])</div>
                    @else
                        <p class="text-sm text-slate-500 mt-2">Belum ada tagihan bulan ini.</p>
                    @endif
                </div>
                @endif
                @if ($f('rapor'))
                <div class="rounded-2xl bg-violet-50 p-4">
                    <p class="text-xs font-bold text-slate-600">Rapor {{ \Illuminate\Support\Str::before($periode, ' 20') }}</p>
                    @if ($a->rapor->isNotEmpty())
                        <div class="flex flex-wrap gap-1 mt-2">
                            @foreach (['BSB', 'BSH', 'MB', 'BB'] as $n)
                                @if ($a->rapor[$n] ?? 0)<span class="text-xs font-extrabold bg-white text-violet-700 px-2 py-1 rounded-lg" title="{{ \App\Models\Perkembangan::NILAI[$n] }}">{{ $a->rapor[$n] }} {{ $n }}</span>@endif
                            @endforeach
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1.5">{{ $a->rapor->sum() }} dari 6 aspek dinilai</p>
                    @else
                        <p class="text-sm text-slate-500 mt-2">Belum diisi wali kelas.</p>
                    @endif
                </div>
                @endif
            </div>

            <div class="flex flex-wrap gap-2 mt-5">
                <a href="{{ route('orangtua.anak', $a) }}" class="inline-flex items-center gap-2 bg-sky text-white text-sm font-extrabold px-4 py-2.5 rounded-xl hover:bg-sky-dark"><i data-lucide="id-card" class="w-4 h-4"></i> Profil lengkap</a>
                @if ($f('kehadiran'))<a href="{{ route('orangtua.anak', [$a, 'tab' => 'kehadiran']) }}" class="inline-flex items-center gap-2 bg-cloud text-navy text-sm font-bold px-4 py-2.5 rounded-xl hover:bg-sky-soft"><i data-lucide="calendar-check" class="w-4 h-4"></i> Kehadiran</a>@endif
                @if ($f('rapor'))<a href="{{ route('orangtua.anak', [$a, 'tab' => 'rapor']) }}" class="inline-flex items-center gap-2 bg-cloud text-navy text-sm font-bold px-4 py-2.5 rounded-xl hover:bg-sky-soft"><i data-lucide="sprout" class="w-4 h-4"></i> Rapor</a>@endif
            </div>
        </article>
    @empty
        <div class="xl:col-span-2 bg-white rounded-3xl border border-slate-100 p-8 text-center">
            <p class="text-navy whitespace-pre-line">{{ $site['portal_belum_terhubung'] }}</p>
            <p class="text-sm text-slate-500 mt-1">Kontak sekolah: {{ $site['whatsapp'] ?: $site['telepon'] }}</p>
        </div>
    @endforelse
</div>

{{-- Pengumuman & agenda --}}
@if ($f('pengumuman'))
<div class="grid lg:grid-cols-3 gap-5 mt-5">
    <section class="lg:col-span-2 bg-white rounded-[2rem] border border-slate-100 p-6">
        <div class="flex items-center justify-between">
            <h2 class="font-display text-xl font-semibold text-navy flex items-center gap-2"><i data-lucide="megaphone" class="w-5 h-5 text-berry"></i> {{ $site['portal_judul_pengumuman'] }}</h2>
            <a href="{{ route('orangtua.pengumuman') }}" class="text-sm font-bold text-sky">Lihat semua</a>
        </div>
        <ul class="mt-4 space-y-3">
            @forelse ($pengumuman as $p)
                <li class="rounded-2xl p-4 {{ $p->penting ? 'bg-rose-50' : 'bg-cloud' }}">
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($p->penting)<span class="text-[11px] font-extrabold bg-berry text-white px-2 py-0.5 rounded-full">Penting</span>@endif
                        <span class="text-[11px] font-bold text-slate-500">{{ $p->kelas ? 'Kelas '.$p->kelas->nama : 'Semua kelas' }} · {{ $p->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="font-extrabold text-navy mt-1">{{ $p->judul }}</p>
                    <p class="text-sm text-slate-600 mt-0.5 line-clamp-2">{{ $p->isi }}</p>
                </li>
            @empty
                <li class="text-sm text-slate-500">Belum ada pengumuman.</li>
            @endforelse
        </ul>
    </section>

    <section class="bg-white rounded-[2rem] border border-slate-100 p-6">
        <h2 class="font-display text-xl font-semibold text-navy flex items-center gap-2"><i data-lucide="calendar-days" class="w-5 h-5 text-sky"></i> {{ $site['portal_judul_agenda'] }}</h2>
        <ul class="mt-4 space-y-3">
            @forelse ($agenda as $ag)
                <li>
                    <a href="{{ $ag['url'] }}" class="flex gap-3 group">
                        <span class="w-14 shrink-0 rounded-2xl bg-sun-soft text-center py-2">
                            <span class="block font-display text-2xl font-semibold text-navy leading-none">{{ $ag['tanggal']->format('d') }}</span>
                            <span class="block text-[11px] font-bold text-amber-700">{{ $ag['tanggal']->translatedFormat('M') }}</span>
                        </span>
                        <span>
                            <span class="block font-bold text-navy group-hover:text-sky">{{ $ag['judul'] }}</span>
                            <span class="block text-xs text-slate-500">{{ $ag['tanggal']->translatedFormat('l') }} · {{ $ag['jenis'] }}</span>
                        </span>
                    </a>
                </li>
            @empty
                <li class="text-sm text-slate-500">Belum ada agenda dalam waktu dekat.</li>
            @endforelse
        </ul>
    </section>
</div>

@endif

{{-- Dokumentasi --}}
@if ($f('dokumentasi'))
<section class="bg-white rounded-[2rem] border border-slate-100 p-6 mt-5">
    <div class="flex items-center justify-between">
        <h2 class="font-display text-xl font-semibold text-navy flex items-center gap-2"><i data-lucide="camera" class="w-5 h-5 text-leaf"></i> {{ $site['portal_judul_dokumentasi'] }}</h2>
        <a href="{{ route('orangtua.dokumentasi') }}" class="text-sm font-bold text-sky">Lihat semua</a>
    </div>
    <ul class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 mt-4">
        @forelse ($dokumentasi as $d)
            <li>
                <button type="button" data-foto="{{ $d->url }}" data-judul="{{ $d->keterangan }} · {{ $d->tanggal->translatedFormat('d M Y') }}" class="block w-full aspect-square rounded-2xl overflow-hidden bg-cloud">
                    <img src="{{ $d->url }}" alt="{{ $d->keterangan ?? 'Dokumentasi kegiatan' }}" class="w-full h-full object-cover hover:scale-105 transition-transform" loading="lazy">
                </button>
            </li>
        @empty
            <li class="col-span-full text-sm text-slate-500">Foto kegiatan dari wali kelas akan muncul di sini.</li>
        @endforelse
    </ul>
</section>
@endif
@include('partials.lightbox')
@endsection
