<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Pengaturan isi halaman visitor (teks & gambar) yang bisa diubah Admin.
 * Semua field + nilai bawaan didefinisikan di tabs(). Nilai dari database
 * menimpa nilai bawaan; field yang dikosongkan kembali ke nilai bawaan.
 */
class Pengaturan
{
    protected static ?array $memo = null;

    public const IKON = [
        'graduation-cap' => 'Topi wisuda', 'shield-check' => 'Perisai', 'users' => 'Orang', 'heart' => 'Hati',
        'star' => 'Bintang', 'sprout' => 'Tunas', 'school' => 'Sekolah', 'palette' => 'Palet cat',
        'trophy' => 'Piala', 'music' => 'Musik', 'book-open' => 'Buku', 'baby' => 'Bayi', 'smile' => 'Senyum',
        'sun' => 'Matahari', 'puzzle' => 'Puzzle', 'paintbrush' => 'Kuas', 'bus' => 'Bus', 'apple' => 'Apel',
        'blocks' => 'Balok', 'footprints' => 'Jejak kaki', 'hand-heart' => 'Tangan & hati', 'sparkles' => 'Kilau',
        'trees' => 'Pohon', 'bike' => 'Sepeda', 'house' => 'Rumah', 'leaf' => 'Daun', 'camera' => 'Kamera',
        'brain' => 'Otak', 'languages' => 'Bahasa', 'calculator' => 'Berhitung', 'drama' => 'Drama', 'dumbbell' => 'Olahraga', 'wallet' => 'Dompet', 'heart-handshake' => 'Kepedulian',
    ];

    /** Field banner untuk halaman dalam (Tentang, Program, dst). */
    protected static function banner(string $p, string $judul, string $sub): array
    {
        return [
            ['group' => 'Banner halaman'],
            "{$p}_banner_judul" => ['Judul banner', 'text', $judul],
            "{$p}_banner_sub" => ['Subjudul banner', 'text', $sub],
            "{$p}_banner_gambar" => ['Gambar latar banner', 'image', null, 'Foto lebar, disarankan 1600×500 piksel. Kosong = latar warna bawaan.'],
        ];
    }

