<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tagihan', function (Blueprint $table) {
            $table->enum('metode', ['transfer', 'tunai'])->nullable()->after('status');
            $table->string('no_kuitansi', 30)->nullable()->unique()->after('metode');
            $table->foreignId('diterima_oleh')->nullable()->after('no_kuitansi')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tagihan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('diterima_oleh');
            $table->dropUnique(['no_kuitansi']);
            $table->dropColumn(['metode', 'no_kuitansi']);
        });
    }
};
