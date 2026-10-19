<a href="{{ route('kegiatan.show', $k) }}" class="group block">
    <div class="relative aspect-[4/3] rounded-2xl overflow-hidden bg-gradient-to-br from-amber-100 to-sky-100 grid place-items-center">
        @if ($k->gambar)
            <img src="{{ $k->sampul_url }}" alt="{{ $k->judul }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform" loading="lazy">
        @else
            <i data-lucide="image" class="w-8 h-8 text-navy/30"></i>
        @endif
        @if (isset($k->foto_count) && $k->foto_count > 1)
            <span class="absolute bottom-2 right-2 bg-navy-deep/60 text-white text-[11px] font-bold px-2 py-0.5 rounded-full inline-flex items-center gap-1"><i data-lucide="images" class="w-3 h-3"></i>{{ $k->foto_count }} foto</span>
        @endif
    </div>
    <p class="text-[13px] font-bold text-navy mt-2 group-hover:text-sky">{{ $k->judul }}</p>
    @if ($tanggal ?? false)<p class="text-xs text-slate-400">{{ $k->tanggal->translatedFormat('d F Y') }}</p>@endif
</a>
