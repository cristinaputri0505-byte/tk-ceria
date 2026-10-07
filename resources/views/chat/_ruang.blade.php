{{-- Ruang obrolan bersama. Parameter: $dataUrl, $kirimUrl, $ortuId (boleh null), $tampilNama (bool), $kosong (teks) --}}
<div class="chat-ruang flex flex-col bg-white rounded-3xl border border-slate-100 overflow-hidden max-w-3xl" style="height: calc(100vh - 13rem); min-height: 420px">
    <div id="chat-isi" class="flex-1 overflow-y-auto p-4 space-y-2" aria-live="polite">
        <p id="chat-kosong" class="text-center text-sm text-slate-500 py-10">{{ $kosong }}</p>
    </div>
    <form id="chat-form" class="border-t border-slate-100 p-3 flex items-end gap-2">
        <textarea id="chat-input" rows="1" maxlength="2000" placeholder="Tulis pesan… (Enter untuk kirim, Shift+Enter baris baru)"
            class="flex-1 resize-none rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky/40" style="max-height: 8rem"></textarea>
        <button id="chat-kirim" class="bg-sky text-white rounded-2xl px-4 py-2.5 font-extrabold text-sm inline-flex items-center gap-1.5 hover:bg-sky-dark disabled:opacity-60">
            <i data-lucide="send" class="w-4 h-4"></i> Kirim
        </button>
    </form>
</div>

@push('scripts')
<script>
(function () {
    const DATA = @json($dataUrl), KIRIM = @json($kirimUrl), ORTU = @json($ortuId ?? null), NAMA = @json($tampilNama), CSRF = @json(csrf_token());
    const isi = document.getElementById('chat-isi'), kosong = document.getElementById('chat-kosong');
    const form = document.getElementById('chat-form'), input = document.getElementById('chat-input'), tombol = document.getElementById('chat-kirim');
    let terakhir = 0, tglTerakhir = null, pertama = true, sibuk = false;

    function tambah(m) {
        if (m.tgl_key !== tglTerakhir) {
            tglTerakhir = m.tgl_key;
            const d = document.createElement('div');
            d.className = 'text-center text-[11px] font-bold text-slate-400 py-2';
            d.textContent = m.tgl;
            isi.appendChild(d);
        }
        const baris = document.createElement('div');
        baris.className = 'flex ' + (m.mine ? 'justify-end' : 'justify-start');
        const g = document.createElement('div');
        g.className = 'max-w-[80%] rounded-2xl px-4 py-2 ' + (m.mine ? 'bg-sky text-white rounded-br-md' : 'bg-cloud text-navy rounded-bl-md');
        if (!m.mine && NAMA) {
            const n = document.createElement('p');
            n.className = 'text-[11px] font-extrabold mb-0.5 ' + (m.admin ? 'text-berry' : 'text-sky');
            n.textContent = m.nama + (m.admin ? ' · Admin' : '');
            g.appendChild(n);
        }
        const t = document.createElement('p');
        t.className = 'text-sm whitespace-pre-wrap break-words';
        t.textContent = m.isi;
        g.appendChild(t);
        const j = document.createElement('p');
        j.className = 'text-[10px] mt-1 text-right ' + (m.mine ? 'text-white/70' : 'text-slate-400');
        j.textContent = m.jam + ' WIB';
        g.appendChild(j);
        baris.appendChild(g);
        isi.appendChild(baris);
        terakhir = Math.max(terakhir, m.id);
    }

    async function ambil() {
        if (sibuk) return;
        sibuk = true;
        try {
            const u = new URL(DATA, location.origin);
            u.searchParams.set('setelah', terakhir);
            if (ORTU) u.searchParams.set('ortu', ORTU);
            const r = await fetch(u, { headers: { Accept: 'application/json' } });
            if (!r.ok) return;
            const daftar = await r.json();
            if (daftar.length) {
                const bawah = isi.scrollHeight - isi.scrollTop - isi.clientHeight < 120;
                daftar.forEach(tambah);
                kosong.style.display = 'none';
                if (pertama || bawah) isi.scrollTop = isi.scrollHeight;
            }
            pertama = false;
        } catch (e) {} finally { sibuk = false; }
    }

    async function kirim() {
        const teks = input.value.trim();
        if (!teks) return;
        tombol.disabled = true;
        try {
            const r = await fetch(KIRIM, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ isi: teks, ortu: ORTU }),
            });
            if (!r.ok) throw new Error();
            input.value = '';
            input.style.height = 'auto';
            await ambil();
            isi.scrollTop = isi.scrollHeight;
        } catch (e) {
            alert('Pesan gagal terkirim. Periksa koneksi lalu coba lagi.');
        } finally { tombol.disabled = false; input.focus(); }
    }

    form.addEventListener('submit', e => { e.preventDefault(); kirim(); });
    input.addEventListener('keydown', e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); kirim(); } });
    input.addEventListener('input', () => { input.style.height = 'auto'; input.style.height = Math.min(input.scrollHeight, 128) + 'px'; });

    ambil();
    setInterval(() => { if (!document.hidden) ambil(); }, 4000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) ambil(); });
})();
</script>
@endpush
