@extends('layouts.dashboard')
@section('title', 'Tagihan & SPP')
@section('heading', 'Tagihan & SPP')
@section('actions') @include('partials.btn-tambah', ['href' => route('admin.tagihan.create'), 'label' => 'Buat tagihan']) @endsection
@section('content')
@php $r = fn ($s, $f) => $ringkas[$s]->$f ?? 0; @endphp
<ul class="grid sm:grid-cols-3 gap-4 mb-5">
    <li class="bg-amber-50 rounded-3xl p-5"><p class="text-sm font-bold text-amber-700">Perlu diverifikasi</p><p class="font-display text-3xl font-semibold text-navy mt-1">{{ $r('menunggu', 'n') }}</p><p class="text-xs text-slate-500">{{ \App\Models\Tagihan::rupiah($r('menunggu', 'total')) }}</p></li>
    <li class="bg-rose-50 rounded-3xl p-5"><p class="text-sm font-bold text-rose-700">Belum dibayar</p><p class="font-display text-3xl font-semibold text-navy mt-1">{{ $r('belum', 'n') }}</p><p class="text-xs text-slate-500">{{ \App\Models\Tagihan::rupiah($r('belum', 'total')) }}</p></li>
    <li class="bg-emerald-50 rounded-3xl p-5"><p class="text-sm font-bold text-emerald-700">Lunas</p><p class="font-display text-3xl font-semibold text-navy mt-1">{{ $r('lunas', 'n') }}</p><p class="text-xs text-slate-500">{{ \App\Models\Tagihan::rupiah($r('lunas', 'total')) }}</p></li>
</ul>

<form class="flex flex-wrap gap-2 mb-5" role="search">
    <input name="q" value="{{ request('q') }}" placeholder="Cari nama / NIS" aria-label="Cari siswa" class="flex-1 min-w-[160px] rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
    <select name="periode" aria-label="Periode" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
        <option value="">Semua periode</option>
        @foreach ($periodeList as $p)<option @selected(request('periode') === $p)>{{ $p }}</option>@endforeach
    </select>
    <select name="status" aria-label="Status" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
        <option value="">Semua status</option>
        @foreach (\App\Models\Tagihan::STATUS as $k => $v)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>@endforeach
    </select>
    <select name="kelas_id" aria-label="Kelas" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
        <option value="">Semua kelas</option>
        @foreach ($kelas as $k)<option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->nama }}</option>@endforeach
    </select>
    <button class="bg-navy text-white font-bold text-sm px-5 rounded-xl">Terapkan</button>
</form>

