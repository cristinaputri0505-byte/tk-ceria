<?php

namespace App\Support;

use App\Models\Siswa;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * "Soal Hari Ini" untuk Ruang Belajar: soal baru setiap hari, dibuat otomatis dari bank soal di bawah.
 *
 * Soal ditentukan oleh tanggal + kelas, jadi:
 *  - sepanjang hari itu soalnya tetap sama (dibuka ulang / di HP lain pun sama, teman sekelas juga sama);
 *  - besok soalnya berganti dengan sendirinya, tanpa perlu dibuat guru.
 * Bentuk datanya sama dengan tugas buatan guru, jadi dimainkan dengan permainan yang sama.
 */
class SoalHarian
{
    public const BINTANG = 2;

    /** Nilai pengaturan 'belajar_harian' => label */
    public const PILIHAN = [
        '0' => 'Mati (hanya soal dari guru)',
        '3' => '3 soal per hari',
        '4' => '4 soal per hari (disarankan)',
        '5' => '5 soal per hari',
        '6' => '6 soal per hari',
    ];

    /** Urutan jenis soal per hari (diambil sebanyak jumlah soal). */
    private const POLA = ['kuis', 'hitung', 'cocok', 'kuis-khusus', 'kuis', 'hitung'];

    private const WARNA = ['pink', 'biru', 'hijau', 'kuning', 'ungu', 'oranye'];

    /** Tebak gambar: kategori => [judul, pertanyaan, ikon, [[emoji, nama], ...]] */
    private const KUIS = [
        'hewan' => ['Tebak Hewan', 'Hewan apakah ini? Ayo tebak!', '🐾', [
            ['🐘', 'Gajah'], ['🐄', 'Sapi'], ['🐔', 'Ayam'], ['🐱', 'Kucing'], ['🐶', 'Anjing'], ['🐰', 'Kelinci'],
            ['🐒', 'Monyet'], ['🦒', 'Jerapah'], ['🐯', 'Harimau'], ['🦁', 'Singa'], ['🐸', 'Katak'], ['🐢', 'Kura-kura'],
            ['🐟', 'Ikan'], ['🦆', 'Bebek'], ['🦋', 'Kupu-kupu'], ['🐝', 'Lebah'], ['🐑', 'Domba'], ['🐴', 'Kuda'],
            ['🐧', 'Penguin'], ['🦓', 'Zebra'], ['🐊', 'Buaya'], ['🐌', 'Siput'], ['🐭', 'Tikus'], ['🐼', 'Panda'],
        ]],
        'buah' => ['Tebak Buah', 'Buah apakah ini? Ayo tebak!', '🍉', [
            ['🍎', 'Apel'], ['🍌', 'Pisang'], ['🍇', 'Anggur'], ['🍉', 'Semangka'], ['🍊', 'Jeruk'], ['🍓', 'Stroberi'],
            ['🍍', 'Nanas'], ['🥭', 'Mangga'], ['🍐', 'Pir'], ['🥥', 'Kelapa'], ['🍒', 'Ceri'], ['🥝', 'Kiwi'],
        ]],
        'kendaraan' => ['Tebak Kendaraan', 'Kendaraan apakah ini? Ayo tebak!', '🚗', [
            ['🚗', 'Mobil'], ['🚌', 'Bus'], ['🚲', 'Sepeda'], ['🏍️', 'Motor'], ['✈️', 'Pesawat'], ['🚢', 'Kapal'],
            ['🚂', 'Kereta api'], ['🚁', 'Helikopter'], ['🚑', 'Ambulans'], ['🚒', 'Mobil pemadam'], ['🚜', 'Traktor'], ['🚀', 'Roket'],
        ]],
        'warna' => ['Tebak Warna', 'Warna apakah ini? Ayo tebak!', '🎨', [
            ['🔴', 'Merah'], ['🔵', 'Biru'], ['🟢', 'Hijau'], ['🟡', 'Kuning'], ['🟣', 'Ungu'], ['🟠', 'Oranye'],
            ['⚫', 'Hitam'], ['⚪', 'Putih'], ['🟤', 'Cokelat'],
        ]],
        'bentuk' => ['Tebak Bentuk', 'Bentuk apakah ini? Ayo tebak!', '🔷', [
            ['⭐', 'Bintang'], ['❤️', 'Hati'], ['🔺', 'Segitiga'], ['🟥', 'Persegi'], ['🔶', 'Belah ketupat'], ['🌙', 'Bulan sabit'], ['🔵', 'Lingkaran'],
        ]],
        'benda' => ['Tebak Benda', 'Benda apakah ini? Ayo tebak!', '🎒', [
            ['⚽', 'Bola'], ['📚', 'Buku'], ['✏️', 'Pensil'], ['🎒', 'Tas'], ['☂️', 'Payung'], ['⏰', 'Jam'],
            ['👟', 'Sepatu'], ['🎈', 'Balon'], ['🪥', 'Sikat gigi'], ['🥄', 'Sendok'], ['🔑', 'Kunci'], ['🧸', 'Boneka'],
        ]],
    ];

