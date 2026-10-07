@php
    $cls = ['baru' => 'bg-sky-100 text-sky-700', 'diproses' => 'bg-amber-100 text-amber-700', 'diterima' => 'bg-emerald-100 text-emerald-700', 'ditolak' => 'bg-slate-200 text-slate-600'][$status] ?? '';
@endphp
<span class="text-xs font-extrabold px-2.5 py-1 rounded-full {{ $cls }}">{{ \App\Models\Pendaftaran::STATUS[$status] ?? $status }}</span>
