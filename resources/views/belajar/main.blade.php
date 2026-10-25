<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Ruang Belajar {{ $siswa->nama_panggilan }} — {{ $site['nama_sekolah'] }}</title>
    @include('partials.head')
</head>
<body class="belajar min-h-screen flex flex-col font-display text-slate-800">

{{-- ============ HEADER ============ --}}
<header class="sticky top-0 z-30 bg-white/95 backdrop-blur border-b-4 border-pink-200 px-4 py-3 shadow-sm">
    <div class="max-w-6xl mx-auto flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('orangtua.dashboard') }}" class="flex items-center gap-3" title="Kembali ke panel orang tua">
            <span class="belajar-float">@include('partials.logo', ['size' => 44])</span>
            <span>
                <span class="block text-lg md:text-2xl font-bold text-pink-500 leading-tight">Ruang Belajar</span>
                <span class="block text-xs md:text-sm text-slate-500 font-medium">{{ $site['nama_sekolah'] }} · {{ $siswa->kelas?->nama ?? 'Belajar sambil bermain' }}</span>
            </span>
        </a>

        <div class="flex items-center gap-2 md:gap-3 ml-auto">
            @if ($semuaAnak->count() > 1)
                <div class="flex gap-1.5">
                    @foreach ($semuaAnak as $a)
                        <a href="{{ route('belajar.main', $a) }}" class="btn-3d {{ $a->id === $siswa->id ? 'btn-pink text-white' : 'btn-white text-slate-700' }} px-3 py-1.5 rounded-2xl text-sm font-bold">{{ $a->nama_panggilan }}</a>
                    @endforeach
                </div>
            @else
                <span class="btn-white flex items-center gap-2 px-3 py-1.5 rounded-2xl text-sm md:text-base font-semibold text-slate-700">
                    @include('partials.foto-anak', ['s' => $siswa, 'size' => 'w-8 h-8 !rounded-full !border-2 !shadow-none', 'teks' => 'text-sm'])
                    <span class="max-w-[110px] truncate">{{ $siswa->nama_panggilan }}</span>
                </span>
            @endif

            <div class="flex items-center gap-2 bg-yellow-100 border-2 border-yellow-400 px-3.5 py-1.5 rounded-2xl">
                <span class="text-2xl animate-bounce" aria-hidden="true">⭐</span>
                <span><span id="jumlah-bintang" class="text-lg md:text-xl font-bold text-yellow-700">0</span> <span class="text-xs text-yellow-700 hidden sm:inline font-semibold">Bintang</span></span>
            </div>

            <a href="{{ route('orangtua.dashboard') }}" class="btn-3d btn-purple text-white px-3 md:px-4 py-2 rounded-2xl font-bold text-sm flex items-center gap-2" title="Kembali ke panel orang tua">
                <i data-lucide="log-out" class="w-4 h-4"></i><span class="hidden md:inline">Panel Orang Tua</span>
            </a>
        </div>
    </div>
</header>

