<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — {{ $site['nama_sekolah'] }}</title>
    @include('partials.head')
</head>
<body class="min-h-screen bg-gradient-to-br from-sky-soft via-cloud to-sun-soft font-body text-ink grid place-items-center p-4">
    <main class="w-full max-w-md">
        <a href="{{ route('home') }}" class="flex items-center justify-center gap-3 mb-6">
            @include('partials.logo', ['size' => 56])
            <span class="font-display text-3xl font-semibold text-navy">{{ $site['nama_sekolah'] }}</span>
        </a>
        <div class="bg-white rounded-3xl p-8 shadow-[0_20px_60px_-30px_rgba(20,48,125,.35)]">
            <h1 class="font-display text-2xl font-semibold text-navy">{{ $site['login_judul'] }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $site['login_sub'] }}</p>

            <form method="POST" action="{{ route('login.attempt') }}" class="mt-6 space-y-4">
                @csrf
                @include('partials.field', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'attrs' => 'autocomplete=email autofocus'])
                <div>
                    <label for="password" class="block text-sm font-bold text-navy mb-1.5">Kata sandi</label>
                    <div class="relative">
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 pr-11 text-sm focus:border-sky focus:ring-2 focus:ring-sky/20 outline-none">
                        <button type="button" aria-label="Tampilkan kata sandi" class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 text-slate-400 hover:text-navy"
                            onclick="const i=document.getElementById('password');i.type=i.type==='password'?'text':'password'">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
                <div class="flex items-center justify-between gap-3"><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" class="rounded accent-sky"> Ingat saya di perangkat ini</label><a href="{{ Route::has('lupa.form') ? route('lupa.form') : url('lupa-password') }}" class="text-sm font-bold text-sky hover:underline">Lupa kata sandi?</a></div>
                <button class="w-full bg-sky text-white font-extrabold py-3 rounded-xl hover:bg-sky-dark">Masuk</button>
            </form>
            <p class="text-xs text-slate-500 mt-5 text-center whitespace-pre-line">{{ $site['login_bantuan'] }} Hubungi sekolah di {{ $site['whatsapp'] ?: $site['telepon'] }}.</p>
        </div>
        <p class="text-center mt-5 text-sm"><a href="{{ route('home') }}" class="font-bold text-navy hover:text-sky">Kembali ke beranda</a> · <a href="{{ route('privasi') }}" class="font-bold text-navy hover:text-sky">Kebijakan Privasi</a></p>
    </main>
    <script>lucide.createIcons();</script>
</body>
</html>
