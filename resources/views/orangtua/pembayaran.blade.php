@extends('layouts.dashboard')
@section('title', 'Pembayaran')
@section('heading', 'Pembayaran & SPP')
@section('content')
@php
    $belum = $tagihan->where('status', 'belum');
    $menunggu = $tagihan->where('status', 'menunggu');
    $lunas = $tagihan->where('status', 'lunas');
@endphp
<div class="grid lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">
        <ul class="grid grid-cols-3 gap-3">
            <li class="bg-rose-50 rounded-3xl p-4"><p class="text-xs font-bold text-rose-700">Belum dibayar</p><p class="font-display text-xl sm:text-2xl font-semibold text-navy mt-1">{{ \App\Models\Tagihan::rupiah($belum->sum('jumlah')) }}</p><p class="text-xs text-slate-500">{{ $belum->count() }} tagihan</p></li>
            <li class="bg-amber-50 rounded-3xl p-4"><p class="text-xs font-bold text-amber-700">Diverifikasi</p><p class="font-display text-xl sm:text-2xl font-semibold text-navy mt-1">{{ \App\Models\Tagihan::rupiah($menunggu->sum('jumlah')) }}</p><p class="text-xs text-slate-500">{{ $menunggu->count() }} tagihan</p></li>
            <li class="bg-emerald-50 rounded-3xl p-4"><p class="text-xs font-bold text-emerald-700">Lunas</p><p class="font-display text-xl sm:text-2xl font-semibold text-navy mt-1">{{ \App\Models\Tagihan::rupiah($lunas->sum('jumlah')) }}</p><p class="text-xs text-slate-500">{{ $lunas->count() }} tagihan</p></li>
        </ul>

        <ul class="space-y-3">
            @forelse ($tagihan as $t)
                @include('orangtua._tagihan', ['t' => $t, 'tampilNama' => $anak->count() > 1])
            @empty
                <li class="bg-white rounded-3xl border border-slate-100 p-8 text-center text-slate-500">Belum ada tagihan.</li>
            @endforelse
        </ul>
    </div>

    <aside class="space-y-5">
        <h2 class="font-display text-lg font-semibold text-navy">Cara membayar</h2>
        <section class="bg-navy text-white rounded-3xl p-6">
            <p class="flex items-center gap-2 text-sm text-white/70 font-bold"><i data-lucide="landmark" class="w-4 h-4"></i> 1. Transfer bank</p>
            <p class="font-display text-xl font-semibold mt-2">{{ $site['bank_nama'] }}</p>
            <div class="flex items-center gap-2 mt-1">
                <p id="norek" class="font-display text-2xl font-semibold tracking-wide text-sun">{{ $site['bank_nomor'] }}</p>
                <button type="button" class="p-1.5 rounded-lg bg-white/10 hover:bg-white/20" aria-label="Salin nomor rekening"
                    onclick="navigator.clipboard.writeText(document.getElementById('norek').textContent.replace(/\D/g,''));this.innerHTML='<span class=&quot;text-xs font-bold px-1&quot;>Tersalin</span>'">
                    <i data-lucide="copy" class="w-4 h-4"></i>
                </button>
            </div>
            <p class="text-sm text-white/80">a.n. {{ $site['bank_atas_nama'] }}</p>
            <p class="text-sm text-white/70 mt-3 whitespace-pre-line">{{ $site['bayar_petunjuk'] }}</p>
        </section>
        <section class="bg-leaf-soft rounded-3xl p-6">
            <p class="flex items-center gap-2 text-sm text-leaf font-bold"><i data-lucide="banknote" class="w-4 h-4"></i> 2. Tunai (cash)</p>
            <p class="font-display text-xl font-semibold text-navy mt-2">{{ $site['tunai_tempat'] }}</p>
            <p class="text-sm text-slate-600 mt-1 flex items-center gap-1.5"><i data-lucide="clock" class="w-4 h-4"></i> {{ $site['tunai_jam'] }}</p>
            <p class="text-sm text-slate-600 mt-3 whitespace-pre-line">{{ $site['tunai_petunjuk'] }}</p>
        </section>
        <p class="text-xs text-slate-500">Setiap pembayaran yang lunas mendapat <b>kuitansi digital</b> yang bisa dibuka dan dicetak dari tagihan.</p>
    </aside>
</div>
@endsection
