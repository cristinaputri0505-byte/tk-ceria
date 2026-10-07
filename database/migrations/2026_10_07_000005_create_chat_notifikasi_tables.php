<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('kategori', 20);          // kegiatan | pembayaran | pengumuman | chat
            $t->string('judul', 150);
            $t->string('isi', 255)->nullable();
            $t->string('url', 255)->nullable();
            $t->timestamp('dibaca_at')->nullable();
            $t->timestamps();
            $t->index(['user_id', 'dibaca_at']);
        });

        Schema::create('chat_pesan', function (Blueprint $t) {
            $t->id();
            $t->string('jenis', 10);              // pribadi | grup
            $t->foreignId('orang_tua_id')->nullable()->constrained('users')->cascadeOnDelete(); // pemilik percakapan pribadi
            $t->foreignId('pengirim_id')->constrained('users')->cascadeOnDelete();
            $t->text('isi');
            $t->boolean('dibaca')->default(false); // sudah dibaca pihak lawan (khusus pribadi)
            $t->timestamps();
            $t->index(['jenis', 'orang_tua_id', 'id']);
        });

        Schema::create('chat_grup_baca', function (Blueprint $t) {
            $t->unsignedBigInteger('user_id')->primary();
            $t->unsignedBigInteger('terakhir_id')->default(0);
            $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        foreach (['chat_grup_baca', 'chat_pesan', 'notifikasi'] as $t) Schema::dropIfExists($t);
    }
};
