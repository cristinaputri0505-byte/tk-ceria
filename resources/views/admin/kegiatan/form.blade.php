@extends('layouts.dashboard')
@section('title', 'Kegiatan')
@section('heading', $kegiatan->exists ? 'Ubah kegiatan' : 'Tambah kegiatan')
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $kegiatan->exists ? route('admin.kegiatan.update', $kegiatan) : route('admin.kegiatan.store') }}" class="bg-white rounded-3xl border border-slate-100 p-6 grid sm:grid-cols-2 gap-5 max-w-3xl">
    @csrf @if ($kegiatan->exists) @method('PUT') @endif
    @include('partials.field', ['name' => 'judul', 'label' => 'Nama kegiatan', 'value' => $kegiatan->judul])
    @include('partials.field', ['name' => 'tanggal', 'label' => 'Tanggal', 'type' => 'date', 'value' => $kegiatan->tanggal?->format('Y-m-d')])
    @include('partials.textarea', ['name' => 'deskripsi', 'label' => 'Deskripsi', 'value' => $kegiatan->deskripsi, 'required' => false, 'class' => 'sm:col-span-2'])
    @include('partials.file', ['name' => 'gambar', 'label' => 'Foto kegiatan', 'current' => $kegiatan->gambar, 'max' => 4, 'class' => 'sm:col-span-2'])
    <div class="sm:col-span-2">@include('partials.form-actions', ['back' => route('admin.kegiatan.index')])</div>
</form>
@endsection
