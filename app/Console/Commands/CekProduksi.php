<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CekProduksi extends Command
{
    protected $signature = 'tk:cek-produksi';
    protected $description = 'Memeriksa kesiapan aplikasi sebelum / sesudah online';

    public function handle(): int
    {
        $hasil = [];
        $cek = function (string $nama, bool $ok, string $saran, bool $wajib = true) use (&$hasil) {
            $hasil[] = [$ok ? '<fg=green>✔ OK</>' : ($wajib ? '<fg=red>✘ PERBAIKI</>' : '<fg=yellow>! SARAN</>'), $nama, $ok ? '' : $saran];
        };

        $cek('APP_ENV = production', app()->isProduction(), 'Ubah APP_ENV=production di .env server');
        $cek('APP_DEBUG = false', ! config('app.debug'), 'Ubah APP_DEBUG=false agar error tidak menampilkan kode & password');
        $cek('APP_KEY terisi', (bool) config('app.key'), 'Jalankan: php artisan key:generate');
        $cek('APP_URL memakai https', str_starts_with((string) config('app.url'), 'https://'), 'Isi APP_URL=https://domain-anda');
        $cek('Cookie sesi hanya lewat HTTPS', (bool) config('session.secure'), 'Tambahkan SESSION_SECURE_COOKIE=true (setelah HTTPS aktif)');
        $cek('Zona waktu Asia/Jakarta', config('app.timezone') === 'Asia/Jakarta', 'Isi APP_TIMEZONE=Asia/Jakarta');
        $cek('Bahasa Indonesia', config('app.locale') === 'id', 'Isi APP_LOCALE=id');
        $cek('Tabel database sudah dibuat', Schema::hasTable('users') && Schema::hasTable('pengaturan'), 'Jalankan: php artisan migrate --force');
        $cek('File CSS hasil build ada', file_exists(public_path('build/manifest.json')), 'Jalankan: npm run build (atau unggah folder public/build)');
        $supabase = (bool) env('SUPABASE_STORAGE');
        if ($supabase) {
            $cek('Penyimpanan file: Supabase Storage', true, '');
            foreach (['local' => 'privat', 'public' => 'publik'] as $disk => $nama) {
                $tes = 'cek-produksi/' . uniqid() . '.txt';
                $ok = rescue(function () use ($disk, $tes) {
                    Storage::disk($disk)->put($tes, 'tes');
                    $baca = Storage::disk($disk)->get($tes) === 'tes';
                    Storage::disk($disk)->delete($tes);
                    return $baca;
                }, false, false);
                $cek("Bucket {$nama} bisa ditulis & dibaca", $ok, "Buat bucket {$nama} di Supabase Storage dan periksa SUPABASE_URL / SUPABASE_SECRET_KEY / SUPABASE_BUCKET_" . strtoupper($nama === 'privat' ? 'PRIVAT' : 'PUBLIK'));
            }
            $tes = 'cek-produksi/' . uniqid() . '.txt';
            $privatTerbuka = rescue(function () use ($tes) {
                Storage::disk('local')->put($tes, 'rahasia');
                $url = rtrim(env('SUPABASE_PUBLIC_URL') ?: rtrim((string) env('SUPABASE_URL'), '/') . '/storage/v1/object/public', '/') . '/' . config('filesystems.disks.local.bucket') . '/' . $tes;
                $kode = \Illuminate\Support\Facades\Http::timeout(10)->get($url)->status();
                Storage::disk('local')->delete($tes);
                return $kode === 200;
            }, true, false);
            $cek('Bucket privat TIDAK bisa dibuka publik (foto anak & bukti bayar aman)', ! $privatTerbuka, 'Di Supabase Storage, matikan opsi "Public bucket" untuk bucket privat');
        } else {
            $cek('Link folder upload (storage:link)', file_exists(public_path('storage')), 'Jalankan: php artisan storage:link (atau isi SUPABASE_STORAGE=true di Vercel)');
            $cek('Folder storage bisa ditulisi', is_writable(storage_path('app')) && is_writable(storage_path('logs')), 'Beri izin tulis ke folder storage/ dan bootstrap/cache/');
        }

        $adaAdmin = rescue(fn () => User::where('role', User::ADMIN)->exists(), false, false);
        $cek('Ada akun admin', $adaAdmin, 'Jalankan: php artisan tk:buat-admin');

        $demo = rescue(fn () => User::whereIn('email', ['admin@tkceria.sch.id', 'rina@tkceria.sch.id', 'sari@tkceria.sch.id', 'dimas@tkceria.sch.id', 'budi@contoh.com', 'dewi@contoh.com'])->get()
            ->filter(fn ($u) => Hash::check('password', $u->password))->pluck('email'), collect(), false);
        $cek('Tidak ada akun contoh berpassword "password"', $demo->isEmpty(), 'Hapus/ganti password akun: ' . $demo->join(', '));

        $lemah = rescue(fn () => User::all()->filter(fn ($u) => Hash::check('password', $u->password))->count(), 0, false);
        $cek('Tidak ada akun lain berpassword "password"', $lemah === 0, "{$lemah} akun masih memakai password \"password\"");

        $cek('Driver database bukan SQLite (PostgreSQL Supabase / MySQL)', config('database.default') !== 'sqlite', 'SQLite boleh untuk sekolah kecil; MySQL lebih kuat untuk banyak pengguna', false);
        $cek('Koneksi database terenkripsi (SSL)', config('database.default') !== 'pgsql' || in_array(config('database.connections.pgsql.sslmode'), ['require', 'verify-ca', 'verify-full'], true), 'Isi DB_SSLMODE=require untuk Supabase');
        $kolomSesi = rescue(fn () => Schema::hasColumns('sessions', ['id', 'user_id', 'ip_address', 'user_agent', 'payload', 'last_activity']), false, false);
        $cek('Tabel sessions sesuai Laravel', config('session.driver') !== 'database' || $kolomSesi, 'Struktur tabel sessions tidak sesuai. Jalankan skrip perbaikan tabel sessions dari panduan deploy');
        $cek('Sesi & cache disimpan bersama (bukan file)', ! in_array(config('session.driver'), ['file', 'array'], true) && ! in_array(config('cache.default'), ['file', 'array'], true), 'Isi SESSION_DRIVER=database dan CACHE_STORE=database (di Vercel ada banyak server sekaligus)');
        $cek('Proxy Vercel dipercaya (TRUSTED_PROXIES)', (bool) env('TRUSTED_PROXIES'), 'Isi TRUSTED_PROXIES=* di Vercel', false);

        $this->table(['Status', 'Pemeriksaan', 'Saran'], $hasil);
        $gagal = collect($hasil)->filter(fn ($h) => str_contains($h[0], 'PERBAIKI'))->count();
        $gagal ? $this->warn("{$gagal} hal wajib perlu diperbaiki sebelum online.") : $this->info('Semua pemeriksaan wajib lulus. Siap online!');

        return $gagal ? self::FAILURE : self::SUCCESS;
    }
}
