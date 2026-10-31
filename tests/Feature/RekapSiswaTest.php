<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\ElearningHasil;
use App\Models\ElearningTugas;
use App\Models\Perkembangan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Rekap siswa (kehadiran, nilai Ruang Belajar, perkembangan) untuk laporan harian/mingguan/bulanan guru. */
class RekapSiswaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function u(string $email): User { return User::where('email', $email)->firstOrFail(); }

    public function test_guru_melihat_rekap_harian_kelasnya_lengkap(): void
    {
        $this->travelTo('2026-11-04 10:00:00');
        $guru = $this->u('rina@tkceria.sch.id');
        $kelas = $guru->kelasDiampu()->firstOrFail();
        $anak = $kelas->siswa()->orderBy('nama')->firstOrFail();
        $lain = \App\Models\Kelas::whereKeyNot($kelas->id)->firstOrFail()->siswa()->firstOrFail();

        Absensi::where('siswa_id', $anak->id)->whereDate('tanggal', today())->delete();
        Absensi::create(['siswa_id' => $anak->id, 'kelas_id' => $kelas->id, 'guru_id' => $guru->id, 'tanggal' => today(), 'status' => 'sakit', 'keterangan' => 'Demam ringan']);
        Perkembangan::updateOrCreate(['siswa_id' => $anak->id, 'periode' => \App\Http\Controllers\Guru\PerkembanganController::periodeAktif(), 'aspek' => 'bahasa'], ['guru_id' => $guru->id, 'nilai' => 'BSB']);
        $tugas = ElearningTugas::firstOrFail();
        ElearningHasil::create(['tugas_id' => $tugas->id, 'siswa_id' => $anak->id, 'bintang' => 3, 'selesai_pada' => now()]);

        $this->actingAs($guru)->get(route('guru.dashboard'))->assertSee(route('guru.rekap.index'), false);
        $this->actingAs($guru)->get(route('guru.rekap.index'))->assertOk()
            ->assertSee($kelas->nama)->assertSee($anak->nama)
            ->assertSee('Sakit')->assertSee('Demam ringan')->assertSee('Sangat Baik')->assertSee('⭐ 3')
            ->assertDontSee($lain->nama);
    }

    public function test_rekap_mingguan_bulanan_dan_unduh_excel(): void
    {
        $guru = $this->u('rina@tkceria.sch.id');
        $kelas = $guru->kelasDiampu()->firstOrFail();

        $this->actingAs($guru)->get(route('guru.rekap.index', ['mode' => 'mingguan']))->assertOk()->assertSee('Laporan mingguan');
        $this->actingAs($guru)->get(route('guru.rekap.index', ['mode' => 'bulanan', 'tanggal' => 'bukan-tanggal']))->assertOk()->assertSee('Laporan bulanan');

        $r = $this->actingAs($guru)->get(route('guru.rekap.index', ['unduh' => 'csv']));
        $r->assertOk();
        $this->assertStringContainsString('text/csv', $r->headers->get('Content-Type'));
        $isi = $r->streamedContent();
        $this->assertStringContainsString('Rekap siswa ' . $kelas->nama, $isi);
        $this->assertStringContainsString($kelas->siswa()->orderBy('nama')->first()->nama, $isi);
    }

    public function test_guru_tidak_bisa_melihat_kelas_lain_admin_bisa_semua(): void
    {
        $guru = $this->u('rina@tkceria.sch.id');
        $lainKelas = \App\Models\Kelas::whereKeyNot($guru->kelasDiampu()->firstOrFail()->id)->firstOrFail();
        $anakLain = $lainKelas->siswa()->firstOrFail();

        // kelas_id kelas lain diabaikan: guru tetap melihat kelasnya sendiri
        $this->actingAs($guru)->get(route('guru.rekap.index', ['kelas_id' => $lainKelas->id]))->assertOk()->assertDontSee($anakLain->nama);
        $this->actingAs($this->u('admin@tkceria.sch.id'))->get(route('admin.rekap.index', ['kelas_id' => $lainKelas->id]))->assertOk()->assertSee($anakLain->nama);
        $this->actingAs($this->u('budi@contoh.com'))->get(route('guru.rekap.index'))->assertForbidden();
    }
}
