/*
 * Animasi tema "Taman Bermain" (tanpa library, ±3 KB).
 * - Kartu muncul satu per satu sambil memantul saat terlihat di layar.
 * - Angka di kartu ringkasan menghitung naik dari 0.
 * - Bar progres terisi perlahan.
 * - Matahari berputar pelan, hiasan melayang.
 * - Konfeti kecil saat ada pesan berhasil (mis. absensi tersimpan).
 * Semua dimatikan bila pengguna memilih "kurangi gerakan" di perangkatnya.
 */
(function () {
    'use strict';
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    var root = document.documentElement;
    root.classList.add('animasi');

    function siap(fn) { if (document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }

    siap(function () {
        var wadah = document.querySelector('.dash main, body.situs main') || document.body;

        /* ---------- 1. Kartu muncul satu per satu ---------- */
        var pilih = '.rounded-3xl, .rounded-\\[2rem\\], li > .rounded-2xl, li.rounded-2xl, figure, .miring-kiri, .miring-kanan';
        var kartu = Array.prototype.filter.call(wadah.querySelectorAll(pilih), function (el) {
            if (el.closest('[data-tanpa-animasi], table, form .rounded-2xl, #toast, [role="dialog"]')) return false;
            var induk = el.parentElement && el.parentElement.closest(pilih);
            return !(induk && wadah.contains(induk));       // hanya kartu terluar
        });
        var urutan = new Map();
        kartu.forEach(function (el) {
            var p = el.parentElement, n = urutan.get(p) || 0;
            urutan.set(p, n + 1);
            el.style.setProperty('--tunda', Math.min(n, 8) * 70 + 'ms');
            el.classList.add('muncul');
        });
        var tampilkan = function (el) { el.classList.add('tampil'); };
        if ('IntersectionObserver' in window) {
            var io = new IntersectionObserver(function (entri) {
                entri.forEach(function (e) { if (e.isIntersecting) { tampilkan(e.target); io.unobserve(e.target); } });
            }, { rootMargin: '0px 0px -40px 0px', threshold: 0.05 });
            kartu.forEach(function (el) { io.observe(el); });
        } else {
            kartu.forEach(tampilkan);
        }
        // Pengaman: apa pun yang terjadi, semua kartu tampil setelah 2,5 detik
        setTimeout(function () { kartu.forEach(tampilkan); }, 2500);

        /* ---------- 2. Angka menghitung naik ---------- */
        if (document.body.classList.contains('dash')) {
            var angka = wadah.querySelectorAll('.font-display.font-extrabold, .font-display.font-bold');
            Array.prototype.forEach.call(angka, function (el) {
                if (el.children.length || el.closest('table, [data-tanpa-animasi]')) return;
                var teks = el.textContent.trim(), m = teks.match(/^(\d{1,6})(%?)$/);
                if (!m) return;
                var akhir = parseInt(m[1], 10), akhiran = m[2];
                if (akhir < 2) return;
                var mulai = null, lama = Math.min(1200, 500 + akhir * 25);
                el.textContent = '0' + akhiran;
                var langkah = function (t) {
                    if (mulai === null) mulai = t;
                    var k = Math.min(1, (t - mulai) / lama), mudah = 1 - Math.pow(1 - k, 3);
                    el.textContent = Math.round(akhir * mudah) + akhiran;
                    if (k < 1) requestAnimationFrame(langkah);
                };
                setTimeout(function () { requestAnimationFrame(langkah); }, 250);
            });
        }

        /* ---------- 3. Bar progres terisi perlahan ---------- */
        var bar = wadah.querySelectorAll('.rounded-full.overflow-hidden > [style*="width"]');
        Array.prototype.forEach.call(bar, function (el) {
            var lebar = el.style.width;
            el.style.width = '0%';
            el.classList.add('bar-isi');
            setTimeout(function () { el.style.width = lebar; }, 350);
        });

        /* ---------- 4. Matahari berputar, hiasan melayang ---------- */
        Array.prototype.forEach.call(document.querySelectorAll('svg[aria-hidden="true"]'), function (svg) {
            var bulat = svg.querySelector('circle[fill="#ffc53d"], circle[fill="#fbbf24"]');
            var sinar = svg.querySelector('g[stroke-linecap="round"]');
            if (bulat && sinar) { sinar.classList.add('anim-putar'); svg.classList.add('anim-melayang'); }
            else if (svg.closest('section') && svg.classList.contains('absolute')) svg.classList.add('anim-melayang');
        });

        /* ---------- 5. Konfeti saat berhasil ---------- */
        var berhasil = document.querySelector('main [role="status"].bg-leaf-soft, [data-konfeti]');
        if (berhasil) konfeti();
    });

    function konfeti() {
        var c = document.createElement('canvas');
        c.setAttribute('aria-hidden', 'true');
        c.style.cssText = 'position:fixed;inset:0;width:100%;height:100%;pointer-events:none;z-index:70';
        document.body.appendChild(c);
        var x = c.getContext('2d'), w = c.width = innerWidth, h = c.height = innerHeight;
        var warna = ['#ffc53d', '#3dbe72', '#ff8fb1', '#6ec3ff', '#b9a4ff', '#ff9a5c'];
        var p = [];
        for (var i = 0; i < 90; i++) {
            p.push({ x: w / 2 + (Math.random() - .5) * w * .4, y: h * .35, vx: (Math.random() - .5) * 9, vy: -Math.random() * 9 - 4,
                s: 5 + Math.random() * 6, r: Math.random() * 6, vr: (Math.random() - .5) * .3, c: warna[i % warna.length] });
        }
        var mulai = performance.now();
        (function gambar(t) {
            x.clearRect(0, 0, w, h);
            p.forEach(function (q) {
                q.vy += .25; q.x += q.vx; q.y += q.vy; q.r += q.vr;
                x.save(); x.translate(q.x, q.y); x.rotate(q.r); x.fillStyle = q.c;
                x.fillRect(-q.s / 2, -q.s / 3, q.s, q.s * .66); x.restore();
            });
            if (t - mulai < 2200) requestAnimationFrame(gambar); else c.remove();
        })(mulai);
    }
})();
