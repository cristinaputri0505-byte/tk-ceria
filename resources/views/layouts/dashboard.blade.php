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
    // Menu bawah (khusus HP): 4 pintasan + tombol "Menu"
    $navBawah = match ($user->role) {
        'admin' => [
            ['Ringkasan', 'admin.dashboard', 'layout-dashboard', 'admin.dashboard'],
            ['Siswa', 'admin.siswa.index', 'baby', 'admin.siswa.*'],
            ['Tagihan', 'admin.tagihan.index', 'wallet', 'admin.tagihan.*'],
            ['Chat', 'chat.index', 'message-circle', 'chat.*'],
        ],
        'guru' => [
            ['Ringkasan', 'guru.dashboard', 'layout-dashboard', 'guru.dashboard'],
            ['Absensi', 'guru.absensi.index', 'calendar-check', 'guru.absensi.*'],
            ['Laporan', 'guru.perkembangan.index', 'sprout', 'guru.perkembangan.*'],
            ['Foto', 'guru.dokumentasi.index', 'camera', 'guru.dokumentasi.*'],
        ],
        default => array_values(array_filter([
            ['Beranda', 'orangtua.dashboard', 'house', 'orangtua.dashboard'],
            \App\Support\Pengaturan::fitur('pembayaran') ? ['Bayar', 'orangtua.pembayaran', 'wallet', 'orangtua.pembayaran'] : null,
            \App\Support\Pengaturan::fitur('pengumuman') ? ['Info', 'orangtua.pengumuman', 'megaphone', 'orangtua.pengumuman'] : null,
            ['Chat', 'chat.pribadi', 'message-circle', 'chat.*'],
        ])),
    };
@endphp
<body class="dash bg-cloud font-body text-ink antialiased">
{{-- Latar gelap di belakang menu samping (HP) --}}
<div id="sidebar-latar" onclick="toggleSidebar(false)" class="print:hidden lg:hidden fixed inset-0 z-40 bg-navy-deep/60 backdrop-blur-[2px] opacity-0 pointer-events-none transition-opacity duration-300"></div>
<div class="min-h-screen lg:grid lg:grid-cols-[260px_1fr] print:block">
    <aside id="sidebar" class="print:!hidden fixed inset-y-0 left-0 z-50 w-[86%] max-w-[320px] -translate-x-full transition-transform duration-300 ease-out rounded-r-3xl shadow-2xl lg:shadow-none lg:rounded-none lg:w-auto lg:max-w-none lg:translate-x-0 lg:transition-none flex flex-col bg-navy-deep text-white lg:sticky lg:top-0 lg:h-screen" aria-label="Menu">
        <a href="{{ route('home') }}" class="flex items-center gap-3 px-6 h-20 border-b border-white/10">
            @include('partials.logo', ['size' => 40])
            <span>
                <span class="block font-display text-xl font-semibold leading-none">{{ $site['nama_sekolah'] }}</span>
                <span class="block text-[11px] text-white/60 mt-1">Panel {{ $user->roleLabel() }}</span>
            </span>
        </a>
        <button type="button" onclick="toggleSidebar(false)" class="lg:hidden absolute right-4 top-6 p-1.5 rounded-xl text-white/80 hover:bg-white/10" aria-label="Tutup menu"><svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
        {{-- Kartu akun + tombol Keluar (HP) --}}
        <div class="lg:hidden mx-4 mt-4 p-3 rounded-2xl bg-white/[.07] flex items-center gap-3">
            <span class="w-11 h-11 shrink-0 rounded-full bg-sun text-navy font-display text-lg grid place-items-center">{{ $user->inisial }}</span>
            <span class="min-w-0 flex-1">
                <span class="block text-sm font-bold truncate">{{ $user->name }}</span>
                <span class="block text-xs text-white/60 truncate">{{ $user->email }}</span>
            </span>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="flex items-center gap-1.5 text-xs font-extrabold bg-berry/90 hover:bg-berry text-white px-3 py-2 rounded-xl" aria-label="Keluar dari akun"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg> Keluar</button>
            </form>
        </div>
        <nav class="flex-1 p-4 space-y-0.5 overflow-y-auto" aria-label="Menu dashboard">
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
        <div class="hidden lg:block p-4 border-t border-white/10">
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
                <button type="button" class="lg:hidden p-2 -ml-2 rounded-xl text-navy hover:bg-cloud" aria-label="Buka menu"
                    onclick="toggleSidebar(true)">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
                </button>
                <h1 class="font-display text-xl lg:text-2xl font-semibold text-navy truncate">@yield('heading')</h1>
            </div>
            <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                @yield('actions')
                {{-- Jam WIB --}}
                <div id="jam-wib" class="hidden sm:block text-right leading-tight" title="Waktu Indonesia Barat (WIB)">
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
                <button type="button" onclick="toggleSidebar(true)" class="sm:hidden w-9 h-9 rounded-full bg-sky-soft text-sky font-display text-base grid place-items-center" aria-label="Akun & menu">{{ $user->inisial }}</button>
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
        <main class="p-4 pb-28 lg:p-8 max-w-6xl">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
