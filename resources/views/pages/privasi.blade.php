@extends('layouts.public')
@section('title', $site['privasi_banner_judul'].' — '.$site['nama_sekolah'])
@section('content')
@include('partials.page-banner', ['p' => 'privasi', 'crumb' => 'Kebijakan Privasi'])
<article class="max-w-3xl mx-auto px-4 py-12">
    @if ($site['privasi_berlaku'])<p class="text-sm font-bold text-slate-500 mb-6">Berlaku sejak {{ $site['privasi_berlaku'] }}</p>@endif
    <div class="bg-white rounded-3xl border border-slate-100 p-6 sm:p-10 space-y-4 text-[15px] leading-relaxed">
        @foreach (preg_split("/\r?\n\s*\r?\n/", trim($site['privasi_isi'])) as $blok)
            @foreach (preg_split("/\r?\n/", trim($blok)) as $baris)
                @if (str_starts_with($baris, '## '))
                    <h2 class="font-display text-xl font-semibold text-navy pt-2">{{ substr($baris, 3) }}</h2>
                @elseif (trim($baris) !== '')
                    <p>{{ $baris }}</p>
                @endif
            @endforeach
        @endforeach
        <div class="mt-6 rounded-2xl bg-sky-soft p-5">
            <h2 class="font-display text-lg font-semibold text-navy">Menghubungi kami tentang data</h2>
            <p class="text-sm mt-1">Untuk meminta melihat, memperbaiki, atau menghapus data, hubungi
                <b>{{ $site['privasi_kontak'] ?: collect([$site['email'], $site['whatsapp']])->filter()->join(' / ') }}</b>.</p>
        </div>
    </div>
</article>
@endsection
