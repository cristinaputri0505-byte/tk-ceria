<a href="{{ route('kegiatan.show', $k) }}" class="group block bg-white border-[2.5px] border-navy rounded-2xl p-2 pb-3 shadow-[4px_4px_0_#2b2140] {{ $k->id % 2 ? 'miring-kanan' : 'miring-kiri' }}">
    <div class="relative aspect-[4/3] rounded-xl overflow-hidden bg-sun-soft grid place-items-center">
        @if ($k->gambar)
            <img src="{{ $k->sampul_url }}" alt="{{ $k->judul }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform" loading="lazy">
        @else
            <i data-lucide="image" class="w-8 h-8 text-navy/30"></i>
        @endif
        @if (isset($k->foto_count) && $k->foto_count > 1)
            <span class="absolute bottom-2 right-2 bg-navy-deep/60 text-white text-[11px] font-bold px-2 py-0.5 rounded-full inline-flex items-center gap-1"><i data-lucide="images" class="w-3 h-3"></i>{{ $k->foto_count }} foto</span>
        @endif
    </div>
    <p class="font-display text-[15px] font-bold leading-tight text-navy mt-2 px-1 group-hover:text-sky">{{ $k->judul }}</p>
    @if ($tanggal ?? false)<p class="text-xs text-ink px-1">{{ $k->tanggal->translatedFormat('d F Y') }}</p>@endif
</a>
