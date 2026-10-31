<?php

namespace Tests\Feature;

use App\Models\Notifikasi;
use App\Models\User;
use App\Support\Pengaturan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Notifikasi lama ke fitur yang sudah dimatikan admin tidak boleh berujung halaman 404. */
class NotifikasiFiturMatiTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifikasi_ke_fitur_mati_kembali_ke_daftar_notifikasi(): void
    {
        $this->seed();
        $ortu = User::where('email', 'budi@contoh.com')->firstOrFail();
        Notifikasi::kirim([$ortu->id], 'pembayaran', 'Tagihan baru', 'SPP November', route('orangtua.pembayaran', [], false));
        $n = Notifikasi::where('user_id', $ortu->id)->latest('id')->firstOrFail();

        $this->actingAs($ortu)->get(route('notifikasi.buka', $n))->assertRedirect(route('orangtua.pembayaran', [], false));

        Pengaturan::simpan(['portal_fitur_pembayaran' => '0']);
        $this->actingAs($ortu)->get(route('notifikasi.buka', $n))->assertRedirect(route('notifikasi.index'));
        $this->actingAs($ortu)->get(route('notifikasi.index'))->assertOk();
    }
}
