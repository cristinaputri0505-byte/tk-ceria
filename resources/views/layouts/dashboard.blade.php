<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — {{ $site['nama_sekolah'] }}</title>
    @include('partials.head')
    @include('partials.tema')
</head>
@php
    $user = auth()->user();
    $notifBelum = rescue(fn () => \App\Models\Notifikasi::where('user_id', $user->id)->whereNull('dibaca_at')->count(), 0, false);
    $nav = match ($user->role) {
        'admin' => [
            ['Ringkasan', 'admin.dashboard', 'layout-dashboard', 'admin.dashboard'],
            ['group' => 'Akademik'],
            ['Pendaftaran', 'admin.pendaftaran.index', 'clipboard-list', 'admin.pendaftaran.*'],
            ['Siswa', 'admin.siswa.index', 'baby', 'admin.siswa.*'],
            ['Kelas', 'admin.kelas.index', 'school', 'admin.kelas.*'],
            ['Absensi', 'admin.absensi.index', 'calendar-check', 'admin.absensi.*'],
            ['Perkembangan', 'admin.perkembangan.index', 'sprout', 'admin.perkembangan.*'],
            ['Akun Pengguna', 'admin.pengguna.index', 'key-round', 'admin.pengguna.*'],
            ['Reset Password', 'admin.reset.index', 'lock-keyhole', 'admin.reset.*'],
            ['group' => 'Portal Orang Tua'],
            ['Pengaturan Portal', ['admin.pengaturan.index', ['tab' => 'portal']], 'heart-handshake', null, request()->routeIs('admin.pengaturan.*') && request('tab') === 'portal'],
            ['Tagihan & SPP', 'admin.tagihan.index', 'wallet', 'admin.tagihan.*'],
            ['Pengumuman', 'admin.pengumuman.index', 'megaphone', 'admin.pengumuman.*'],
            ['Dokumentasi', 'admin.dokumentasi.index', 'camera', 'admin.dokumentasi.*'],
            ['Chat Orang Tua', 'chat.index', 'message-circle', null, request()->routeIs('chat.index', 'chat.pribadi', 'chat.pribadi.*')],
            ['Grup Orang Tua', 'chat.grup', 'users-round', 'chat.grup*'],
            ['group' => 'Website'],
            ['Pengaturan Halaman', 'admin.pengaturan.index', 'settings', null, request()->routeIs('admin.pengaturan.*') && request('tab') !== 'portal'],
            ['Keunggulan', 'admin.keunggulan.index', 'sparkles', 'admin.keunggulan.*'],
            ['Program', 'admin.program.index', 'book-open', 'admin.program.*'],
            ['Kegiatan', 'admin.kegiatan.index', 'party-popper', 'admin.kegiatan.*'],
            ['Galeri', 'admin.galeri.index', 'images', 'admin.galeri.*'],
            ['Guru & Staff', 'admin.staff.index', 'users', 'admin.staff.*'],
            ['Berita', 'admin.berita.index', 'newspaper', 'admin.berita.*'],
            ['Pesan Masuk', 'admin.pesan.index', 'inbox', 'admin.pesan.*'],
        ],
        'guru' => [
            ['Ringkasan', 'guru.dashboard', 'layout-dashboard', 'guru.dashboard'],
            ['Absensi', 'guru.absensi.index', 'calendar-check', 'guru.absensi.*'],
            ['Laporan Perkembangan', 'guru.perkembangan.index', 'sprout', 'guru.perkembangan.*'],
            ['Dokumentasi Kelas', 'guru.dokumentasi.index', 'camera', 'guru.dokumentasi.*'],
        ],
        default => [
            ['Beranda', 'orangtua.dashboard', 'house', 'orangtua.dashboard'],
            ['group' => 'Anak saya'],
            ...$user->anak()->orderBy('nama')->get()->map(fn ($a) => [$a->nama_panggilan, ['orangtua.anak', $a], 'baby', null, request()->route('siswa')?->id === $a->id])->all(),
            ['group' => 'Informasi'],
            ...array_values(array_filter([
                \App\Support\Pengaturan::fitur('pembayaran') ? ['Pembayaran', 'orangtua.pembayaran', 'wallet', 'orangtua.pembayaran'] : null,
                \App\Support\Pengaturan::fitur('pengumuman') ? ['Pengumuman', 'orangtua.pengumuman', 'megaphone', 'orangtua.pengumuman'] : null,
                \App\Support\Pengaturan::fitur('dokumentasi') ? ['Dokumentasi', 'orangtua.dokumentasi', 'camera', 'orangtua.dokumentasi'] : null,
            ])),
            ['Profil Saya', 'orangtua.profil', 'user-round', 'orangtua.profil'],
            ['group' => 'Komunikasi'],
            ['Chat dengan Admin', 'chat.pribadi', 'message-circle', 'chat.pribadi*'],
            ['Grup Orang Tua', 'chat.grup', 'users-round', 'chat.grup*'],
        ],
    };
