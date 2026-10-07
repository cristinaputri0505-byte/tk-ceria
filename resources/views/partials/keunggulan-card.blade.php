@php $warnaK = ['bg-rose-100 text-rose-500', 'bg-emerald-100 text-emerald-600', 'bg-amber-100 text-amber-600', 'bg-violet-100 text-violet-600', 'bg-sky-100 text-sky-600', 'bg-teal-100 text-teal-600']; @endphp
<div class="bg-white rounded-3xl p-5 border border-slate-100 h-full">
    <span class="w-12 h-12 grid place-items-center rounded-full {{ $warnaK[$i % 6] }}"><i data-lucide="{{ $k->ikon }}" class="w-6 h-6"></i></span>
    <h3 class="font-extrabold text-navy text-[14px] mt-3">{{ $k->judul }}</h3>
    <p class="text-[12.5px] text-slate-500 mt-1.5 leading-snug">{{ $k->deskripsi }}</p>
</div>
