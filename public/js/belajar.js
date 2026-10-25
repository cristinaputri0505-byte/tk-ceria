/*!
 * Ruang Belajar anak TK Ceria.
 * Diadaptasi dari prototipe "E-Learning Anak TK Interaktif": tugas kini dari server (dibuat guru/admin),
 * dan bintang serta karya gambar tersimpan per anak. Semua teks dari guru ditampilkan sebagai teks biasa
 * (textContent), tidak pernah sebagai HTML.
 */
(function () {
    'use strict';

    var D = window.BELAJAR || { tugas: [], hasil: {}, anak: {} };
    var hasil = D.hasil || {};          // { tugasId: {selesai, bintang, gambar} }
    var filterAktif = 'semua';
    var tugasAktif = null;

    var TIPE = { kuis: '❓ Tebak Gambar', hitung: '🔢 Berhitung', cocok: '🧩 Mencocokkan', gambar: '🎨 Menggambar' };
    var TEMA = {
        pink:   { kartu: 'bg-pink-50 border-pink-200',       label: 'bg-pink-500 text-white',    tombol: 'btn-pink' },
        biru:   { kartu: 'bg-sky-50 border-sky-200',         label: 'bg-sky-500 text-white',     tombol: 'btn-blue' },
        hijau:  { kartu: 'bg-emerald-50 border-emerald-200', label: 'bg-emerald-500 text-white', tombol: 'btn-green' },
        kuning: { kartu: 'bg-amber-50 border-amber-200',     label: 'bg-amber-500 text-white',   tombol: 'btn-yellow' },
        ungu:   { kartu: 'bg-purple-50 border-purple-200',   label: 'bg-purple-500 text-white',  tombol: 'btn-purple' },
        oranye: { kartu: 'bg-orange-50 border-orange-200',   label: 'bg-orange-500 text-white',  tombol: 'btn-orange' }
    };
    var $ = function (id) { return document.getElementById(id); };

    /* Kalimat ceria untuk anak (dibacakan dengan suara) */
    var ANGKA = ['', 'Satu!', 'Dua!', 'Tiga!', 'Empat!', 'Lima!', 'Enam!', 'Tujuh!', 'Delapan!', 'Sembilan!', 'Sepuluh!',
                 'Sebelas!', 'Dua belas!', 'Tiga belas!', 'Empat belas!', 'Lima belas!'];
    var SALAH = ['Hmm, belum tepat nih. Ayo coba lagi, kamu pasti bisa!', 'Ups! Hampir benar. Coba sekali lagi, yuk!', 'Wah, belum pas. Ayo pilih yang lain ya!'];
    var COCOK = ['Cocok!', 'Pintar!', 'Yeay, benar!', 'Hebat!'];
    var acak = function (daftar) { return daftar[Math.floor(Math.random() * daftar.length)]; };

    /* ---------- Pembuat elemen yang aman (tanpa innerHTML untuk teks guru) ---------- */
    function h(tag, kelas, isi, attr) {
        var el = document.createElement(tag);
        if (kelas) el.className = kelas;
        if (attr) for (var k in attr) el.setAttribute(k, attr[k]);
        [].concat(isi === undefined || isi === null ? [] : isi).forEach(function (c) {
            el.appendChild(typeof c === 'string' || typeof c === 'number' ? document.createTextNode(String(c)) : c);
        });
        return el;
    }
    function ikon(nama, kelas) { return h('i', kelas || 'w-5 h-5', null, { 'data-lucide': nama }); }
    function segarkanIkon() { if (window.lucide) window.lucide.createIcons(); }

    /* ---------- Suara: efek (Web Audio) & pembaca soal (Bahasa Indonesia) ---------- */
    var audio = null;
    function bunyi(jenis) {
        try {
            var AC = window.AudioContext || window.webkitAudioContext;
            if (!AC) return;
            audio = audio || new AC();
            if (audio.state === 'suspended') audio.resume();
            var t = audio.currentTime;
            var nada = function (f, mulai, lama, tipe, vol) {
                var o = audio.createOscillator(), g = audio.createGain();
                o.type = tipe; o.frequency.setValueAtTime(f, t + mulai);
                g.gain.setValueAtTime(vol, t + mulai);
                g.gain.exponentialRampToValueAtTime(0.001, t + mulai + lama);
                o.connect(g); g.connect(audio.destination);
                o.start(t + mulai); o.stop(t + mulai + lama);
            };
            if (jenis === 'klik') nada(600, 0, 0.08, 'sine', 0.12);
            else if (jenis === 'benar') [523.25, 659.25, 783.99, 1046.5].forEach(function (f, i) { nada(f, i * 0.09, 0.25, 'triangle', 0.2); });
            else if (jenis === 'salah') { nada(220, 0, 0.25, 'sawtooth', 0.12); }
            else if (jenis === 'hore') [523.25, 659.25, 783.99, 1046.5, 1318.5].forEach(function (f, i) { nada(f, i * 0.07, 0.45, 'triangle', 0.2); });
        } catch (e) { /* browser memblokir audio sebelum ada sentuhan: abaikan */ }
    }

    /* Suara Bahasa Indonesia:
       1. Dari SERVER website (Piper / espeak-ng): pasti berbahasa Indonesia, tanpa pengaturan di HP.
       2. Cadangan bila server tidak bisa: suara browser, dengan memilih suara Indonesia bila ada. */
    var suaraID = null;
    function muatSuara() {
        if (!('speechSynthesis' in window)) return;
        var semua = window.speechSynthesis.getVoices();
        var cari = function (uji) { for (var i = 0; i < semua.length; i++) if (uji(semua[i])) return semua[i]; return null; };
        suaraID = cari(function (v) { return /^id([-_]|$)/i.test(v.lang) && /google/i.test(v.name); })
            || cari(function (v) { return /^id([-_]|$)/i.test(v.lang); })
            || cari(function (v) { return /indonesia/i.test(v.name); })
            || cari(function (v) { return /^ms([-_]|$)/i.test(v.lang); });
    }
    if ('speechSynthesis' in window) { muatSuara(); window.speechSynthesis.onvoiceschanged = muatSuara; }

    function bersihkanTeks(teks) {
        // Emoji tidak dibacakan agar suara tidak menyebut nama simbol
        return String(teks || '').replace(/[\u{1F000}-\u{1FAFF}\u{2600}-\u{27BF}\u{FE0F}\u{200D}\u{20E3}]/gu, ' ').replace(/\s+/g, ' ').trim();
    }

    function suaraBrowser(teks) {
        if (!('speechSynthesis' in window)) return;
        if (!suaraID) muatSuara();
        var u = new SpeechSynthesisUtterance(teks);
        if (suaraID) u.voice = suaraID;
        u.lang = suaraID ? suaraID.lang : 'id-ID';
        u.rate = 0.92; u.pitch = 1.15;
        window.speechSynthesis.speak(u);
    }

    var pemutar = new Audio();
    pemutar.preload = 'auto';
    var serverBisa = !!D.suara;   // menjadi false bila server gagal, agar tidak mencoba terus
    var giliran = 0;

    function diam() {
        giliran++;
        try { pemutar.pause(); } catch (e) {}
        if ('speechSynthesis' in window) window.speechSynthesis.cancel();
    }

    function bicara(teks) {
        var bersih = bersihkanTeks(teks);
        diam();
        if (!bersih) return;
        if (!serverBisa) return suaraBrowser(bersih);

        var ini = giliran;
        var cadangan = function () {
            if (ini !== giliran) return;   // sudah diganti ucapan lain
            serverBisa = false;
            suaraBrowser(bersih);
        };
        pemutar.onerror = cadangan;
        pemutar.src = D.suara + '?teks=' + encodeURIComponent(bersih.slice(0, 300));
        // play() dipanggil langsung saat disentuh agar diizinkan browser HP (termasuk iPhone)
        var janji = pemutar.play();
        if (janji && janji.catch) janji.catch(function (e) { if (e && e.name !== 'AbortError') cadangan(); });
    }

    /* ---------- Konfeti sederhana (tanpa pustaka luar) ---------- */
    function konfeti() {
        var c = $('konfeti'), ctx = c.getContext('2d');
        if (matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        c.width = innerWidth; c.height = innerHeight;
        var warna = ['#FF85A1', '#4CC9F0', '#06D6A0', '#FFD166', '#9B5DE5', '#FF9F1C'];
        var p = [];
        for (var i = 0; i < 140; i++) p.push({
            x: c.width / 2, y: c.height * 0.6, vx: (Math.random() - 0.5) * 16, vy: -Math.random() * 16 - 6,
            r: Math.random() * 6 + 4, w: warna[i % warna.length], a: Math.random() * Math.PI
        });
        var mulai = performance.now();
        (function gerak(now) {
            ctx.clearRect(0, 0, c.width, c.height);
            p.forEach(function (q) {
                q.vy += 0.45; q.x += q.vx; q.y += q.vy; q.a += 0.2;
                ctx.save(); ctx.translate(q.x, q.y); ctx.rotate(q.a); ctx.fillStyle = q.w;
                ctx.fillRect(-q.r / 2, -q.r / 2, q.r, q.r * 0.6); ctx.restore();
            });
            if (now - mulai < 2200) requestAnimationFrame(gerak); else ctx.clearRect(0, 0, c.width, c.height);
        })(mulai);
    }

    function toast(teks) {
        var t = $('toast');
        t.textContent = teks;
        t.classList.remove('hidden');
        clearTimeout(toast.w);
        toast.w = setTimeout(function () { t.classList.add('hidden'); }, 3200);
    }

    /* ---------- Tampilan utama ---------- */
    function totalBintang() {
        var n = 0;
        for (var k in hasil) n += Number(hasil[k].bintang) || 0;
        return n;
    }

    function render() {
        $('pesan-guru').textContent = D.sambutan || '';
        $('jumlah-bintang').textContent = totalBintang();

        var total = D.tugas.length;
        var selesai = D.tugas.filter(function (t) { return hasil[t.id] && hasil[t.id].selesai; }).length;
        $('progres-angka').textContent = selesai + ' / ' + total + ' Selesai';
        $('progres-bar').style.width = (total ? Math.round(selesai / total * 100) : 0) + '%';
        $('progres-teks').textContent = total && selesai === total
            ? '🎉 Wah hebat! Semua tugas sudah selesai!'
            : 'Ayo selesaikan semua tugas untuk mendapat bintang!';

        renderKartu();
    }

    function renderKartu() {
        var wadah = $('daftar-tugas');
        wadah.textContent = '';
        var daftar = D.tugas.filter(function (t) { return filterAktif === 'semua' || t.tipe === filterAktif; });

        if (!daftar.length) {
            wadah.appendChild(h('div', 'col-span-full bg-white rounded-3xl p-10 text-center border-4 border-dashed border-slate-200', [
                h('div', 'text-6xl mb-3', '📭'),
                h('h3', 'text-xl font-bold text-slate-700', D.tugas.length ? 'Belum ada tugas jenis ini' : 'Belum ada tugas'),
                h('p', 'text-slate-500 mt-1', 'Ibu/Bapak Guru akan segera menambahkan tugas seru.')
            ]));
            return;
        }

        daftar.forEach(function (t) {
            var r = hasil[t.id] || {}, tema = TEMA[t.warna] || TEMA.pink;
            var aksi = h('div', 'flex items-center gap-2');
            if (r.gambar) {
                var lihat = h('button', 'btn-3d btn-white px-3 py-2.5 rounded-2xl text-slate-700 font-bold text-sm', '🖼️ Karyaku', { type: 'button' });
                lihat.onclick = function () { lihatKarya(t, r.gambar); };
                aksi.appendChild(lihat);
            }
            var main = h('button', 'btn-3d ' + (r.selesai ? 'btn-green text-white' : tema.tombol + (t.warna === 'kuning' ? ' text-slate-900' : ' text-white')) + ' px-5 py-2.5 rounded-2xl font-bold text-base md:text-lg flex items-center gap-2',
                [r.selesai ? 'Main Lagi' : 'Mulai Main', ikon('play', 'w-4 h-4')], { type: 'button' });
            main.onclick = function () { mulai(t); };
            aksi.appendChild(main);

            wadah.appendChild(h('article', tema.kartu + ' border-4 rounded-3xl p-5 md:p-6 shadow-md hover:shadow-xl transition-all flex flex-col justify-between', [
                h('div', null, [
                    h('div', 'flex items-center justify-between gap-2 mb-4', [
                        h('span', tema.label + ' text-xs md:text-sm font-bold px-3 py-1 rounded-full', TIPE[t.tipe] || 'Tugas'),
                        r.selesai
                            ? h('span', 'bg-emerald-100 border-2 border-emerald-400 text-emerald-700 text-xs md:text-sm font-bold px-3 py-1 rounded-full', 'Selesai ✅')
                            : h('span', 'bg-white border-2 border-slate-200 text-slate-600 text-xs md:text-sm font-bold px-3 py-1 rounded-full', 'Belum dikerjakan')
                    ]),
                    h('div', 'flex items-start gap-4 mb-5', [
                        h('div', 'w-20 h-20 rounded-3xl bg-white shadow-inner border-2 border-white flex items-center justify-center text-5xl shrink-0', t.ikon || '🌟'),
                        h('div', null, [
                            h('h3', 'text-xl md:text-2xl font-bold text-slate-800 leading-snug mb-1', t.judul),
                            h('p', 'text-sm md:text-base text-slate-600 line-clamp-2', t.instruksi)
                        ])
                    ])
                ]),
                h('div', 'flex items-center justify-between gap-3 pt-3 border-t-2 border-white', [
                    h('span', 'flex items-center gap-1.5 bg-white px-3 py-1.5 rounded-2xl border border-yellow-300 font-bold text-yellow-700 text-sm md:text-base', '⭐ +' + t.bintang + ' Bintang'),
                    aksi
                ])
            ]));
        });
        segarkanIkon();
    }

    function pilihFilter(nama) {
        bunyi('klik');
        filterAktif = nama;
        document.querySelectorAll('#filter [data-filter]').forEach(function (b) {
            var aktif = b.dataset.filter === nama;
            b.classList.toggle('btn-pink', aktif); b.classList.toggle('text-white', aktif);
            b.classList.toggle('btn-white', !aktif); b.classList.toggle('text-slate-700', !aktif);
        });
        tutup(true);
    }

    /* ---------- Ruang bermain ---------- */
    function mulai(t) {
        bunyi('klik');
        tugasAktif = t;
        t._terkirim = false;
        $('daftar-tugas').classList.add('hidden');
        $('filter').classList.add('hidden');
        $('pemain').classList.remove('hidden');
        $('pemain-jenis').textContent = TIPE[t.tipe] || 'Tugas';
        $('pemain-bintang').textContent = '+' + t.bintang + ' ⭐';
        $('pemain-ikon').textContent = t.ikon || '🌟';
        $('pemain-judul').textContent = t.judul;
        $('pemain-instruksi').textContent = t.instruksi;
        var area = $('area-main');
        area.textContent = '';
        ({ kuis: mainKuis, hitung: mainHitung, cocok: mainCocok, gambar: mainGambar }[t.tipe] || function () {})(t, area);
        segarkanIkon();
        window.scrollTo({ top: 0, behavior: 'smooth' });
        bicara(t.judul + '. ' + t.instruksi);
    }

    function tutup(senyap) {
        if (!senyap) bunyi('klik');
        diam();
        tugasAktif = null;
        $('pemain').classList.add('hidden');
        $('daftar-tugas').classList.remove('hidden');
        $('filter').classList.remove('hidden');
        render();
    }

    function umpan(el, teks, benar) {
        el.textContent = teks;
        el.className = 'min-h-8 text-lg font-bold ' + (benar ? 'text-emerald-600' : 'text-rose-500');
    }
    function goyang(el) {
        el.classList.add('belajar-shake');
        setTimeout(function () { el.classList.remove('belajar-shake'); }, 450);
    }

    // 1. Tebak gambar
    function mainKuis(t, area) {
        var d = t.data || {}, warna = ['btn-pink', 'btn-blue', 'btn-green', 'btn-purple'];
        var fb = h('div', 'min-h-8 text-lg font-bold');
        var grid = h('div', 'grid grid-cols-1 sm:grid-cols-2 gap-4');
        (d.opsi || []).forEach(function (opsi, i) {
            var b = h('button', 'btn-3d ' + warna[i % 4] + ' text-white p-5 rounded-3xl font-bold text-xl md:text-2xl flex items-center justify-center min-h-[90px]', opsi, { type: 'button' });
            b.onclick = function () {
                if (i === Number(d.benar)) { bunyi('benar'); umpan(fb, '🎉 Yeay! Jawabanmu benar!', true); selesai(); }
                else { bunyi('salah'); goyang(b); umpan(fb, '🙈 Hmm, belum tepat nih. Coba lagi, kamu pasti bisa!', false); bicara(acak(SALAH)); }
            };
            grid.appendChild(b);
        });
        area.appendChild(h('div', 'text-center space-y-6', [
            h('div', 'bg-sky-50 border-4 border-sky-100 rounded-3xl py-8 px-4 inline-block min-w-[240px] shadow-inner', h('div', 'text-6xl md:text-8xl tracking-widest belajar-float', d.gambar || t.ikon)),
            fb, grid
        ]));
    }

    // 2. Berhitung
    function mainHitung(t, area) {
        var d = t.data || {}, n = Math.max(1, Math.min(15, Number(d.jumlah) || 5));
        var benda = h('div', 'bg-amber-50 border-4 border-amber-200 rounded-3xl p-6 flex flex-wrap items-center justify-center gap-4');
        var urut = 0;
        for (var i = 0; i < n; i++) (function () {
            var lencana = h('span', 'hidden mt-1 text-xs font-bold bg-pink-500 text-white px-2 py-0.5 rounded-full');
            var b = h('button', 'w-20 h-20 md:w-24 md:h-24 rounded-3xl bg-white border-4 border-sky-200 shadow-sm flex flex-col items-center justify-center transition hover:scale-110',
                [h('span', 'text-4xl md:text-5xl', d.emoji || '🍎'), lencana], { type: 'button', 'aria-label': 'Hitung' });
            b.onclick = function () {
                if (!lencana.classList.contains('hidden')) return;
                urut++;
                lencana.textContent = urut;
                lencana.classList.remove('hidden');
                b.classList.add('border-pink-400', 'bg-pink-50');
                bicara(ANGKA[urut] || String(urut));
            };
            benda.appendChild(b);
        })();

        var pilihan = [n];
        while (pilihan.length < 4) {
            var x = n + Math.floor(Math.random() * 5) - 2;
            if (x < 1) x = Math.floor(Math.random() * 6) + 1;
            if (pilihan.indexOf(x) < 0) pilihan.push(x);
        }
        pilihan.sort(function (a, b) { return a - b; });
        var fb = h('div', 'min-h-8 text-lg font-bold');
        var warna = ['btn-pink', 'btn-blue', 'btn-green', 'btn-orange'];
        var tombol = h('div', 'grid grid-cols-2 sm:grid-cols-4 gap-4');
        pilihan.forEach(function (angka, i) {
            var b = h('button', 'btn-3d ' + warna[i] + ' text-white py-5 rounded-3xl font-bold text-3xl md:text-4xl', angka, { type: 'button' });
            b.onclick = function () {
                if (angka === n) { bunyi('benar'); umpan(fb, '🎉 Pintar sekali! Jumlahnya ada ' + n + '!', true); selesai(); }
                else { bunyi('salah'); goyang(b); umpan(fb, '🙈 Ups! Yuk, hitung pelan-pelan sekali lagi!', false); bicara('Ups! Yuk, kita hitung pelan-pelan sekali lagi!'); }
            };
            tombol.appendChild(b);
        });
        area.appendChild(h('div', 'text-center space-y-6', [
            h('p', 'text-sm font-bold text-sky-600 bg-sky-50 inline-block px-4 py-1.5 rounded-full', '👆 Sentuh ' + (d.nama || 'benda') + ' satu per satu untuk ikut menghitung!'),
            benda, fb,
            h('div', null, [h('p', 'text-base font-bold text-slate-600 mb-3', 'Pilih angka yang tepat:'), tombol])
        ]));
    }

    // 3. Mencocokkan
    function mainCocok(t, area) {
        var pasangan = (t.data && t.data.pasangan) || [];
        var kanan = pasangan.map(function (p, i) { return { teks: p.kanan, i: i }; });
        for (var i = kanan.length - 1; i > 0; i--) { var j = Math.floor(Math.random() * (i + 1)); var s = kanan[i]; kanan[i] = kanan[j]; kanan[j] = s; }
        var pilihKiri = null, cocok = [];
        var fb = h('div', 'text-center min-h-7 font-bold text-base text-slate-600');
        var papan = h('div', 'grid grid-cols-2 gap-4 md:gap-8');

        function gambarPapan() {
            papan.textContent = '';
            var kolKiri = h('div', 'space-y-3'), kolKanan = h('div', 'space-y-3');
            pasangan.forEach(function (p, i) {
                var sudah = cocok.indexOf(i) >= 0, dipilih = pilihKiri === i;
                var b = h('button', 'btn-3d w-full p-4 rounded-2xl font-bold text-lg md:text-xl flex items-center justify-between gap-2 ' +
                    (sudah ? 'bg-emerald-100 border-4 border-emerald-400 text-emerald-700 pointer-events-none' : dipilih ? 'btn-pink text-white ring-4 ring-pink-300' : 'btn-white text-slate-800'),
                    [h('span', null, p.kiri), h('span', null, sudah ? '✅' : '👉')], { type: 'button' });
                b.onclick = function () { if (sudah) return; bunyi('klik'); pilihKiri = i; gambarPapan(); };
                kolKiri.appendChild(b);
            });
            kanan.forEach(function (item) {
                var sudah = cocok.indexOf(item.i) >= 0;
                var b = h('button', 'btn-3d w-full p-4 rounded-2xl font-bold text-lg md:text-xl flex items-center justify-between gap-2 ' +
                    (sudah ? 'bg-emerald-100 border-4 border-emerald-400 text-emerald-700 pointer-events-none' : 'btn-white text-slate-800'),
                    [h('span', null, '👈'), h('span', null, item.teks)], { type: 'button' });
                b.onclick = function () {
                    if (sudah) return;
                    if (pilihKiri === null) { bunyi('salah'); umpan(fb, '👆 Pilih dulu kotak di sebelah KIRI ya!', false); return; }
                    if (pilihKiri === item.i) {
                        bunyi('benar'); cocok.push(item.i); pilihKiri = null; umpan(fb, '👍 Cocok! Pintar!', true); bicara(acak(COCOK)); gambarPapan();
                        if (cocok.length >= pasangan.length) setTimeout(selesai, 350);
                    } else { bunyi('salah'); goyang(b); umpan(fb, '🙈 Ups, bukan pasangannya. Coba lagi yuk!', false); bicara(acak(SALAH)); }
                };
                kolKanan.appendChild(b);
            });
            papan.appendChild(kolKiri); papan.appendChild(kolKanan);
        }
        gambarPapan();
        area.appendChild(h('div', 'space-y-4', [
            h('p', 'text-center text-sm md:text-base font-bold text-purple-600 bg-purple-50 py-2 px-4 rounded-full', '1️⃣ Sentuh kotak KIRI, lalu 2️⃣ sentuh pasangannya di KANAN!'),
            fb, papan
        ]));
    }

    // 4. Menggambar
    var kanvas = { el: null, ctx: null, gambar: false, warna: '#FF595E', kuas: 10, stiker: null };
    function mainGambar(t, area) {
        var warna = [['#FF595E', 'Merah'], ['#FF924C', 'Oranye'], ['#FFCA3A', 'Kuning'], ['#8AC926', 'Hijau'], ['#1982C4', 'Biru'], ['#6A4C93', 'Ungu'], ['#F15BB5', 'Merah muda'], ['#334155', 'Hitam']];
        var barisWarna = h('div', 'flex items-center gap-2 flex-wrap', h('span', 'text-xs font-bold text-slate-500 uppercase mr-1', '🖍️ Warna:'));
        warna.forEach(function (w) {
            var b = h('button', 'pilih-warna w-10 h-10 rounded-full border-4 border-white shadow-md transition hover:scale-110', null, { type: 'button', title: w[1], 'aria-label': w[1], style: 'background-color:' + w[0] });
            b.onclick = function () {
                bunyi('klik'); kanvas.warna = w[0]; kanvas.stiker = null;
                area.querySelectorAll('.pilih-warna, .pilih-stiker').forEach(function (x) { x.classList.remove('ring-4', 'ring-slate-400', 'ring-amber-400'); });
                b.classList.add('ring-4', 'ring-slate-400');
            };
            barisWarna.appendChild(b);
        });
        var kuas = h('div', 'flex items-center gap-2 flex-wrap');
        [['Kecil', 6], ['Sedang', 14], ['Besar', 26]].forEach(function (k) {
            var b = h('button', 'btn-3d btn-white px-3 py-1.5 rounded-xl text-xs font-bold', k[0], { type: 'button' });
            b.onclick = function () { bunyi('klik'); kanvas.kuas = k[1]; kanvas.stiker = null; };
            kuas.appendChild(b);
        });
        var hapus = h('button', 'btn-3d bg-rose-100 text-rose-700 px-3 py-1.5 rounded-xl text-xs font-bold', '🗑️ Bersihkan', { type: 'button' });
        hapus.onclick = function () { bunyi('klik'); latarPutih(); };
        kuas.appendChild(hapus);

        var barisStiker = h('div', 'bg-amber-50 p-3 rounded-2xl border-2 border-amber-200 flex items-center gap-2 flex-wrap',
            h('span', 'text-xs font-bold text-amber-800 uppercase mr-1', '✨ Sentuh stiker lalu sentuh kanvas:'));
        ((t.data && t.data.stiker) || []).forEach(function (s) {
            var b = h('button', 'pilih-stiker btn-3d btn-white px-3 py-1.5 rounded-xl text-2xl', s, { type: 'button' });
            b.onclick = function () {
                bunyi('klik'); kanvas.stiker = s;
                area.querySelectorAll('.pilih-stiker').forEach(function (x) { x.classList.remove('ring-4', 'ring-amber-400'); });
                b.classList.add('ring-4', 'ring-amber-400');
            };
            barisStiker.appendChild(b);
        });

        var c = h('canvas', 'w-full h-[340px] md:h-[400px] block bg-white touch-none cursor-crosshair', null, { 'aria-label': 'Kanvas menggambar' });
        var simpan = h('button', 'btn-3d btn-green text-white px-8 py-4 rounded-2xl font-bold text-xl inline-flex items-center gap-3', [ikon('circle-check', 'w-6 h-6'), 'Selesai & Simpan Karyaku!'], { type: 'button' });
        simpan.onclick = function () { simpan.disabled = true; selesai(kanvas.el.toDataURL('image/png')).then(function () { simpan.disabled = false; }); };

        area.appendChild(h('div', 'space-y-4', [
            h('div', 'bg-slate-50 p-4 rounded-2xl border-2 border-slate-200 flex flex-wrap items-center justify-between gap-4', [barisWarna, kuas]),
            barisStiker,
            h('div', 'bg-white border-4 border-pink-300 rounded-3xl overflow-hidden shadow-inner', c),
            h('div', 'text-center pt-2', simpan)
        ]));
        siapkanKanvas(c, (hasil[t.id] || {}).gambar);
    }

    function latarPutih() {
        kanvas.ctx.fillStyle = '#FFFFFF';
        kanvas.ctx.fillRect(0, 0, kanvas.el.width, kanvas.el.height);
    }

    function siapkanKanvas(c, gambarLama) {
        var r = c.getBoundingClientRect();
        // Resolusi tetap agar ukuran file wajar di semua layar
        c.width = Math.min(1200, Math.round(r.width));
        c.height = Math.round(c.width * r.height / r.width);
        kanvas.el = c; kanvas.ctx = c.getContext('2d'); kanvas.gambar = false; kanvas.stiker = null; kanvas.warna = '#FF595E'; kanvas.kuas = 10;
        latarPutih();
        if (gambarLama) {
            var img = new Image();
            img.onload = function () { kanvas.ctx.drawImage(img, 0, 0, c.width, c.height); };
            img.src = gambarLama;
        }
        var skala = function () { return c.width / c.getBoundingClientRect().width; };
        var posisi = function (e) {
            var b = c.getBoundingClientRect(), p = e.touches ? e.touches[0] : e;
            return { x: (p.clientX - b.left) * skala(), y: (p.clientY - b.top) * skala() };
        };
        var mulaiGores = function (e) {
            e.preventDefault();
            var p = posisi(e), ctx = kanvas.ctx;
            if (kanvas.stiker) {
                bunyi('klik');
                ctx.font = Math.round(48 * skala()) + 'px serif';
                ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
                ctx.fillText(kanvas.stiker, p.x, p.y);
                return;
            }
            kanvas.gambar = true;
            ctx.beginPath(); ctx.moveTo(p.x, p.y);
        };
        var gores = function (e) {
            if (!kanvas.gambar) return;
            e.preventDefault();
            var p = posisi(e), ctx = kanvas.ctx;
            ctx.lineTo(p.x, p.y);
            ctx.strokeStyle = kanvas.warna; ctx.lineWidth = kanvas.kuas * skala();
            ctx.lineCap = 'round'; ctx.lineJoin = 'round';
            ctx.stroke();
        };
        var berhenti = function () { kanvas.gambar = false; };
        c.addEventListener('mousedown', mulaiGores);
        c.addEventListener('mousemove', gores);
        window.addEventListener('mouseup', berhenti);
        c.addEventListener('touchstart', mulaiGores, { passive: false });
        c.addEventListener('touchmove', gores, { passive: false });
        c.addEventListener('touchend', berhenti);
    }

    /* ---------- Selesai: simpan ke server, lalu rayakan ---------- */
    function selesai(gambar) {
        var t = tugasAktif;
        if (!t || t._terkirim) return Promise.resolve();
        t._terkirim = true; // cegah kiriman ganda bila tombol benar disentuh berkali-kali
        var body = gambar ? JSON.stringify({ gambar: gambar }) : '{}';

        return fetch(D.kirim.replace('__ID__', t.id), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': D.csrf },
            body: body
        }).then(function (r) {
            return r.json().catch(function () { return {}; }).then(function (j) {
                if (!r.ok || !j.ok) throw new Error(j.pesan || 'Gagal menyimpan');
                return j;
            });
        }).then(function (j) {
            hasil[t.id] = { selesai: true, bintang: j.bintang, gambar: j.gambar || (hasil[t.id] || {}).gambar };
            $('jumlah-bintang').textContent = j.total;
            rayakan(t, j.baru);
        }).catch(function (e) {
            // Tetap rayakan agar anak tidak kecewa; orang tua diberi tahu bahwa hasil belum tersimpan.
            toast('⚠️ ' + (e.message || 'Hasil belum tersimpan') + '. Periksa internet lalu main lagi.');
            rayakan(t, false);
        });
    }

    function rayakan(t, bintangBaru) {
        bunyi('hore');
        konfeti();
        $('hore-nama').textContent = 'Hebat sekali, ' + (D.anak.nama || 'kamu') + '!';
        $('hore-bintang').textContent = bintangBaru ? '+' + t.bintang + ' Bintang!' : 'Bintangmu sudah tersimpan ⭐';
        var lanjut = tugasBerikutnya(t);
        $('hore-berikut').classList.toggle('hidden', !lanjut);
        $('hore-berikut-teks').textContent = lanjut ? 'Berikutnya: ' + (lanjut.ikon || '🌟') + ' ' + lanjut.judul : '';
        $('hore-kembali').textContent = lanjut ? '🏠 Kembali ke daftar soal' : '🏠 Semua soal selesai! Kembali';
        $('jendela-hore').classList.remove('hidden');
        var nama = D.anak.nama || '';
        var jumlah = (ANGKA[t.bintang] || String(t.bintang)).replace('!', '').toLowerCase();
        bicara(bintangBaru
            ? 'Yeay! Hebat sekali, ' + nama + '! Kamu dapat ' + jumlah + ' bintang! Tepuk tangan dulu, yuk!'
            : acak(['Yeay! Kamu hebat, ' + nama + '!', 'Hore! Pintar sekali, ' + nama + '!', 'Keren! Kamu berhasil lagi, ' + nama + '!']));
    }

    /** Soal berikutnya: yang belum dikerjakan setelah soal ini (urut sesuai daftar), lalu dari awal.
     *  Bila semua sudah selesai, soal sesudahnya di daftar (untuk main lagi). null bila ini soal terakhir. */
    function tugasBerikutnya(t) {
        var daftar = D.tugas.filter(function (x) { return filterAktif === 'semua' || x.tipe === filterAktif; });
        var i = daftar.indexOf(t);
        var urut = daftar.slice(i + 1).concat(daftar.slice(0, Math.max(i, 0)));
        for (var k = 0; k < urut.length; k++) if (!(hasil[urut[k].id] || {}).selesai) return urut[k];
        return daftar[i + 1] || null;
    }

    function lihatKarya(t, url) {
        bunyi('klik');
        $('karya-judul').textContent = 'Karyaku: ' + t.judul;
        $('karya-img').src = url + (url.indexOf('?') < 0 ? '?' : '&') + 't=' + Date.now();
        $('jendela-karya').classList.remove('hidden');
    }

    /* ---------- Tombol-tombol ---------- */
    document.addEventListener('click', function (e) {
        var f = e.target.closest('[data-filter]');
        if (f) return pilihFilter(f.dataset.filter);
        var a = e.target.closest('[data-aksi]');
        if (!a) return;
        switch (a.dataset.aksi) {
            case 'dengar-guru': bunyi('klik'); bicara(D.sambutan); break;
            case 'bacakan': if (tugasAktif) { bunyi('klik'); bicara(tugasAktif.judul + '. ' + tugasAktif.instruksi); } break;
            case 'tutup': tutup(); break;
            case 'lanjut': bunyi('klik'); $('jendela-hore').classList.add('hidden'); tutup(true); break;
            case 'berikutnya':
                var lanjut = tugasAktif && tugasBerikutnya(tugasAktif);
                $('jendela-hore').classList.add('hidden');
                if (lanjut) { diam(); mulai(lanjut); } else tutup(true);
                break;
            case 'tutup-karya': bunyi('klik'); $('jendela-karya').classList.add('hidden'); break;
        }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { $('jendela-karya').classList.add('hidden'); }
    });

    render();
})();
