@extends('layouts.dashboard')
@section('title', 'Kegiatan')
@section('heading', $kegiatan->exists ? 'Ubah kegiatan' : 'Tambah kegiatan')
@section('content')
@php
    $albumAktif = \App\Models\Kegiatan::albumAktif();
    $maks = \App\Http\Controllers\Admin\KegiatanController::MAKS_FOTO_SEKALI;
@endphp
<form method="POST" enctype="multipart/form-data" action="{{ $kegiatan->exists ? route('admin.kegiatan.update', $kegiatan) : route('admin.kegiatan.store') }}" class="bg-white rounded-3xl border border-slate-100 p-6 grid sm:grid-cols-2 gap-5 max-w-4xl">
    @csrf @if ($kegiatan->exists) @method('PUT') @endif
    @include('partials.field', ['name' => 'judul', 'label' => 'Nama kegiatan', 'value' => $kegiatan->judul])
    @include('partials.field', ['name' => 'tanggal', 'label' => 'Tanggal', 'type' => 'date', 'value' => $kegiatan->tanggal?->format('Y-m-d')])
    @include('partials.textarea', ['name' => 'deskripsi', 'label' => 'Deskripsi', 'value' => $kegiatan->deskripsi, 'required' => false, 'class' => 'sm:col-span-2'])

    @if ($albumAktif)
        <div class="sm:col-span-2">
            <p class="block text-sm font-bold text-navy mb-1.5">Foto kegiatan</p>

            @if ($album->isNotEmpty())
                <p class="text-xs text-slate-500 mb-3">{{ $album->count() }} foto. Pilih <b>Sampul</b> untuk foto yang tampil di kartu kegiatan. Centang <b>Hapus</b> untuk membuang foto.</p>
                <ul class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
                    @foreach ($album as $f)
                        <li class="rounded-2xl border border-slate-100 overflow-hidden">
                            <img src="{{ $f->url }}" alt="" class="w-full aspect-[4/3] object-cover" loading="lazy">
                            <div class="p-2 flex items-center justify-between gap-2 text-xs font-bold">
                                <label class="inline-flex items-center gap-1 text-navy cursor-pointer">
                                    <input type="radio" name="sampul" value="{{ $f->id }}" @checked(old('sampul') ? (int) old('sampul') === $f->id : $f->gambar === $kegiatan->gambar)> Sampul
                                </label>
                                <label class="inline-flex items-center gap-1 text-rose-600 cursor-pointer">
                                    <input type="checkbox" name="hapus_foto[]" value="{{ $f->id }}" @checked(in_array($f->id, (array) old('hapus_foto', [])))> Hapus
                                </label>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @elseif ($kegiatan->exists)
                <p class="text-sm text-amber-700 bg-amber-50 rounded-xl px-4 py-2 mb-3">Kegiatan ini belum punya foto, sehingga kartunya tampil kosong di website.</p>
            @endif

            <label for="f_foto" class="block text-sm font-bold text-navy mb-1.5">{{ $album->isNotEmpty() ? 'Tambah foto' : 'Unggah foto' }}</label>
            <input id="f_foto" name="foto[]" type="file" accept="image/*" multiple
                class="block w-full text-sm file:mr-3 file:rounded-full file:border-0 file:bg-sky-soft file:px-4 file:py-2 file:font-bold file:text-sky">
            <p class="text-xs text-slate-500 mt-1">Bisa pilih beberapa foto sekaligus (maksimal {{ $maks }} sekali simpan; simpan dulu lalu tambah lagi bila lebih). Foto besar dari HP otomatis diperkecil. Foto pertama otomatis menjadi sampul bila belum ada.</p>
            @error('foto')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            @error('foto.*')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>
    @else
        <div class="sm:col-span-2">
            <p class="text-sm text-amber-700 bg-amber-50 rounded-xl px-4 py-2 mb-3">Untuk mengunggah banyak foto per kegiatan, perbarui database dulu di <a href="{{ route('admin.cek-sistem') }}" class="underline font-bold">Cek Sistem</a>.</p>
            @include('partials.file', ['name' => 'gambar', 'label' => 'Foto kegiatan', 'current' => $kegiatan->gambar, 'max' => 4])
            @error('foto')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>
    @endif

    <div class="sm:col-span-2">@include('partials.form-actions', ['back' => route('admin.kegiatan.index')])</div>
</form>
@endsection
