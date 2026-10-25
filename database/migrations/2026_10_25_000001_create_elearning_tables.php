<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** E-Learning anak: tugas interaktif buatan guru/admin dan hasil pengerjaan tiap siswa. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('elearning_tugas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('kelas_id')->nullable()->constrained('kelas')->cascadeOnDelete(); // null = semua kelas
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();     // pembuat
            $t->string('tipe', 20);                 // kuis | hitung | cocok | gambar
            $t->string('judul', 100);
            $t->string('instruksi', 300);
            $t->string('ikon', 16)->default('⭐');
            $t->string('warna', 10)->default('pink');
            $t->unsignedTinyInteger('bintang')->default(3);
            $t->json('data');
            $t->boolean('aktif')->default(true);
            $t->unsignedSmallInteger('urutan')->default(0);
            $t->timestamps();
            $t->index(['kelas_id', 'aktif']);
        });

        Schema::create('elearning_hasil', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tugas_id')->constrained('elearning_tugas')->cascadeOnDelete();
            $t->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $t->unsignedTinyInteger('bintang')->default(0);
            $t->unsignedSmallInteger('dimainkan')->default(1);
            $t->string('gambar')->nullable();       // karya menggambar, disimpan privat
            $t->timestamp('selesai_pada')->nullable();
            $t->timestamps();
            $t->unique(['tugas_id', 'siswa_id']);
        });

        // 4 tugas contoh (untuk semua kelas) agar Ruang Belajar langsung bisa dicoba. Admin bisa mengubah atau menghapusnya.
        $now = now();
        $contoh = [
            ['kuis', 'Tebak Hewan Kesukaan', 'Hewan manakah yang memiliki belalai panjang dan telinga yang lebar?', '🐘', 'pink', 3,
                ['gambar' => '🌿🐘🥜', 'opsi' => ['🐘 Gajah', '🦒 Jerapah', '🐒 Monyet', '🦆 Bebek'], 'benar' => 0]],
            ['hitung', 'Berhitung Buah Apel', 'Ayo hitung bersama! Ada berapa buah apel merah di bawah ini?', '🍎', 'biru', 3,
                ['emoji' => '🍎', 'nama' => 'buah apel', 'jumlah' => 5]],
            ['cocok', 'Mencocokkan Warna Benda', 'Pasangkan benda di sebelah kiri dengan warna yang tepat di sebelah kanan!', '🎨', 'hijau', 4,
                ['pasangan' => [['kiri' => '🍓 Stroberi', 'kanan' => '🔴 Merah'], ['kiri' => '🍌 Pisang', 'kanan' => '🟡 Kuning'], ['kiri' => '🍃 Daun', 'kanan' => '🟢 Hijau'], ['kiri' => '☁️ Langit', 'kanan' => '🔵 Biru']]]],
            ['gambar', 'Menggambar Rumah Impian', 'Gambarlah rumah impianmu yang indah! Kamu juga bisa menempelkan stiker pohon dan matahari.', '🖍️', 'kuning', 5,
                ['stiker' => ['🏠', '🌳', '🌻', '☀️', '☁️', '🌈', '🦋', '🚗']]],
        ];
        foreach ($contoh as $i => [$tipe, $judul, $instruksi, $ikon, $warna, $bintang, $data]) {
            DB::table('elearning_tugas')->insert([
                'kelas_id' => null, 'user_id' => null, 'tipe' => $tipe, 'judul' => $judul, 'instruksi' => $instruksi, 'ikon' => $ikon,
                'warna' => $warna, 'bintang' => $bintang, 'data' => json_encode($data, JSON_UNESCAPED_UNICODE), 'aktif' => true, 'urutan' => $i,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('elearning_hasil');
        Schema::dropIfExists('elearning_tugas');
    }
};
