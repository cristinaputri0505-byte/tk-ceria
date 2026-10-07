{{-- Pakai: <button data-foto="url" data-judul="teks"> ... ; sertakan partial ini sekali per halaman --}}
<dialog id="lightbox" class="backdrop:bg-navy-deep/85 bg-transparent p-0 max-w-5xl w-[92vw]">
    <figure class="relative">
        <img id="lbImg" src="" alt="" class="w-full max-h-[80vh] object-contain rounded-2xl bg-black/20">
        <figcaption id="lbCap" class="text-white font-bold text-center mt-3"></figcaption>
        <div class="flex justify-center gap-2 mt-3">
            <a id="lbDl" href="#" download class="inline-flex items-center gap-2 bg-white text-navy text-sm font-bold px-4 py-2 rounded-xl"><i data-lucide="download" class="w-4 h-4"></i> Simpan foto</a>
        </div>
        <button type="button" onclick="document.getElementById('lightbox').close()" class="absolute -top-3 -right-3 w-10 h-10 grid place-items-center rounded-full bg-white text-navy" aria-label="Tutup"><i data-lucide="x" class="w-5 h-5"></i></button>
    </figure>
</dialog>
<script>
    (function () {
        const lb = document.getElementById('lightbox');
        document.addEventListener('click', e => {
            const b = e.target.closest('[data-foto]');
            if (!b) return;
            document.getElementById('lbImg').src = b.dataset.foto;
            document.getElementById('lbImg').alt = b.dataset.judul || '';
            document.getElementById('lbCap').textContent = b.dataset.judul || '';
            document.getElementById('lbDl').href = b.dataset.foto;
            lb.showModal();
        });
        lb.addEventListener('click', e => { if (e.target === lb) lb.close(); });
    })();
</script>
