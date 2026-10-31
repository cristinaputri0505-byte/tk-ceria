<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Tampilan "Taman Bermain": ringkasan tiap panel dan Absen cepat di panel guru. */
class TampilanCeriaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function u(string $email): User { return User::where('email', $email)->firstOrFail(); }

    public function test_tiap_panel_memakai_warna_sendiri_dan_halaman_utama_tampil(): void
    {
        $this->get('/')->assertOk()->assertSee('class="situs', false);
        $this->actingAs($this->u('admin@tkceria.sch.id'))->get(route('admin.dashboard'))->assertOk()
            ->assertSee('bg-daun-soft', false)->assertSee('Perlu ditindaklanjuti');
        $this->actingAs($this->u('rina@tkceria.sch.id'))->get(route('guru.dashboard'))->assertOk()
            ->assertSee('bg-jeruk-soft', false)->assertSee('Absen cepat');
        $this->actingAs($this->u('budi@contoh.com'))->get(route('orangtua.dashboard'))->assertOk()
            ->assertSee('bg-langit-soft', false);
    }

    public function test_absen_cepat_dari_ringkasan_guru_tersimpan(): void
    {
        $guru = $this->u('rina@tkceria.sch.id');
        $kelas = $guru->kelasDiampu()->firstOrFail();
        $siswa = $kelas->siswa()->orderBy('nama')->get();
        $status = $siswa->mapWithKeys(fn ($s, $i) => [$s->id => $i === 0 ? 'izin' : 'hadir'])->all();

        $this->actingAs($guru)->post(route('guru.absensi.store'), [
            'kelas_id' => $kelas->id, 'tanggal' => today()->toDateString(), 'status' => $status,
        ])->assertRedirect();

        $this->assertSame('izin', Absensi::where('siswa_id', $siswa[0]->id)->whereDate('tanggal', today())->value('status'));
        // Ringkasan menampilkan status yang sudah tersimpan sebagai pilihan aktif
        $this->actingAs($guru)->get(route('guru.dashboard'))->assertOk()
            ->assertSee('name="status[' . $siswa[0]->id . ']" value="izin" checked', false)
            ->assertSee('Absensi hari ini sudah diisi');
    }
}
