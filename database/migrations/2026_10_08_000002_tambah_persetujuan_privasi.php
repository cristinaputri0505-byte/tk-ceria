<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Persetujuan orang tua (UU PDP): kebijakan privasi & izin foto anak tampil di website publik. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftaran', function (Blueprint $table) {
            $table->timestamp('setuju_privasi_pada')->nullable();
            $table->boolean('izin_foto_publik')->default(false);
        });
        Schema::table('siswa', function (Blueprint $table) {
            $table->boolean('izin_foto_publik')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran', fn (Blueprint $t) => $t->dropColumn(['setuju_privasi_pada', 'izin_foto_publik']));
        Schema::table('siswa', fn (Blueprint $t) => $t->dropColumn('izin_foto_publik'));
    }
};
