<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'guru', 'orangtua'])->default('orangtua')->after('email');
            $table->string('telepon', 20)->nullable()->after('role');
            $table->string('jabatan', 100)->nullable()->after('telepon');
            $table->string('foto')->nullable()->after('jabatan');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'telepon', 'jabatan', 'foto']);
        });
    }
};
