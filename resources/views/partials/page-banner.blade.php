{{-- Banner halaman dalam. $p = prefix pengaturan (tentang, program, ...), $crumb = nama halaman --}}
@php
    $bgUrl = \App\Support\Pengaturan::url($p.'_banner_gambar');
    $judulBanner = $site[$p.'_banner_judul'] ?? $crumb;
    $subBanner = $site[$p.'_banner_sub'] ?? '';
@endphp
<section class="relative overflow-hidden {{ $bgUrl ? 'bg-navy-deep' : 'bg-gradient-to-br from-sky-soft via-cloud to-sun-soft' }}">
    @if ($bgUrl)
        <img src="{{ $bgUrl }}" alt="" class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-r from-navy-deep/85 via-navy-deep/60 to-navy-deep/20"></div>
    @else
        <svg class="absolute right-10 top-6 w-24 h-24 hidden sm:block" viewBox="0 0 100 100" aria-hidden="true">
            <g stroke="#fbbf24" stroke-width="5" stroke-linecap="round"><path d="M50 6v12M50 82v12M6 50h12M82 50h12M19 19l8 8M73 73l8 8M19 81l8-8M73 27l8-8"/></g>
            <circle cx="50" cy="50" r="22" fill="#fbbf24"/>
        </svg>
        <svg class="absolute -left-6 bottom-2 w-40 text-white" viewBox="0 0 160 60" aria-hidden="true"><path d="M20 50a20 20 0 0 1 10-37 26 26 0 0 1 48-4 22 22 0 0 1 40 10 18 18 0 0 1 14 31z" fill="currentColor"/></svg>
    @endif
    <div class="relative max-w-7xl mx-auto px-4 lg:px-8 py-14 sm:py-20">
        <nav aria-label="Breadcrumb" class="text-sm font-bold {{ $bgUrl ? 'text-white/80' : 'text-navy/60' }}">
            <a href="{{ route('home') }}" class="hover:underline">Home</a> <span aria-hidden="true">/</span> <span aria-current="page">{{ $crumb }}</span>
        </nav>
        <h1 class="font-display text-4xl sm:text-5xl font-semibold mt-2 {{ $bgUrl ? 'text-white' : 'text-navy' }}">{{ $judulBanner }}</h1>
        @if ($subBanner)<p class="mt-3 text-lg max-w-xl {{ $bgUrl ? 'text-white/90' : 'text-ink' }}">{{ $subBanner }}</p>@endif
    </div>
    <svg class="absolute -bottom-px left-0 w-full h-8 text-cloud" viewBox="0 0 1200 40" preserveAspectRatio="none" aria-hidden="true"><path d="M0 25c200-20 400 15 600 8S1000-5 1200 20v20H0z" fill="currentColor"/></svg>
</section>
