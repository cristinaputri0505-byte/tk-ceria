@extends('layouts.dashboard')
@section('title', 'E-Learning Anak')
@section('heading', $tugas->exists ? 'Ubah tugas' : 'Tambah tugas')
@section('content')
@php
    $isAdmin = auth()->user()->role === 'admin';
    $d = ($tugas->data ?? []) + \App\Http\Controllers\ElearningController::contohData();
    $tipe = old('tipe', $tugas->tipe);
    $kotak = 'rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-sky outline-none w-full';
    $pasangan = $d['pasangan'] ?? [];
@endphp
<form method="POST" action="{{ $tugas->exists ? route('elearning.update', $tugas) : route('elearning.store') }}" class="max-w-4xl space-y-5" id="form-tugas">
    @csrf @if ($tugas->exists) @method('PUT') @endif

    {{-- Jenis permainan --}}
    <div class="bg-white rounded-3xl border border-slate-100 p-6">
        <p class="text-sm font-bold text-navy mb-3">Jenis permainan</p>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach (\App\Models\ElearningTugas::TIPE as $kode => [$namaTipe, $emoji])
                <label class="cursor-pointer">
                    <input type="radio" name="tipe" value="{{ $kode }}" class="peer sr-only" @checked($tipe === $kode)>
                    <span class="block text-center rounded-2xl border-2 border-slate-200 p-3 peer-checked:border-sky peer-checked:ring-4 peer-checked:ring-sky/30 hover:border-sky/40">
                        <span class="block text-3xl">{{ $emoji }}</span>
                        <span class="block text-sm font-bold text-navy mt-1">{{ $namaTipe }}</span>
                    </span>
                </label>
            @endforeach
        </div>
        @error('tipe')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
    </div>

    {{-- Informasi umum --}}
    <div class="bg-white rounded-3xl border border-slate-100 p-6 grid sm:grid-cols-6 gap-4">
        @include('partials.field', ['name' => 'judul', 'label' => 'Judul tugas', 'value' => $tugas->judul, 'class' => 'sm:col-span-4', 'attrs' => 'maxlength=100', 'hint' => 'Contoh: Tebak Hewan Kesukaan'])
        @include('partials.field', ['name' => 'ikon', 'label' => 'Ikon (emoji)', 'value' => $tugas->ikon, 'class' => 'sm:col-span-1', 'attrs' => 'maxlength=16'])
        @include('partials.field', ['name' => 'bintang', 'label' => 'Hadiah ⭐', 'type' => 'number', 'value' => $tugas->bintang, 'class' => 'sm:col-span-1', 'attrs' => 'min=1 max=10'])
        @include('partials.field', ['name' => 'instruksi', 'label' => 'Instruksi / pertanyaan (dibacakan dengan suara)', 'value' => $tugas->instruksi, 'class' => 'sm:col-span-6', 'attrs' => 'maxlength=300', 'hint' => 'Contoh: Hewan manakah yang punya belalai panjang?'])
        @include('partials.select', ['name' => 'kelas_id', 'label' => 'Untuk kelas', 'value' => $tugas->kelas_id, 'class' => 'sm:col-span-2',
            'options' => $kelasList->pluck('nama', 'id')->all(), 'required' => ! $isAdmin] + ($isAdmin ? ['placeholder' => 'Semua kelas'] : []))
        @include('partials.select', ['name' => 'warna', 'label' => 'Warna kartu', 'value' => $tugas->warna, 'class' => 'sm:col-span-2',
            'options' => collect(\App\Models\ElearningTugas::WARNA)->map(fn ($w) => $w[1] . ' ' . $w[0])->all()])
        @include('partials.field', ['name' => 'urutan', 'label' => 'Nomor urut', 'type' => 'number', 'value' => $tugas->urutan ?? 0, 'class' => 'sm:col-span-1', 'required' => false, 'attrs' => 'min=0 max=999'])
        <label class="sm:col-span-1 flex items-end gap-2 pb-3 text-sm font-bold text-navy cursor-pointer">
            <input type="checkbox" name="aktif" value="1" class="w-5 h-5 rounded" @checked(old('aktif', $tugas->exists ? $tugas->aktif : true))> Tampil
        </label>
    </div>

    {{-- Pengaturan per jenis --}}
    <div class="bg-white rounded-3xl border border-slate-100 p-6">
        <fieldset data-tipe="kuis" class="space-y-4">
            <legend class="font-display text-lg font-semibold text-navy mb-2">❓ Soal tebak gambar</legend>
            @include('partials.field', ['name' => 'kuis_gambar', 'label' => 'Gambar soal (emoji besar di tengah)', 'value' => $d['gambar'], 'required' => false, 'attrs' => 'maxlength=40'])
            <div class="grid sm:grid-cols-2 gap-3">
                @foreach (range(0, 3) as $i)
                    <div>
                        <label class="block text-sm font-bold text-navy mb-1.5" for="kuis_opsi_{{ $i }}">Pilihan {{ chr(65 + $i) }}</label>
                        <input id="kuis_opsi_{{ $i }}" name="kuis_opsi[{{ $i }}]" value="{{ old("kuis_opsi.$i", $d['opsi'][$i] ?? '') }}" maxlength="40" class="{{ $kotak }}">
                    </div>
                @endforeach
            </div>
            @error('kuis_opsi.*')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
            @include('partials.select', ['name' => 'kuis_benar', 'label' => 'Kunci jawaban', 'value' => (string) ($d['benar'] ?? 0), 'required' => false,
                'options' => ['0' => 'Pilihan A', '1' => 'Pilihan B', '2' => 'Pilihan C', '3' => 'Pilihan D'], 'placeholder' => 'Pilih…', 'class' => 'max-w-xs'])
        </fieldset>

        <fieldset data-tipe="hitung" class="space-y-4">
            <legend class="font-display text-lg font-semibold text-navy mb-2">🔢 Soal berhitung</legend>
            <div class="grid sm:grid-cols-3 gap-3">
                @include('partials.field', ['name' => 'hitung_emoji', 'label' => 'Emoji benda', 'value' => $d['emoji'], 'required' => false, 'attrs' => 'maxlength=16'])
                @include('partials.field', ['name' => 'hitung_nama', 'label' => 'Nama benda', 'value' => $d['nama'], 'required' => false, 'attrs' => 'maxlength=40'])
                @include('partials.field', ['name' => 'hitung_jumlah', 'label' => 'Jumlah benar (1–15)', 'type' => 'number', 'value' => $d['jumlah'], 'required' => false, 'attrs' => 'min=1 max=15'])
            </div>
            <p class="text-xs text-slate-500">Pilihan angka pengecoh dibuat otomatis.</p>
        </fieldset>

        <fieldset data-tipe="cocok" class="space-y-3">
            <legend class="font-display text-lg font-semibold text-navy mb-2">🧩 Pasangan (2–4 pasang)</legend>
            @foreach (range(0, 3) as $i)
                <div class="grid grid-cols-2 gap-3">
                    <input name="cocok_kiri[{{ $i }}]" value="{{ old("cocok_kiri.$i", $pasangan[$i]['kiri'] ?? '') }}" maxlength="40" placeholder="Kiri {{ $i + 1 }}{{ $i > 1 ? ' (opsional)' : '' }}" class="{{ $kotak }}" aria-label="Kiri {{ $i + 1 }}">
                    <input name="cocok_kanan[{{ $i }}]" value="{{ old("cocok_kanan.$i", $pasangan[$i]['kanan'] ?? '') }}" maxlength="40" placeholder="Pasangan kanan {{ $i + 1 }}" class="{{ $kotak }}" aria-label="Kanan {{ $i + 1 }}">
                </div>
            @endforeach
            @error('cocok_kiri')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
            <p class="text-xs text-slate-500">Urutan kolom kanan diacak otomatis saat dimainkan.</p>
        </fieldset>

        <fieldset data-tipe="gambar" class="space-y-3">
            <legend class="font-display text-lg font-semibold text-navy mb-2">🎨 Kanvas menggambar</legend>
            @include('partials.field', ['name' => 'gambar_stiker', 'label' => 'Stiker yang tersedia (pisahkan dengan spasi, maks. 12)', 'value' => implode(' ', $d['stiker']), 'required' => false, 'attrs' => 'maxlength=200'])
            <p class="text-xs text-slate-500">Anak menggambar bebas dengan kuas warna-warni dan menempel stiker. Hasil gambar tersimpan dan bisa dilihat guru di <b>Rekap anak</b>.</p>
        </fieldset>
    </div>

    @include('partials.form-actions', ['label' => 'Simpan tugas', 'back' => route('elearning.index', ['kelas_id' => $tugas->kelas_id])])
</form>
<script>
(function () {
    const form = document.getElementById('form-tugas');
    function tampilkan() {
        const tipe = (form.querySelector('input[name=tipe]:checked') || {}).value;
        form.querySelectorAll('fieldset[data-tipe]').forEach(f => {
            const aktif = f.dataset.tipe === tipe;
            f.hidden = !aktif;
            f.disabled = !aktif; // kolom jenis lain tidak ikut dikirim
        });
    }
    form.addEventListener('change', e => { if (e.target.name === 'tipe') tampilkan(); });
    tampilkan();
})();
</script>
@endsection
