/*!
 * foto.js - berlaku untuk SEMUA foto di website TK Ceria (publik, admin, guru, orang tua).
 *
 * 1. Foto yang gagal dimuat (file hilang / tautan rusak) diganti gambar pengganti yang rapi,
 *    bukan ikon gambar rusak. Gambar yang punya onerror sendiri (foto anak, foto profil) memakai
 *    caranya sendiri: diganti huruf awal nama.
 * 2. Foto yang dipilih di formulir unggah diperkecil dulu di HP/laptop sebelum dikirim
 *    (sisi terpanjang maks. 1600 px). Foto HP 4-8 MB menjadi sekitar 200-500 KB: unggah lebih cepat,
 *    tidak kena batas ukuran server, dan hemat kuota Supabase Storage.
 *    Logo/PNG kecil, GIF, SVG, dan PDF tidak diubah.
 */
(function () {
    'use strict';

    /* ---------- 1. Gambar pengganti untuk foto yang gagal dimuat ---------- */
    var PENGGANTI = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300">' +
        '<rect width="400" height="300" fill="#e8eef8"/>' +
        '<g fill="none" stroke="#9fb0c8" stroke-width="10" stroke-linecap="round" stroke-linejoin="round">' +
        '<rect x="130" y="95" width="140" height="110" rx="16"/><circle cx="170" cy="132" r="12"/>' +
        '<path d="m270 180-32-32-68 57"/></g></svg>'
    );

    document.addEventListener('error', function (e) {
        var img = e.target;
        if (!img || img.tagName !== 'IMG') return;
        if (img.hasAttribute('onerror') || img.dataset.fotoGagal) return; // punya cadangan sendiri / sudah diganti
        if (!img.getAttribute('src')) return;                             // mis. lightbox yang belum diisi
        img.dataset.fotoGagal = '1';
        img.src = PENGGANTI;
        img.alt = img.alt || 'Foto tidak tersedia';
        img.title = 'Foto tidak tersedia';
    }, true);

    /* ---------- 2. Perkecil foto sebelum diunggah ---------- */
    var SISI_MAKS = 1600;          // piksel
    var BATAS_TANPA_UBAH = 1200000; // file <= 1,2 MB dan sisi <= 1600 px dibiarkan apa adanya
    var MUTU_JPEG = 0.85;
    var LEWATI = /^image\/(gif|svg\+xml)$/i;

    function muatGambar(file) {
        if (window.createImageBitmap) {
            return createImageBitmap(file, { imageOrientation: 'from-image' }).catch(function () {
                return createImageBitmap(file);
            });
        }
        return new Promise(function (ok, gagal) {
            var url = URL.createObjectURL(file), im = new Image();
            im.onload = function () { URL.revokeObjectURL(url); ok(im); };
            im.onerror = function () { URL.revokeObjectURL(url); gagal(new Error('tidak bisa dibaca')); };
            im.src = url;
        });
    }

    function perkecil(file) {
        if (!file.type || file.type.indexOf('image/') !== 0 || LEWATI.test(file.type)) return Promise.resolve(file);

        return muatGambar(file).then(function (gbr) {
            var w = gbr.width, h = gbr.height, sisi = Math.max(w, h);
            if (sisi <= SISI_MAKS && file.size <= BATAS_TANPA_UBAH) return file;

            var skala = Math.min(1, SISI_MAKS / sisi);
            var kanvas = document.createElement('canvas');
            kanvas.width = Math.round(w * skala);
            kanvas.height = Math.round(h * skala);
            var ctx = kanvas.getContext('2d');
            var png = file.type === 'image/png';
            if (!png) { ctx.fillStyle = '#ffffff'; ctx.fillRect(0, 0, kanvas.width, kanvas.height); }
            ctx.drawImage(gbr, 0, 0, kanvas.width, kanvas.height);
            if (gbr.close) gbr.close();

            var tipe = png ? 'image/png' : 'image/jpeg';
            return new Promise(function (ok) {
                kanvas.toBlob(function (blob) {
                    if (!blob || blob.size >= file.size) return ok(file); // tidak lebih kecil: pakai asli
                    var nama = png ? file.name : file.name.replace(/\.[^.]+$/, '') + '.jpg';
                    ok(new File([blob], nama, { type: tipe, lastModified: Date.now() }));
                }, tipe, MUTU_JPEG);
            });
        }).catch(function () { return file; }); // format tak dikenal browser: kirim apa adanya
    }

    function setTombol(form, sibuk) {
        if (!form) return;
        Array.prototype.forEach.call(form.querySelectorAll('button[type=submit], button:not([type]), input[type=submit]'), function (b) {
            if (sibuk) { b.dataset.fotoNonaktif = b.disabled ? '0' : '1'; b.disabled = true; }
            else if (b.dataset.fotoNonaktif === '1') { b.disabled = false; delete b.dataset.fotoNonaktif; }
        });
    }

    document.addEventListener('change', function (e) {
        var input = e.target;
        if (!input || input.type !== 'file' || !input.files || !input.files.length || input.dataset.fotoDiproses) return;
        if (!window.DataTransfer || !window.File) return;
        var semua = Array.prototype.slice.call(input.files);
        if (!semua.some(function (f) { return f.type && f.type.indexOf('image/') === 0; })) return;

        var form = input.form;
        setTombol(form, true);
        var info = document.createElement('p');
        info.className = 'text-xs text-slate-500 mt-1';
        info.textContent = 'Menyiapkan foto\u2026';
        input.insertAdjacentElement('afterend', info);

        Promise.all(semua.map(perkecil)).then(function (hasil) {
            var dt = new DataTransfer();
            hasil.forEach(function (f) { dt.items.add(f); });
            input.dataset.fotoDiproses = '1';
            input.files = dt.files;
            delete input.dataset.fotoDiproses;
            var awal = semua.reduce(function (n, f) { return n + f.size; }, 0);
            var akhir = hasil.reduce(function (n, f) { return n + f.size; }, 0);
            info.textContent = akhir < awal
                ? 'Foto diperkecil: ' + (awal / 1048576).toFixed(1) + ' MB \u2192 ' + (akhir / 1048576).toFixed(1) + ' MB'
                : '';
            if (!info.textContent) info.remove();
        }).catch(function () { info.remove(); })
          .then(function () { setTombol(form, false); });
    }, true);
})();