{{-- ============ ISI ============ --}}
<main class="flex-grow max-w-6xl w-full mx-auto px-4 py-6 space-y-6">

    {{-- Pesan guru --}}
    <section class="bg-gradient-to-r from-sky-400 via-cyan-400 to-teal-400 rounded-3xl p-5 md:p-6 text-white shadow-lg border-4 border-white relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4 text-center md:text-left flex-col md:flex-row">
                <div class="text-5xl md:text-6xl bg-white/25 p-3 rounded-3xl" aria-hidden="true">👩‍🏫</div>
                <div>
                    <span class="bg-white/30 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">Pesan Ibu/Bapak Guru</span>
                    <h1 id="pesan-guru" class="text-xl md:text-2xl font-bold mt-1.5"></h1>
                </div>
            </div>
            <button type="button" data-aksi="dengar-guru" class="btn-3d btn-yellow text-slate-800 px-5 py-3 rounded-2xl font-bold flex items-center gap-2 shrink-0">
                <i data-lucide="volume-2" class="w-5 h-5"></i><span>Dengarkan Guru</span>
            </button>
        </div>
        <div class="absolute -right-6 -bottom-6 text-8xl opacity-25 pointer-events-none" aria-hidden="true">🎨</div>
    </section>

    {{-- Progres --}}
    <section class="bg-white rounded-3xl p-4 md:p-5 border-4 border-pink-100 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="text-3xl" aria-hidden="true">🚀</span>
            <div>
                <h2 class="font-bold text-lg text-slate-700">Petualangan Belajar {{ $siswa->nama_panggilan }}</h2>
                <p id="progres-teks" class="text-sm text-slate-500">Ayo selesaikan semua tugas untuk mendapat bintang!</p>
            </div>
        </div>
        <div class="w-full sm:w-64">
            <div class="flex justify-between text-sm font-bold mb-1">
                <span class="text-pink-500">Progres</span>
                <span id="progres-angka" class="text-slate-700">0 / 0 Selesai</span>
            </div>
            <div class="w-full h-5 bg-slate-100 rounded-full overflow-hidden border-2 border-slate-200 p-0.5">
                <div id="progres-bar" class="h-full bg-gradient-to-r from-green-400 to-emerald-500 rounded-full transition-all duration-500" style="width:0%"></div>
            </div>
        </div>
    </section>

    {{-- Filter --}}
    <nav id="filter" class="flex items-center gap-2 overflow-x-auto pb-2" aria-label="Jenis permainan">
        <button type="button" data-filter="semua" class="btn-3d btn-pink text-white px-4 py-2 rounded-2xl font-bold text-sm md:text-base shrink-0">🌟 Semua</button>
        <button type="button" data-filter="kuis" class="btn-3d btn-white text-slate-700 px-4 py-2 rounded-2xl font-bold text-sm md:text-base shrink-0">❓ Tebak Gambar</button>
        <button type="button" data-filter="hitung" class="btn-3d btn-white text-slate-700 px-4 py-2 rounded-2xl font-bold text-sm md:text-base shrink-0">🔢 Berhitung</button>
        <button type="button" data-filter="cocok" class="btn-3d btn-white text-slate-700 px-4 py-2 rounded-2xl font-bold text-sm md:text-base shrink-0">🧩 Mencocokkan</button>
        <button type="button" data-filter="gambar" class="btn-3d btn-white text-slate-700 px-4 py-2 rounded-2xl font-bold text-sm md:text-base shrink-0">🎨 Menggambar</button>
    </nav>

    {{-- Daftar kartu tugas --}}
    <section id="daftar-tugas" class="grid grid-cols-1 sm:grid-cols-2 gap-6" aria-live="polite"></section>

    {{-- Ruang bermain --}}
    <section id="pemain" class="hidden bg-white rounded-3xl border-4 border-sky-200 shadow-xl p-5 md:p-8 belajar-pop">
        <div class="flex flex-wrap items-center justify-between gap-4 pb-4 mb-6 border-b-2 border-dashed border-slate-200">
            <button type="button" data-aksi="tutup" class="btn-3d btn-white px-4 py-2.5 rounded-2xl font-bold text-slate-700 flex items-center gap-2">
                <i data-lucide="arrow-left" class="w-5 h-5 text-pink-500"></i><span>Kembali</span>
            </button>
            <div class="flex items-center gap-2">
                <span id="pemain-jenis" class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-sky-100 text-sky-700"></span>
                <span id="pemain-bintang" class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm font-bold"></span>
            </div>
        </div>
        <div class="text-center max-w-2xl mx-auto mb-8">
            <div id="pemain-ikon" class="text-6xl mb-3" aria-hidden="true"></div>
            <h2 id="pemain-judul" class="text-2xl md:text-3xl font-bold text-slate-800 mb-2"></h2>
            <p id="pemain-instruksi" class="text-lg md:text-xl text-slate-600 font-medium mb-4"></p>
            <button type="button" data-aksi="bacakan" class="btn-3d btn-yellow text-slate-900 px-6 py-3 rounded-2xl font-bold text-base md:text-lg inline-flex items-center gap-3">
                <i data-lucide="volume-2" class="w-6 h-6 text-orange-600 animate-pulse"></i><span>Bacakan Soal</span>
            </button>
        </div>
        <div id="area-main" class="max-w-3xl mx-auto"></div>
    </section>
</main>

<footer class="mt-auto py-4 text-center text-xs md:text-sm text-slate-400 border-t border-pink-100 bg-white/60">
    🌈 <strong>Ruang Belajar {{ $site['nama_sekolah'] }}</strong> — belajar sambil bermain
</footer>

{{-- ============ JENDELA ============ --}}
<div id="jendela-hore" class="fixed inset-0 z-50 hidden bg-slate-900/60 flex items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 md:p-8 text-center border-4 border-yellow-300 shadow-2xl belajar-pop">
        <div class="text-7xl mb-3 animate-bounce" aria-hidden="true">🏆</div>
        <h3 class="text-3xl font-bold text-pink-500 mb-1">Hebat Sekali!</h3>
        <p id="hore-nama" class="text-lg font-semibold text-slate-600 mb-4"></p>
        <div class="bg-yellow-50 border-2 border-yellow-300 rounded-2xl p-4 mb-6 inline-flex items-center gap-3">
            <span class="text-4xl" aria-hidden="true">⭐</span>
            <span class="text-left">
                <span class="block text-xs font-bold text-yellow-700 uppercase">Hadiah Bintang</span>
                <span id="hore-bintang" class="block text-2xl font-bold text-yellow-600"></span>
            </span>
        </div>
        <button type="button" data-aksi="lanjut" class="btn-3d btn-green text-white w-full py-3.5 rounded-2xl font-bold text-xl">Lanjut Bermain! 😄</button>
    </div>
</div>

<div id="jendela-karya" class="fixed inset-0 z-50 hidden bg-slate-900/60 flex items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 border-4 border-amber-300 shadow-2xl belajar-pop text-center">
        <h3 id="karya-judul" class="text-2xl font-bold text-amber-600 mb-3">Karyaku</h3>
        <div class="bg-slate-50 border-2 border-dashed border-slate-300 rounded-2xl p-2 mb-4">
            <img id="karya-img" src="" alt="Hasil gambar" class="w-full h-auto rounded-xl mx-auto">
        </div>
        <button type="button" data-aksi="tutup-karya" class="btn-3d btn-pink text-white px-6 py-2.5 rounded-xl font-bold">Tutup</button>
    </div>
</div>

<div id="toast" class="fixed bottom-5 right-5 z-50 hidden bg-slate-800 text-white px-5 py-3 rounded-2xl shadow-xl font-bold belajar-pop"></div>
<canvas id="konfeti" class="fixed inset-0 z-[60] pointer-events-none w-full h-full"></canvas>

<script>window.BELAJAR = @json($muatan);</script>
<script src="{{ asset('js/belajar.js') }}?v={{ filemtime(public_path('js/belajar.js')) }}"></script>
<script>lucide.createIcons();</script>
</body>
</html>
