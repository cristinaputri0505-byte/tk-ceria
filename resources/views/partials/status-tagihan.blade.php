@php
    $cls = ['belum' => 'bg-rose-100 text-rose-700', 'menunggu' => 'bg-amber-100 text-amber-700', 'lunas' => 'bg-emerald-100 text-emerald-700'][$t->status];
    $ikon = ['belum' => 'circle-alert', 'menunggu' => 'hourglass', 'lunas' => 'circle-check'][$t->status];
@endphp
<span class="inline-flex items-center gap-1 text-xs font-extrabold px-2.5 py-1 rounded-full {{ $cls }}">
    <i data-lucide="{{ $ikon }}" class="w-3.5 h-3.5"></i>{{ $t->terlambat ? 'Lewat jatuh tempo' : \App\Models\Tagihan::STATUS[$t->status] }}
</span>