    /**
     * Tab => [judul, ikon, fields, catatan (menu terkait)]
     * Field: key => [label, tipe, nilai bawaan, petunjuk]
     * Tipe: text, textarea, lines (satu per baris), image, url, ikon, embed
     */
    public static function tabs(): array
    {
        return [
            'umum' => ['Identitas Sekolah', 'school', [
                'nama_sekolah' => ['Nama sekolah', 'text', 'TK Ceria'],
                'tagline' => ['Slogan', 'text', 'Tumbuh Bersama, Ceria Selalu'],
                'logo' => ['Logo', 'image', null, 'Tampil di menu atas, footer, login, dan dashboard. Gunakan PNG persegi dengan latar transparan.'],
            ]],

            'beranda' => ['Home', 'house', [
                ['group' => 'Banner utama'],
                'hero_badge' => ['Label kecil di atas judul', 'text', 'Selamat Datang di TK Ceria'],
                'hero_judul_1' => ['Judul baris pertama', 'text', 'Tempat Terbaik untuk'],
                'hero_kata_kuning' => ['Kata berwarna kuning', 'text', 'Tumbuh'],
                'hero_kata_sambung' => ['Kata penghubung', 'text', 'dan'],
                'hero_kata_hijau' => ['Kata berwarna hijau', 'text', 'Berkembang'],
                'hero_deskripsi' => ['Deskripsi', 'textarea', 'Kami hadir untuk memberikan pendidikan terbaik bagi anak-anak usia dini dengan lingkungan yang aman, nyaman, dan penuh keceriaan.'],
                'hero_tombol' => ['Teks tombol kuning', 'text', 'Kenali Lebih Lanjut'],
                'hero_gelembung' => ['Teks gelembung di foto', 'lines', "Anak Hebat\nMasa Depan\nBangsa", 'Satu baris per baris teks. Kosongkan untuk menyembunyikan gelembung.'],
                'hero_gambar' => ['Foto banner (kanan)', 'image', null, 'Foto lebar, disarankan 1600×900 piksel.'],
                'hero_latar' => ['Gambar latar banner', 'image', null, 'Opsional. Tampil samar di belakang tulisan banner.'],
                ['group' => 'Judul bagian di Home'],
                'program_judul' => ['Judul bagian Program', 'text', 'Program Pendidikan'],
                'program_deskripsi' => ['Deskripsi bagian Program', 'text', 'Program belajar yang dirancang khusus untuk mendukung tumbuh kembang anak secara optimal.'],
                'kegiatan_judul' => ['Judul bagian Kegiatan', 'text', 'Kegiatan Kami'],
                'kegiatan_deskripsi' => ['Deskripsi bagian Kegiatan', 'text', 'Kegiatan untuk mengembangkan kreativitas, kecerdasan, dan keterampilan anak.'],
                'guru_judul' => ['Judul bagian Guru', 'text', 'Guru & Staff'],
                'guru_deskripsi' => ['Deskripsi bagian Guru', 'text', 'Pendidik yang mendampingi si kecil setiap hari.'],
                'berita_judul' => ['Judul bagian Berita', 'text', 'Berita Terbaru'],
                ['group' => 'Kutipan'],
                'kutipan_teks' => ['Teks kutipan', 'textarea', 'Pendidikan anak usia dini adalah investasi terbaik untuk masa depan.'],
                'kutipan_gambar' => ['Gambar latar kutipan', 'image', null, 'Kosong = ilustrasi bawaan.'],
            ], ['Kartu keunggulan' => 'admin.keunggulan.index']],

            'tentang' => ['Tentang Kami', 'info', [
                ...static::banner('tentang', 'Tentang Kami', 'Mengenal lebih dekat sekolah kami'),
                ['group' => 'Profil singkat (juga tampil di Home)'],
                'tentang_label' => ['Label kecil', 'text', 'Tentang Kami'],
                'tentang_judul' => ['Judul', 'text', 'TK Ceria'],
                'tentang_subjudul' => ['Subjudul', 'text', 'Tumbuh Bersama, Ceria Selalu'],
                'tentang_isi' => ['Isi', 'textarea', 'TK Ceria adalah lembaga pendidikan anak usia dini yang berkomitmen memberikan pendidikan berkualitas lewat pendekatan bermain sambil belajar. Kami percaya setiap anak punya potensi unik yang perlu dikembangkan dengan cinta, kesabaran, dan dukungan dari lingkungan yang positif.'],
                'tentang_gambar' => ['Foto gedung / sekolah', 'image', null, 'Disarankan foto tegak atau persegi.'],
                ['group' => 'Sambutan kepala sekolah'],
                'kepsek_nama' => ['Nama kepala sekolah', 'text', 'Ibu Siti Rahmawati, S.Pd.'],
                'kepsek_jabatan' => ['Jabatan', 'text', 'Kepala Sekolah'],
                'kepsek_sambutan' => ['Isi sambutan', 'textarea', "Selamat datang di TK Ceria. Kami percaya masa kanak-kanak adalah masa emas untuk menanamkan rasa ingin tahu, kemandirian, dan karakter yang baik.\n\nBersama orang tua, kami ingin menjadi rumah kedua yang hangat bagi setiap anak."],
                'kepsek_foto' => ['Foto kepala sekolah', 'image', null, 'Foto potret, disarankan persegi.'],
                ['group' => 'Sejarah'],
                'sejarah_judul' => ['Judul', 'text', 'Sejarah Singkat'],
                'sejarah_isi' => ['Isi', 'textarea', 'TK Ceria berdiri pada tahun 2010 berawal dari kelompok bermain kecil di lingkungan perumahan. Kini TK Ceria melayani anak usia 4–6 tahun dengan dukungan guru-guru yang berpengalaman.'],
                ['group' => 'Visi, misi & fasilitas'],
                'visi' => ['Visi', 'textarea', 'Menjadi TK unggulan yang menghasilkan anak-anak cerdas, mandiri, dan berakhlak mulia.'],
                'misi' => ['Misi', 'lines', "Menyelenggarakan pendidikan berkualitas sesuai standar nasional.\nMengembangkan potensi anak secara holistik.\nMenciptakan lingkungan belajar yang aman dan menyenangkan.\nMenjalin komunikasi yang baik dengan orang tua.", 'Satu misi per baris.'],
                'fasilitas' => ['Fasilitas', 'lines', "Ruang kelas ber-AC\nTaman bermain outdoor\nPerpustakaan anak\nRuang musik & tari\nUKS\nCCTV di setiap ruangan", 'Satu fasilitas per baris.'],
            ], ['Kartu keunggulan' => 'admin.keunggulan.index']],

            'program' => ['Program', 'book-open', [
                ...static::banner('program', 'Program Pendidikan', 'Pilihan program sesuai usia dan minat anak'),
            ], ['Kartu program' => 'admin.program.index']],

            'kegiatan' => ['Kegiatan', 'party-popper', [
                ...static::banner('kegiatan', 'Kegiatan Kami', 'Keseruan belajar dan bermain bersama'),
            ], ['Daftar kegiatan' => 'admin.kegiatan.index']],

            'galeri' => ['Galeri', 'images', [
                ...static::banner('galeri', 'Galeri Foto', 'Momen-momen ceria di sekolah'),
            ], ['Foto galeri' => 'admin.galeri.index']],

            'guru' => ['Guru & Staff', 'users', [
                ...static::banner('guru', 'Guru & Staff', 'Pendidik yang sabar, peduli, dan profesional'),
            ], ['Data guru & staff' => 'admin.staff.index']],

            'berita' => ['Berita', 'newspaper', [
                ...static::banner('berita', 'Berita & Pengumuman', 'Kabar terbaru dari kegiatan dan informasi sekolah'),
            ], ['Tulis berita' => 'admin.berita.index']],

            'kontak' => ['Kontak', 'phone', [
                ...static::banner('kontak', 'Hubungi Kami', 'Kami senang menjawab pertanyaan Anda'),
                ['group' => 'Informasi kontak (juga tampil di footer)'],
                'alamat' => ['Alamat', 'textarea', 'Jl. Pelangi No. 12, Jakarta'],
                'jam_operasional' => ['Jam operasional', 'text', 'Senin–Jumat, 07.00–15.00 WIB'],
                'telepon' => ['Telepon', 'text', '(021) 1234 5678'],
                'whatsapp' => ['WhatsApp', 'text', '0812 3456 7890', 'Juga tampil di halaman login sebagai kontak bantuan.'],
                'email' => ['Email', 'text', 'info@tkceria.sch.id'],
                'peta' => ['Peta Google Maps', 'embed', null, 'Buka Google Maps → cari sekolah → Bagikan → Sematkan peta → Salin HTML, lalu tempel di sini.'],
                ['group' => 'Media sosial'],
                'facebook' => ['Link Facebook', 'url', null, 'Kosongkan untuk menyembunyikan ikon.'],
                'instagram' => ['Link Instagram', 'url', null],
                'youtube' => ['Link YouTube', 'url', null],
                'tiktok' => ['Link TikTok', 'url', null],
                ['group' => 'Formulir pesan'],
                'kontak_form_judul' => ['Judul formulir', 'text', 'Kirim Pesan'],
                'kontak_form_deskripsi' => ['Keterangan formulir', 'text', 'Pesan Anda akan kami balas melalui WhatsApp atau email.'],
            ], ['Pesan masuk' => 'admin.pesan.index']],

            'pendaftaran' => ['Pendaftaran', 'clipboard-list', [
                ...static::banner('daftar', 'Formulir Murid Baru', 'Pendaftaran online, mudah dan cepat'),
                ['group' => 'Kartu pendaftaran di Home'],
                'daftar_label' => ['Label kecil', 'text', 'Pendaftaran'],
                'daftar_judul' => ['Judul', 'text', 'Murid Baru'],
                'daftar_tahun' => ['Tahun ajaran', 'text', null, 'Contoh: 2027/2028. Kosongkan agar dihitung otomatis.'],
                'daftar_deskripsi' => ['Deskripsi', 'textarea', 'Bergabunglah bersama kami. Dapatkan pengalaman belajar yang menyenangkan dan bermakna untuk masa depan si kecil.'],
                'daftar_tombol' => ['Teks tombol', 'text', 'Daftar Sekarang'],
                'daftar_poin_1' => ['Poin 1', 'text', 'Proses mudah & cepat'],
                'daftar_poin_2' => ['Poin 2', 'text', 'Informasi lengkap'],
                'daftar_poin_3' => ['Poin 3', 'text', 'Tim siap membantu'],
                'daftar_gambar' => ['Gambar ilustrasi', 'image', null, 'PNG latar transparan, misalnya ilustrasi anak. Kosong = ilustrasi bawaan.'],
                ['group' => 'Halaman formulir'],
                'daftar_form_deskripsi' => ['Teks pembuka formulir', 'textarea', 'Isi data di bawah ini. Setelah terkirim, tim kami akan menghubungi Anda untuk jadwal observasi anak.'],
                'daftar_syarat' => ['Persyaratan', 'lines', "Fotokopi akta kelahiran\nFotokopi kartu keluarga\nPas foto anak 3×4 (2 lembar)", 'Satu syarat per baris. Tampil di samping formulir.'],
            ], ['Data pendaftar' => 'admin.pendaftaran.index']],

            'portal' => ['Portal Orang Tua', 'heart-handshake', [
                ['group' => 'Banner sapaan di Beranda'],
                'portal_salam_judul' => ['Judul sapaan', 'text', '{salam}, {nama}!', 'Kode otomatis: {salam} = Selamat pagi/siang/sore/malam, {nama} = nama depan orang tua.'],
                'portal_salam_sub' => ['Kalimat di bawah sapaan', 'text', 'Berikut kabar terbaru {anak} di {sekolah}.', 'Kode otomatis: {anak} = nama panggilan anak, {sekolah} = nama sekolah.'],
                'portal_banner_gambar' => ['Gambar latar banner sapaan', 'image', null, 'Opsional. Kosong = latar biru dengan matahari.'],
                ['group' => 'Menu yang ditampilkan untuk orang tua'],
                'portal_fitur_kehadiran' => ['Kehadiran (kalender absensi)', 'toggle', '1'],
                'portal_fitur_rapor' => ['Rapor perkembangan', 'toggle', '1'],
                'portal_fitur_pembayaran' => ['Pembayaran & SPP', 'toggle', '1'],
                'portal_fitur_pengumuman' => ['Pengumuman & agenda', 'toggle', '1'],
                'portal_fitur_dokumentasi' => ['Dokumentasi foto', 'toggle', '1'],
                ['group' => 'Judul bagian di Beranda'],
                'portal_judul_pengumuman' => ['Judul Pengumuman', 'text', 'Pengumuman'],
                'portal_judul_agenda' => ['Judul Agenda', 'text', 'Agenda'],
                'portal_judul_dokumentasi' => ['Judul Dokumentasi', 'text', 'Dokumentasi terbaru'],
                ['group' => 'Teks bantuan'],
                'portal_bantuan' => ['Teks di bawah profil anak', 'text', 'Ada data yang keliru? Hubungi admin sekolah untuk memperbaikinya.'],
                'portal_belum_terhubung' => ['Pesan jika akun belum terhubung ke anak', 'textarea', 'Akun Anda belum terhubung dengan data anak. Hubungi admin sekolah untuk menghubungkan akun dengan data siswa.'],
                'portal_dokumentasi_info' => ['Keterangan halaman Dokumentasi', 'text', 'Foto kegiatan yang dibagikan wali kelas. Foto ini hanya bisa dilihat oleh orang tua di kelas yang sama.'],
                ['group' => 'Halaman login'],
                'login_judul' => ['Judul halaman login', 'text', 'Masuk ke akun Anda'],
                'login_sub' => ['Keterangan halaman login', 'text', 'Untuk orang tua murid, guru, dan admin sekolah.'],
                'login_bantuan' => ['Teks bantuan di bawah tombol masuk', 'textarea', 'Belum punya akun? Akun orang tua dibuat oleh admin setelah anak terdaftar.'],
            ], ['Pengumuman' => 'admin.pengumuman.index', 'Tagihan' => 'admin.tagihan.index', 'Dokumentasi' => 'admin.dokumentasi.index']],

            'privasi' => ['Kebijakan Privasi', 'shield-check', [
                ...static::banner('privasi', 'Kebijakan Privasi', 'Bagaimana kami menjaga data anak dan orang tua'),
                ['group' => 'Isi kebijakan'],
                'privasi_berlaku' => ['Berlaku sejak', 'text', null, 'Contoh: 1 November 2026. Kosongkan jika tidak ingin ditampilkan.'],
                'privasi_isi' => ['Isi kebijakan privasi', 'textarea', "## Data yang kami kumpulkan\nData anak (nama, tanggal lahir, alamat, foto, catatan kesehatan, kehadiran, perkembangan), data orang tua (nama, pekerjaan, nomor telepon, email), serta data pembayaran sekolah.\n\n## Tujuan penggunaan\nData dipakai hanya untuk keperluan pendidikan, komunikasi dengan orang tua, administrasi pembayaran, dan keselamatan anak di sekolah.\n\n## Siapa yang dapat melihat\nData anak hanya dapat dilihat oleh orang tuanya sendiri, wali kelas, dan admin sekolah. Foto dokumentasi kelas hanya dapat dilihat oleh orang tua di kelas yang sama.\n\n## Foto di website publik\nFoto anak hanya ditampilkan di halaman website publik (galeri, kegiatan, banner) jika orang tua memberikan izin.\n\n## Penyimpanan & keamanan\nData disimpan di server sekolah, dilindungi kata sandi dan koneksi aman (HTTPS). Data tidak dijual atau dibagikan kepada pihak lain.\n\n## Hak orang tua\nOrang tua dapat meminta melihat, memperbaiki, atau menghapus data anak dan dirinya dengan menghubungi admin sekolah.", 'Baris yang diawali "## " menjadi subjudul. Kosongkan satu baris untuk paragraf baru.'],
                'privasi_kontak' => ['Kontak permintaan data', 'text', null, 'Contoh: privasi@tkceria.sch.id. Kosong = memakai email & WhatsApp sekolah.'],
                ['group' => 'Formulir pendaftaran'],
                'privasi_setuju_label' => ['Teks persetujuan (wajib dicentang)', 'text', 'Saya telah membaca dan menyetujui Kebijakan Privasi sekolah.'],
                'privasi_foto_label' => ['Teks izin foto (boleh tidak dicentang)', 'text', 'Saya mengizinkan foto anak saya ditampilkan di website publik sekolah (galeri & kegiatan).'],
            ], ['Data pendaftar' => 'admin.pendaftaran.index']],

            'pembayaran' => ['Info Pembayaran', 'wallet', [
                ['group' => 'Tampil di panel orang tua (menu Pembayaran)'],
                'bank_nama' => ['Nama bank', 'text', 'Bank BRI'],
                'bank_nomor' => ['Nomor rekening', 'text', '0123-01-000123-30-1'],
                'bank_atas_nama' => ['Atas nama', 'text', 'Yayasan TK Ceria'],
                'spp_nominal' => ['Nominal SPP bawaan (angka saja)', 'text', '350000', 'Dipakai sebagai isian awal saat membuat tagihan SPP.'],
                'bayar_petunjuk' => ['Petunjuk transfer', 'textarea', 'Transfer sesuai nominal tagihan, lalu unggah foto bukti transfer pada tagihan yang dibayar.'],
                ['group' => 'Pembayaran tunai (cash)'],
                'tunai_tempat' => ['Tempat pembayaran tunai', 'text', 'Kantor Tata Usaha TK Ceria'],
                'tunai_jam' => ['Jam layanan', 'text', 'Senin–Jumat, 07.30–14.00 WIB'],
                'tunai_petunjuk' => ['Petunjuk tunai', 'textarea', 'Sebutkan nama anak dan bulan yang dibayar kepada petugas. Status tagihan otomatis menjadi Lunas setelah dicatat, dan kuitansi digital bisa dilihat di aplikasi.'],
            ], ['Tagihan & SPP' => 'admin.tagihan.index']],
        ];
    }

