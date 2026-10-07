<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kuitansi {{ $t->no_kuitansi }} — {{ $site['nama_sekolah'] }}</title>
    @include('partials.head')
    <style>@page { size: A5 landscape; margin: 10mm; }</style>
</head>
<body class="bg-cloud font-body text-ink antialiased print:bg-white">
<main class="max-w-3xl mx-auto p-4 sm:p-8 print:p-0">
    <div class="flex justify-end gap-2 mb-4 print:hidden">
        <button onclick="window.print()" class="inline-flex items-center gap-2 bg-navy text-white text-sm font-extrabold px-4 py-2.5 rounded-xl"><i data-lucide="printer" class="w-4 h-4"></i> Cetak / simpan PDF</button>
    </div>

    <article class="relative overflow-hidden bg-white rounded-3xl border border-slate-200 p-8 print:rounded-none print:border-slate-300">
        <div class="absolute right-6 top-24 -rotate-12 border-4 border-emerald-500/70 text-emerald-600/80 font-display text-4xl font-semibold px-5 py-1 rounded-2xl pointer-events-none" aria-hidden="true">LUNAS</div>

        <header class="flex items-start justify-between gap-4 border-b-2 border-dashed border-slate-200 pb-5">
            <div class="flex items-center gap-3">
                @include('partials.logo', ['size' => 52])
                <div>
                    <p class="font-display text-2xl font-semibold text-navy leading-none">{{ $site['nama_sekolah'] }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ $site['alamat'] }} · {{ $site['telepon'] }}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="font-display text-2xl font-semibold text-navy">KUITANSI</p>
                <p class="text-sm font-bold text-slate-500">No. {{ $t->no_kuitansi }}</p>
            </div>
        </header>

        <dl class="mt-6 grid grid-cols-[170px_1fr] gap-y-3 text-[15px]">
            <dt class="text-slate-500">Telah terima dari</dt>
            <dd class="font-bold text-navy">{{ $t->siswa->orangTua->name ?? ($t->siswa->nama_ayah ?: $t->siswa->nama_ibu) }}</dd>
            <dt class="text-slate-500">Untuk pembayaran</dt>
            <dd class="font-bold text-navy">{{ $t->jenis }} {{ $t->periode }}</dd>
            <dt class="text-slate-500">Nama siswa</dt>
            <dd class="font-bold text-navy">{{ $t->siswa->nama }} <span class="font-normal text-slate-500">· NIS {{ $t->siswa->nis }} · {{ $t->siswa->kelas->nama ?? '' }}</span></dd>
            <dt class="text-slate-500">Terbilang</dt>
            <dd class="italic text-navy bg-sun-soft rounded-lg px-3 py-1.5 capitalize">{{ \App\Models\Tagihan::terbilang($t->jumlah) }} rupiah</dd>
            <dt class="text-slate-500">Cara bayar</dt>
            <dd class="font-bold text-navy">{{ \App\Models\Tagihan::METODE[$t->metode] ?? '—' }}</dd>
        </dl>

        <footer class="mt-8 flex items-end justify-between gap-6">
            <div class="bg-navy text-white rounded-2xl px-6 py-3">
                <p class="text-xs text-white/70 font-bold">Jumlah</p>
                <p class="font-display text-3xl font-semibold">{{ $t->rupiah }}</p>
            </div>
            <div class="text-center text-sm">
                <p class="text-slate-500">{{ trim(\Illuminate\Support\Str::afterLast($site['alamat'], ',')) }}, {{ $t->dibayar_pada?->translatedFormat('d F Y') }}</p>
                <p class="text-slate-500">Petugas</p>
                <p class="font-bold text-navy mt-10 border-t border-slate-300 pt-1 min-w-[160px]">{{ $t->penerima->name ?? 'Tata Usaha' }}</p>
            </div>
        </footer>
        <p class="text-[11px] text-slate-400 mt-6">Kuitansi ini dibuat otomatis oleh sistem {{ $site['nama_sekolah'] }} dan sah tanpa tanda tangan basah.</p>
    </article>
</main>
<script>lucide.createIcons();</script>
</body>
</html>
