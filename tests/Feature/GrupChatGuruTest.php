<?php

namespace Tests\Feature;

use App\Models\ChatPesan;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Guru ikut di Grup Orang Tua (bersama admin & semua orang tua), tapi tidak bisa membuka chat pribadi. */
class GrupChatGuruTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function u(string $email): User { return User::where('email', $email)->firstOrFail(); }

    public function test_guru_bisa_membaca_dan_mengirim_di_grup_orang_tua(): void
    {
        $guru = $this->u('rina@tkceria.sch.id');
        $ortu = $this->u('budi@contoh.com');

        // Menu samping guru punya "Grup Orang Tua"
        $this->actingAs($guru)->get(route('guru.dashboard'))->assertOk()->assertSee(route('chat.grup'), false)->assertSee('Grup Orang Tua');
        $this->actingAs($guru)->get(route('chat.grup'))->assertOk()->assertSee('guru, dan admin');

        $this->actingAs($guru)->postJson(route('chat.grup.kirim'), ['isi' => 'Besok anak-anak membawa bekal buah ya, Bunda.'])->assertOk();
        $this->assertSame(1, ChatPesan::where('jenis', 'grup')->where('pengirim_id', $guru->id)->count());

        // Orang tua melihat pesan guru dengan label Guru, dan mendapat notifikasi
        $this->actingAs($ortu)->getJson(route('chat.grup.data'))->assertOk()
            ->assertJsonFragment(['nama' => $guru->name, 'guru' => true, 'admin' => false]);
        $this->assertTrue(Notifikasi::where('user_id', $ortu->id)->where('kategori', 'chat')->exists());

        // Pesan orang tua sampai ke guru (notifikasi + angka belum dibaca di menu)
        $this->actingAs($ortu)->postJson(route('chat.grup.kirim'), ['isi' => 'Siap, Bu Rina!'])->assertOk();
        $this->assertTrue(Notifikasi::where('user_id', $guru->id)->where('kategori', 'chat')->exists());
        $this->assertSame(1, ChatPesan::belumGrup($guru));
        $this->actingAs($guru)->getJson(route('chat.grup.data'))->assertOk()->assertJsonFragment(['isi' => 'Siap, Bu Rina!']);
        $this->assertSame(0, ChatPesan::belumGrup($guru));
    }

    public function test_guru_tidak_bisa_membuka_chat_pribadi_orang_tua(): void
    {
        $guru = $this->u('rina@tkceria.sch.id');
        $ortu = $this->u('budi@contoh.com');

        $this->actingAs($guru)->get(route('chat.index'))->assertForbidden();
        $this->actingAs($guru)->get(route('chat.pribadi', ['ortu' => $ortu->id]))->assertForbidden();
        $this->actingAs($guru)->getJson(route('chat.pribadi.data', ['ortu' => $ortu->id]))->assertForbidden();
        $this->actingAs($guru)->postJson(route('chat.pribadi.kirim', ['ortu' => $ortu->id]), ['isi' => 'x'])->assertForbidden();
    }
}
