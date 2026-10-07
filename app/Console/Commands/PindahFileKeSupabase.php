<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Menyalin semua file unggahan lama dari laptop (storage/app/private & storage/app/public)
 * ke Supabase Storage. Aman dijalankan berulang: file yang sudah ada di Supabase dilewati.
 *
 * Jalankan di laptop, dengan .env berisi SUPABASE_STORAGE=true dan kunci Supabase:
 *   php artisan tk:pindah-file-ke-supabase
 */
class PindahFileKeSupabase extends Command
{
    protected $signature = 'tk:pindah-file-ke-supabase {--timpa : Unggah ulang walau file sudah ada di Supabase}';
    protected $description = 'Menyalin file unggahan lama dari laptop ke Supabase Storage';

    public function handle(): int
    {
        if (config('filesystems.disks.local.driver') !== 'supabase') {
            $this->error('SUPABASE_STORAGE belum aktif. Isi SUPABASE_STORAGE=true, SUPABASE_URL, dan SUPABASE_SECRET_KEY di .env, lalu jalankan lagi.');
            return self::FAILURE;
        }

        $total = ['disalin' => 0, 'dilewati' => 0, 'gagal' => 0];
        foreach (['arsip_privat' => 'local', 'arsip_publik' => 'public'] as $asal => $tujuan) {
            $file = collect(Storage::disk($asal)->allFiles())->reject(fn ($f) => str_starts_with(basename($f), '.'))->values();
            $this->info(($tujuan === 'local' ? 'Bucket privat' : 'Bucket publik') . ": {$file->count()} file");
            $bar = $this->output->createProgressBar($file->count());
            foreach ($file as $path) {
                try {
                    if (! $this->option('timpa') && Storage::disk($tujuan)->exists($path)) {
                        $total['dilewati']++;
                    } else {
                        Storage::disk($tujuan)->writeStream($path, Storage::disk($asal)->readStream($path));
                        $total['disalin']++;
                    }
                } catch (\Throwable $e) {
                    $total['gagal']++;
                    $this->newLine();
                    $this->warn("Gagal: {$path} ({$e->getMessage()})");
                }
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();
        }

        $this->table(['Disalin', 'Sudah ada (dilewati)', 'Gagal'], [[$total['disalin'], $total['dilewati'], $total['gagal']]]);
        $total['gagal'] ? $this->warn('Ada file yang gagal. Jalankan perintah ini sekali lagi.') : $this->info('Selesai. Semua file sudah ada di Supabase Storage.');

        return $total['gagal'] ? self::FAILURE : self::SUCCESS;
    }
}
