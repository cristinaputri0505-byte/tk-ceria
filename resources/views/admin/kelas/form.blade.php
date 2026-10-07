@extends('layouts.dashboard')
@section('title', 'Kelas')
@section('heading', $kelas->exists ? 'Ubah kelas '.$kelas->nama : 'Tambah kelas')
@section('content')
<form method="POST" action="{{ $kelas->exists ? route('admin.kelas.update', $kelas) : route('admin.kelas.store') }}" class="bg-white rounded-3xl border border-slate-100 p-6 grid sm:grid-cols-2 gap-5 max-w-3xl">
    @csrf @if ($kelas->exists) @method('PUT') @endif
    @include('partials.field', ['name' => 'nama', 'label' => 'Nama kelas', 'value' => $kelas->nama, 'hint' => 'Contoh: A1 Matahari'])
    @include('partials.select', ['name' => 'kelompok', 'label' => 'Kelompok', 'value' => $kelas->kelompok, 'options' => ['A' => 'Kelompok A (4–5 tahun)', 'B' => 'Kelompok B (5–6 tahun)']])
    @include('partials.field', ['name' => 'tahun_ajaran', 'label' => 'Tahun ajaran', 'value' => $kelas->tahun_ajaran, 'hint' => 'Contoh: 2026/2027'])
    @include('partials.select', ['name' => 'wali_guru_id', 'label' => 'Wali kelas', 'value' => $kelas->wali_guru_id, 'required' => false, 'placeholder' => 'Belum ditentukan', 'options' => $guru->pluck('name', 'id')->all()])
    <div class="sm:col-span-2">@include('partials.form-actions', ['back' => route('admin.kelas.index')])</div>
</form>
@endsection
