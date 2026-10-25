<?php

namespace Tests\Feature;

use App\Models\ElearningHarian;
use App\Models\ElearningHasil;
use App\Models\ElearningTugas;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use App\Support\Basisdata;
use App\Support\Pengaturan;
use App\Support\SoalHarian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** E-Learning anak: kelola tugas (guru/admin) dan Ruang Belajar (lewat akun orang tua). */
class ElearningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Basisdata::lupakan();
    }

    private function u(string $email): User { return User::where('email', $email)->firstOrFail(); }
    private function admin(): User { return $this->u('admin@tkceria.sch.id'); }
    private function rina(): User { return $this->u('rina@tkceria.sch.id'); }
    private function budi(): User { return $this->u('budi@contoh.com'); }
    private function dewi(): User { return $this->u('dewi@contoh.com'); }
    private function kelasRina(): Kelas { return $this->rina()->kelasDiampu()->firstOrFail(); }
    private function kelasLain(): Kelas { return Kelas::whereKeyNot($this->kelasRina()->id)->firstOrFail(); }

    private function dasar(array $tambah = []): array
    {
        return $tambah + ['judul' => 'Tebak Hewan', 'instruksi' => 'Hewan apa yang punya belalai?', 'ikon' => '🐘', 'warna' => 'pink', 'bintang' => 3, 'aktif' => 1];
    }

    private function kuis(?int $kelasId, array $tambah = []): array
    {
        return $this->dasar($tambah + ['tipe' => 'kuis', 'kelas_id' => $kelasId, 'kuis_gambar' => '🐘', 'kuis_opsi' => ['🐘 Gajah', '🦒 Jerapah', '🐒 Monyet', '🦆 Bebek'], 'kuis_benar' => 0]);
    }

    private function buatTugas(?int $kelasId, array $tambah = []): ElearningTugas
    {
        return ElearningTugas::create($tambah + [
            'kelas_id' => $kelasId, 'tipe' => 'kuis', 'judul' => 'Tebak', 'instruksi' => 'Pilih', 'ikon' => '⭐', 'warna' => 'pink', 'bintang' => 3, 'aktif' => true,
            'data' => ['gambar' => '🐘', 'opsi' => ['A', 'B', 'C', 'D'], 'benar' => 0],
        ]);
    }

    private function anakBudiDiKelas(Kelas $k): Siswa
    {
        $anak = $this->budi()->anak()->firstOrFail();
        $anak->update(['kelas_id' => $k->id]);

        return $anak;
    }

    public function test_guru_membuat_keempat_jenis_tugas_untuk_kelasnya(): void
    {
        $k = $this->kelasRina();
        $jenis = [
            'kuis' => $this->kuis($k->id),
            'hitung' => $this->dasar(['tipe' => 'hitung', 'kelas_id' => $k->id, 'hitung_emoji' => '🍎', 'hitung_nama' => 'apel', 'hitung_jumlah' => 5]),
            'cocok' => $this->dasar(['tipe' => 'cocok', 'kelas_id' => $k->id, 'cocok_kiri' => ['🐰 Kelinci', '🐒 Monyet', '', ''], 'cocok_kanan' => ['🥕 Wortel', '🍌 Pisang', '', '']]),
            'gambar' => $this->dasar(['tipe' => 'gambar', 'kelas_id' => $k->id, 'gambar_stiker' => '🏠  🌳 🌻']),
        ];
        foreach ($jenis as $tipe => $data) {
            $this->actingAs($this->rina())->post(route('elearning.store'), $data)->assertRedirect()->assertSessionHasNoErrors();
            $t = ElearningTugas::where('tipe', $tipe)->latest('id')->firstOrFail();
            $this->assertSame($k->id, $t->kelas_id);
        }

        $this->assertSame(['🏠', '🌳', '🌻'], ElearningTugas::where('tipe', 'gambar')->latest('id')->first()->data['stiker']);
        $this->assertCount(2, ElearningTugas::where('tipe', 'cocok')->latest('id')->first()->data['pasangan']);
        $this->actingAs($this->rina())->get(route('elearning.index'))->assertOk()->assertSee('Tebak Hewan');
        $this->actingAs($this->rina())->get(route('elearning.create'))->assertOk();
    }

    public function test_guru_tidak_bisa_membuat_atau_mengubah_tugas_kelas_lain_atau_semua_kelas(): void
    {
        $this->actingAs($this->rina())->post(route('elearning.store'), $this->kuis($this->kelasLain()->id))->assertSessionHasErrors('kelas_id');
        $this->actingAs($this->rina())->post(route('elearning.store'), $this->kuis(null))->assertSessionHasErrors('kelas_id');

        $global = $this->buatTugas(null);
        $lain = $this->buatTugas($this->kelasLain()->id);
        foreach ([$global, $lain] as $t) {
            $this->actingAs($this->rina())->get(route('elearning.edit', $t))->assertForbidden();
            $this->actingAs($this->rina())->put(route('elearning.update', $t), $this->kuis($this->kelasRina()->id))->assertForbidden();
            $this->actingAs($this->rina())->delete(route('elearning.destroy', $t))->assertForbidden();
        }

        // Admin boleh membuat tugas untuk semua kelas
        $this->actingAs($this->admin())->post(route('elearning.store'), $this->kuis(null, ['judul' => 'Untuk semua']))->assertSessionHasNoErrors();
        $this->assertNull(ElearningTugas::where('judul', 'Untuk semua')->value('kelas_id'));
    }

    public function test_orang_tua_dan_tamu_tidak_bisa_membuka_kelola_tugas(): void
    {
        $this->actingAs($this->budi())->get(route('elearning.index'))->assertForbidden();
        $this->actingAs($this->budi())->post(route('elearning.store'), $this->kuis(null))->assertForbidden();
        auth()->logout();
        $this->get(route('elearning.index'))->assertRedirect(route('login'));
        $this->get(route('belajar.index'))->assertRedirect(route('login'));
    }

    public function test_anak_hanya_melihat_tugas_kelasnya_dan_tugas_semua_kelas(): void
    {
        $anak = $this->anakBudiDiKelas($this->kelasRina());
        $this->buatTugas($this->kelasRina()->id, ['judul' => 'Tugas Kelasku']);
        $this->buatTugas(null, ['judul' => 'Tugas Semua Kelas']);
        $this->buatTugas($this->kelasLain()->id, ['judul' => 'Tugas Kelas Lain']);
        $this->buatTugas($this->kelasRina()->id, ['judul' => 'Tugas Disembunyikan', 'aktif' => false]);

        $this->actingAs($this->budi())->get(route('belajar.main', $anak))->assertOk()
            ->assertSee('Tugas Kelasku')->assertSee('Tugas Semua Kelas')
            ->assertDontSee('Tugas Kelas Lain')->assertDontSee('Tugas Disembunyikan');
    }

    public function test_orang_tua_tidak_bisa_membuka_atau_mengirim_hasil_anak_orang_lain(): void
    {
        $anak = $this->anakBudiDiKelas($this->kelasRina());
        $t = $this->buatTugas(null);

        $this->actingAs($this->dewi())->get(route('belajar.main', $anak))->assertForbidden();
        $this->actingAs($this->dewi())->postJson(route('belajar.selesai', [$anak, $t]))->assertForbidden();
        $this->assertSame(0, ElearningHasil::count());

        // Tugas untuk kelas lain tidak bisa dikirim walau anaknya sendiri
        $lain = $this->buatTugas($this->kelasLain()->id);
        $this->actingAs($this->budi())->postJson(route('belajar.selesai', [$anak, $lain]))->assertNotFound();
    }

    public function test_bintang_hanya_dihitung_sekali_per_tugas(): void
    {
        $anak = $this->anakBudiDiKelas($this->kelasRina());
        $t1 = $this->buatTugas(null, ['bintang' => 3]);
        $t2 = $this->buatTugas($this->kelasRina()->id, ['bintang' => 5]);

        $this->actingAs($this->budi())->postJson(route('belajar.selesai', [$anak, $t1]))->assertOk()->assertJson(['ok' => true, 'baru' => true, 'total' => 3]);
        $this->actingAs($this->budi())->postJson(route('belajar.selesai', [$anak, $t1]))->assertOk()->assertJson(['baru' => false, 'total' => 3]);
        $this->actingAs($this->budi())->postJson(route('belajar.selesai', [$anak, $t2]))->assertOk()->assertJson(['total' => 8]);

        $this->assertSame(2, ElearningHasil::where('tugas_id', $t1->id)->value('dimainkan'));
        $this->actingAs($this->rina())->get(route('elearning.rekap', ['kelas_id' => $this->kelasRina()->id]))->assertOk()->assertSee('⭐ 8');
    }

    public function test_karya_gambar_tersimpan_privat_dan_hanya_untuk_yang_berhak(): void
    {
        Storage::fake('local');
        $anak = $this->anakBudiDiKelas($this->kelasRina());
        $t = $this->buatTugas($this->kelasRina()->id, ['tipe' => 'gambar', 'data' => ['stiker' => ['🏠']]]);

        // PNG hitam 200x100 (ditulis langsung agar test tidak butuh ekstensi GD)
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAMgAAABkCAIAAABM5OhcAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAUElEQVR4nO3BAQEAAACCIP+vbkhAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPwZ6sQAAZV8l2IAAAAASUVORK5CYII=');

        $j = $this->actingAs($this->budi())->postJson(route('belajar.selesai', [$anak, $t]), ['gambar' => 'data:image/png;base64,' . base64_encode($png)])
            ->assertOk()->json();
        $h = ElearningHasil::firstOrFail();
        Storage::disk('local')->assertExists($h->gambar);
        $this->assertSame($h->gambar_url, $j['gambar']);

        $this->actingAs($this->budi())->get($h->gambar_url)->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->actingAs($this->rina())->get($h->gambar_url)->assertOk();
        $this->actingAs($this->admin())->get($h->gambar_url)->assertOk();
        $this->actingAs($this->dewi())->get($h->gambar_url)->assertForbidden();
        $this->actingAs($this->u('sari@tkceria.sch.id'))->get($h->gambar_url)->assertForbidden();

        // Menggambar ulang mengganti file lama
        $lama = $h->gambar;
        $this->actingAs($this->budi())->postJson(route('belajar.selesai', [$anak, $t]), ['gambar' => 'data:image/png;base64,' . base64_encode($png)])->assertOk();
        Storage::disk('local')->assertMissing($lama);

        // Bukan PNG ditolak
        $this->actingAs($this->budi())->postJson(route('belajar.selesai', [$anak, $t]), ['gambar' => 'data:image/png;base64,' . base64_encode('<?php echo 1; ?>')])->assertStatus(422);

        // Menghapus tugas ikut menghapus file karya
        $sekarang = $h->fresh()->gambar;
        $this->actingAs($this->rina())->delete(route('elearning.destroy', $t))->assertRedirect();
        Storage::disk('local')->assertMissing($sekarang);
    }

    public function test_teks_guru_tidak_bisa_menyisipkan_skrip(): void
    {
        $anak = $this->anakBudiDiKelas($this->kelasRina());
        $this->buatTugas(null, ['judul' => '</script><script>alert(1)</script>']);

        $res = $this->actingAs($this->budi())->get(route('belajar.main', $anak))->assertOk();
        $this->assertStringNotContainsString('<script>alert(1)</script>', $res->getContent());
    }

    public function test_fitur_bisa_dimatikan_dari_pengaturan_portal(): void
    {
        $anak = $this->anakBudiDiKelas($this->kelasRina());
        Pengaturan::simpan(['portal_fitur_belajar' => '0']);

        $this->actingAs($this->budi())->get(route('belajar.main', $anak))->assertNotFound();
        $this->actingAs($this->budi())->get(route('orangtua.dashboard'))->assertOk()->assertDontSee(route('belajar.index'));
    }

    public function test_website_tetap_jalan_sebelum_database_diperbarui(): void
    {
        Schema::drop('elearning_hasil');
        Schema::drop('elearning_tugas');
        Basisdata::lupakan();

        $this->actingAs($this->admin())->get(route('elearning.index'))->assertOk()->assertSee('belum aktif');
        $this->actingAs($this->rina())->get(route('elearning.rekap'))->assertOk()->assertSee('belum aktif');
        $this->actingAs($this->budi())->get(route('orangtua.dashboard'))->assertOk()->assertDontSee('Ayo Belajar');
        $this->actingAs($this->budi())->get(route('belajar.index'))->assertNotFound();
        $this->actingAs($this->admin())->delete(route('admin.siswa.destroy', Siswa::firstOrFail()))->assertRedirect();
    }

    public function test_suara_server_hanya_untuk_orang_tua_dan_aman_bila_mesin_tidak_ada(): void
    {
        $url = route('belajar.suara', ['teks' => 'Halo anak hebat']);
        auth()->logout();
        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs($this->dewi())->get(route('belajar.suara'))->assertSessionHasErrors('teks');

        putenv('SUARA_DIR=' . sys_get_temp_dir() . '/tidak-ada-mesin-suara');
        try {
            $this->assertFalse(\App\Support\Suara::tersedia());
            $this->actingAs($this->dewi())->get($url)->assertStatus(503);
            // Halaman tetap berjalan: memakai suara browser
            $anak = $this->anakBudiDiKelas($this->kelasRina());
            $this->actingAs($this->budi())->get(route('belajar.main', $anak))->assertOk()->assertSee('"suara":null', false);
        } finally {
            putenv('SUARA_DIR');
        }

        $this->assertSame('Ayo hitung apel! 5', \App\Support\Suara::bersihkan('🍎 Ayo hitung 🍎 apel! 5 ✨'));
    }

    public function test_suara_server_menghasilkan_wav_bila_mesin_tersedia(): void
    {
        $dir = getenv('SUARA_UJI_DIR');
        if (! $dir || ! is_dir($dir)) $this->markTestSkipped('Mesin suara tidak ada di komputer ini (normal di laptop; tersedia di server Vercel).');

        putenv('SUARA_DIR=' . $dir);
        try {
            $res = $this->actingAs($this->budi())->get(route('belajar.suara', ['teks' => 'Ada berapa buah apel?']))->assertOk();
            $this->assertSame('audio/wav', $res->headers->get('Content-Type'));
            $this->assertStringStartsWith('RIFF', $res->getContent());
        } finally {
            putenv('SUARA_DIR');
        }
    }

    public function test_soal_hari_ini_sama_sepanjang_hari_dan_berganti_besok(): void
    {
        $anak = $this->anakBudiDiKelas($this->kelasRina());
        $this->travelTo('2026-10-26 08:00:00');
        $pagi = SoalHarian::untuk($anak);
        $this->travelTo('2026-10-26 20:00:00');
        $this->assertSame($pagi, SoalHarian::untuk($anak));
        $this->assertCount(4, $pagi);
        $this->assertSame(['kuis', 'hitung', 'cocok', 'kuis'], array_column($pagi, 'tipe'));

        // Seminggu ke depan: setiap hari soalnya berbeda dari hari sebelumnya
        $kemarin = $pagi;
        for ($i = 1; $i <= 7; $i++) {
            $this->travelTo("2026-10-26 08:00:00");
            $this->travel($i)->days();
            $hari = SoalHarian::untuk($anak);
            $this->assertNotSame(array_column($kemarin, 'data'), array_column($hari, 'data'));
            foreach ($hari as $soal) {
                if ($soal['tipe'] === 'kuis') {
                    $this->assertCount(4, $soal['data']['opsi']);
                    $this->assertCount(4, array_unique($soal['data']['opsi']));
                    $this->assertArrayHasKey($soal['data']['benar'], $soal['data']['opsi']);
                }
                if ($soal['tipe'] === 'hitung') $this->assertTrue($soal['data']['jumlah'] >= 2 && $soal['data']['jumlah'] <= 10);
                if ($soal['tipe'] === 'cocok') $this->assertCount(3, $soal['data']['pasangan']);
            }
            $kemarin = $hari;
        }

        $this->actingAs($this->budi())->get(route('belajar.main', $anak))->assertOk()->assertSee('"harian":true', false);
    }

    public function test_bintang_soal_hari_ini_sekali_per_hari_dan_terjumlah(): void
    {
        $anak = $this->anakBudiDiKelas($this->kelasRina());
        $this->travelTo('2026-10-26 09:00:00');
        $url = fn ($tgl, $no) => route('belajar.harian', ['siswa' => $anak->id, 'tanggal' => $tgl, 'nomor' => $no]);

        $this->actingAs($this->budi())->postJson($url('2026-10-26', 1))->assertOk()->assertJson(['baru' => true, 'total' => 2]);
        $this->actingAs($this->budi())->postJson($url('2026-10-26', 1))->assertOk()->assertJson(['baru' => false, 'total' => 2]);
        $this->actingAs($this->budi())->postJson($url('2026-10-26', 2))->assertOk()->assertJson(['total' => 4]);
        $this->actingAs($this->budi())->postJson($url('2026-10-26', 9))->assertNotFound();
        $this->actingAs($this->budi())->postJson($url('2026-10-20', 1))->assertNotFound();
        $this->actingAs($this->dewi())->postJson($url('2026-10-26', 3))->assertForbidden();

        // Besok: soal baru bisa dikerjakan lagi, bintang kemarin tetap terhitung
        $this->travelTo('2026-10-27 09:00:00');
        $this->actingAs($this->budi())->postJson($url('2026-10-27', 1))->assertOk()->assertJson(['baru' => true, 'total' => 6]);
        $this->actingAs($this->budi())->get(route('belajar.main', $anak))->assertOk()->assertSee('"bintangLama":4', false);
        $this->assertSame(3, ElearningHarian::count());
    }

    public function test_soal_hari_ini_bisa_dimatikan_dan_aman_sebelum_database_diperbarui(): void
    {
        $anak = $this->anakBudiDiKelas($this->kelasRina());
        Pengaturan::simpan(['belajar_harian' => '0']);
        $this->actingAs($this->budi())->get(route('belajar.main', $anak))->assertOk()->assertDontSee('"harian":true', false);

        Pengaturan::simpan(['belajar_harian' => '4']);
        Schema::drop('elearning_harian');
        Basisdata::lupakan();
        $this->actingAs($this->budi())->get(route('belajar.main', $anak))->assertOk()->assertDontSee('"harian":true', false);
        $this->actingAs($this->budi())->postJson(route('belajar.harian', ['siswa' => $anak->id, 'tanggal' => now()->toDateString(), 'nomor' => 1]))->assertNotFound();
    }
}
