{{-- Satu tagihan: status, pilihan bayar transfer / tunai, kuitansi. $t = Tagihan, $tampilNama = bool --}}
<li class="bg-white rounded-3xl border {{ $t->terlambat ? 'border-rose-200' : 'border-slate-100' }} p-5">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="flex items-start gap-3">
            <span class="w-11 h-11 rounded-2xl grid place-items-center shrink-0 {{ $t->status === 'lunas' ? 'bg-emerald-50 text-emerald-600' : 'bg-sun-soft text-amber-600' }}">
                <i data-lucide="{{ $t->metode === 'tunai' ? 'banknote' : 'receipt' }}" class="w-5 h-5"></i>
            </span>
            <div>
                <p class="font-extrabold text-navy">{{ $t->jenis }} · {{ $t->periode }}</p>
                <p class="text-xs text-slate-500">
                    @if ($tampilNama ?? false){{ $t->siswa->nama }} · @endif
                    @if ($t->status === 'lunas')
                        Dibayar {{ $t->dibayar_pada?->translatedFormat('d F Y') }}{{ $t->metode ? ' · '.\App\Models\Tagihan::METODE[$t->metode] : '' }}
                    @elseif ($t->jatuh_tempo)
                        Jatuh tempo {{ $t->jatuh_tempo->translatedFormat('d F Y') }}
                    @endif
                </p>
            </div>
        </div>
        <div class="text-right">
            <p class="font-display text-xl font-semibold text-navy">{{ $t->rupiah }}</p>
            @include('partials.status-tagihan', ['t' => $t])
        </div>
    </div>

    @if ($t->catatan)<p class="text-xs text-slate-500 mt-3 bg-cloud rounded-xl px-3 py-2">Catatan: {{ $t->catatan }}</p>@endif

    @if ($t->status === 'lunas')
        <div class="mt-3 flex flex-wrap items-center gap-3">
            <a href="{{ route('tagihan.kuitansi', $t) }}" target="_blank" class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 text-sm font-extrabold px-4 py-2 rounded-xl"><i data-lucide="file-text" class="w-4 h-4"></i> Lihat kuitansi</a>
            <span class="text-xs text-slate-400">No. {{ $t->no_kuitansi }}</span>
        </div>
    @elseif ($t->status === 'menunggu')
        <div class="mt-3 flex flex-wrap items-center gap-3">
            @if ($t->bukti)<a href="{{ route('tagihan.bukti', $t) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-bold text-sky"><i data-lucide="paperclip" class="w-3.5 h-3.5"></i> Lihat bukti yang dikirim</a>@endif
            <details class="w-full">
                <summary class="inline-flex items-center gap-2 cursor-pointer text-sm font-bold text-navy border border-slate-200 px-4 py-2 rounded-xl list-none"><i data-lucide="upload" class="w-4 h-4"></i> Ganti bukti transfer</summary>
                @include('orangtua._form-bukti', ['t' => $t])
            </details>
        </div>
    @else
        {{-- Pilih cara bayar --}}
        <div class="mt-4 grid sm:grid-cols-2 gap-2" data-pilih-bayar>
            <button type="button" data-cara="transfer" aria-expanded="{{ $errors->any() && old('tagihan_id') == $t->id ? 'true' : 'false' }}"
                class="flex items-center gap-3 rounded-2xl border-2 border-slate-200 px-4 py-3 text-left hover:border-sky aria-expanded:border-sky aria-expanded:bg-sky-soft">
                <span class="w-9 h-9 rounded-xl grid place-items-center bg-sky-soft text-sky shrink-0"><i data-lucide="landmark" class="w-5 h-5"></i></span>
                <span><span class="block text-sm font-extrabold text-navy">Transfer bank</span><span class="block text-xs text-slate-500">Unggah bukti transfer</span></span>
            </button>
            <button type="button" data-cara="tunai" aria-expanded="false"
                class="flex items-center gap-3 rounded-2xl border-2 border-slate-200 px-4 py-3 text-left hover:border-leaf aria-expanded:border-leaf aria-expanded:bg-leaf-soft">
                <span class="w-9 h-9 rounded-xl grid place-items-center bg-leaf-soft text-leaf shrink-0"><i data-lucide="banknote" class="w-5 h-5"></i></span>
                <span><span class="block text-sm font-extrabold text-navy">Tunai (cash)</span><span class="block text-xs text-slate-500">Bayar langsung di sekolah</span></span>
            </button>
            <div data-panel-cara="transfer" class="sm:col-span-2" @unless($errors->any() && old('tagihan_id') == $t->id) hidden @endunless>
                <p class="text-xs text-slate-600 mt-1">Transfer <b>{{ $t->rupiah }}</b> ke {{ $site['bank_nama'] }} <b>{{ $site['bank_nomor'] }}</b> a.n. {{ $site['bank_atas_nama'] }}, lalu unggah buktinya.</p>
                @include('orangtua._form-bukti', ['t' => $t])
            </div>
            <div data-panel-cara="tunai" class="sm:col-span-2 bg-leaf-soft rounded-2xl p-4 text-sm" hidden>
                <p class="font-extrabold text-navy">Bayar {{ $t->rupiah }} di {{ $site['tunai_tempat'] }}</p>
                <p class="text-slate-600 mt-1"><i data-lucide="clock" class="w-3.5 h-3.5 inline -mt-0.5"></i> {{ $site['tunai_jam'] }}</p>
                <p class="text-slate-600 mt-2">{{ $site['tunai_petunjuk'] }}</p>
                <p class="text-xs text-slate-500 mt-2">Sebutkan: <b>{{ $t->siswa->nama }}</b> · {{ $t->jenis }} {{ $t->periode }}</p>
            </div>
        </div>
    @endif
</li>
@once
    @push('scripts')
    <script>
        document.addEventListener('click', e => {
            const b = e.target.closest('[data-cara]');
            if (!b) return;
            const box = b.closest('[data-pilih-bayar]');
            const buka = b.getAttribute('aria-expanded') !== 'true';
            box.querySelectorAll('[data-cara]').forEach(x => x.setAttribute('aria-expanded', x === b && buka ? 'true' : 'false'));
            box.querySelectorAll('[data-panel-cara]').forEach(p => p.hidden = !(buka && p.dataset.panelCara === b.dataset.cara));
        });
    </script>
    @endpush
@endonce
