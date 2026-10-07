@extends('layouts.dashboard')
@section('title', 'Siswa')
@section('heading', 'Data Siswa')
@section('actions') @include('partials.btn-tambah', ['href' => route('admin.siswa.create'), 'label' => 'Tambah siswa']) @endsection
@section('content')
<form class="flex flex-wrap gap-3 mb-5" role="search">
    <input name="q" value="{{ request('q') }}" placeholder="Cari nama atau NIS" aria-label="Cari siswa" class="flex-1 min-w-[200px] rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
    <select name="kelas_id" aria-label="Filter kelas" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm" onchange="this.form.submit()">
        <option value="">Semua kelas</option>
        @foreach ($kelas as $k)<option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->nama }}</option>@endforeach
    </select>
    <button class="bg-navy text-white font-bold text-sm px-5 rounded-xl">Cari</button>
</form>

@include('partials.table-open')
    <thead class="bg-cloud text-left text-navy"><tr>
        <th class="px-5 py-3 font-extrabold">NIS</th><th class="px-5 py-3 font-extrabold">Nama</th><th class="px-5 py-3 font-extrabold">Usia</th>
        <th class="px-5 py-3 font-extrabold">Kelas</th><th class="px-5 py-3 font-extrabold">Orang tua</th><th class="px-5 py-3"><span class="sr-only">Aksi</span></th>
    </tr></thead>
    <tbody class="divide-y divide-slate-100">
    @forelse ($siswa as $s)
        <tr>
            <td class="px-5 py-3 text-slate-500">{{ $s->nis }}</td>
            <td class="px-5 py-3"><div class="flex items-center gap-3">@include('partials.foto-anak', ['s' => $s, 'size' => 'w-10 h-10 !rounded-xl !border-0 !shadow-none', 'teks' => 'text-base'])<span class="font-bold text-navy">{{ $s->nama }} <span class="text-xs font-normal text-slate-400">({{ $s->jenis_kelamin }})</span>@unless ($s->izin_foto_publik)<i data-lucide="camera-off" class="inline w-3.5 h-3.5 text-amber-600 ml-1 -mt-0.5" title="Foto tidak boleh tampil di website publik"></i>@endunless</span></div></td>
            <td class="px-5 py-3">{{ $s->usia }}</td>
            <td class="px-5 py-3">{{ $s->kelas->nama ?? '—' }}</td>
            <td class="px-5 py-3">{{ $s->orangTua->name ?? '—' }}</td>
            <td class="px-5 py-3 text-right whitespace-nowrap">
                @if ($s->orangTua)
                    <form method="POST" action="{{ route('admin.pengguna.masuk', $s->orangTua) }}" class="inline">@csrf
                        <button class="p-2 rounded-lg text-amber-600 hover:bg-sun-soft inline-flex" aria-label="Lihat portal orang tua {{ $s->nama }}" title="Lihat portal orang tua"><i data-lucide="eye" class="w-4 h-4"></i></button>
                    </form>
                @endif
                @include('partials.edit-link', ['href' => route('admin.siswa.edit', $s)])
                @include('partials.hapus', ['action' => route('admin.siswa.destroy', $s), 'confirm' => "Hapus data {$s->nama} beserta absensi dan laporan perkembangannya?"])
            </td>
        </tr>
    @empty
        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">Tidak ada siswa yang cocok. Tambahkan siswa atau ubah pencarian.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-5">{{ $siswa->links() }}</div>
@endsection
