@extends('layouts.public')
@section('title', $site['berita_banner_judul'].' — '.$site['nama_sekolah'])
@section('content')
@include('partials.page-banner', ['p' => 'berita', 'crumb' => 'Berita'])
<section class="max-w-7xl mx-auto px-4 lg:px-8 py-12">
    <ul class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($berita as $b)
            <li>@include('berita._card', ['b' => $b])</li>
        @empty
            <li class="text-slate-500">Belum ada berita yang diterbitkan.</li>
        @endforelse
    </ul>
    <div class="mt-8">{{ $berita->links() }}</div>
</section>
@endsection
