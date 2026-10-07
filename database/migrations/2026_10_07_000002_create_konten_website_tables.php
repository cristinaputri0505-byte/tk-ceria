<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keunggulan', function (Blueprint $table) {
            $table->id();
            $table->string('ikon', 50)->default('star');
            $table->string('judul', 100);
            $table->string('deskripsi')->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('jabatan', 100)->nullable();
            $table->string('pendidikan', 150)->nullable();
            $table->string('foto')->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('galeri', function (Blueprint $table) {
            $table->id();
            $table->string('gambar');
            $table->string('judul', 150)->nullable();
            $table->string('album', 100)->nullable()->index();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('pesan', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('email', 100)->nullable();
            $table->string('telepon', 20)->nullable();
            $table->text('pesan');
            $table->boolean('dibaca')->default(false);
            $table->timestamps();
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->string('gambar')->nullable();
            $table->text('detail')->nullable();
        });

        // ---- Isi awal kartu keunggulan (pakai isian lama dari Pengaturan jika ada) ----
        $lama = Schema::hasTable('pengaturan') ? DB::table('pengaturan')->pluck('value', 'key') : collect();
        $bawaan = [
            ['graduation-cap', 'Pendidikan Berkualitas', 'Kurikulum sesuai tahap perkembangan anak usia dini.'],
            ['shield-check', 'Lingkungan Aman', 'Fasilitas lengkap dan diawasi tenaga profesional.'],
            ['users', 'Guru Berpengalaman', 'Pendidik yang sabar, peduli, dan profesional.'],
            ['heart', 'Belajar Aktif', 'Belajar sambil bermain untuk hasil yang optimal.'],
            ['star', 'Kegiatan Kreatif', 'Beragam kegiatan untuk mengembangkan potensi anak.'],
            ['sprout', 'Tumbuh Kembang Optimal', 'Mendukung perkembangan kognitif, fisik, sosial, dan emosional.'],
        ];
        foreach ($bawaan as $i => [$ikon, $judul, $desk]) {
            $n = $i + 1;
            DB::table('keunggulan')->insert([
                'ikon' => $lama["k{$n}_ikon"] ?? $ikon,
                'judul' => $lama["k{$n}_judul"] ?? $judul,
                'deskripsi' => $lama["k{$n}_deskripsi"] ?? $desk,
                'urutan' => $n,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // ---- Isi awal Guru & Staff dari akun guru yang sudah ada ----
        $guru = DB::table('users')->where('role', 'guru')->orderBy('name')->get();
        foreach ($guru as $i => $g) {
            DB::table('staff')->insert([
                'nama' => $g->name, 'jabatan' => $g->jabatan ?? 'Guru', 'foto' => $g->foto,
                'urutan' => $i + 1, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('programs', fn (Blueprint $t) => $t->dropColumn(['gambar', 'detail']));
        foreach (['pesan', 'galeri', 'staff', 'keunggulan'] as $t) Schema::dropIfExists($t);
    }
};