    /** Tebak suara hewan: [emoji, nama, bunyi] */
    private const SUARA_HEWAN = [
        ['🐄', 'Sapi', 'Mooo!'], ['🐱', 'Kucing', 'Meong!'], ['🐶', 'Anjing', 'Guk guk!'], ['🐔', 'Ayam', 'Kukuruyuk!'],
        ['🦆', 'Bebek', 'Kwek kwek!'], ['🐑', 'Domba', 'Mbeek!'], ['🐸', 'Katak', 'Kwak kwak!'], ['🐭', 'Tikus', 'Cit cit!'],
        ['🦁', 'Singa', 'Aauumm!'], ['🐝', 'Lebah', 'Nguuung!'],
    ];

    /** Berhitung: [emoji, nama] */
    private const HITUNG = [
        ['🍎', 'buah apel'], ['⭐', 'bintang'], ['⚽', 'bola'], ['🐟', 'ikan'], ['🌸', 'bunga'], ['🎈', 'balon'],
        ['🦋', 'kupu-kupu'], ['🚗', 'mobil'], ['🧁', 'kue'], ['🦆', 'bebek'], ['🍌', 'pisang'], ['🐞', 'kepik'],
    ];

    /** Mencocokkan: tema => [judul, instruksi, ikon, [[kiri, kanan], ...]] */
    private const COCOK = [
        'makanan' => ['Makanan Kesukaan Hewan', 'Pasangkan hewan dengan makanan kesukaannya!', '🥕', [
            ['🐰 Kelinci', '🥕 Wortel'], ['🐒 Monyet', '🍌 Pisang'], ['🐄 Sapi', '🌿 Rumput'], ['🐱 Kucing', '🐟 Ikan'],
            ['🐶 Anjing', '🦴 Tulang'], ['🐝 Lebah', '🌼 Bunga'], ['🐭 Tikus', '🧀 Keju'], ['🐼 Panda', '🎋 Bambu'], ['🐔 Ayam', '🌽 Jagung'],
        ]],
        'warna' => ['Warna Benda', 'Pasangkan benda dengan warnanya!', '🎨', [
            ['🍌 Pisang', '🟡 Kuning'], ['🍅 Tomat', '🔴 Merah'], ['🥦 Brokoli', '🟢 Hijau'], ['🍆 Terong', '🟣 Ungu'],
            ['🍊 Jeruk', '🟠 Oranye'], ['☁️ Awan', '⚪ Putih'], ['🍫 Cokelat', '🟤 Cokelat'], ['🌊 Laut', '🔵 Biru'],
        ]],
        'profesi' => ['Alat Para Pekerja', 'Pasangkan pekerja dengan alat yang dipakainya!', '🧑‍🚒', [
            ['🧑‍🚒 Pemadam', '🚒 Mobil pemadam'], ['🧑‍⚕️ Dokter', '🩺 Stetoskop'], ['🧑‍🍳 Koki', '🍳 Wajan'],
            ['🧑‍🌾 Petani', '🌾 Padi'], ['👮 Polisi', '🚓 Mobil polisi'], ['🧑‍🏫 Guru', '📚 Buku'], ['🧑‍🎨 Pelukis', '🖌️ Kuas'],
        ]],
        'pasangan' => ['Benda Berpasangan', 'Pasangkan benda yang selalu dipakai bersama!', '🧦', [
            ['🪥 Sikat gigi', '🦷 Gigi'], ['🔑 Kunci', '🔒 Gembok'], ['✏️ Pensil', '📝 Kertas'], ['☂️ Payung', '🌧️ Hujan'],
            ['🥣 Mangkuk', '🥄 Sendok'], ['👟 Sepatu', '🧦 Kaos kaki'], ['🧼 Sabun', '🫧 Busa'],
        ]],
        'rumah' => ['Rumah Para Hewan', 'Pasangkan hewan dengan tempat tinggalnya!', '🏠', [
            ['🐟 Ikan', '🌊 Air'], ['🐦 Burung', '🪺 Sarang'], ['🐝 Lebah', '🍯 Sarang lebah'], ['🐒 Monyet', '🌳 Pohon'],
            ['🐄 Sapi', '🛖 Kandang'], ['🐧 Penguin', '🧊 Es'],
        ]],
    ];

    public static function jumlah(): int
    {
        $n = (int) (Pengaturan::semua()['belajar_harian'] ?? 4);

        return max(0, min(count(self::POLA), $n));
    }

    public static function aktif(): bool
    {
        return self::jumlah() > 0 && Basisdata::ada('elearning_harian');
    }

    public static function hariIni(): string
    {
        return now()->toDateString();
    }

