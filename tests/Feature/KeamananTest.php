<?php

namespace Tests\Feature;

use App\Models\Dokumentasi;
use App\Models\Kelas;
use App\Models\Notifikasi;
use App\Models\Pendaftaran;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Uji keamanan & regresi TK Ceria.
 * Jalankan:  php artisan test --filter=KeamananTest
 * Memakai database SQLite di memori (lihat phpunit.xml), data asli tidak tersentuh.
 */
class KeamananTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(); // data contoh (akun admin, guru, orang tua, siswa, tagihan) untuk setiap tes
    }

    private function u(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    private function admin(): User { return $this->u('admin@tkceria.sch.id'); }
    private function rina(): User { return $this->u('rina@tkceria.sch.id'); }
    private function budi(): User { return $this->u('budi@contoh.com'); }
    private function dewi(): User { return $this->u('dewi@contoh.com'); }

    private function tagihanMilik(User $ortu, string $status): Tagihan
    {
        return Tagihan::whereHas('siswa', fn ($q) => $q->where('orang_tua_id', $ortu->id))->where('status', $status)->firstOrFail();
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        foreach (['/admin', '/guru', '/orangtua', '/chat', '/notifikasi'] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_setiap_peran_hanya_bisa_membuka_panelnya(): void
    {
        $this->actingAs($this->budi())->get('/admin')->assertForbidden();
        $this->actingAs($this->budi())->get('/guru')->assertForbidden();
        $this->actingAs($this->rina())->get('/admin')->assertForbidden();
        $this->actingAs($this->rina())->get('/orangtua')->assertForbidden();
    }

    public function test_orang_tua_tidak_bisa_melihat_data_anak_lain(): void
    {
        $anakDewi = Siswa::where('orang_tua_id', $this->dewi()->id)->first();
        $this->actingAs($this->budi())->get(route('orangtua.anak', $anakDewi))->assertForbidden();
        $this->actingAs($this->budi())->get(route('tagihan.kuitansi', $this->tagihanMilik($this->dewi(), 'lunas')))->assertForbidden();
    }

    public function test_orang_tua_tidak_bisa_membayar_tagihan_orang_lain(): void
    {
        $t = $this->tagihanMilik($this->dewi(), 'belum');
        $this->actingAs($this->budi())->post(route('orangtua.bayar', $t), ['bukti' => UploadedFile::fake()->image('a.png')])->assertForbidden();
    }

    public function test_bukti_bayar_berisi_kode_php_ditolak(): void
    {
        $t = $this->tagihanMilik($this->budi(), 'belum');
        $this->actingAs($this->budi())->post(route('orangtua.bayar', $t), [
            'bukti' => UploadedFile::fake()->createWithContent('shell.php', '<?php system($_GET["c"]); ?>'),
        ])->assertSessionHasErrors('bukti');
        $this->assertNull($t->fresh()->bukti);
    }

    public function test_guru_tidak_bisa_mengelola_kelas_lain(): void
    {
        $b1 = Kelas::where('nama', 'B1 Pelangi')->first();
        $siswaB1 = $b1->siswa()->first();
        $this->actingAs($this->rina())->post(route('guru.absensi.store'), [
            'kelas_id' => $b1->id, 'tanggal' => today()->toDateString(), 'status' => [$siswaB1->id => 'alpa'],
        ])->assertForbidden();
        $this->actingAs($this->rina())->get(route('guru.perkembangan.edit', $siswaB1))->assertForbidden();
    }

    public function test_tanggal_rusak_di_url_absensi_tidak_error(): void
    {
        $this->actingAs($this->rina())->get('/guru/absensi?tanggal=bukan-tanggal')->assertOk();
        $this->actingAs($this->admin())->get('/admin/absensi?tanggal=2026-13-45')->assertOk();
    }

    public function test_chat_pribadi_tidak_bocor_ke_orang_tua_lain(): void
    {
        $this->actingAs($this->admin())->postJson(route('chat.pribadi.kirim'), ['isi' => 'RAHASIA-DEWI', 'ortu' => $this->dewi()->id])->assertOk();
        $this->actingAs($this->budi())->getJson(route('chat.pribadi.data', ['ortu' => $this->dewi()->id]))
            ->assertOk()->assertDontSee('RAHASIA-DEWI');
    }

    public function test_notifikasi_orang_lain_tidak_bisa_dibuka(): void
    {
        $n = Notifikasi::create(['user_id' => $this->dewi()->id, 'kategori' => 'pengumuman', 'judul' => 'x', 'url' => '/']);
        $this->actingAs($this->budi())->get(route('notifikasi.buka', $n))->assertForbidden();
        $this->actingAs($this->dewi())->get(route('notifikasi.buka', $n))->assertRedirect('/');
    }

    public function test_orang_tua_tidak_bisa_menaikkan_hak_akses(): void
    {
        $budi = $this->budi();
        $this->actingAs($budi)->put(route('orangtua.profil.update'), ['name' => 'Budi Santoso', 'role' => 'admin', 'email' => 'x@x.com']);
        $this->assertSame('orangtua', $budi->fresh()->role);
        $this->assertSame('budi@contoh.com', $budi->fresh()->email);
        $this->actingAs($budi)->post(route('admin.pengguna.masuk', $this->dewi()))->assertForbidden();
    }

    public function test_foto_anak_hanya_untuk_yang_berhak(): void
    {
        Storage::fake('local');
        $anak = Siswa::where('orang_tua_id', $this->budi()->id)->first();
        $anak->update(['foto' => UploadedFile::fake()->image('anak.jpg')->store('siswa', 'local')]);

        $this->get(route('siswa.foto', $anak))->assertRedirect(route('login'));
        $this->actingAs($this->dewi())->get(route('siswa.foto', $anak))->assertForbidden();
        $this->actingAs($this->budi())->get(route('siswa.foto', $anak))->assertOk();
        $this->actingAs($this->admin())->get(route('siswa.foto', $anak))->assertOk();
    }

    public function test_foto_dokumentasi_privat(): void
    {
        Storage::fake('local');
        $a1 = Kelas::where('nama', 'A1 Matahari')->first();
        $d = Dokumentasi::create(['kelas_id' => $a1->id, 'gambar' => UploadedFile::fake()->image('d.jpg')->store('dokumentasi', 'local'), 'tanggal' => today()]);
        $this->get(route('dokumentasi.foto', $d))->assertRedirect(route('login'));
        $this->actingAs($this->budi())->get(route('dokumentasi.foto', $d))->assertOk();
    }

    public function test_input_xss_ditampilkan_aman(): void
    {
        $p = '<img src=x onerror=alert(1)>';
        Pendaftaran::create(['nama_anak' => $p, 'tanggal_lahir' => '2021-01-01', 'jenis_kelamin' => 'L', 'program' => 'A', 'nama_orang_tua' => $p, 'telepon' => '1', 'alamat' => $p, 'status' => 'baru']);
        $this->actingAs($this->admin())->get('/admin/pendaftaran')->assertOk()->assertDontSee($p, false);
    }

    public function test_link_media_sosial_javascript_ditolak(): void
    {
        $this->actingAs($this->admin())->post(route('admin.pengaturan.update', 'kontak'), ['facebook' => 'javascript:alert(1)'])
            ->assertSessionHasErrors('facebook');
    }

    public function test_formulir_pendaftaran_dibatasi_dari_spam(): void
    {
        $data = ['nama_anak' => 'A', 'tanggal_lahir' => '2021-01-01', 'jenis_kelamin' => 'L', 'program' => 'Kelompok A', 'nama_orang_tua' => 'B', 'telepon' => '1', 'alamat' => 'C'];
        for ($i = 0; $i < 5; $i++) $this->post(route('pendaftaran.store'), $data)->assertRedirect();
        $this->post(route('pendaftaran.store'), $data)->assertStatus(429);
        // Formulir kontak punya penghitung sendiri, tidak ikut terblokir
        $this->post(route('kontak.kirim'), ['nama' => 'X', 'telepon' => '1', 'pesan' => 'halo'])->assertRedirect();
    }

    public function test_batas_login_per_akun(): void
    {
        for ($i = 0; $i < 5; $i++) $this->post(route('login.attempt'), ['email' => 'admin@tkceria.sch.id', 'password' => 'salah']);
        $this->post(route('login.attempt'), ['email' => 'admin@tkceria.sch.id', 'password' => 'salah'])->assertStatus(429);
        // Akun lain dari IP yang sama tetap bisa login (Wi-Fi sekolah bersama)
        $this->post(route('login.attempt'), ['email' => 'budi@contoh.com', 'password' => 'password'])->assertRedirect();
    }

    public function test_header_keamanan_terpasang(): void
    {
        $this->get('/')->assertHeader('X-Frame-Options', 'SAMEORIGIN')->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_zona_waktu_aplikasi_wib(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
    }
}
