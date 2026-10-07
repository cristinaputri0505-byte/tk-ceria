<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('kode') · {{ $site['nama_sekolah'] ?? 'TK Ceria' }}</title>
    @include('partials.head')
</head>
<body class="min-h-screen bg-gradient-to-br from-sky-soft via-cloud to-sun-soft font-body text-ink grid place-items-center p-4">
    <main class="w-full max-w-lg text-center">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-3">@include('partials.logo', ['size' => 48])<span class="font-display text-2xl font-semibold text-navy">{{ $site['nama_sekolah'] ?? 'TK Ceria' }}</span></a>
        <div class="bg-white rounded-[2rem] p-8 sm:p-10 mt-6 shadow-[0_20px_60px_-30px_rgba(20,48,125,.35)]">
            <span class="mx-auto w-20 h-20 rounded-full grid place-items-center bg-sun-soft text-amber-600"><i data-lucide="@yield('ikon', 'circle-help')" class="w-10 h-10"></i></span>
            <p class="font-display text-6xl font-semibold text-navy mt-4">@yield('kode')</p>
            <h1 class="font-display text-2xl font-semibold text-navy mt-2">@yield('judul')</h1>
            <p class="text-slate-500 mt-2">@yield('pesan')</p>
            <div class="flex flex-wrap justify-center gap-2 mt-6">
                @yield('tombol')
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 bg-sky text-white font-extrabold px-5 py-2.5 rounded-xl hover:bg-sky-dark"><i data-lucide="house" class="w-4 h-4"></i> Ke beranda</a>
            </div>
        </div>
    </main>
    <script>if (window.lucide) lucide.createIcons();</script>
</body>
</html>
