<?php

namespace App\Support;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

/**
 * Pembantu struktur database.
 * Di Vercel, migrasi tidak berjalan otomatis saat deploy. Fitur yang butuh tabel baru memeriksa
 * dulu apakah tabelnya sudah ada, agar website tidak error sebelum admin menekan
 * "Perbarui database" di halaman Admin → Cek Sistem.
 */
class Basisdata
{
    protected static array $tabel = [];

    public static function ada(string $tabel): bool
    {
        return static::$tabel[$tabel] ??= rescue(fn () => Schema::hasTable($tabel), false, false);
    }

    public static function lupakan(): void
    {
        static::$tabel = [];
    }

    /** Nama migrasi yang belum dijalankan. */
    public static function migrasiTertunda(): array
    {
        $migrator = app('migrator');
        if (! $migrator->repositoryExists()) return ['(tabel migrations belum ada)'];
        $sudah = $migrator->getRepository()->getRan();
        $semua = array_keys($migrator->getMigrationFiles([database_path('migrations')]));

        return array_values(array_diff($semua, $sudah));
    }

    /** Jalankan migrasi yang tertunda. Mengembalikan [berhasil, keluaran]. */
    public static function perbarui(): array
    {
        try {
            $kode = Artisan::call('migrate', ['--force' => true]);
            static::lupakan();

            return [$kode === 0, trim(Artisan::output())];
        } catch (\Throwable $e) {
            return [false, mb_substr($e->getMessage(), 0, 500)];
        }
    }
}
