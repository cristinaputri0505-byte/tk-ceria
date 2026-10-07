<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permintaan_reset', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('telepon_kontak', 20)->nullable();   // nomor yang diisi peminta (hanya sebagai info)
            $t->string('catatan', 255)->nullable();
            $t->string('status', 10)->default('menunggu');  // menunggu | selesai | ditolak
            $t->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('diproses_pada')->nullable();
            $t->string('ip', 45)->nullable();
            $t->timestamps();
            $t->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaan_reset');
    }
};
