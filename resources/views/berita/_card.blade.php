<a href="{{ route('berita.show', $b) }}" class="group block bg-white rounded-3xl overflow-hidden border border-slate-100 h-full">
    <div class="aspect-[16/9] bg-gradient-to-br from-sky-100 to-amber-100 grid place-items-center">
        @if ($b->gambar)
            <img src="{{ Storage::disk('public')->url($b->gambar) }}" alt="" class="w-full h-full object-cover" loading="lazy">
        @else
            <i data-lucide="newspaper" class="w-10 h-10 text-navy/25"></i>
        @endif
    </div>
    <div class="p-5">
        <p class="text-xs font-bold text-slate-400">{{ $b->published_at->translatedFormat('d F Y') }}</p>
        <h3 class="font-extrabold text-navy mt-1 group-hover:text-sky">{{ $b->judul }}</h3>
        <p class="text-sm text-slate-500 mt-2 line-clamp-2">{{ $b->ringkasan ?? \Illuminate\Support\Str::limit(strip_tags($b->isi), 110) }}</p>
    </div>
</a>
