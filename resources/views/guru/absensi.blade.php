@extends('layouts.dashboard')
@section('title', 'Absensi')
@section('heading', 'Absensi Siswa')
@section('content')
@php $rp = auth()->user()->role === 'admin' ? 'admin.' : 'guru.'; $isAdmin = $rp === 'admin.'; @endphp
@if (! $kelas)
    <p class="text-slate-500">{{ $isAdmin ? 'Belum ada kelas. Buat kelas di menu Kelas.' : 'Anda belum menjadi wali kelas. Hubungi admin.' }}</p>
@else
<form class="flex flex-wrap items-end gap-3 mb-5">
    <div>
        <label for="kelas_id" class="block text-sm font-bold text-navy mb-1">Kelas</label>
        <select id="kelas_id" name="kelas_id" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm" onchange="this.form.submit()">
            @foreach ($kelasList as $k)<option value="{{ $k->id }}" @selected($k->id === $kelas->id)>{{ $k->nama }}{{ $isAdmin && $k->wali ? ' · '.$k->wali->name : '' }}</option>@endforeach
        </select>
    </div>
    <div>
        <label for="tanggal" class="block text-sm font-bold text-navy mb-1">Tanggal</label>
        <input id="tanggal" type="date" name="tanggal" value="{{ $tanggal->format('Y-m-d') }}" max="{{ today()->format('Y-m-d') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm" onchange="this.form.submit()">
    </div>
    <p class="text-sm text-slate-500 pb-2.5">{{ $tanggal->translatedFormat('l, d F Y') }} · {{ $absensi->isEmpty() ? 'belum diisi' : 'sudah diisi, Anda bisa mengubahnya' }}</p>
</form>

<form method="POST" action="{{ route($rp.'absensi.store') }}">
    @csrf
    <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
    <input type="hidden" name="tanggal" value="{{ $tanggal->format('Y-m-d') }}">

    <div class="flex justify-end mb-3">
        <button type="button" class="text-sm font-bold text-leaf" onclick="document.querySelectorAll('input[value=hadir]').forEach(r=>r.checked=true)">Tandai semua hadir</button>
    </div>

    @include('partials.table-open')
        <thead class="bg-cloud text-left text-navy"><tr>
            <th class="px-5 py-3 font-extrabold">Nama siswa</th><th class="px-5 py-3 font-extrabold">Kehadiran</th><th class="px-5 py-3 font-extrabold">Keterangan</th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($siswa as $s)
            @php $cur = $absensi[$s->id] ?? null; @endphp
            <tr>
                <td class="px-5 py-3 font-bold text-navy whitespace-nowrap">{{ $s->nama }}</td>
                <td class="px-5 py-3">
                    <fieldset class="flex gap-1.5">
                        <legend class="sr-only">Kehadiran {{ $s->nama }}</legend>
                        @foreach (\App\Models\Absensi::STATUS as $kode => $label)
                            <label class="cursor-pointer">
                                <input type="radio" class="peer sr-only" name="status[{{ $s->id }}]" value="{{ $kode }}" @checked(($cur->status ?? 'hadir') === $kode)>
                                <span class="block px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-500
                                    peer-focus-visible:ring-2 peer-focus-visible:ring-sun
                                    {{ ['hadir' => 'peer-checked:bg-leaf', 'izin' => 'peer-checked:bg-sky', 'sakit' => 'peer-checked:bg-amber-500', 'alpa' => 'peer-checked:bg-berry'][$kode] }} peer-checked:text-white peer-checked:border-transparent">{{ $label }}</span>
                            </label>
                        @endforeach
                    </fieldset>
                </td>
                <td class="px-5 py-3"><input name="keterangan[{{ $s->id }}]" value="{{ $cur->keterangan ?? '' }}" placeholder="Opsional" aria-label="Keterangan {{ $s->nama }}" class="w-full min-w-[160px] rounded-lg border border-slate-200 px-3 py-1.5 text-sm"></td>
            </tr>
        @empty
            <tr><td colspan="3" class="px-5 py-10 text-center text-slate-500">Belum ada siswa di kelas ini.</td></tr>
        @endforelse
        </tbody>
    </table></div></div>

    @if ($siswa->isNotEmpty())
        <button class="mt-5 bg-sky text-white font-extrabold px-6 py-3 rounded-xl hover:bg-sky-dark">Simpan absensi</button>
    @endif
</form>
@endif
@endsection
