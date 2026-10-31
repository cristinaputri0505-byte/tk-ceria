<?php

namespace Tests\Feature;

use App\Models\ChatPesan;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Chat pribadi guru <-> admin: guru hanya melihat percakapannya sendiri; orang tua tidak bisa membuka. */
class ChatGuruAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function u(string $email): User { return User::where('email', $email)->firstOrFail(); }

    public function test_guru_dan_admin_saling_berkirim_pesan(): void
    {
        $rina = $this->u('rina@tkceria.sch.id');
        $admin = $this->u('admin@tkceria.sch.id');

        $this->actingAs($rina)->get(route('guru.dashboard'))->assertSee(route('chat.guru'), false)->assertSee('Chat dengan Admin');
        $this->actingAs($rina)->get(route('chat.guru'))->assertOk()->assertSee('Chat dengan Admin');
        $this->actingAs($rina)->postJson(route('chat.guru.kirim'), ['isi' => 'Bu, spidol kelas habis.'])->assertOk();

        // Admin melihat daftar guru dengan pesan belum dibaca, lalu membalas
        $this->assertSame(1, ChatPesan::belumGuru($admin));
        $this->assertTrue(Notifikasi::where('user_id', $admin->id)->where('kategori', 'chat')->exists());
        $this->actingAs($admin)->get(route('chat.guru'))->assertOk()->assertSee($rina->name)->assertSee('spidol kelas habis');
        $this->actingAs($admin)->getJson(route('chat.guru.data', ['ortu' => $rina->id]))->assertOk()->assertJsonFragment(['isi' => 'Bu, spidol kelas habis.']);
        $this->assertSame(0, ChatPesan::belumGuru($admin));
        $this->actingAs($admin)->postJson(route('chat.guru.kirim'), ['isi' => 'Siap, besok dibelikan.', 'ortu' => $rina->id])->assertOk();

        $this->assertSame(1, ChatPesan::belumGuru($rina));
        $this->actingAs($rina)->getJson(route('chat.guru.data'))->assertOk()->assertJsonFragment(['isi' => 'Siap, besok dibelikan.']);
        $this->assertSame(0, ChatPesan::belumGuru($rina));
    }

    public function test_percakapan_guru_terpisah_dan_tertutup_untuk_orang_tua(): void
    {
        $rina = $this->u('rina@tkceria.sch.id');
        $sari = $this->u('sari@tkceria.sch.id');
        $this->actingAs($rina)->postJson(route('chat.guru.kirim'), ['isi' => 'Pesan rahasia Bu Rina'])->assertOk();

        // Guru lain tidak melihat pesan Bu Rina walau mengirim ?ortu=
        $this->actingAs($sari)->getJson(route('chat.guru.data', ['ortu' => $rina->id]))->assertOk()->assertJsonMissing(['isi' => 'Pesan rahasia Bu Rina']);
        $this->actingAs($this->u('budi@contoh.com'))->get(route('chat.guru'))->assertForbidden();
        $this->actingAs($this->u('budi@contoh.com'))->getJson(route('chat.pribadi.data'))->assertOk()->assertJsonMissing(['isi' => 'Pesan rahasia Bu Rina']);
        $this->actingAs($this->u('admin@tkceria.sch.id'))->get(route('chat.guru', ['ortu' => $this->u('budi@contoh.com')->id]))->assertNotFound();
    }
}
