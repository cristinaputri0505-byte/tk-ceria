<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Memeriksa penyimpanan file (Supabase Storage di Vercel, folder storage/ di laptop) langsung dari
 * server yang sedang berjalan. Dipakai halaman Admin → Cek Penyimpanan.
 * Tidak pernah menampilkan isi kunci rahasia, hanya jenis dan ada/tidaknya.
 */
class CekPenyimpanan
{
    /** @return array<int, array{0:string, 1:bool|null, 2:string}>  [pemeriksaan, lulus?, keterangan] */
    public static function jalankan(): array
    {
        $hasil = [];
        $supabase = (bool) env('SUPABASE_STORAGE');
        $hasil[] = ['Tempat penyimpanan', null, $supabase ? 'Supabase Storage' : 'Folder storage/ di server (hanya cocok untuk laptop)'];

        if ($supabase) {
            $url = (string) env('SUPABASE_URL');
            $kunci = (string) env('SUPABASE_SECRET_KEY');
            $hasil[] = ['SUPABASE_URL terisi', $url !== '' && str_starts_with($url, 'https://'),
                $url !== '' ? (parse_url($url, PHP_URL_HOST) ?: 'format tidak valid') : 'Kosong. Isi di Vercel → Settings → Environment Variables'];

            // Hanya awalan kunci yang diperiksa (pola ditulis sebagai regex agar tidak dikira kunci asli
            // oleh pemeriksa rahasia sebelum commit).
            $jenis = match (true) {
                $kunci === '' => [false, 'Kosong. Isi dengan secret key dari Supabase → Project Settings → API Keys'],
                (bool) preg_match('/^sb_(secret)_/', $kunci) => [true, 'Secret key (format baru)'],
                (bool) preg_match('/^sb_(publishable)_/', $kunci) => [false, 'Yang terisi adalah PUBLISHABLE key. Ganti dengan SECRET key'],
                str_starts_with($kunci, 'eyJ') => [true, 'Kunci format lama (JWT). Pastikan itu kunci "service role", bukan "anon"'],
                default => [false, 'Format kunci tidak dikenal'],
            };
            $hasil[] = ['SUPABASE_SECRET_KEY', $jenis[0], $jenis[1]];
            $hasil[] = ['Nama bucket', null, 'privat: ' . config('filesystems.disks.local.bucket') . ' · publik: ' . config('filesystems.disks.public.bucket')];
        }

        foreach (['local' => 'privat (foto anak, dokumentasi, bukti bayar)', 'public' => 'publik (galeri, berita, guru, logo, foto profil)'] as $disk => $nama) {
            $tes = 'cek-penyimpanan/' . uniqid() . '.txt';
            try {
                Storage::disk($disk)->put($tes, 'tes');
                $baca = Storage::disk($disk)->get($tes);
                Storage::disk($disk)->delete($tes);
                $hasil[] = ["Bucket {$nama}: tulis, baca, hapus", $baca === 'tes', $baca === 'tes' ? 'Berhasil' : 'File tertulis tetapi isinya tidak terbaca'];
            } catch (\Throwable $e) {
                $hasil[] = ["Bucket {$nama}: tulis, baca, hapus", false, self::ringkas($e->getMessage())];
            }
        }

        if ($supabase) {
            $hasil[] = self::cekUrlPublik('local', false, 'Bucket privat TIDAK bisa dibuka publik', 'Di Supabase Storage, matikan "Public bucket" untuk bucket privat');
            $hasil[] = self::cekUrlPublik('public', true, 'Bucket publik bisa dibuka dari browser', 'Di Supabase Storage, aktifkan "Public bucket" untuk bucket publik');
        }

        return $hasil;
    }

    private static function cekUrlPublik(string $disk, bool $harusTerbuka, string $nama, string $saran): array
    {
        $tes = 'cek-penyimpanan/' . uniqid() . '.txt';
        try {
            Storage::disk($disk)->put($tes, 'tes');
            $dasar = rtrim(env('SUPABASE_PUBLIC_URL') ?: rtrim((string) env('SUPABASE_URL'), '/') . '/storage/v1/object/public', '/');
            $kode = Http::timeout(10)->get($dasar . '/' . config("filesystems.disks.{$disk}.bucket") . '/' . $tes)->status();
            Storage::disk($disk)->delete($tes);
            $terbuka = $kode === 200;
            return [$nama, $terbuka === $harusTerbuka, $terbuka === $harusTerbuka ? "Sesuai (HTTP {$kode})" : "{$saran} (HTTP {$kode})"];
        } catch (\Throwable $e) {
            return [$nama, false, self::ringkas($e->getMessage())];
        }
    }

    private static function ringkas(string $pesan): string
    {
        return mb_substr(preg_replace('/\s+/', ' ', $pesan), 0, 300);
    }
}
