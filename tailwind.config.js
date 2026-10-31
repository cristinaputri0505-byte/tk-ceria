/**
 * Konfigurasi Tailwind TK Ceria (sama dengan konfigurasi CDN sebelumnya).
 * Setelah mengubah tampilan / menambah class baru, jalankan:  npm run build
 */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './app/**/*.php', // class warna yang disusun di PHP (Program, Notifikasi, dll.)
        './public/js/belajar.js', // class yang disusun di JavaScript Ruang Belajar
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],
    theme: {
        extend: {
            // Tema "Taman Bermain": warna permen cerah + garis tinta ungu tua (seperti stiker)
            colors: {
                navy: { DEFAULT: '#2b2140', deep: '#211934' },          // tinta: judul, garis, tombol gelap
                sky: { DEFAULT: '#1f6fe5', dark: '#1658bd', soft: '#d9ecff' },
                sun: { DEFAULT: '#ffc53d', soft: '#fff1c7' },
                leaf: { DEFAULT: '#1e8a4c', soft: '#d6f2e0' },
                berry: '#d93a63',
                cloud: '#fff8ec',                                       // latar krem seperti kertas gambar
                ink: '#463c5c',
                permen: { DEFAULT: '#ff8fb1', soft: '#ffd6e3' },
                anggur: { DEFAULT: '#b9a4ff', soft: '#e6deff' },
                jeruk: { DEFAULT: '#ff9a5c', soft: '#ffe1cc' },
                langit: { DEFAULT: '#6ec3ff', soft: '#cde8ff' },
                daun: { DEFAULT: '#3dbe72', soft: '#d6f2e0' },
            },
            fontFamily: {
                display: ['"Baloo 2"', 'Fredoka', 'ui-rounded', 'system-ui', 'sans-serif'],
                body: ['Nunito', 'system-ui', 'sans-serif'],
            },
        },
    },
    plugins: [],
};
