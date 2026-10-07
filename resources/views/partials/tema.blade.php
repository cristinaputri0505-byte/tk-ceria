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

    window.tkSegarkanTeks = function () {
        var n = new Date(), w = waktu();
        document.querySelectorAll('[data-salam]').forEach(function (e) { e.textContent = w.salam; });
        document.querySelectorAll('[data-tgl-wib],[data-tgl]').forEach(function (e) { e.textContent = fTgl.format(n); });
        var j = fWaktu.format(n).replace(/\./g, ':') + ' WIB';
        document.querySelectorAll('[data-jam]').forEach(function (e) { e.textContent = j; });
    };

    try { localStorage.removeItem('tema'); } catch (e) {}   // kunci lama
    tkTerapkanTema();
    setInterval(function () { tkTerapkanTema(); tkSegarkanTeks(); }, 1000);
    window.addEventListener('storage', tkTerapkanTema);
})();
</script>
<script>
document.addEventListener('DOMContentLoaded', function () { tkSegarkanTeks(); });
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

    html.dark { color-scheme: dark; }
    html.dark body { background: #0b1220 !important; color: #e2e8f0; }

    /* Permukaan */
    html.dark .bg-white { background-color: #152238; }
    html.dark .bg-cloud { background-color: #1f2f4d; }
    html.dark .bg-slate-50 { background-color: #1b2a45; }
    html.dark .bg-sky-soft { background-color: #1b2f55; }
    html.dark .bg-sun-soft { background-color: #3b3114; }
    html.dark .bg-leaf-soft { background-color: #12382a; }
    html.dark .bg-rose-50 { background-color: #3a1a26; }
    html.dark .bg-rose-100 { background-color: #4a2030; }
    html.dark .bg-amber-50 { background-color: #3a2f12; }
    html.dark .bg-amber-100 { background-color: #4a3a14; }
    html.dark .bg-emerald-50 { background-color: #123226; }
    html.dark .bg-emerald-100 { background-color: #14473a; }
    html.dark .bg-violet-50 { background-color: #2a2250; }
    html.dark .hover\:bg-cloud:hover { background-color: #1f2f4d; }
    html.dark .hover\:bg-sky-soft:hover { background-color: #243a63; }

    /* Garis */
    html.dark .border-slate-100 { border-color: #24344f; }
    html.dark .border-slate-200 { border-color: #2c3d5c; }
    html.dark .border-rose-100, html.dark .border-rose-200 { border-color: #5a2a3a; }
    html.dark .border-amber-100 { border-color: #5a4a1c; }

    /* Teks */
    html.dark .text-navy { color: #e8eefc; }
    html.dark .text-ink { color: #cbd5e1; }
    html.dark .text-slate-400 { color: #8392aa; }
    html.dark .text-slate-500 { color: #9aa8bf; }
    html.dark .text-slate-600, html.dark .text-slate-700 { color: #b8c4d6; }
    html.dark .text-sky { color: #6aa8ff; }
    html.dark .text-leaf { color: #4ade80; }
    html.dark .text-amber-600, html.dark .text-amber-700 { color: #fcd34d; }
    html.dark .text-rose-600, html.dark .text-rose-700 { color: #fda4af; }
    html.dark .text-emerald-700 { color: #6ee7b7; }
    html.dark .text-violet-700 { color: #c4b5fd; }

    /* Pengecualian: teks navy di atas latar kuning/sidebar tetap gelap */
    html.dark .bg-sun.text-navy, html.dark .bg-sun .text-navy, html.dark aside .text-navy { color: #0f2650; }

    /* Formulir */
    html.dark input, html.dark select, html.dark textarea { background-color: #0f1a2e; color: #e2e8f0; border-color: #2c3d5c; }
    html.dark ::placeholder { color: #7a8aa3; }
</style>
