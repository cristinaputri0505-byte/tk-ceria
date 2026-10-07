@extends('layouts.public')
@section('title', $site['kegiatan_banner_judul'].' — '.$site['nama_sekolah'])
@section('content')
@include('partials.page-banner', ['p' => 'kegiatan', 'crumb' => 'Kegiatan'])
<div class="max-w-7xl mx-auto px-4 lg:px-8 py-12">
    <ul class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse ($kegiatan as $k)
            <li>@include('partials.kegiatan-card', ['k' => $k, 'tanggal' => true])</li>
        @empty
            <li class="col-span-full text-slate-500">Belum ada kegiatan yang dibagikan.</li>
        @endforelse
    </ul>
    <div class="mt-8">{{ $kegiatan->links() }}</div>
</div>
@endsection
