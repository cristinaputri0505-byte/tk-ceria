@php $warnaK = ['bg-permen', 'bg-daun', 'bg-sun', 'bg-anggur', 'bg-langit', 'bg-jeruk']; @endphp
<div class="bg-white rounded-3xl p-5 border border-slate-100 h-full {{ $i % 2 ? 'miring-kanan' : 'miring-kiri' }}">
    <span class="w-14 h-14 grid place-items-center rounded-2xl border-2 border-navy text-navy {{ $warnaK[$i % 6] }}"><i data-lucide="{{ $k->ikon }}" class="w-7 h-7"></i></span>
    <h3 class="font-display font-bold text-navy text-lg leading-tight mt-3">{{ $k->judul }}</h3>
    <p class="text-[13px] text-ink mt-1.5 leading-snug">{{ $k->deskripsi }}</p>
</div>