    /**
     * Daftar soal untuk siswa pada tanggal tertentu (Y-m-d). Setiap soal berbentuk sama dengan
     * ElearningTugas::untukPemain(), dengan id "h1", "h2", ... dan penanda 'harian' => true.
     */
    public static function untuk(Siswa $siswa, ?string $tanggal = null): array
    {
        $tanggal ??= self::hariIni();
        $acak = new Randomizer(new Mt19937(crc32('tk-ceria|' . $tanggal . '|' . (int) $siswa->kelas_id)));

        $kategoriKuis = $acak->shuffleArray(array_keys(self::KUIS));
        $temaCocok = $acak->shuffleArray(array_keys(self::COCOK));
        $warna = $acak->shuffleArray(self::WARNA);
        $hasil = [];

        foreach (array_slice(self::POLA, 0, self::jumlah()) as $i => $jenis) {
            $soal = match ($jenis) {
                'kuis' => self::kuis($acak, array_shift($kategoriKuis)),
                'kuis-khusus' => $acak->getInt(0, 1) ? self::suaraHewan($acak) : self::hurufAwal($acak),
                'hitung' => self::hitung($acak),
                'cocok' => self::cocok($acak, array_shift($temaCocok)),
            };
            $hasil[] = $soal + [
                'id' => 'h' . ($i + 1),
                'warna' => $warna[$i % count($warna)],
                'bintang' => self::BINTANG,
                'harian' => true,
            ];
        }

        return $hasil;
    }

    /** Ambil 1 jawaban benar + 3 pengecoh dari daftar, lalu acak urutannya. */
    private static function pilihan(Randomizer $acak, array $daftar, int $benar): array
    {
        $lain = array_values(array_diff(array_keys($daftar), [$benar]));
        $kunci = $acak->shuffleArray(array_merge([$benar], array_slice($acak->shuffleArray($lain), 0, 3)));

        return [array_map(fn ($k) => $daftar[$k], $kunci), array_search($benar, $kunci, true)];
    }

    private static function kuis(Randomizer $acak, string $kategori): array
    {
        [$judul, $tanya, $ikon, $daftar] = self::KUIS[$kategori];
        $benar = $acak->getInt(0, count($daftar) - 1);
        [$opsi, $posisi] = self::pilihan($acak, $daftar, $benar);

        return [
            'tipe' => 'kuis', 'judul' => $judul, 'instruksi' => $tanya, 'ikon' => $ikon,
            'data' => ['gambar' => $daftar[$benar][0], 'opsi' => array_map(fn ($x) => $x[1], $opsi), 'benar' => $posisi],
        ];
    }

    private static function suaraHewan(Randomizer $acak): array
    {
        $benar = $acak->getInt(0, count(self::SUARA_HEWAN) - 1);
        [$opsi, $posisi] = self::pilihan($acak, self::SUARA_HEWAN, $benar);
        $bunyi = self::SUARA_HEWAN[$benar][2];

        return [
            'tipe' => 'kuis', 'judul' => 'Suara Siapa Ini?', 'ikon' => '🔊',
            'instruksi' => 'Dengarkan baik-baik. ' . $bunyi . ' Hewan apa yang bersuara seperti itu?',
            'data' => ['gambar' => '🔊', 'bunyi' => $bunyi, 'opsi' => array_map(fn ($x) => $x[0] . ' ' . $x[1], $opsi), 'benar' => $posisi],
        ];
    }

    private static function hurufAwal(Randomizer $acak): array
    {
        $semua = array_merge(self::KUIS['hewan'][3], self::KUIS['buah'][3]);
        [$emoji, $nama] = $semua[$acak->getInt(0, count($semua) - 1)];
        $huruf = mb_strtoupper(mb_substr($nama, 0, 1));
        $lain = array_values(array_diff(str_split('ABCDEGHIJKMNPRSTU'), [$huruf]));
        $opsi = $acak->shuffleArray(array_merge([$huruf], array_slice($acak->shuffleArray($lain), 0, 3)));

        return [
            'tipe' => 'kuis', 'judul' => 'Huruf Pertama', 'ikon' => '🔤',
            'instruksi' => 'Ini gambar ' . mb_strtolower($nama) . '. Kata ' . mb_strtolower($nama) . ' diawali huruf apa?',
            'data' => ['gambar' => $emoji, 'opsi' => $opsi, 'benar' => array_search($huruf, $opsi, true)],
        ];
    }

    private static function hitung(Randomizer $acak): array
    {
        [$emoji, $nama] = self::HITUNG[$acak->getInt(0, count(self::HITUNG) - 1)];
        $jumlah = $acak->getInt(2, 10);

        return [
            'tipe' => 'hitung', 'judul' => 'Ayo Hitung ' . ucwords($nama), 'ikon' => $emoji,
            'instruksi' => 'Ada berapa ' . $nama . ' di bawah ini? Yuk, kita hitung bersama!',
            'data' => ['emoji' => $emoji, 'nama' => $nama, 'jumlah' => $jumlah],
        ];
    }

    private static function cocok(Randomizer $acak, string $tema): array
    {
        [$judul, $instruksi, $ikon, $daftar] = self::COCOK[$tema];
        $pilih = array_slice($acak->shuffleArray($daftar), 0, 3);

        return [
            'tipe' => 'cocok', 'judul' => $judul, 'instruksi' => $instruksi, 'ikon' => $ikon,
            'data' => ['pasangan' => array_map(fn ($p) => ['kiri' => $p[0], 'kanan' => $p[1]], $pilih)],
        ];
    }
}
