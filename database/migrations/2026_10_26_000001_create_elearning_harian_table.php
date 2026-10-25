<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Hasil "Soal Hari Ini" di Ruang Belajar: satu baris per anak, per tanggal, per nomor soal. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('elearning_harian', function (Blueprint $t) {
            $t->id();
            $t->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $t->date('tanggal');
            $t->unsignedTinyInteger('nomor');
            $t->unsignedTinyInteger('bintang')->default(0);
            $t->unsignedSmallInteger('dimainkan')->default(1);
            $t->timestamps();
            $t->unique(['siswa_id', 'tanggal', 'nomor']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elearning_harian');
    }
};