@include('partials.table-open')
    <thead class="bg-cloud text-left text-navy"><tr>
        <th class="px-5 py-3 font-extrabold">Siswa</th><th class="px-5 py-3 font-extrabold">Tagihan</th><th class="px-5 py-3 font-extrabold">Jumlah</th>
        <th class="px-5 py-3 font-extrabold">Status</th><th class="px-5 py-3 font-extrabold">Ubah status</th><th class="px-5 py-3"><span class="sr-only">Hapus</span></th>
    </tr></thead>
    <tbody class="divide-y divide-slate-100">
    @forelse ($tagihan as $t)
        <tr class="{{ $t->status === 'menunggu' ? 'bg-amber-50/50' : '' }}">
            <td class="px-5 py-3"><p class="font-bold text-navy">{{ $t->siswa->nama }}</p><p class="text-xs text-slate-500">{{ $t->siswa->kelas->nama ?? '—' }}</p></td>
            <td class="px-5 py-3"><p class="font-bold">{{ $t->jenis }} · {{ $t->periode }}</p>
                <p class="text-xs text-slate-500">{{ $t->jatuh_tempo ? 'Jatuh tempo '.$t->jatuh_tempo->translatedFormat('d M Y') : '' }}</p>
                @if ($t->bukti)<a href="{{ route('tagihan.bukti', $t) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-sky mt-1"><i data-lucide="paperclip" class="w-3.5 h-3.5"></i> Lihat bukti</a>@endif
                @if ($t->catatan)<p class="text-xs text-slate-500 mt-1">“{{ $t->catatan }}”</p>@endif
            </td>
            <td class="px-5 py-3 font-bold whitespace-nowrap">{{ $t->rupiah }}</td>
            <td class="px-5 py-3 whitespace-nowrap">@include('partials.status-tagihan', ['t' => $t])
                @if ($t->status === 'lunas')
                    <p class="text-[11px] text-slate-500 mt-1 flex items-center gap-1"><i data-lucide="{{ $t->metode === 'tunai' ? 'banknote' : 'landmark' }}" class="w-3 h-3"></i>{{ \App\Models\Tagihan::METODE[$t->metode] ?? '' }} · {{ $t->dibayar_pada?->translatedFormat('d M Y') }}</p>
                    <a href="{{ route('tagihan.kuitansi', $t) }}" target="_blank" class="text-[11px] font-bold text-sky">{{ $t->no_kuitansi }}</a>
                @endif
            </td>
            <td class="px-5 py-3">
                @if ($t->status === 'menunggu')
                    <form method="POST" action="{{ route('admin.tagihan.update', $t) }}" class="flex gap-1.5">
                        @csrf @method('PATCH')
                        <input type="hidden" name="metode" value="transfer">
                        <button name="status" value="lunas" class="text-xs font-extrabold bg-leaf text-white px-3 py-1.5 rounded-lg whitespace-nowrap">Verifikasi lunas</button>
                        <button name="status" value="belum" class="text-xs font-bold text-slate-500 border border-slate-200 px-3 py-1.5 rounded-lg whitespace-nowrap" onclick="return confirm('Tolak bukti ini? Status kembali menjadi belum dibayar.')">Tolak bukti</button>
                    </form>
                @elseif ($t->status === 'belum')
                    <details class="group">
                        <summary class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-extrabold bg-leaf text-white px-3 py-1.5 rounded-lg list-none whitespace-nowrap"><i data-lucide="banknote" class="w-3.5 h-3.5"></i> Catat pembayaran</summary>
                        <form method="POST" action="{{ route('admin.tagihan.update', $t) }}" class="mt-2 p-3 bg-cloud rounded-xl space-y-2 w-60">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="lunas">
                            <div class="flex gap-1">
                                <label class="flex-1 flex items-center gap-1.5 text-xs font-bold bg-white rounded-lg px-2 py-1.5 cursor-pointer has-[:checked]:ring-2 has-[:checked]:ring-leaf"><input type="radio" name="metode" value="tunai" checked class="accent-leaf"> Tunai</label>
                                <label class="flex-1 flex items-center gap-1.5 text-xs font-bold bg-white rounded-lg px-2 py-1.5 cursor-pointer has-[:checked]:ring-2 has-[:checked]:ring-sky"><input type="radio" name="metode" value="transfer" class="accent-sky"> Transfer</label>
                            </div>
                            <label class="block text-[11px] font-bold text-navy">Tanggal bayar
                                <input type="date" name="tanggal_bayar" value="{{ today()->format('Y-m-d') }}" max="{{ today()->format('Y-m-d') }}" class="mt-0.5 w-full rounded-lg border border-slate-200 px-2 py-1 text-xs font-normal">
                            </label>
                            <input name="catatan" placeholder="Catatan (opsional)" class="w-full rounded-lg border border-slate-200 px-2 py-1 text-xs" aria-label="Catatan">
                            <button class="w-full text-xs font-extrabold bg-navy text-white py-1.5 rounded-lg">Simpan lunas & buat kuitansi</button>
                        </form>
                    </details>
                @else
                    <form method="POST" action="{{ route('admin.tagihan.update', $t) }}" class="flex gap-1.5">
                        @csrf @method('PATCH')
                        <a href="{{ route('tagihan.kuitansi', $t) }}" target="_blank" class="text-xs font-extrabold bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-lg whitespace-nowrap">Kuitansi</a>
                        <button name="status" value="belum" class="text-xs font-bold text-slate-500 border border-slate-200 px-3 py-1.5 rounded-lg whitespace-nowrap" onclick="return confirm('Batalkan pelunasan? Nomor kuitansi akan dihapus.')">Batalkan</button>
                    </form>
                @endif
            </td>
            <td class="px-5 py-3 text-right">@include('partials.hapus', ['action' => route('admin.tagihan.destroy', $t)])</td>
        </tr>
    @empty
        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">Tidak ada tagihan. <a href="{{ route('admin.tagihan.create') }}" class="font-bold text-sky">Buat tagihan SPP</a> untuk bulan ini.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-5">{{ $tagihan->links() }}</div>
@endsection
