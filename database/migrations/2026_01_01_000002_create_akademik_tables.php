<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50);
            $table->enum('kelompok', ['A', 'B']);
            $table->string('tahun_ajaran', 20);
            $table->foreignId('wali_guru_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->string('nis', 20)->unique();
            $table->string('nama', 100);
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->date('tanggal_lahir');
            $table->string('alamat')->nullable();
            $table->string('foto')->nullable();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->foreignId('orang_tua_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('guru_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpa']);
            $table->string('keterangan')->nullable();
            $table->timestamps();
            $table->unique(['siswa_id', 'tanggal']);
        });

        Schema::create('perkembangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('guru_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('periode', 50);
            $table->string('aspek', 30);
            $table->enum('nilai', ['BB', 'MB', 'BSH', 'BSB']);
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->unique(['siswa_id', 'periode', 'aspek']);
        });

        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('keterangan', 100)->nullable();
            $table->string('deskripsi')->nullable();
            $table->string('ikon', 50)->default('star');
            $table->string('warna', 20)->default('rose');
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('kegiatan', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 100);
            $table->date('tanggal');
            $table->text('deskripsi')->nullable();
            $table->string('gambar')->nullable();
            $table->timestamps();
        });

        Schema::create('berita', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 150);
            $table->string('slug')->unique();
            $table->string('ringkasan')->nullable();
            $table->longText('isi');
            $table->string('gambar')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pendaftaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama_anak', 100);
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('program', 100);
            $table->string('nama_orang_tua', 100);
            $table->string('telepon', 20);
            $table->string('email', 100)->nullable();
            $table->string('alamat');
            $table->enum('status', ['baru', 'diproses', 'diterima', 'ditolak'])->default('baru');
            $table->string('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['pendaftaran', 'berita', 'kegiatan', 'programs', 'perkembangan', 'absensi', 'siswa', 'kelas'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
