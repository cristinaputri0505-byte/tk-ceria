/**
 * Membuat public/js/ikon.min.js: hanya berisi ikon Lucide yang benar-benar dipakai website.
 *
 * Dulu setiap halaman memuat seluruh pustaka Lucide (±350 KB, 1.700+ ikon) padahal yang dipakai
 * hanya ±100 ikon. File hasil skrip ini ±30 KB.
 *
 * Skrip berjalan otomatis lewat `npm run build`. Jalankan manual bila hanya menambah ikon:
 *     node scripts/buat-ikon.cjs
 *
 * Nama ikon dikumpulkan dari app/, resources/, database/, routes/: semua atribut data-lucide="..."
 * dan semua teks dalam tanda kutip yang kebetulan sama dengan nama ikon Lucide.
 * Sumber ikon lengkap: resources/ikon/lucide-lengkap.js (Lucide v0.468.0, lisensi ISC).
 */
const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const sumber = fs.readFileSync(path.join(root, 'resources/ikon/lucide-lengkap.js'), 'utf8');
const g = {};
new Function('globalThis', 'self', 'window', 'module', 'exports', 'define', sumber)(g, g, g);
const semua = g.lucide.icons;
const pascal = (n) => n.split('-').map((s) => s.charAt(0).toUpperCase() + s.slice(1)).join('');

function telusuri(dir, hasil = []) {
    for (const f of fs.readdirSync(dir, { withFileTypes: true })) {
        const p = path.join(dir, f.name);
        if (f.isDirectory()) {
            if (p !== path.join(root, 'resources/ikon')) telusuri(p, hasil);
        } else if (/\.(php|js)$/.test(f.name)) {
            hasil.push(p);
        }
    }
    return hasil;
}

const dipakai = new Set();
const tidakAda = new Set();
for (const file of ['app', 'resources', 'database', 'routes'].flatMap((d) => telusuri(path.join(root, d)))) {
    const isi = fs.readFileSync(file, 'utf8');
    for (const m of isi.matchAll(/data-lucide="([a-z0-9-]+)"/g)) {
        if (!semua[pascal(m[1])]) tidakAda.add(m[1]);
    }
    for (const m of isi.matchAll(/['"`]([a-z][a-z0-9]*(?:-[a-z0-9]+)*)['"`]/g)) {
        if (semua[pascal(m[1])]) dipakai.add(m[1]);
    }
}

const data = {};
for (const nama of [...dipakai].sort()) data[nama] = semua[pascal(nama)][2].map(([tag, attr]) => [tag, attr]);

const runtime = `/*! Ikon Lucide v0.468.0 (ISC) - hanya ikon yang dipakai. Dibuat oleh scripts/buat-ikon.cjs, jangan diedit. */
(function (w, d) {
    var I = ${JSON.stringify(data)};
    var NS = 'http://www.w3.org/2000/svg';
    var DASAR = { xmlns: NS, width: 24, height: 24, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 2, 'stroke-linecap': 'round', 'stroke-linejoin': 'round' };
    function ganti(el) {
        var nama = el.getAttribute('data-lucide'), isi = I[nama];
        if (!isi || !el.parentNode) return;
        var svg = d.createElementNS(NS, 'svg'), k;
        for (k in DASAR) svg.setAttribute(k, DASAR[k]);
        for (var i = 0; i < el.attributes.length; i++) svg.setAttribute(el.attributes[i].name, el.attributes[i].value);
        svg.setAttribute('class', ('lucide lucide-' + nama + ' ' + (el.getAttribute('class') || '')).trim());
        isi.forEach(function (n) {
            var c = d.createElementNS(NS, n[0]);
            for (var a in n[1]) c.setAttribute(a, n[1][a]);
            svg.appendChild(c);
        });
        el.parentNode.replaceChild(svg, el);
    }
    w.lucide = {
        icons: I,
        createIcons: function () { Array.prototype.forEach.call(d.querySelectorAll('[data-lucide]:not(svg)'), ganti); }
    };
})(window, document);
`;

fs.writeFileSync(path.join(root, 'public/js/ikon.min.js'), runtime);
console.log(`ikon.min.js: ${dipakai.size} ikon, ${(Buffer.byteLength(runtime) / 1024).toFixed(1)} KB`);
if (tidakAda.size) {
    console.error('PERINGATAN, nama ikon tidak dikenal: ' + [...tidakAda].join(', '));
    process.exitCode = 1;
}