    /** Daftar field datar: key => [label, tipe, bawaan, petunjuk] */
    public static function fields(?string $tab = null): array
    {
        $out = [];
        foreach (static::tabs() as $key => $tabInfo) {
            $fields = $tabInfo[2];
            if ($tab && $tab !== $key) continue;
            foreach ($fields as $k => $f) {
                if (is_string($k)) $out[$k] = $f;
            }
        }
        return $out;
    }

    public static function semua(): array
    {
        if (static::$memo !== null) return static::$memo;

        $bawaan = array_map(fn ($f) => $f[2] ?? null, static::fields());

        try {
            $db = Cache::rememberForever('pengaturan', fn () => DB::table('pengaturan')->whereNotNull('value')->pluck('value', 'key')->all());
        } catch (\Throwable) {
            $db = []; // tabel belum dimigrasi
        }

        $db = array_filter($db, fn ($v) => $v !== null && $v !== '');

        return static::$memo = array_merge($bawaan, $db);
    }

    public static function simpan(array $data): void
    {
        foreach ($data as $key => $value) {
            DB::table('pengaturan')->updateOrInsert(['key' => $key], ['value' => $value, 'updated_at' => now()]);
        }
        static::lupakan();
    }

    public static function mentah(string $key): ?string
    {
        return DB::table('pengaturan')->where('key', $key)->value('value');
    }

