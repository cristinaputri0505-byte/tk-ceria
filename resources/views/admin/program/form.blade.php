@extends('layouts.dashboard')
@section('title', 'Program')
@section('heading', $program->exists ? 'Ubah program' : 'Tambah program')
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $program->exists ? route('admin.program.update', $program) : route('admin.program.store') }}" class="bg-white rounded-3xl border border-slate-100 p-6 grid sm:grid-cols-2 gap-5 max-w-3xl">
    @csrf @if ($program->exists) @method('PUT') @endif
    @include('partials.field', ['name' => 'nama', 'label' => 'Nama program', 'value' => $program->nama, 'hint' => 'Contoh: Kelompok A'])
    @include('partials.field', ['name' => 'keterangan', 'label' => 'Keterangan singkat', 'value' => $program->keterangan, 'required' => false, 'hint' => 'Contoh: Usia 4–5 tahun'])
    @include('partials.field', ['name' => 'deskripsi', 'label' => 'Deskripsi', 'value' => $program->deskripsi, 'required' => false, 'class' => 'sm:col-span-2'])
    @include('partials.select', ['name' => 'ikon', 'label' => 'Ikon', 'value' => $program->ikon ?? 'star', 'options' => \App\Support\Pengaturan::IKON])
    @include('partials.select', ['name' => 'warna', 'label' => 'Warna kartu', 'value' => $program->warna ?? 'sky', 'options' => \App\Http\Controllers\Admin\ProgramController::WARNA])
    @include('partials.field', ['name' => 'urutan', 'label' => 'Nomor urut', 'type' => 'number', 'value' => $program->urutan, 'attrs' => 'min=0'])
    @include('partials.textarea', ['name' => 'detail', 'label' => 'Penjelasan lengkap (halaman Program)', 'value' => $program->detail, 'required' => false, 'rows' => 5, 'class' => 'sm:col-span-2'])
    @include('partials.file', ['name' => 'gambar', 'label' => 'Foto program (halaman Program)', 'current' => $program->gambar, 'max' => 4, 'class' => 'sm:col-span-2'])
    @if ($program->gambar)<label class="sm:col-span-2 -mt-3 flex items-center gap-2 text-sm text-rose-600 font-bold"><input type="checkbox" name="hapus_gambar" value="1" class="accent-rose-500"> Hapus foto</label>@endif
    <div class="sm:col-span-2">@include('partials.form-actions', ['back' => route('admin.program.index')])</div>
</form>
@endsection
