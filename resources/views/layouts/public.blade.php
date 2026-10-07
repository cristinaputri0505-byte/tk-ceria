<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $site['nama_sekolah'].' — '.$site['tagline'])</title>
    <meta name="description" content="{{ $site['nama_sekolah'] }}: {{ \Illuminate\Support\Str::limit($site['hero_deskripsi'], 150) }}">
    @include('partials.head')
</head>
<body class="bg-cloud font-body text-ink antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 bg-white px-4 py-2 rounded-lg">Lewati ke konten</a>

    {{-- NAVBAR --}}
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-slate-100">
        <nav class="max-w-7xl mx-auto px-4 lg:px-8 h-[72px] flex items-center justify-between gap-3 sm:gap-6">
            <a href="{{ route('home') }}" class="flex items-center gap-2 sm:gap-3 min-w-0">
                @include('partials.logo', ['size' => 44])
                <span class="leading-none min-w-0">
                    <span class="block font-display text-xl sm:text-[1.7rem] font-semibold text-navy truncate">{{ $site['nama_sekolah'] }}</span>
                    <span class="hidden sm:block text-[11px] font-bold text-navy/80 mt-0.5 truncate">{{ $site['tagline'] }}</span>
                </span>
            </a>

            @php
                $menu = [
                    ['Home', route('home'), request()->routeIs('home')],
                    ['Tentang Kami', route('tentang'), request()->routeIs('tentang')],
                    ['Program', route('program'), request()->routeIs('program')],
                    ['Kegiatan', route('kegiatan.index'), request()->routeIs('kegiatan.*')],
                    ['Galeri', route('galeri'), request()->routeIs('galeri')],
                    ['Guru & Staff', route('guru'), request()->routeIs('guru')],
                    ['Berita', route('berita.index'), request()->routeIs('berita.*')],
                    ['Kontak', route('kontak'), request()->routeIs('kontak')],
                ];
            @endphp

            <ul class="hidden lg:flex items-center gap-1 text-[13.5px] font-bold text-navy">
                @foreach ($menu as [$label, $url, $active])
                    <li><a href="{{ $url }}" @if($active) aria-current="page" @endif class="relative px-3 py-2 rounded-lg hover:text-sky transition-colors {{ $active ? 'text-navy after:absolute after:left-3 after:right-3 after:-bottom-1 after:h-[3px] after:rounded-full after:bg-leaf' : '' }}">{{ $label }}</a></li>
                @endforeach
            </ul>

            <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                @auth
                    <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="inline-flex items-center gap-2 bg-sky text-white text-sm font-bold px-3 sm:px-5 py-2.5 rounded-full hover:bg-sky-dark" aria-label="Dashboard">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i> <span class="hidden sm:inline">Dashboard</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-sky text-white text-sm font-bold px-4 sm:px-6 py-2.5 rounded-full hover:bg-sky-dark">
                        <i data-lucide="user-round" class="w-4 h-4"></i> Login
                    </a>
                @endauth
                <button type="button" class="lg:hidden p-2 rounded-lg text-navy" aria-label="Buka menu" aria-expanded="false" onclick="const m=document.getElementById('mobileMenu');m.classList.toggle('hidden');this.setAttribute('aria-expanded',!m.classList.contains('hidden'))">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
            </div>
        </nav>
        <ul id="mobileMenu" class="hidden lg:hidden border-t border-slate-100 px-4 py-3 grid grid-cols-2 gap-1 font-bold text-navy text-sm">
            @foreach ($menu as [$label, $url])
                <li><a href="{{ $url }}" class="block px-3 py-2 rounded-lg hover:bg-cloud">{{ $label }}</a></li>
            @endforeach
        </ul>
    </header>

    <main id="main">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    @php
        $sosmed = array_filter(['facebook' => ['facebook', 'Facebook'], 'instagram' => ['instagram', 'Instagram'], 'youtube' => ['youtube', 'YouTube'], 'tiktok' => ['music-2', 'TikTok']], fn ($v, $k) => ! empty($site[$k]), ARRAY_FILTER_USE_BOTH);
        $wa = preg_replace('/^0/', '62', preg_replace('/\D/', '', (string) $site['whatsapp']));
    @endphp
    <footer class="bg-navy-deep text-white mt-16">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 py-10 grid gap-8 md:grid-cols-3">
            <div class="flex items-start gap-3">
                @include('partials.logo', ['size' => 44])
                <div>
                    <p class="font-display text-2xl font-semibold leading-none">{{ $site['nama_sekolah'] }}</p>
                    <p class="text-sm text-white/80 mt-1">{{ $site['tagline'] }}</p>
                    <p class="text-sm text-white/70 mt-4 leading-relaxed">{!! nl2br(e($site['alamat'])) !!}@if($site['jam_operasional'])<br>{{ $site['jam_operasional'] }}@endif</p>
                </div>
            </div>
            <div class="text-sm text-white/80 space-y-2">
                <p class="font-bold text-white"><a href="{{ route('kontak') }}" class="hover:underline">Hubungi kami</a></p>
                @if ($site['telepon'])<p class="flex items-center gap-2"><i data-lucide="phone" class="w-4 h-4"></i> {{ $site['telepon'] }}</p>@endif
                @if ($site['whatsapp'])<p><a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="flex items-center gap-2 hover:text-white"><i data-lucide="message-circle" class="w-4 h-4"></i> {{ $site['whatsapp'] }} (WhatsApp)</a></p>@endif
                @if ($site['email'])<p><a href="mailto:{{ $site['email'] }}" class="flex items-center gap-2 hover:text-white"><i data-lucide="mail" class="w-4 h-4"></i> {{ $site['email'] }}</a></p>@endif
            </div>
            <div class="md:text-right">
                @if ($sosmed)
                    <div class="flex md:justify-end gap-3">
                        @foreach ($sosmed as $k => [$ic, $label])
                            <a href="{{ $site[$k] }}" target="_blank" rel="noopener" aria-label="{{ $label }}" class="w-10 h-10 grid place-items-center rounded-full bg-white/10 hover:bg-white/20"><i data-lucide="{{ $ic }}" class="w-5 h-5"></i></a>
                        @endforeach
                    </div>
                @endif
                <p class="text-xs text-white/60 mt-6">&copy; {{ date('Y') }} {{ $site['nama_sekolah'] }}. Hak cipta dilindungi. · <a href="{{ route('privasi') }}" class="underline hover:text-white">Kebijakan Privasi</a></p>
            </div>
        </div>
    </footer>

    <script>lucide.createIcons();</script>
    @stack('scripts')
</body>
</html>
