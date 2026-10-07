@extends('layouts.dashboard')
@section('title', 'Akun')
@section('heading', $pengguna->exists ? 'Ubah akun '.$pengguna->name : 'Tambah akun')
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $pengguna->exists ? route('admin.pengguna.update', $pengguna) : route('admin.pengguna.store') }}" class="bg-white rounded-3xl border border-slate-100 p-6 grid sm:grid-cols-2 gap-5 max-w-3xl">
    @csrf @if ($pengguna->exists) @method('PUT') @endif
    @include('partials.select', ['name' => 'role', 'label' => 'Jenis akun', 'value' => $pengguna->role, 'options' => $roles])
    @include('partials.field', ['name' => 'name', 'label' => 'Nama lengkap', 'value' => $pengguna->name])
    @include('partials.field', ['name' => 'email', 'label' => 'Email (dipakai untuk masuk)', 'type' => 'email', 'value' => $pengguna->email])
    @include('partials.field', ['name' => 'telepon', 'label' => 'Nomor HP', 'type' => 'tel', 'value' => $pengguna->telepon, 'required' => false])
    @include('partials.field', ['name' => 'jabatan', 'label' => 'Jabatan (untuk guru)', 'value' => $pengguna->jabatan, 'required' => false, 'hint' => 'Tampil di halaman Guru & Staff.'])
    @include('partials.field', ['name' => 'password', 'label' => $pengguna->exists ? 'Kata sandi baru' : 'Kata sandi', 'type' => 'password', 'required' => ! $pengguna->exists,
        'hint' => $pengguna->exists ? 'Kosongkan jika tidak ingin mengganti. Minimal 8 karakter.' : 'Minimal 8 karakter.'])
    @include('partials.file', ['name' => 'foto', 'label' => 'Foto profil', 'current' => $pengguna->foto, 'class' => 'sm:col-span-2'])
    <div class="sm:col-span-2">@include('partials.form-actions', ['back' => route('admin.pengguna.index', ['role' => $pengguna->role])])</div>
</form>
@endsection
