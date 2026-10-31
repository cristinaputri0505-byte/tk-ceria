{{-- Waktu WIB, tema otomatis (malam = gelap), khusus dashboard. Dipasang lewat layouts/dashboard. --}}
<script>
(function () {
    var TZ = 'Asia/Jakarta', KUNCI = 'tema2';
    var fJam = new Intl.DateTimeFormat('en-GB', { timeZone: TZ, hour: 'numeric', hourCycle: 'h23' });
    var fWaktu = new Intl.DateTimeFormat('id-ID', { timeZone: TZ, hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
    var fTgl = new Intl.DateTimeFormat('id-ID', { timeZone: TZ, weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });

    // Batas waktu (WIB): malam 18.00-03.59 | pagi 04.00-10.59 | siang 11.00-14.59 | sore 15.00-17.59
    // Tema otomatis: malam = gelap, selain itu = terang.
    function waktu() {
        var h = parseInt(fJam.format(new Date()), 10) % 24;
        var p = (h >= 18 || h < 4) ? 'malam' : (h < 11 ? 'pagi' : (h < 15 ? 'siang' : 'sore'));
        return { periode: p, fase: p === 'malam' ? 'malam' : 'siang', salam: 'Selamat ' + p };
    }
    function pilihan() { try { return JSON.parse(localStorage.getItem(KUNCI) || 'null'); } catch (e) { return null; } }

    window.tkTerapkanTema = function () {
        var w = waktu(), gelap = w.fase === 'malam', s = pilihan();
        // Pilihan manual hanya berlaku selama fase yang sama (siang/malam). Ganti fase = kembali otomatis.
        if (s && s.fase === w.fase) gelap = s.mode === 'gelap';
        else if (s) { try { localStorage.removeItem(KUNCI); } catch (e) {} }
        var h = document.documentElement;
        h.classList.toggle('dark', gelap);
        h.setAttribute('data-fase', w.fase);
        return w;
    };

    window.gantiTema = function () {
        var w = waktu(), h = document.documentElement;
        var gelap = !h.classList.contains('dark');
        h.classList.toggle('dark', gelap);
        try { localStorage.setItem(KUNCI, JSON.stringify({ mode: gelap ? 'gelap' : 'terang', fase: w.fase })); } catch (e) {}
    };

    var jam = [];
    function segarkanJam() {
        var j = fWaktu.format(new Date()).replace(/\./g, ':') + ' WIB';
        for (var i = 0; i < jam.length; i++) jam[i].textContent = j;
    }

    window.tkSegarkanTeks = function () {
        var n = new Date(), w = waktu();
        document.querySelectorAll('[data-salam]').forEach(function (e) { e.textContent = w.salam; });
        document.querySelectorAll('[data-tgl-wib],[data-tgl]').forEach(function (e) { e.textContent = fTgl.format(n); });
        jam = document.querySelectorAll('[data-jam]');
        segarkanJam();
    };

    try { localStorage.removeItem('tema'); } catch (e) {}   // kunci lama
    tkTerapkanTema();
    window.addEventListener('storage', tkTerapkanTema);

    document.addEventListener('DOMContentLoaded', function () {
        tkSegarkanTeks();
        // Jam berdetik tiap detik (hanya jika jamnya ada di halaman). Tema, salam, dan tanggal
        // cukup diperiksa tiap 30 detik, tidak perlu memindai seluruh halaman setiap detik.
        if (jam.length) setInterval(function () { if (!document.hidden) segarkanJam(); }, 1000);
        setInterval(function () { tkTerapkanTema(); tkSegarkanTeks(); }, 30000);
        document.addEventListener('visibilitychange', function () { if (!document.hidden) { tkTerapkanTema(); tkSegarkanTeks(); } });
    });
})();
</script>
<style>
    /* Ikon sesuai waktu: bulan di malam hari, matahari di siang hari */
    .i-malam, .ilu-malam { display: none; }
    html[data-fase="malam"] .i-malam { display: inline-block; }
    html[data-fase="malam"] .ilu-malam { display: block; }
    html[data-fase="malam"] .i-siang, html[data-fase="malam"] .ilu-siang { display: none; }

    /* Tombol tema: tampilkan matahari saat gelap (untuk kembali terang), bulan saat terang */
    .ikon-sun { display: none; }
    html.dark .ikon-sun { display: inline-block; }
    html.dark .ikon-moon { display: none; }

    /* ===== Mode gelap tema "Taman Bermain": malam ungu tua, stiker tetap berwarna ===== */
    html.dark { color-scheme: dark; }
    html.dark body { background: #17122a !important; color: #ece7f8; }
    html.dark .dash { --garis: #6b5c96; --bayang: #07050f; }

    /* Permukaan */
    html.dark .bg-white { background-color: #241c3a; }
    html.dark .bg-cloud { background-color: #2c2346; }
    html.dark .bg-cloud\/95 { background-color: rgba(23, 18, 42, .95); }
    html.dark .bg-slate-50 { background-color: #2a2242; }
    html.dark .bg-sky-soft, html.dark .bg-langit-soft { background-color: #1c2f52; }
    html.dark .bg-sun-soft { background-color: #3b3016; }
    html.dark .bg-leaf-soft, html.dark .bg-daun-soft { background-color: #143528; }
    html.dark .bg-permen-soft { background-color: #45203a; }
    html.dark .bg-anggur-soft { background-color: #2f2658; }
    html.dark .bg-jeruk-soft { background-color: #45281a; }
    html.dark .bg-rose-50 { background-color: #3d1c2c; }
    html.dark .bg-sky-50 { background-color: #1a2a4d; }
    html.dark .bg-rose-100 { background-color: #4a2030; }
    html.dark .bg-amber-50 { background-color: #3b3016; }
    html.dark .bg-amber-100 { background-color: #4a3a14; }
    html.dark .bg-emerald-50 { background-color: #143528; }
    html.dark .bg-emerald-100 { background-color: #14473a; }
    html.dark .bg-violet-50 { background-color: #2f2658; }
    html.dark .bg-navy { background-color: #4a3d73; }
    html.dark .hover\:bg-cloud:hover { background-color: #2c2346; }
    html.dark .hover\:bg-sky-soft:hover { background-color: #243a63; }
    html.dark .hover\:bg-white:hover, html.dark .hover\:bg-white\/70:hover { background-color: rgba(255, 255, 255, .08); }

    /* Garis */
    html.dark .border-navy { border-color: #6b5c96; }
    html.dark .border-navy\/15 { border-color: rgba(107, 92, 150, .45); }
    html.dark .border-slate-100 { border-color: #3a3058; }
    html.dark .border-slate-200 { border-color: #463a68; }
    html.dark .border-rose-100, html.dark .border-rose-200 { border-color: #5a2a3a; }
    html.dark .border-amber-100 { border-color: #5a4a1c; }
    html.dark .divide-slate-100 > :not([hidden]) ~ :not([hidden]) { border-color: #3a3058; }

    /* Teks */
    html.dark .text-navy { color: #f3eefc; }
    html.dark .text-ink { color: #cfc6e6; }
    html.dark .text-slate-400 { color: #9c92ba; }
    html.dark .text-slate-500 { color: #b1a8cc; }
    html.dark .text-slate-600, html.dark .text-slate-700 { color: #c9c1e0; }
    html.dark .text-sky { color: #7cb6ff; }
    html.dark .text-leaf { color: #5ee08f; }
    html.dark .text-amber-600, html.dark .text-amber-700 { color: #fcd34d; }
    html.dark .text-rose-600, html.dark .text-rose-700 { color: #fda4af; }
    html.dark .text-emerald-700 { color: #6ee7b7; }
    html.dark .text-violet-700 { color: #c4b5fd; }

    /* Stiker berwarna cerah tetap memakai tulisan tinta gelap agar terbaca */
    html.dark :is(.bg-sun, .bg-daun, .bg-langit, .bg-jeruk, .bg-permen, .bg-anggur):is(.text-navy, .text-ink),
    html.dark :is(.bg-sun, .bg-daun, .bg-langit, .bg-jeruk, .bg-permen, .bg-anggur) :is(.text-navy, .text-ink) { color: #2b2140; }
    html.dark :is(.bg-sun, .bg-daun, .bg-langit, .bg-jeruk, .bg-permen, .bg-anggur) :is(.bg-white, .bg-cloud) :is(.text-navy, .text-ink),
    html.dark :is(.bg-sun, .bg-daun, .bg-langit, .bg-jeruk, .bg-permen, .bg-anggur) :is(.bg-white, .bg-cloud):is(.text-navy, .text-ink) { color: #f3eefc; }

    /* Kotak pilihan (sakelar, pilihan jenis) yang sedang aktif */
    html.dark label:has(:checked).has-\[\:checked\]\:bg-leaf-soft { background-color: #143528; }
    html.dark label:has(:checked).has-\[\:checked\]\:bg-sky-soft { background-color: #1c2f52; }
    html.dark .bg-slate-300 { background-color: #4a3f6b; }

    /* Pilihan status (Absen cepat) yang sedang dipilih */
    html.dark label:has(:checked).has-\[\:checked\]\:bg-daun { background-color: #3dbe72; color: #2b2140; }
    html.dark label:has(:checked).has-\[\:checked\]\:bg-langit { background-color: #6ec3ff; color: #2b2140; }
    html.dark label:has(:checked).has-\[\:checked\]\:bg-sun { background-color: #ffc53d; color: #2b2140; }
    html.dark label:has(:checked).has-\[\:checked\]\:bg-permen { background-color: #ff8fb1; color: #2b2140; }

    /* Formulir */
    html.dark input, html.dark select, html.dark textarea { background-color: #1d1631; color: #ece7f8; border-color: #6b5c96; }
    html.dark ::placeholder { color: #8f86ad; }
    html.dark table thead tr { background-color: #3b3016; }
    html.dark table thead th { color: #fcefc4; }
</style>
