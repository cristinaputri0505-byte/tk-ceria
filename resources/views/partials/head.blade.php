{{-- CSS + font hasil build (npm run build) dan ikon disimpan di server sendiri --}}
@vite('resources/css/app.css')
<script src="{{ asset('js/lucide.min.js') }}"></script>
<style>
    :focus-visible { outline: 3px solid #fbbf24; outline-offset: 2px; border-radius: 6px; }
    @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation: none !important; transition: none !important; scroll-behavior: auto !important; } }
    html { scroll-behavior: smooth; }
    [x-cloak] { display: none; }
</style>
