<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Satu kegiatan bisa punya banyak foto (album kegiatan). Kolom kegiatan.gambar tetap dipakai sebagai foto sampul. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatan_foto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('kegiatan')->cascadeOnDelete();
            $table->string('gambar');
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
            $table->index(['kegiatan_id', 'urutan']);
        });

        // Foto lama (satu per kegiatan) menjadi foto pertama album kegiatan tersebut.
        $now = now();
        foreach (DB::table('kegiatan')->whereNotNull('gambar')->get(['id', 'gambar']) as $k) {
            DB::table('kegiatan_foto')->insert(['kegiatan_id' => $k->id, 'gambar' => $k->gambar, 'urutan' => 0, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan_foto');
    }
};
