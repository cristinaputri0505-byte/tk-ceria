@extends('layouts.dashboard')
@section('title', 'Laporan '.$siswa->nama)
@section('heading', 'Laporan perkembangan '.$siswa->nama)
@section('content')
@php $rp = auth()->user()->role === 'admin' ? 'admin.' : 'guru.'; $isAdmin = $rp === 'admin.'; @endphp
<p class="text-sm text-slate-500 mb-5">{{ $siswa->kelas->nama }} · {{ $siswa->usia }} · Periode {{ $periode }}</p>

<details class="bg-sky-soft rounded-2xl p-4 mb-5 text-sm">
    <summary class="font-bold text-navy cursor-pointer">Keterangan nilai</summary>
    <ul class="mt-2 grid sm:grid-cols-2 gap-1">
        @foreach (\App\Models\Perkembangan::NILAI as $k => $v)<li><b>{{ $k }}</b> — {{ $v }}</li>@endforeach
    </ul>
</details>

<form method="POST" action="{{ route($rp.'perkembangan.store', $siswa) }}" class="space-y-4">
    @csrf
    <input type="hidden" name="periode" value="{{ $periode }}">
    @foreach (\App\Models\Perkembangan::ASPEK as $kode => $label)
        @php $cur = $nilai[$kode] ?? null; @endphp
        <fieldset class="bg-white rounded-3xl border border-slate-100 p-5">
            <legend class="sr-only">{{ $label }}</legend>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-extrabold text-navy">{{ $label }}</h2>
                <div class="flex gap-1.5" role="radiogroup" aria-label="Nilai {{ $label }}">
                    @foreach (\App\Models\Perkembangan::NILAI as $n => $desc)
                        <label class="cursor-pointer" title="{{ $desc }}">
                            <input type="radio" class="peer sr-only" name="nilai[{{ $kode }}]" value="{{ $n }}" @checked(old("nilai.$kode", $cur->nilai ?? null) === $n)>
                            <span class="block w-14 text-center py-1.5 rounded-lg border border-slate-200 text-xs font-extrabold text-slate-500 peer-checked:bg-navy peer-checked:text-white peer-checked:border-navy peer-focus-visible:ring-2 peer-focus-visible:ring-sun">{{ $n }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <label for="c_{{ $kode }}" class="sr-only">Catatan {{ $label }}</label>
            <textarea id="c_{{ $kode }}" name="catatan[{{ $kode }}]" rows="2" placeholder="Catatan untuk orang tua (opsional)" class="mt-3 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">{{ old("catatan.$kode", $cur->catatan ?? '') }}</textarea>
        </fieldset>
    @endforeach
    @include('partials.form-actions', ['back' => route($rp.'perkembangan.index', ['periode' => $periode]), 'label' => 'Simpan laporan'])
</form>
@endsection
