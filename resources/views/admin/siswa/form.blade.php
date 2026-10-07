@extends('layouts.dashboard')
@section('title', $siswa->exists ? 'Ubah Siswa' : 'Tambah Siswa')
@section('heading', $siswa->exists ? 'Ubah data '.$siswa->nama : 'Tambah siswa')
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $siswa->exists ? route('admin.siswa.update', $siswa) : route('admin.siswa.store') }}" class="space-y-5 max-w-4xl">
    @csrf @if ($siswa->exists) @method('PUT') @endif

    <fieldset class="bg-white rounded-3xl border border-slate-100 p-6 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <legend class="sr-only">Data anak</legend>
        <h2 class="sm:col-span-2 lg:col-span-3 font-display text-lg font-semibold text-navy">Data anak</h2>
        @include('partials.field', ['name' => 'nis', 'label' => 'NIS', 'value' => $siswa->nis])
        @include('partials.field', ['name' => 'nama', 'label' => 'Nama lengkap', 'value' => $siswa->nama])
        @include('partials.field', ['name' => 'panggilan', 'label' => 'Nama panggilan', 'value' => $siswa->panggilan, 'required' => false])
        @include('partials.select', ['name' => 'jenis_kelamin', 'label' => 'Jenis kelamin', 'value' => $siswa->jenis_kelamin, 'options' => ['L' => 'Laki-laki', 'P' => 'Perempuan']])
        @include('partials.field', ['name' => 'tempat_lahir', 'label' => 'Tempat lahir', 'value' => $siswa->tempat_lahir, 'required' => false])
        @include('partials.field', ['name' => 'tanggal_lahir', 'label' => 'Tanggal lahir', 'type' => 'date', 'value' => $siswa->tanggal_lahir?->format('Y-m-d')])
        @include('partials.select', ['name' => 'agama', 'label' => 'Agama', 'value' => $siswa->agama, 'required' => false, 'placeholder' => '—', 'options' => array_combine($a = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'], $a)])
        @include('partials.field', ['name' => 'anak_ke', 'label' => 'Anak ke', 'type' => 'number', 'value' => $siswa->anak_ke, 'required' => false, 'attrs' => 'min=1'])
        @include('partials.field', ['name' => 'tanggal_masuk', 'label' => 'Tanggal masuk', 'type' => 'date', 'value' => $siswa->tanggal_masuk?->format('Y-m-d'), 'required' => false])
        @include('partials.textarea', ['name' => 'alamat', 'label' => 'Alamat', 'value' => $siswa->alamat, 'required' => false, 'rows' => 2, 'class' => 'sm:col-span-2 lg:col-span-3'])
        @include('partials.file', ['name' => 'foto', 'label' => 'Foto anak', 'current' => $siswa->foto, 'class' => 'sm:col-span-2 lg:col-span-3'])
    </fieldset>

    <fieldset class="bg-white rounded-3xl border border-slate-100 p-6 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <legend class="sr-only">Kelas & akun</legend>
        <h2 class="sm:col-span-2 lg:col-span-3 font-display text-lg font-semibold text-navy">Kelas & akun orang tua</h2>
        @include('partials.select', ['name' => 'kelas_id', 'label' => 'Kelas', 'value' => $siswa->kelas_id, 'required' => false, 'placeholder' => 'Belum ditempatkan', 'options' => $kelas->pluck('nama', 'id')->all()])
        @include('partials.select', ['name' => 'orang_tua_id', 'label' => 'Akun login orang tua', 'value' => $siswa->orang_tua_id, 'required' => false, 'placeholder' => 'Belum dihubungkan', 'class' => 'lg:col-span-2', 'options' => $orangTua->mapWithKeys(fn ($o) => [$o->id => $o->name.' ('.$o->email.')'])->all()])
        <p class="sm:col-span-2 lg:col-span-3 text-xs text-slate-500 -mt-2">Akun belum ada? <a href="{{ route('admin.pengguna.create', ['role' => 'orangtua']) }}" class="font-bold text-sky">Buat akun orang tua</a> terlebih dahulu.</p>
    </fieldset>

    <fieldset class="bg-white rounded-3xl border border-slate-100 p-6 grid sm:grid-cols-3 gap-5">
        <legend class="sr-only">Data orang tua</legend>
        <h2 class="sm:col-span-3 font-display text-lg font-semibold text-navy">Data ayah & ibu</h2>
        @include('partials.field', ['name' => 'nama_ayah', 'label' => 'Nama ayah', 'value' => $siswa->nama_ayah, 'required' => false])
        @include('partials.field', ['name' => 'pekerjaan_ayah', 'label' => 'Pekerjaan ayah', 'value' => $siswa->pekerjaan_ayah, 'required' => false])
        @include('partials.field', ['name' => 'telepon_ayah', 'label' => 'Telepon ayah', 'type' => 'tel', 'value' => $siswa->telepon_ayah, 'required' => false])
        @include('partials.field', ['name' => 'nama_ibu', 'label' => 'Nama ibu', 'value' => $siswa->nama_ibu, 'required' => false])
        @include('partials.field', ['name' => 'pekerjaan_ibu', 'label' => 'Pekerjaan ibu', 'value' => $siswa->pekerjaan_ibu, 'required' => false])
        @include('partials.field', ['name' => 'telepon_ibu', 'label' => 'Telepon ibu', 'type' => 'tel', 'value' => $siswa->telepon_ibu, 'required' => false])
    </fieldset>

    <fieldset class="bg-white rounded-3xl border border-slate-100 p-6 grid sm:grid-cols-3 gap-5">
        <legend class="sr-only">Kesehatan</legend>
        <h2 class="sm:col-span-3 font-display text-lg font-semibold text-navy">Kesehatan</h2>
        @include('partials.select', ['name' => 'golongan_darah', 'label' => 'Golongan darah', 'value' => $siswa->golongan_darah, 'required' => false, 'placeholder' => 'Tidak diketahui', 'options' => ['A' => 'A', 'B' => 'B', 'AB' => 'AB', 'O' => 'O']])
        @include('partials.textarea', ['name' => 'catatan_kesehatan', 'label' => 'Alergi / catatan kesehatan', 'value' => $siswa->catatan_kesehatan, 'required' => false, 'rows' => 2, 'class' => 'sm:col-span-2'])
    </fieldset>

    @include('partials.form-actions', ['back' => route('admin.siswa.index')])
</form>
@endsection
