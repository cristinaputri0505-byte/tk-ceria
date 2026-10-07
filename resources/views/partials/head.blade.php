<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@0.460.0/dist/umd/lucide.min.js"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    navy: { DEFAULT: '#14307d', deep: '#0f2650' },
                    sky: { DEFAULT: '#1f6fe5', dark: '#1658bd', soft: '#e8f1ff' },
                    sun: { DEFAULT: '#fbbf24', soft: '#fff6d8' },
                    leaf: { DEFAULT: '#1f9d55', soft: '#e3f6ea' },
                    berry: '#e5486b',
                    cloud: '#f5f9ff',
                    ink: '#2b3a55',
                },
                fontFamily: {
                    display: ['Fredoka', 'ui-rounded', 'system-ui', 'sans-serif'],
                    body: ['Nunito', 'system-ui', 'sans-serif'],
                },
            },
        },
    };
</script>
<style>
    :focus-visible { outline: 3px solid #fbbf24; outline-offset: 2px; border-radius: 6px; }
    @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation: none !important; transition: none !important; scroll-behavior: auto !important; } }
    html { scroll-behavior: smooth; }
    [x-cloak] { display: none; }
</style>
