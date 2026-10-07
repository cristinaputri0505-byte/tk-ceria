<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Hal-hal yang berbeda perilakunya antara SQLite (lokal) dan PostgreSQL (Supabase).
 * Uji juga di PostgreSQL:  DB_CONNECTION=pgsql DB_DATABASE=nama_db_uji php artisan test
 */
class DatabaseKompatibelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function admin(): User
    {
        return User::where('email', 'admin@tkceria.sch.id')->firstOrFail();
    }

    public function test_halaman_chat_admin_terbuka(): void
    {
        // PostgreSQL menolak "boolean = 0" yang di SQLite lolos
        $this->actingAs($this->admin())->get('/chat')->assertOk();
    }

    public function test_pencarian_tidak_membedakan_huruf_besar_kecil(): void
    {
        $this->actingAs($this->admin())->get('/admin/siswa?q=alya')->assertOk()->assertSee('Alya Putri');
        $this->actingAs($this->admin())->get('/admin/siswa?q=ALYA')->assertOk()->assertSee('Alya Putri');
        $this->actingAs($this->admin())->get('/admin/pengguna?role=orangtua&q=SANTOSO')->assertOk()->assertSee('Budi Santoso');
        $this->actingAs($this->admin())->get('/admin/tagihan?q=alya')->assertOk()->assertSee('Alya Putri');
    }
}
