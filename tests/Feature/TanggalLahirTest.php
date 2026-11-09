<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Kalender tanggal lahir di formulir pendaftaran dibatasi ke usia anak TK (2–8 tahun). */
class TanggalLahirTest extends TestCase
{
    use RefreshDatabase;

    private function data(string $tgl): array
    {
        return ['nama_anak' => 'Anak Uji', 'tanggal_lahir' => $tgl, 'jenis_kelamin' => 'P', 'program' => 'Kelompok A',
            'nama_orang_tua' => 'Ortu', 'telepon' => '0812', 'alamat' => 'Jakarta', 'setuju_privasi' => '1'];
    }

    public function test_kalender_dibatasi_dan_tanggal_tidak_wajar_ditolak(): void
    {
        $this->travelTo('2026-11-09 10:00:00');
        $this->get(route('pendaftaran.create'))->assertOk()
            ->assertSee('min=2018-11-09', false)->assertSee('max=2024-11-09', false)->assertSee('ketuk angka tahun');

        // Tanggal bulan lalu (anak "umur 1 bulan") dan masa depan ditolak dengan pesan jelas
        $this->post(route('pendaftaran.store'), $this->data('2026-10-09'))->assertSessionHasErrors('tanggal_lahir');
        $this->post(route('pendaftaran.store'), $this->data('2027-01-01'))->assertSessionHasErrors('tanggal_lahir');
        $this->post(route('pendaftaran.store'), $this->data('2015-01-01'))->assertSessionHasErrors('tanggal_lahir');
        $this->post(route('pendaftaran.store'), $this->data('2021-08-17'))->assertSessionHasNoErrors();
    }
}