    public static function lupakan(): void
    {
        Cache::forget('pengaturan');
        static::$memo = null;
    }

    /** URL gambar dari pengaturan, atau file cadangan di public/, atau null. */
    public static function url(string $key, ?string $cadangan = null): ?string
    {
        $path = static::semua()[$key] ?? null;
        if ($path) return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
        if ($cadangan && file_exists(public_path($cadangan))) return asset($cadangan);
        return null;
    }

    /** Ambil src aman dari kode embed Google Maps. */
    public static function peta(): ?string
    {
        $raw = (string) (static::semua()['peta'] ?? '');
        if (preg_match('/src="([^"]+)"/', $raw, $m)) $raw = html_entity_decode($m[1]);
        $raw = trim($raw);
        return str_starts_with($raw, 'https://www.google.com/maps/embed') ? $raw : null;
    }

    /** Fitur portal orang tua aktif? (kehadiran, rapor, pembayaran, pengumuman, dokumentasi) */
    public static function fitur(string $nama): bool
    {
        return (string) (static::semua()["portal_fitur_{$nama}"] ?? '1') === '1';
    }

    /** Ganti kode {salam}, {nama}, dst. */
    public static function isi(string $key, array $ganti): string
    {
        return strtr((string) (static::semua()[$key] ?? ''), $ganti);
    }

    public static function baris(string $key): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string) (static::semua()[$key] ?? '')))));
    }
}
