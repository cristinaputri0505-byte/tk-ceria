<?php

namespace Tests\Feature;

use App\Models\Pendaftaran;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Tanggal lahir dipilih lewat tiga daftar (tanggal · bulan · tahun) dan dibatasi ke usia anak TK. */
class TanggalLahirTest extends TestCase
{
    use RefreshDatabase;

    private function data(?int $h, ?int $b, ?int $t): array
    {
        return ['nama_anak' => 'Anak Uji', 'tanggal_lahir_hari' => $h, 'tanggal_lahir_bulan' => $b, 'tanggal_lahir_tahun' => $t,
            'jenis_kelamin' => 'P', 'program' => 'Kelompok A', 'nama_orang_tua' => 'Ortu', 'telepon' => '0812', 'alamat' => 'Jakarta', 'setuju_privasi' => '1'];
    }

    public function test_formulir_pendaftaran_memakai_pilihan_tanggal_bulan_tahun(): void
    {
        $this->travelTo('2026-11-09 10:00:00');
        $this->get(route('pendaftaran.create'))->assertOk()
            ->assertSee('name="tanggal_lahir_hari"', false)->assertSee('name="tanggal_lahir_bulan"', false)->assertSee('name="tanggal_lahir_tahun"', false)
            ->assertSee('<option value="2024"', false)->assertSee('<option value="2018"', false)
            ->assertDontSee('<option value="2025"', false)->assertSee('Agustus');

        $this->post(route('pendaftaran.store'), $this->data(17, 8, 2021))->assertSessionHasNoErrors();
        $this->assertSame('2021-08-17', Pendaftaran::latest('id')->first()->tanggal_lahir->toDateString());

        $this->post(route('pendaftaran.store'), $this->data(9, 10, 2026))->assertSessionHasErrors('tanggal_lahir');   // terlalu muda
        $this->post(route('pendaftaran.store'), $this->data(31, 2, 2021))->assertSessionHasErrors('tanggal_lahir');   // tidak ada di kalender
        $this->post(route('pendaftaran.store'), $this->data(null, 5, 2021))->assertSessionHasErrors('tanggal_lahir'); // belum lengkap
        $this->post(route('pendaftaran.store'), $this->data(1, 1, 2015))->assertSessionHasErrors('tanggal_lahir');    // terlalu tua
    }

    public function test_form_siswa_admin_menampilkan_dan_menyimpan_tanggal_lahir(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@tkceria.sch.id')->firstOrFail();
        $siswa = Siswa::firstOrFail();
        $siswa->update(['tanggal_lahir' => '2021-03-05']);

        $this->actingAs($admin)->get(route('admin.siswa.edit', $siswa))->assertOk()
            ->assertSee('<option value="5" selected', false)->assertSee('<option value="3" selected', false)->assertSee('<option value="2021" selected', false);

        $data = ['nis' => $siswa->nis, 'nama' => $siswa->nama, 'jenis_kelamin' => $siswa->jenis_kelamin,
            'tanggal_lahir_hari' => 12, 'tanggal_lahir_bulan' => 6, 'tanggal_lahir_tahun' => 2020];
        $this->actingAs($admin)->put(route('admin.siswa.update', $siswa), $data)->assertSessionHasNoErrors();
        $this->assertSame('2020-06-12', $siswa->fresh()->tanggal_lahir->toDateString());
    }
}
