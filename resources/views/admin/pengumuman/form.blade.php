@extends('layouts.dashboard')
@section('title', 'Pengumuman')
@section('heading', $pengumuman->exists ? 'Ubah pengumuman' : 'Tulis pengumuman')
@section('content')
<form method="POST" action="{{ $pengumuman->exists ? route('admin.pengumuman.update', $pengumuman) : route('admin.pengumuman.store') }}" class="bg-white rounded-3xl border border-slate-100 p-6 grid sm:grid-cols-2 gap-5 max-w-3xl">
    @csrf @if ($pengumuman->exists) @method('PUT') @endif
    @include('partials.field', ['name' => 'judul', 'label' => 'Judul', 'value' => $pengumuman->judul, 'class' => 'sm:col-span-2'])
    @include('partials.textarea', ['name' => 'isi', 'label' => 'Isi pengumuman', 'value' => $pengumuman->isi, 'rows' => 6, 'class' => 'sm:col-span-2'])
    @include('partials.select', ['name' => 'kelas_id', 'label' => 'Ditujukan untuk', 'value' => $pengumuman->kelas_id, 'required' => false, 'placeholder' => 'Semua kelas', 'options' => $kelas->mapWithKeys(fn ($k) => [$k->id => 'Kelas '.$k->nama])->all()])
    @include('partials.field', ['name' => 'tanggal_acara', 'label' => 'Tanggal acara (opsional)', 'type' => 'date', 'value' => $pengumuman->tanggal_acara?->format('Y-m-d'), 'required' => false, 'hint' => 'Jika diisi, tampil di Agenda orang tua.'])
    <label class="sm:col-span-2 flex items-center gap-2 text-sm font-bold text-navy">
        <input type="checkbox" name="penting" value="1" class="accent-berry w-4 h-4" @checked(old('penting', $pengumuman->penting))> Tandai sebagai penting (diberi label merah)
    </label>
    <div class="sm:col-span-2">@include('partials.form-actions', ['back' => route('admin.pengumuman.index'), 'label' => $pengumuman->exists ? 'Simpan' : 'Kirim pengumuman'])</div>
</form>
@endsection
