<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Password — {{ $site['nama_sekolah'] ?? 'TK Ceria' }}</title>
    @include('partials.head')
</head>
<body class="bg-cloud font-body text-ink antialiased min-h-screen grid place-items-center p-4">
<main class="w-full max-w-md">
    <div class="text-center mb-6">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
            @include('partials.logo', ['size' => 48])
            <span class="font-display text-2xl font-semibold text-navy">{{ $site['nama_sekolah'] ?? 'TK Ceria' }}</span>
        </a>
    </div>

    <div class="bg-white rounded-[2rem] border border-slate-100 p-6 sm:p-8 shadow-sm">
        @if (session('terkirim'))
            <div class="text-center">
                <span class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-700 grid place-items-center mx-auto"><i data-lucide="check" class="w-7 h-7"></i></span>
                <h1 class="font-display text-2xl font-semibold text-navy mt-4">Permintaan terkirim</h1>
                <p class="text-sm text-slate-600 mt-2">Jika email Anda terdaftar, admin sekolah akan menghubungi Anda untuk memverifikasi dan memberikan password baru. Mohon tunggu ya.</p>
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 mt-6 bg-sky text-white text-sm font-extrabold px-5 py-3 rounded-xl hover:bg-sky-dark">Kembali ke halaman masuk</a>
            </div>
        @else
            <h1 class="font-display text-2xl font-semibold text-navy">Lupa password?</h1>
            <p class="text-sm text-slate-600 mt-1">Masukkan email akun Anda. Permintaan akan dikirim ke admin sekolah, lalu admin akan menghubungi Anda untuk memberikan password baru.</p>

            <form method="POST" action="{{ route('lupa.kirim') }}" class="mt-5 space-y-4">@csrf
                <div>
                    <label for="email" class="block text-sm font-bold text-navy">Email akun</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-sky/40">
                    @error('email')<p class="text-sm text-berry mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="telepon" class="block text-sm font-bold text-navy">Nomor WhatsApp aktif <span class="font-normal text-slate-500">(opsional)</span></label>
                    <input id="telepon" name="telepon" type="tel" value="{{ old('telepon') }}" inputmode="tel" autocomplete="tel"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-sky/40">
                    @error('telepon')<p class="text-sm text-berry mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="catatan" class="block text-sm font-bold text-navy">Catatan <span class="font-normal text-slate-500">(opsional, mis. nama anak)</span></label>
                    <input id="catatan" name="catatan" type="text" maxlength="255" value="{{ old('catatan') }}"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-sky/40">
                    @error('catatan')<p class="text-sm text-berry mt-1">{{ $message }}</p>@enderror
                </div>
                <button class="w-full bg-sky text-white font-extrabold py-3 rounded-xl hover:bg-sky-dark">Kirim permintaan ke admin</button>
            </form>
            <p class="text-center mt-5"><a href="{{ route('login') }}" class="text-sm font-bold text-sky">← Kembali ke halaman masuk</a></p>
        @endif
    </div>
</main>
<script>lucide.createIcons();</script>
</body>
</html>
