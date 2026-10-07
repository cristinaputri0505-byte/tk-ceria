@extends('layouts.dashboard')
@section('title', 'Buat Tagihan')
@section('heading', 'Buat tagihan')
@section('content')
<form method="POST" action="{{ route('admin.tagihan.store') }}" class="bg-white rounded-3xl border border-slate-100 p-6 grid sm:grid-cols-2 gap-5 max-w-3xl">
    @csrf
    @include('partials.select', ['name' => 'jenis', 'label' => 'Jenis tagihan', 'value' => 'SPP', 'options' => array_combine(\App\Models\Tagihan::JENIS, \App\Models\Tagihan::JENIS)])
    @include('partials.field', ['name' => 'bulan', 'label' => 'Bulan', 'type' => 'month', 'value' => now()->format('Y-m'), 'hint' => 'Nama periode otomatis, misal "'.now()->translatedFormat('F Y').'".'])
    @include('partials.field', ['name' => 'jumlah', 'label' => 'Jumlah (Rp)', 'type' => 'number', 'value' => $nominal ?: '', 'attrs' => 'min=1000 step=1000'])
    @include('partials.field', ['name' => 'jatuh_tempo', 'label' => 'Jatuh tempo', 'type' => 'date', 'value' => now()->startOfMonth()->addDays(9)->format('Y-m-d'), 'required' => false])
    @include('partials.field', ['name' => 'keterangan', 'label' => 'Nama periode khusus (opsional)', 'required' => false, 'class' => 'sm:col-span-2', 'hint' => 'Isi hanya untuk tagihan non-bulanan, misal "Semester 1 2026/2027" atau "Outbound 2026".'])

    <fieldset class="sm:col-span-2">
        <legend class="text-sm font-bold text-navy mb-2">Tagihkan kepada</legend>
        @php $target = old('target', 'semua'); @endphp
        <div class="grid sm:grid-cols-3 gap-2">
            @foreach (['semua' => 'Semua siswa', 'kelas' => 'Satu kelas', 'siswa' => 'Satu siswa'] as $k => $v)
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold cursor-pointer has-[:checked]:border-sky has-[:checked]:bg-sky-soft">
                    <input type="radio" name="target" value="{{ $k }}" @checked($target === $k) class="accent-sky" onchange="pilihTarget(this.value)"> {{ $v }}
                </label>
            @endforeach
        </div>
    </fieldset>
    <div id="t_kelas" class="sm:col-span-2" @if($target !== 'kelas') hidden @endif>
        @include('partials.select', ['name' => 'kelas_id', 'label' => 'Kelas', 'required' => false, 'placeholder' => 'Pilih kelas', 'options' => $kelas->mapWithKeys(fn ($k) => [$k->id => $k->nama.' ('.$k->siswa_count.' siswa)'])->all()])
    </div>
    <div id="t_siswa" class="sm:col-span-2" @if($target !== 'siswa') hidden @endif>
        @include('partials.select', ['name' => 'siswa_id', 'label' => 'Siswa', 'required' => false, 'placeholder' => 'Pilih siswa', 'options' => $siswa->mapWithKeys(fn ($s) => [$s->id => $s->nama.' · '.($s->kelas->nama ?? 'tanpa kelas')])->all()])
    </div>
    <p class="sm:col-span-2 text-xs text-slate-500">Siswa yang sudah punya tagihan dengan jenis dan periode yang sama akan dilewati, jadi aman jika tombol ditekan dua kali.</p>
    <div class="sm:col-span-2">@include('partials.form-actions', ['back' => route('admin.tagihan.index'), 'label' => 'Buat tagihan'])</div>
</form>
@endsection
@push('scripts')
<script>
    function pilihTarget(v) {
        document.getElementById('t_kelas').hidden = v !== 'kelas';
        document.getElementById('t_siswa').hidden = v !== 'siswa';
    }
</script>
@endpush
