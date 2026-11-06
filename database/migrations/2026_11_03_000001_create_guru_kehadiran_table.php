<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Kehadiran guru mengajar: absen masuk/pulang oleh guru, atau dicatat admin (izin, sakit, dinas luar, alpa). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru_kehadiran', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->date('tanggal');
            $t->string('status', 10)->default('hadir');   // hadir | izin | sakit | dinas | alpa
            $t->string('jam_masuk', 5)->nullable();        // HH:MM (WIB)
            $t->string('jam_pulang', 5)->nullable();
            $t->string('keterangan', 255)->nullable();
            $t->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->unique(['user_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_kehadiran');
    }
};
