<a href="{{ route('kegiatan.show', $k) }}" class="group block">
    <div class="aspect-[4/3] rounded-2xl overflow-hidden bg-gradient-to-br from-amber-100 to-sky-100 grid place-items-center">
        @if ($k->gambar)
            <img src="{{ asset('storage/'.$k->gambar) }}" alt="{{ $k->judul }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform" loading="lazy">
        @else
            <i data-lucide="image" class="w-8 h-8 text-navy/30"></i>
        @endif
    </div>
    <p class="text-[13px] font-bold text-navy mt-2 group-hover:text-sky">{{ $k->judul }}</p>
    @if ($tanggal ?? false)<p class="text-xs text-slate-400">{{ $k->tanggal->translatedFormat('d F Y') }}</p>@endif
</a>
