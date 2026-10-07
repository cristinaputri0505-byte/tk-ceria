<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/** Memindahkan foto anak yang sudah ada dari penyimpanan publik ke privat (storage/app/private). */
return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('siswa')->whereNotNull('foto')->pluck('foto') as $path) {
            if (Storage::disk('public')->exists($path) && ! Storage::disk('local')->exists($path)) {
                Storage::disk('local')->put($path, Storage::disk('public')->get($path));
                Storage::disk('public')->delete($path);
            }
        }
    }

    public function down(): void
    {
        foreach (DB::table('siswa')->whereNotNull('foto')->pluck('foto') as $path) {
            if (Storage::disk('local')->exists($path) && ! Storage::disk('public')->exists($path)) {
                Storage::disk('public')->put($path, Storage::disk('local')->get($path));
                Storage::disk('local')->delete($path);
            }
        }
    }
};
