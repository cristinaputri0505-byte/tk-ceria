<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->string('panggilan', 50)->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->string('agama', 30)->nullable();
            $table->unsignedTinyInteger('anak_ke')->nullable();
            $table->string('golongan_darah', 3)->nullable();
            $table->text('catatan_kesehatan')->nullable();
            $table->date('tanggal_masuk')->nullable();
            $table->string('nama_ayah', 100)->nullable();
            $table->string('pekerjaan_ayah', 100)->nullable();
            $table->string('telepon_ayah', 20)->nullable();
            $table->string('nama_ibu', 100)->nullable();
            $table->string('pekerjaan_ibu', 100)->nullable();
            $table->string('telepon_ibu', 20)->nullable();
        });

        Schema::create('tagihan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->string('jenis', 50)->default('SPP');
            $table->string('periode', 50);           // contoh: Oktober 2026
            $table->date('bulan')->nullable();        // untuk pengurutan
            $table->unsignedInteger('jumlah');
            $table->date('jatuh_tempo')->nullable();
            $table->enum('status', ['belum', 'menunggu', 'lunas'])->default('belum');
            $table->string('bukti')->nullable();      // disimpan privat
            $table->timestamp('dibayar_pada')->nullable();
            $table->string('catatan')->nullable();
            $table->timestamps();
            $table->unique(['siswa_id', 'jenis', 'periode']);
        });

        Schema::create('pengumuman', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 150);
            $table->text('isi');
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete(); // null = semua
            $table->boolean('penting')->default(false);
            $table->date('tanggal_acara')->nullable(); // jika pengumuman berupa agenda
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('dokumentasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('siswa_id')->nullable()->constrained('siswa')->cascadeOnDelete(); // null = seluruh kelas
            $table->foreignId('guru_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('gambar'); // disimpan privat
            $table->string('keterangan', 255)->nullable();
            $table->date('tanggal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['dokumentasi', 'pengumuman', 'tagihan'] as $t) Schema::dropIfExists($t);
        Schema::table('siswa', fn (Blueprint $t) => $t->dropColumn([
            'panggilan', 'tempat_lahir', 'agama', 'anak_ke', 'golongan_darah', 'catatan_kesehatan', 'tanggal_masuk',
            'nama_ayah', 'pekerjaan_ayah', 'telepon_ayah', 'nama_ibu', 'pekerjaan_ibu', 'telepon_ibu',
        ]));
    }
};