</div>
{{-- Menu bawah (HP) --}}
<style>
    html.dark .nav-bawah { background-color: rgba(21, 34, 56, .95); border-color: #24365a; }
    @media (max-width: 1023px) { .chat-ruang { height: calc(100dvh - 16rem) !important; } }
</style>
<nav class="print:hidden lg:hidden fixed bottom-0 inset-x-0 z-30 px-3 pb-[max(.5rem,env(safe-area-inset-bottom))] pt-2 pointer-events-none" aria-label="Menu cepat">
    <div class="nav-bawah pointer-events-auto mx-auto max-w-md grid grid-cols-5 bg-white/95 backdrop-blur border border-slate-100 rounded-2xl shadow-[0_8px_30px_rgba(15,38,80,.15)]">
        @foreach ($navBawah as [$label, $route, $icon, $pattern])
            @php
                $aktif = request()->routeIs($pattern);
                $jumlah = $label === 'Chat' ? collect($badge)->filter(fn ($v, $k) => str_starts_with($k, 'chat.'))->sum() : ($badge[$route] ?? 0);
            @endphp
            <a href="{{ route($route) }}" @if($aktif) aria-current="page" @endif class="relative flex flex-col items-center gap-0.5 py-2 text-[11px] font-extrabold {{ $aktif ? 'text-sky' : 'text-slate-500' }}">
                <span class="grid place-items-center w-11 h-7 rounded-full {{ $aktif ? 'bg-sky-soft' : '' }}"><i data-lucide="{{ $icon }}" class="w-5 h-5"></i></span>
                {{ $label }}
                @if ($jumlah)<span class="absolute top-1 right-[calc(50%-1.4rem)] min-w-[18px] h-[18px] px-1 text-[10px] bg-berry text-white rounded-full grid place-items-center">{{ $jumlah > 99 ? '99+' : $jumlah }}</span>@endif
            </a>
        @endforeach
        <button type="button" onclick="toggleSidebar(true)" class="flex flex-col items-center gap-0.5 py-2 text-[11px] font-extrabold text-slate-500" aria-label="Semua menu">
            <span class="grid place-items-center w-11 h-7 rounded-full"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg></span>
            Menu
        </button>
    </div>
</nav>
<script>
    if (window.lucide) lucide.createIcons();
    function toggleSidebar(buka) {
        const s = document.getElementById('sidebar'), l = document.getElementById('sidebar-latar');
        if (typeof buka !== 'boolean') buka = s.classList.contains('-translate-x-full');
        s.classList.toggle('-translate-x-full', !buka);
        l.classList.toggle('opacity-0', !buka);
        l.classList.toggle('pointer-events-none', !buka);
        document.body.classList.toggle('overflow-hidden', buka);
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') toggleSidebar(false); });

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
