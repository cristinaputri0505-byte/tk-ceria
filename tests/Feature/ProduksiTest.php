<?php

namespace Tests\Feature;

use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Uji kesiapan produksi. Jalankan: php artisan test --filter=ProduksiTest */
class ProduksiTest extends TestCase
{
    use RefreshDatabase;

    private array $daftar = ['nama_anak' => 'Anak Uji', 'tanggal_lahir' => '2021-01-01', 'jenis_kelamin' => 'P', 'program' => 'Kelompok A', 'nama_orang_tua' => 'Ortu Uji', 'telepon' => '0812', 'alamat' => 'Jakarta'];

    public function test_halaman_kebijakan_privasi_tampil(): void
    {
        $this->get(route('privasi'))->assertOk()->assertSee('Kebijakan Privasi')->assertSee('Hak orang tua');
        $this->get('/')->assertSee(route('privasi'), false);
    }

    public function test_pendaftaran_wajib_menyetujui_privasi(): void
    {
        $this->post(route('pendaftaran.store'), $this->daftar)->assertSessionHasErrors('setuju_privasi');
        $this->assertSame(0, Pendaftaran::count());
    }

    public function test_persetujuan_dan_izin_foto_tersimpan(): void
    {
        $this->post(route('pendaftaran.store'), $this->daftar + ['setuju_privasi' => '1'])->assertSessionHasNoErrors();
        $p = Pendaftaran::first();
        $this->assertNotNull($p->setuju_privasi_pada);
        $this->assertFalse($p->izin_foto_publik);

        $this->post(route('pendaftaran.store'), ['nama_anak' => 'Anak Dua'] + $this->daftar + ['setuju_privasi' => '1', 'izin_foto_publik' => '1']);
        $this->assertTrue(Pendaftaran::where('nama_anak', 'Anak Dua')->first()->izin_foto_publik);
    }

    public function test_halaman_error_berbahasa_indonesia(): void
    {
        $this->get('/halaman-yang-tidak-ada')->assertNotFound()->assertSee('Halaman tidak ditemukan');
    }

    public function test_seeder_contoh_ditolak_di_produksi(): void
    {
        // Lapis 1: Laravel meminta konfirmasi untuk db:seed di produksi.
        // Lapis 2 (diuji di sini): seeder kita sendiri menolak walau konfirmasi dijawab "yes" / --force.
        $this->app['env'] = 'production';
        (new \Database\Seeders\DatabaseSeeder)->run();
        $this->assertSame(0, User::count(), 'Akun contoh tidak boleh dibuat di mode produksi');
    }

    public function test_perintah_buat_admin(): void
    {
        $this->artisan('tk:buat-admin')
            ->expectsQuestion('Nama lengkap', 'Kepala TU')
            ->expectsQuestion('Email (dipakai untuk login)', 'tu@sekolah.sch.id')
            ->expectsQuestion('Kata sandi (minimal 10 karakter, campuran huruf besar, kecil & angka)', 'RahasiaKuat2026')
            ->expectsQuestion('Ulangi kata sandi', 'RahasiaKuat2026')
            ->assertSuccessful();
        $this->assertSame('admin', User::where('email', 'tu@sekolah.sch.id')->value('role'));
    }

    public function test_perintah_buat_admin_menolak_password_lemah(): void
    {
        $this->artisan('tk:buat-admin')
            ->expectsQuestion('Nama lengkap', 'X')
            ->expectsQuestion('Email (dipakai untuk login)', 'x@sekolah.sch.id')
            ->expectsQuestion('Kata sandi (minimal 10 karakter, campuran huruf besar, kecil & angka)', 'password')
            ->expectsQuestion('Ulangi kata sandi', 'password')
            ->assertFailed();
        $this->assertSame(0, User::count());
    }

    public function test_cek_produksi_mendeteksi_akun_contoh(): void
    {
        $this->seed();
        $this->artisan('tk:cek-produksi')->assertFailed();
    }

    public function test_halaman_memakai_css_lokal_bukan_cdn(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $html);
        $this->assertStringNotContainsString('unpkg.com', $html);
        $this->assertStringNotContainsString('fonts.googleapis.com', $html);
        $this->assertStringContainsString('/build/assets/', $html);
    }
}
