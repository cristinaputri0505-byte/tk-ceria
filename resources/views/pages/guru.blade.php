@extends('layouts.public')
@section('title', $site['guru_banner_judul'].' — '.$site['nama_sekolah'])
@section('content')
@include('partials.page-banner', ['p' => 'guru', 'crumb' => 'Guru & Staff'])
<div class="max-w-7xl mx-auto px-4 lg:px-8 py-12">
    <ul class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse ($staff as $s)
            <li class="bg-white rounded-3xl p-6 border border-slate-100">@include('partials.staff-card', ['s' => $s, 'besar' => true])</li>
        @empty
            <li class="col-span-full text-slate-500">Data guru dan staff belum ditambahkan.</li>
        @endforelse
    </ul>
</div>
@endsection
