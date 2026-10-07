@extends('layouts.public')
@section('title', $site['kontak_banner_judul'].' — '.$site['nama_sekolah'])
@section('content')
@include('partials.page-banner', ['p' => 'kontak', 'crumb' => 'Kontak'])
@php
    $peta = \App\Support\Pengaturan::peta();
    $wa = preg_replace('/^0/', '62', preg_replace('/\D/', '', (string) $site['whatsapp']));
@endphp
<div class="max-w-7xl mx-auto px-4 lg:px-8 py-12 grid lg:grid-cols-5 gap-8">
    <section class="lg:col-span-2 space-y-4">
        @foreach ([
            ['map-pin', 'Alamat', nl2br(e($site['alamat'])), null],
            ['clock', 'Jam operasional', e($site['jam_operasional']), null],
            ['phone', 'Telepon', e($site['telepon']), $site['telepon'] ? 'tel:'.preg_replace('/[^\d+]/', '', $site['telepon']) : null],
            ['message-circle', 'WhatsApp', e($site['whatsapp']), $site['whatsapp'] ? 'https://wa.me/'.$wa : null],
            ['mail', 'Email', e($site['email']), $site['email'] ? 'mailto:'.$site['email'] : null],
        ] as [$ic, $lbl, $isi, $href])
            @if ($isi)
                <div class="flex gap-4 bg-white rounded-3xl p-5 border border-slate-100">
                    <span class="w-12 h-12 shrink-0 grid place-items-center rounded-full bg-sky-soft text-sky"><i data-lucide="{{ $ic }}" class="w-5 h-5"></i></span>
                    <div>
                        <p class="text-sm font-bold text-slate-500">{{ $lbl }}</p>
                        @if ($href)<a href="{{ $href }}" target="_blank" rel="noopener" class="font-extrabold text-navy hover:text-sky">{!! $isi !!}</a>@else<p class="font-extrabold text-navy">{!! $isi !!}</p>@endif
                    </div>
                </div>
            @endif
        @endforeach
    </section>

    <section class="lg:col-span-3 bg-white rounded-3xl p-6 sm:p-8 border border-slate-100">
        <h2 class="font-display text-2xl font-semibold text-navy">{{ $site['kontak_form_judul'] }}</h2>
        <p class="text-sm text-slate-500 mt-1">{{ $site['kontak_form_deskripsi'] }}</p>
        <div class="mt-5">@include('partials.flash')</div>
        <form method="POST" action="{{ route('kontak.kirim') }}" class="grid sm:grid-cols-2 gap-4">
            @csrf
            @include('partials.field', ['name' => 'nama', 'label' => 'Nama', 'class' => 'sm:col-span-2'])
            @include('partials.field', ['name' => 'telepon', 'label' => 'Nomor WhatsApp', 'type' => 'tel', 'required' => false])
            @include('partials.field', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => false])
            @include('partials.textarea', ['name' => 'pesan', 'label' => 'Pesan', 'rows' => 5, 'class' => 'sm:col-span-2'])
            <div class="sm:col-span-2"><button class="inline-flex items-center gap-2 bg-sky text-white font-extrabold px-6 py-3 rounded-xl hover:bg-sky-dark">Kirim pesan <i data-lucide="send" class="w-4 h-4"></i></button></div>
        </form>
    </section>

    @if ($peta)
        <section class="lg:col-span-5 rounded-3xl overflow-hidden border border-slate-100 bg-white">
            <iframe src="{{ $peta }}" title="Peta lokasi {{ $site['nama_sekolah'] }}" class="w-full h-[380px]" style="border:0" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
        </section>
    @endif
</div>
@endsection
