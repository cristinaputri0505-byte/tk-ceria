/**
 * Konfigurasi Tailwind TK Ceria (sama dengan konfigurasi CDN sebelumnya).
 * Setelah mengubah tampilan / menambah class baru, jalankan:  npm run build
 */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './app/**/*.php', // class warna yang disusun di PHP (Program, Notifikasi, dll.)
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],
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
    plugins: [],
};
