<?php

namespace Tests\Feature;

use App\Models\GuruKehadiran;
use App\Models\User;
use App\Support\Basisdata;
use App\Support\Pengaturan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Kehadiran guru mengajar: absen masuk/pulang oleh guru, rekap & koreksi oleh admin. */
class RekapGuruTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function u(string $email): User { return User::where('email', $email)->firstOrFail(); }

    public function test_guru_absen_masuk_dan_pulang_lalu_tampil_di_rekap_admin(): void
    {
        $rina = $this->u('rina@tkceria.sch.id');
        Pengaturan::simpan(['guru_jam_masuk' => '07:30']);

        $this->travelTo('2026-11-04 07:42:00'); // Rabu, terlambat 12 menit
        $this->actingAs($rina)->get(route('guru.dashboard'))->assertOk()->assertSee('Kehadiran saya hari ini')->assertSee('Absen masuk');
        $this->actingAs($rina)->post(route('guru.kehadiran.store'), ['aksi' => 'masuk'])->assertRedirect()->assertSessionHas('success');
        $this->actingAs($rina)->post(route('guru.kehadiran.store'), ['aksi' => 'masuk'])->assertSessionHas('error'); // tidak bisa dua kali

        $this->travelTo('2026-11-04 13:05:00');
        $this->actingAs($rina)->post(route('guru.kehadiran.store'), ['aksi' => 'pulang'])->assertSessionHas('success');

        $k = GuruKehadiran::where('user_id', $rina->id)->firstOrFail();
        $this->assertSame(['hadir', '07:42', '13:05', true], [$k->status, $k->jam_masuk, $k->jam_pulang, $k->terlambat]);

        $admin = $this->u('admin@tkceria.sch.id');
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertSee(route('admin.rekap-guru.index'), false);
        $this->actingAs($admin)->get(route('admin.rekap-guru.index'))->assertOk()
            ->assertSee($rina->name)->assertSee('07.42')->assertSee('13.05')->assertSee('Terlambat')
            ->assertSee($this->u('sari@tkceria.sch.id')->name)->assertSee('Belum ada catatan');
    }

    public function test_guru_izin_dan_admin_mengoreksi_serta_rekap_bulanan_excel(): void
    {
        $sari = $this->u('sari@tkceria.sch.id');
        $admin = $this->u('admin@tkceria.sch.id');
        $this->travelTo('2026-11-05 06:50:00');
        $this->actingAs($sari)->post(route('guru.kehadiran.store'), ['aksi' => 'sakit', 'keterangan' => 'Demam'])->assertSessionHas('success');
        $this->assertSame('sakit', GuruKehadiran::where('user_id', $sari->id)->value('status'));
        // Ternyata datang: absen masuk mengganti status sakit, alasannya tidak terbawa
        $this->travelTo('2026-11-05 09:00:00');
        $this->actingAs($sari)->post(route('guru.kehadiran.store'), ['aksi' => 'masuk'])->assertSessionHas('success');
        $this->assertSame(['hadir', null], [GuruKehadiran::where('user_id', $sari->id)->value('status'), GuruKehadiran::where('user_id', $sari->id)->value('keterangan')]);
        $this->actingAs($sari)->post(route('guru.kehadiran.store'), ['aksi' => 'sakit', 'keterangan' => 'Demam'])->assertSessionHas('error');
        GuruKehadiran::where('user_id', $sari->id)->update(['status' => 'sakit', 'jam_masuk' => null, 'keterangan' => 'Demam']);

        // Admin mencatat guru lain yang lupa absen, lalu mengoreksi lagi
        $dimas = User::where('role', User::GURU)->whereNotIn('id', [$sari->id, $this->u('rina@tkceria.sch.id')->id])->firstOrFail();
        $this->actingAs($admin)->post(route('admin.rekap-guru.simpan'), ['user_id' => $dimas->id, 'tanggal' => '2026-11-05', 'status' => 'hadir', 'jam_masuk' => '07:10', 'keterangan' => 'Lupa absen'])->assertSessionHas('success');
        $this->assertSame('07:10', GuruKehadiran::where('user_id', $dimas->id)->value('jam_masuk'));
        $this->actingAs($admin)->post(route('admin.rekap-guru.simpan'), ['user_id' => $dimas->id, 'tanggal' => '2026-11-05', 'status' => 'dinas', 'keterangan' => 'Rapat KKG'])->assertSessionHas('success');
        $this->assertSame([null, 'dinas'], [GuruKehadiran::where('user_id', $dimas->id)->value('jam_masuk'), GuruKehadiran::where('user_id', $dimas->id)->value('status')]);
        // Tidak bisa mencatat untuk akun bukan guru atau tanggal di masa depan
        $this->actingAs($admin)->post(route('admin.rekap-guru.simpan'), ['user_id' => $admin->id, 'tanggal' => '2026-11-05', 'status' => 'hadir'])->assertSessionHasErrors('user_id');
        $this->actingAs($admin)->post(route('admin.rekap-guru.simpan'), ['user_id' => $dimas->id, 'tanggal' => '2026-12-01', 'status' => 'hadir'])->assertSessionHasErrors('tanggal');

        $this->actingAs($admin)->get(route('admin.rekap-guru.index', ['mode' => 'bulanan']))->assertOk()->assertSee('November 2026')->assertSee('Demam');
        $r = $this->actingAs($admin)->get(route('admin.rekap-guru.index', ['mode' => 'mingguan', 'unduh' => 'csv']));
        $this->assertStringContainsString($sari->name, $r->streamedContent());
    }

    public function test_hak_akses_dan_aman_sebelum_database_diperbarui(): void
    {
        $this->actingAs($this->u('rina@tkceria.sch.id'))->get(route('admin.rekap-guru.index'))->assertForbidden();
        $this->actingAs($this->u('budi@contoh.com'))->post(route('guru.kehadiran.store'), ['aksi' => 'masuk'])->assertForbidden();

        Schema::drop('guru_kehadiran');
        Basisdata::lupakan();
        $this->actingAs($this->u('rina@tkceria.sch.id'))->get(route('guru.dashboard'))->assertOk()->assertDontSee('Kehadiran saya hari ini');
        $this->actingAs($this->u('admin@tkceria.sch.id'))->get(route('admin.rekap-guru.index'))->assertOk()->assertSee('belum aktif');
    }
}
