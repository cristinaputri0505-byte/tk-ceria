@extends('layouts.dashboard')
@section('title', 'Berita')
@section('heading', $berita->exists ? 'Ubah berita' : 'Tulis berita')
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $berita->exists ? route('admin.berita.update', $berita) : route('admin.berita.store') }}" class="bg-white rounded-3xl border border-slate-100 p-6 space-y-5 max-w-3xl">
    @csrf @if ($berita->exists) @method('PUT') @endif
    @include('partials.field', ['name' => 'judul', 'label' => 'Judul', 'value' => $berita->judul])
    @include('partials.field', ['name' => 'ringkasan', 'label' => 'Ringkasan singkat', 'value' => $berita->ringkasan, 'required' => false, 'hint' => 'Tampil di kartu berita. Maksimal 255 karakter.'])
    @include('partials.textarea', ['name' => 'isi', 'label' => 'Isi berita', 'value' => $berita->isi, 'rows' => 10])
    @include('partials.file', ['name' => 'gambar', 'label' => 'Gambar utama', 'current' => $berita->gambar, 'max' => 4])
    <label class="flex items-center gap-2 text-sm font-bold text-navy">
        <input type="hidden" name="terbitkan" value="0">
        <input type="checkbox" name="terbitkan" value="1" class="accent-sky w-4 h-4" @checked(old('terbitkan', $berita->published_at ? 1 : 0))> Terbitkan di website
    </label>
    @include('partials.form-actions', ['back' => route('admin.berita.index')])
</form>
@endsection