@endphp
<body class="dash bg-cloud font-body text-ink antialiased">
<div class="min-h-screen lg:grid lg:grid-cols-[260px_1fr] print:block">
    <aside id="sidebar" class="print:!hidden hidden lg:flex flex-col bg-navy-deep text-white lg:sticky lg:top-0 lg:h-screen">
        <a href="{{ route('home') }}" class="flex items-center gap-3 px-6 h-20 border-b border-white/10">
            @include('partials.logo', ['size' => 40])
            <span>
                <span class="block font-display text-xl font-semibold leading-none">{{ $site['nama_sekolah'] }}</span>
                <span class="block text-[11px] text-white/60 mt-1">Panel {{ $user->roleLabel() }}</span>
            </span>
        </a>
        <button type="button" onclick="toggleSidebar()" class="lg:hidden absolute right-4 top-6 p-1 text-white/80" aria-label="Tutup menu"><i data-lucide="x" class="w-6 h-6"></i></button>
        <nav class="flex-1 p-4 space-y-0.5 overflow-y-auto" aria-label="Menu dashboard">
            @php
                $badge = rescue(fn () => match ($user->role) {
                    'admin' => [
                        'admin.pesan.index' => \App\Models\Pesan::where('dibaca', false)->count(),
                        'admin.tagihan.index' => \App\Models\Tagihan::where('status', 'menunggu')->count(),
                        'admin.pendaftaran.index' => \App\Models\Pendaftaran::where('status', 'baru')->count(),
                        'admin.reset.index' => \App\Models\PermintaanReset::where('status', 'menunggu')->count(),
                        'chat.index' => \App\Models\ChatPesan::belumPribadi($user),
                        'chat.grup' => \App\Models\ChatPesan::belumGrup($user),
                    ],
                    'orangtua' => [
                        'orangtua.pembayaran' => \App\Models\Tagihan::whereIn('siswa_id', $user->anak()->pluck('id'))->where('status', 'belum')->count(),
                        'orangtua.pengumuman' => \App\Models\Pengumuman::untukKelas($user->anak()->pluck('kelas_id'))->where('created_at', '>=', now()->subDays(7))->count(),
                        'chat.pribadi' => \App\Models\ChatPesan::belumPribadi($user),
                        'chat.grup' => \App\Models\ChatPesan::belumGrup($user),
                    ],
                    default => [],
                }, [], false);
            @endphp
            @foreach ($nav as $item)
                @if (isset($item['group']))
                    <p class="px-4 pt-4 pb-1 text-[11px] font-extrabold text-white/40">{{ $item['group'] }}</p>
                    @continue
                @endif
                @php
                    [$label, $route, $icon, $pattern] = $item;
                    $active = $item[4] ?? ($pattern && request()->routeIs($pattern));
                    $href = is_array($route) ? route(...$route) : route($route);
                    $jumlah = is_string($route) ? ($badge[$route] ?? 0) : 0;
                @endphp
                <a href="{{ $href }}" @if($active) aria-current="page" @endif
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold {{ $active ? 'bg-sun text-navy' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    <i data-lucide="{{ $icon }}" class="w-[18px] h-[18px]"></i> {{ $label }}
                    @if ($jumlah)<span class="ml-auto text-[11px] bg-berry text-white rounded-full px-2 py-0.5">{{ $jumlah > 99 ? '99+' : $jumlah }}</span>@endif
                </a>
            @endforeach
        </nav>
        <div class="p-4 border-t border-white/10">
            <p class="text-sm font-bold truncate">{{ $user->name }}</p>
            <p class="text-xs text-white/60 truncate">{{ $user->email }}</p>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">@csrf
                <button class="flex items-center gap-2 text-sm font-bold text-white/80 hover:text-white"><i data-lucide="log-out" class="w-4 h-4"></i> Keluar</button>
            </form>
        </div>
    </aside>

    <div class="min-w-0">
        <header class="print:hidden h-16 lg:h-20 bg-white border-b border-slate-100 flex items-center justify-between px-4 lg:px-8 sticky top-0 z-30">
            <div class="flex items-center gap-3 min-w-0">
                <button type="button" class="lg:hidden p-2 -ml-2 text-navy" aria-label="Buka menu"
                    onclick="toggleSidebar()">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <h1 class="font-display text-xl lg:text-2xl font-semibold text-navy truncate">@yield('heading')</h1>
            </div>
            <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                @yield('actions')
                {{-- Jam WIB --}}
                <div id="jam-wib" class="text-right leading-tight" title="Waktu Indonesia Barat (WIB)">
                    <span class="flex items-center justify-end gap-1.5 font-display text-sm sm:text-base lg:text-lg font-semibold text-navy tabular-nums">
                        <svg class="i-malam w-4 h-4 text-sky" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg><svg class="i-siang w-4 h-4 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                        <span data-jam>--:--:-- WIB</span>
                    </span>
                    <span data-tgl class="hidden md:block text-[11px] text-slate-500 font-bold"></span>
                </div>
                {{-- Mode gelap/terang --}}
                <button type="button" onclick="gantiTema()" class="p-2 rounded-xl text-navy hover:bg-cloud" aria-label="Ganti mode gelap atau terang" title="Mode gelap / terang (otomatis mengikuti waktu)">
                    <i data-lucide="moon" class="w-5 h-5 ikon-moon"></i><i data-lucide="sun" class="w-5 h-5 ikon-sun"></i>
                </button>
                {{-- Notifikasi --}}
                <a href="{{ route('notifikasi.index') }}" class="relative p-2 rounded-xl text-navy hover:bg-cloud" aria-label="Notifikasi" title="Notifikasi">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                    <span id="badge-notif" style="{{ $notifBelum ? '' : 'display:none' }}" class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 text-[10px] font-extrabold bg-berry text-white rounded-full grid place-items-center">{{ $notifBelum > 99 ? '99+' : $notifBelum }}</span>
                </a>
                <span class="hidden sm:grid w-10 h-10 rounded-full bg-sky-soft text-sky font-display text-lg place-items-center" title="{{ $user->name }}">{{ $user->inisial }}</span>
            </div>
        </header>
        @if (session('admin_asli'))
            <div class="print:hidden bg-sun text-navy px-4 lg:px-8 py-2.5 flex flex-wrap items-center justify-between gap-3 text-sm font-bold">
                <span class="flex items-center gap-2"><i data-lucide="eye" class="w-4 h-4"></i> Anda sedang melihat panel sebagai <u>{{ $user->name }}</u> ({{ $user->roleLabel() }}). Perubahan yang Anda buat tercatat atas nama akun ini.</span>
                <form method="POST" action="{{ route('kembali.admin') }}">@csrf
                    <button class="inline-flex items-center gap-1.5 bg-navy text-white px-4 py-1.5 rounded-lg"><i data-lucide="undo-2" class="w-4 h-4"></i> Kembali ke admin</button>
                </form>
            </div>
        @endif
        <main class="p-4 lg:p-8 max-w-6xl">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
</div>
<script>
    lucide.createIcons();
    function toggleSidebar() {
        const s = document.getElementById('sidebar');
        ['hidden', 'flex', 'fixed', 'inset-0', 'z-50'].forEach(c => s.classList.toggle(c));
    }

    // Jam WIB, salam, dan tema otomatis dikelola di partials/tema.blade.php

    // Angka lonceng notifikasi diperbarui otomatis tiap 20 detik
    (function () {
        const b = document.getElementById('badge-notif');
        if (!b) return;
        async function cek() {
            if (document.hidden) return;
            try {
                const r = await fetch(@json(route('notifikasi.ringkas')), { headers: { Accept: 'application/json' } });
                if (!r.ok) return;
                const d = await r.json();
                b.textContent = d.notif > 99 ? '99+' : d.notif;
                b.style.display = d.notif ? '' : 'none';
            } catch (e) {}
        }
        setInterval(cek, 20000);
    })();
</script>
@stack('scripts')
</body>
</html>
