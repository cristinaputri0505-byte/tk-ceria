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
        $cek('Link folder upload (storage:link)', file_exists(public_path('storage')), 'Jalankan: php artisan storage:link');
        $cek('Folder storage bisa ditulisi', is_writable(storage_path('app')) && is_writable(storage_path('logs')), 'Beri izin tulis ke folder storage/ dan bootstrap/cache/');

        $adaAdmin = rescue(fn () => User::where('role', User::ADMIN)->exists(), false, false);
        $cek('Ada akun admin', $adaAdmin, 'Jalankan: php artisan tk:buat-admin');

        $demo = rescue(fn () => User::whereIn('email', ['admin@tkceria.sch.id', 'rina@tkceria.sch.id', 'sari@tkceria.sch.id', 'dimas@tkceria.sch.id', 'budi@contoh.com', 'dewi@contoh.com'])->get()
            ->filter(fn ($u) => Hash::check('password', $u->password))->pluck('email'), collect(), false);
        $cek('Tidak ada akun contoh berpassword "password"', $demo->isEmpty(), 'Hapus/ganti password akun: ' . $demo->join(', '));

        $lemah = rescue(fn () => User::all()->filter(fn ($u) => Hash::check('password', $u->password))->count(), 0, false);
        $cek('Tidak ada akun lain berpassword "password"', $lemah === 0, "{$lemah} akun masih memakai password \"password\"");

        $cek('Driver database bukan SQLite (disarankan MySQL di hosting)', config('database.default') !== 'sqlite', 'SQLite boleh untuk sekolah kecil; MySQL lebih kuat untuk banyak pengguna', false);
        $cek('Konfigurasi sudah di-cache (lebih cepat)', app()->configurationIsCached(), 'Jalankan: php artisan optimize', false);

        $this->table(['Status', 'Pemeriksaan', 'Saran'], $hasil);
        $gagal = collect($hasil)->filter(fn ($h) => str_contains($h[0], 'PERBAIKI'))->count();
        $gagal ? $this->warn("{$gagal} hal wajib perlu diperbaiki sebelum online.") : $this->info('Semua pemeriksaan wajib lulus. Siap online!');

        return $gagal ? self::FAILURE : self::SUCCESS;
    }
}
