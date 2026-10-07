@extends('layouts.public')
@section('title', 'Pendaftaran Murid Baru — '.$site['nama_sekolah'])
@section('content')
@include('partials.page-banner', ['p' => 'daftar', 'crumb' => 'Pendaftaran'])
@php $syarat = \App\Support\Pengaturan::baris('daftar_syarat'); @endphp
<div class="max-w-6xl mx-auto px-4 lg:px-8 py-12 grid lg:grid-cols-[1fr_300px] gap-8 items-start">
<section>
    <p class="text-slate-600">{{ $site['daftar_form_deskripsi'] }}</p>

    <div class="mt-6">@include('partials.flash')</div>

    <form method="POST" action="{{ route('pendaftaran.store') }}" class="bg-white rounded-3xl border border-slate-100 p-6 sm:p-8 space-y-8">
        @csrf
        <fieldset class="grid sm:grid-cols-2 gap-5">
            <legend class="font-display text-xl font-semibold text-navy mb-4">Data anak</legend>
            @include('partials.field', ['name' => 'nama_anak', 'label' => 'Nama lengkap anak', 'class' => 'sm:col-span-2'])
            @include('partials.field', ['name' => 'tanggal_lahir', 'label' => 'Tanggal lahir', 'type' => 'date'])
            @include('partials.select', ['name' => 'jenis_kelamin', 'label' => 'Jenis kelamin', 'options' => ['L' => 'Laki-laki', 'P' => 'Perempuan']])
            @include('partials.select', ['name' => 'program', 'label' => 'Program yang dipilih', 'class' => 'sm:col-span-2',
                'options' => $programs->mapWithKeys(fn ($p) => [$p->nama => $p->nama.' ('.$p->keterangan.')'])->all()])
        </fieldset>
        <fieldset class="grid sm:grid-cols-2 gap-5">
            <legend class="font-display text-xl font-semibold text-navy mb-4">Data orang tua / wali</legend>
            @include('partials.field', ['name' => 'nama_orang_tua', 'label' => 'Nama orang tua / wali', 'class' => 'sm:col-span-2'])
            @include('partials.field', ['name' => 'telepon', 'label' => 'Nomor HP / WhatsApp', 'type' => 'tel'])
            @include('partials.field', ['name' => 'email', 'label' => 'Email (opsional)', 'type' => 'email', 'required' => false])
            @include('partials.textarea', ['name' => 'alamat', 'label' => 'Alamat rumah', 'class' => 'sm:col-span-2'])
        </fieldset>
        <button class="w-full sm:w-auto inline-flex justify-center items-center gap-2 bg-sky text-white font-extrabold px-8 py-3.5 rounded-xl hover:bg-sky-dark">
            Kirim Pendaftaran <i data-lucide="send" class="w-4 h-4"></i>
        </button>
    </form>
</section>
<aside class="bg-sun-soft rounded-3xl p-6 lg:sticky lg:top-24">
    @if ($syarat)
        <h2 class="font-display text-xl font-semibold text-navy">Persyaratan</h2>
        <ul class="mt-3 space-y-2 text-sm">
            @foreach ($syarat as $sy)<li class="flex gap-2"><i data-lucide="file-check" class="w-4 h-4 text-leaf shrink-0 mt-0.5"></i>{{ $sy }}</li>@endforeach
        </ul>
    @endif
    <h2 class="font-display text-xl font-semibold text-navy {{ $syarat ? 'mt-6' : '' }}">Butuh bantuan?</h2>
    <p class="text-sm mt-2">Hubungi kami di <a href="{{ route('kontak') }}" class="font-bold text-sky">{{ $site['whatsapp'] ?: $site['telepon'] }}</a>.</p>
</aside>
</div>
@endsection
