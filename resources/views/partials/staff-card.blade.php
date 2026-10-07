<div class="text-center">
    @if ($s->foto)
        <img src="{{ Storage::disk('public')->url($s->foto) }}" alt="" class="{{ $besar ?? false ? 'w-32 h-32' : 'w-20 h-20' }} mx-auto rounded-full object-cover border-4 border-white shadow">
    @else
        <span class="{{ $besar ?? false ? 'w-32 h-32 text-4xl' : 'w-20 h-20 text-2xl' }} mx-auto rounded-full grid place-items-center bg-sky-soft font-display text-sky border-4 border-white shadow">{{ $s->inisial }}</span>
    @endif
    <p class="font-bold text-navy {{ $besar ?? false ? 'text-base mt-3' : 'text-sm mt-2' }}">{{ $s->nama }}</p>
    <p class="text-xs text-slate-500">{{ $s->jabatan ?? 'Guru' }}</p>
    @if (($besar ?? false) && $s->pendidikan)<p class="text-xs text-slate-400 mt-1">{{ $s->pendidikan }}</p>@endif
</div>
