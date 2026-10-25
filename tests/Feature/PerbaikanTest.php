<?php

namespace Tests\Feature;

use App\Models\Berita;
use App\Models\ChatPesan;
use App\Models\Dokumentasi;
use App\Models\Kegiatan;
use App\Models\Kelas;
use App\Models\Notifikasi;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Uji regresi untuk perbaikan performa & bug.
 * Jalankan:  php artisan test --filter=PerbaikanTest
 */
class PerbaikanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function tearDown(): void
    {
        Model::preventLazyLoading(false);
        parent::tearDown();
    }

    private function u(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    /** Semua halaman (GET) dibuka oleh tamu & tiap peran: tidak boleh error 500 dan tidak boleh ada query N+1. */
    public function test_semua_halaman_tanpa_error_dan_tanpa_query_berulang(): void
    {
        Berita::query()->update(['published_at' => now()->subDay()]);
        Model::preventLazyLoading();

        $budi = $this->u('budi@contoh.com');
        $param = [
            'kegiatan' => Kegiatan::value('id'),
            'berita' => Berita::value('slug'),
            'siswa' => $budi->anak()->value('id'),
            'tagihan' => Tagihan::where('status', 'lunas')->whereIn('siswa_id', $budi->anak()->pluck('id'))->value('id'),
            'dokumentasi' => Dokumentasi::value('id'),
        ];
        $peran = [null, 'admin@tkceria.sch.id', 'rina@tkceria.sch.id', 'budi@contoh.com'];
        $gagal = [];

        foreach ($peran as $email) {
            foreach (Route::getRoutes() as $r) {
                $uri = $r->uri();
                if (! in_array('GET', $r->methods(), true) || str_starts_with($uri, 'storage') || $uri === 'up') continue;
                $url = preg_replace_callback('/\{(\w+)\??\}/', fn ($m) => (string) ($param[$m[1]] ?? '__lewati__'), $uri);
                if (str_contains($url, '__lewati__')) continue;
                if ($uri === 'chat/pribadi' || $uri === 'chat/pribadi/data') $url .= '?ortu=' . $budi->id;

                $email ? $this->actingAs($this->u($email)) : auth()->logout();
                $res = $this->get('/' . ltrim($url, '/'));
                if ($res->getStatusCode() >= 500) {
                    $gagal[] = ($email ?? 'tamu') . " /{$url}: " . optional($res->exception)->getMessage();
                }
            }
        }

        $this->assertSame([], $gagal);
    }

    /** Setiap nama ikon yang ditulis di view harus ada di file ikon ringkas. */
    public function test_semua_ikon_tersedia_di_file_ikon(): void
    {
        $js = file_get_contents(public_path('js/ikon.min.js'));
        $nama = [];
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views'), \FilesystemIterator::SKIP_DOTS)) as $f) {
            preg_match_all('/data-lucide="([a-z0-9-]+)"/', file_get_contents($f->getPathname()), $m);
            $nama = array_merge($nama, $m[1]);
        }
        foreach (array_keys(\App\Support\Pengaturan::IKON) as $n) $nama[] = $n;

        $hilang = array_values(array_filter(array_unique($nama), fn ($n) => ! str_contains($js, '"' . $n . '":')));
        $this->assertSame([], $hilang, 'Jalankan: node scripts/buat-ikon.cjs');
        $this->assertFileDoesNotExist(public_path('js/lucide.min.js'));
    }

    public function test_slug_berita_tidak_berubah_saat_judul_diedit(): void
    {
        $b = Berita::create(['judul' => 'Pentas Seni', 'isi' => 'Isi', 'published_at' => now()->subDay()]);
        $slug = $b->slug;

        $this->actingAs($this->u('admin@tkceria.sch.id'))
            ->put(route('admin.berita.update', $b), ['judul' => 'Pentas Seni 2026', 'isi' => 'Isi', 'terbitkan' => 1])
            ->assertRedirect();

        $this->assertSame($slug, $b->fresh()->slug);
        $this->get('/berita/' . $slug)->assertOk();
    }

    public function test_admin_tidak_bisa_mengubah_peran_akunnya_sendiri(): void
    {
        $admin = $this->u('admin@tkceria.sch.id');

        $this->actingAs($admin)
            ->put(route('admin.pengguna.update', $admin), ['name' => $admin->name, 'email' => $admin->email, 'role' => 'guru'])
            ->assertSessionHasErrors('role');

        $this->assertSame(User::ADMIN, $admin->fresh()->role);
    }

    public function test_wali_kelas_harus_guru_dan_orang_tua_siswa_harus_orang_tua(): void
    {
        $admin = $this->u('admin@tkceria.sch.id');
        $budi = $this->u('budi@contoh.com');

        $this->actingAs($admin)
            ->post(route('admin.kelas.store'), ['nama' => 'C1', 'kelompok' => 'A', 'tahun_ajaran' => '2026/2027', 'wali_guru_id' => $budi->id])
            ->assertSessionHasErrors('wali_guru_id');

        $this->actingAs($admin)
            ->post(route('admin.siswa.store'), ['nis' => '9999', 'nama' => 'Tes', 'jenis_kelamin' => 'L', 'tanggal_lahir' => '2021-01-01', 'orang_tua_id' => $admin->id])
            ->assertSessionHasErrors('orang_tua_id');
    }

    public function test_hapus_siswa_ikut_menghapus_file_privatnya(): void
    {
        Storage::fake('local');
        $siswa = Siswa::whereNotNull('kelas_id')->firstOrFail();
        $foto = UploadedFile::fake()->image('anak.jpg')->store('siswa', 'local');
        $bukti = UploadedFile::fake()->image('bukti.jpg')->store('bukti-bayar', 'local');
        $dok = UploadedFile::fake()->image('dok.jpg')->store('dokumentasi', 'local');
        $siswa->update(['foto' => $foto]);
        Tagihan::create(['siswa_id' => $siswa->id, 'jenis' => 'Lainnya', 'periode' => 'Tes', 'jumlah' => 5000, 'status' => 'menunggu', 'bukti' => $bukti]);
        Dokumentasi::create(['kelas_id' => $siswa->kelas_id, 'siswa_id' => $siswa->id, 'gambar' => $dok, 'tanggal' => today()]);

        $this->actingAs($this->u('admin@tkceria.sch.id'))->delete(route('admin.siswa.destroy', $siswa))->assertRedirect();

        Storage::disk('local')->assertMissing([$foto, $bukti, $dok]);
    }

    public function test_hapus_kelas_ikut_menghapus_foto_dokumentasinya(): void
    {
        Storage::fake('local');
        $kelas = Kelas::create(['nama' => 'Kosong', 'kelompok' => 'A', 'tahun_ajaran' => '2026/2027']);
        $dok = UploadedFile::fake()->image('dok.jpg')->store('dokumentasi', 'local');
        Dokumentasi::create(['kelas_id' => $kelas->id, 'gambar' => $dok, 'tanggal' => today()]);

        $this->actingAs($this->u('admin@tkceria.sch.id'))->delete(route('admin.kelas.destroy', $kelas))->assertRedirect();

        Storage::disk('local')->assertMissing($dok);
    }

    public function test_keterangan_absensi_harus_teks(): void
    {
        $rina = $this->u('rina@tkceria.sch.id');
        $kelas = $rina->kelasDiampu()->firstOrFail();
        $siswa = $kelas->siswa()->firstOrFail();

        $this->actingAs($rina)->post(route('guru.absensi.store'), [
            'kelas_id' => $kelas->id, 'tanggal' => today()->toDateString(),
            'status' => [$siswa->id => 'izin'], 'keterangan' => [$siswa->id => ['bukan', 'teks']],
        ])->assertSessionHasErrors("keterangan.{$siswa->id}");
    }

    public function test_cek_chat_berkala_tanpa_pesan_baru_tidak_menulis_ke_database(): void
    {
        $budi = $this->u('budi@contoh.com');
        $m = ChatPesan::create(['jenis' => 'pribadi', 'orang_tua_id' => $budi->id, 'pengirim_id' => $budi->id, 'isi' => 'Halo']);

        DB::enableQueryLog();
        $this->actingAs($budi)->getJson(route('chat.pribadi.data', ['setelah' => $m->id]))->assertOk()->assertExactJson([]);
        $tulis = collect(DB::getQueryLog())->pluck('query')->filter(fn ($q) => preg_match('/^\s*(update|insert|delete)/i', $q));

        $this->assertCount(0, $tulis);
    }

    public function test_notifikasi_chat_digabung_untuk_banyak_penerima(): void
    {
        $ortu = User::where('role', User::ORANG_TUA)->pluck('id');
        $url = route('chat.grup', [], false);

        Notifikasi::kirim($ortu, 'chat', 'Grup', 'pesan 1', $url);
        Notifikasi::kirim($ortu, 'chat', 'Grup', 'pesan 2', $url);

        $this->assertSame($ortu->count(), Notifikasi::where('kategori', 'chat')->where('url', $url)->count());
        $this->assertSame($ortu->count(), Notifikasi::where('kategori', 'chat')->where('isi', 'pesan 2')->count());

        // Yang sudah membaca mendapat notifikasi baru, bukan memperbarui yang lama.
        Notifikasi::where('user_id', $ortu->first())->update(['dibaca_at' => now()]);
        Notifikasi::kirim($ortu, 'chat', 'Grup', 'pesan 3', $url);
        $this->assertSame($ortu->count() + 1, Notifikasi::where('kategori', 'chat')->where('url', $url)->count());
    }

    public function test_foto_anak_terkirim_utuh_dengan_jenis_dan_ukuran_benar(): void
    {
        Storage::fake('local');
        $budi = $this->u('budi@contoh.com');
        $anak = $budi->anak()->firstOrFail();
        $file = UploadedFile::fake()->image('anak.jpg', 300, 300);
        $isi = file_get_contents($file->getRealPath());
        $anak->update(['foto' => $file->store('siswa', 'local')]);

        $res = $this->actingAs($budi)->get($anak->fresh()->foto_url)->assertOk();

        $this->assertSame('image/jpeg', $res->headers->get('Content-Type'));
        $this->assertSame((string) strlen($isi), $res->headers->get('Content-Length'));
        $this->assertSame($isi, $res->getContent());
    }

    public function test_foto_anak_yang_filenya_hilang_404_dan_tampilan_memakai_huruf_awal(): void
    {
        Storage::fake('local');
        $budi = $this->u('budi@contoh.com');
        $anak = $budi->anak()->firstOrFail();
        $anak->update(['foto' => 'siswa/tidak-ada.jpg']);

        $this->actingAs($budi)->get($anak->fresh()->foto_url)->assertNotFound();

        // Gambar punya cadangan huruf awal yang muncul otomatis bila gambar gagal dimuat.
        $this->actingAs($budi)->get(route('orangtua.anak', $anak))
            ->assertOk()
            ->assertSee("this.style.display='none';this.nextElementSibling.style.display=''", false)
            ->assertSee('style="display:none" aria-hidden="true">' . mb_substr($anak->nama, 0, 1), false);
    }

    public function test_foto_profil_pengguna_tampil_di_header_panel(): void
    {
        Storage::fake('public');
        $budi = $this->u('budi@contoh.com');

        $this->actingAs($budi)->put(route('orangtua.profil.update'), [
            'name' => $budi->name, 'foto' => UploadedFile::fake()->image('saya.jpg'),
        ])->assertRedirect();

        $budi->refresh();
        $this->assertNotNull($budi->foto);
        $this->actingAs($budi)->get(route('orangtua.dashboard'))->assertOk()->assertSee($budi->foto_url, false);
    }

    public function test_semua_halaman_memuat_penjaga_foto_dan_daftar_akun_menampilkan_foto(): void
    {
        $this->get('/')->assertOk()->assertSee('js/foto.js?v=', false);
        $this->assertFileExists(public_path('js/foto.js'));

        $rina = $this->u('rina@tkceria.sch.id');
        $rina->update(['foto' => 'pengguna/rina.jpg']);
        $this->actingAs($this->u('admin@tkceria.sch.id'))->get(route('admin.pengguna.index', ['role' => 'guru']))
            ->assertOk()->assertSee($rina->foto_url, false);
    }

    public function test_penyimpanan_menolak_unggahan_tampil_pesan_bukan_error_500(): void
    {
        // Disk yang menolak menulis: folder 'siswa' & 'pengguna' sengaja berupa file, bukan folder.
        $root = sys_get_temp_dir() . '/tk-tolak-' . uniqid();
        mkdir($root);
        touch($root . '/siswa');
        touch($root . '/pengguna');
        foreach (['local', 'public'] as $disk) {
            Storage::set($disk, Storage::build(['driver' => 'local', 'root' => $root, 'throw' => true]));
        }

        $admin = $this->u('admin@tkceria.sch.id');
        $siswa = Siswa::firstOrFail();
        $data = ['nis' => $siswa->nis, 'nama' => $siswa->nama, 'jenis_kelamin' => $siswa->jenis_kelamin,
            'tanggal_lahir' => $siswa->tanggal_lahir->toDateString(), 'foto' => UploadedFile::fake()->image('a.jpg')];

        $this->actingAs($admin)->from(route('admin.siswa.edit', $siswa))
            ->put(route('admin.siswa.update', $siswa), $data)
            ->assertRedirect(route('admin.siswa.edit', $siswa))
            ->assertSessionHasErrors('foto');
        $this->assertSame($siswa->foto, $siswa->fresh()->foto);

        $budi = $this->u('budi@contoh.com');
        $this->actingAs($budi)->from(route('orangtua.profil'))
            ->put(route('orangtua.profil.update'), ['name' => $budi->name, 'foto' => UploadedFile::fake()->image('b.jpg')])
            ->assertSessionHasErrors('foto');
    }

    public function test_halaman_cek_sistem_hanya_admin_dan_menampilkan_hasil(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $this->actingAs($this->u('budi@contoh.com'))->get(route('admin.cek-sistem'))->assertForbidden();
        $this->actingAs($this->u('admin@tkceria.sch.id'))->get(route('admin.cek-sistem'))
            ->assertOk()->assertSee('Penyimpanan berjalan normal');
    }

    public function test_kegiatan_bisa_banyak_foto_sampul_dan_hapus(): void
    {
        Storage::fake('public');
        $admin = $this->u('admin@tkceria.sch.id');

        $this->actingAs($admin)->post(route('admin.kegiatan.store'), [
            'judul' => 'Album Uji Coba', 'tanggal' => '2026-11-03',
            'foto' => [UploadedFile::fake()->image('1.jpg'), UploadedFile::fake()->image('2.jpg'), UploadedFile::fake()->image('3.jpg')],
        ])->assertRedirect(route('admin.kegiatan.index'));

        $k = Kegiatan::where('judul', 'Album Uji Coba')->firstOrFail();
        $album = $k->foto;
        $this->assertCount(3, $album);
        $this->assertSame($album[0]->gambar, $k->gambar, 'Foto pertama otomatis jadi sampul');
        Storage::disk('public')->assertExists($album->pluck('gambar')->all());

        // Halaman publik: kartu menampilkan jumlah foto, halaman detail menampilkan semua foto
        $this->get(route('kegiatan.index'))->assertOk()->assertSee('3 foto');
        $detail = $this->get(route('kegiatan.show', $k))->assertOk();
        foreach ($album as $f) $detail->assertSee($f->url, false);

        // Ubah: hapus foto pertama (sampul), pilih foto ke-3 sebagai sampul, tambah 1 foto
        $this->actingAs($admin)->put(route('admin.kegiatan.update', $k), [
            'judul' => 'Album Uji Coba', 'tanggal' => '2026-11-03',
            'hapus_foto' => [$album[0]->id], 'sampul' => $album[2]->id,
            'foto' => [UploadedFile::fake()->image('4.jpg')],
        ])->assertRedirect(route('admin.kegiatan.edit', $k));

        $k->refresh();
        $this->assertCount(3, $k->foto);
        $this->assertSame($album[2]->gambar, $k->gambar);
        Storage::disk('public')->assertMissing($album[0]->gambar);

        // Hapus kegiatan: semua file ikut terhapus
        $sisa = $k->foto->pluck('gambar')->all();
        $this->actingAs($admin)->delete(route('admin.kegiatan.destroy', $k))->assertRedirect();
        Storage::disk('public')->assertMissing($sisa);
    }

    public function test_kegiatan_maksimal_foto_sekali_simpan(): void
    {
        Storage::fake('public');
        $foto = array_map(fn ($i) => UploadedFile::fake()->image("$i.jpg"), range(1, \App\Http\Controllers\Admin\KegiatanController::MAKS_FOTO_SEKALI + 1));

        $this->actingAs($this->u('admin@tkceria.sch.id'))->post(route('admin.kegiatan.store'), [
            'judul' => 'Terlalu banyak', 'tanggal' => '2026-11-03', 'foto' => $foto,
        ])->assertSessionHasErrors('foto');
    }

    public function test_website_tetap_jalan_sebelum_database_diperbarui(): void
    {
        Storage::fake('public');
        \Illuminate\Support\Facades\Schema::drop('kegiatan_foto');
        \App\Support\Basisdata::lupakan();
        $k = Kegiatan::firstOrFail();

        $this->get('/')->assertOk();
        $this->get(route('kegiatan.index'))->assertOk();
        $this->get(route('kegiatan.show', $k))->assertOk();

        $admin = $this->u('admin@tkceria.sch.id');
        $this->actingAs($admin)->get(route('admin.kegiatan.edit', $k))->assertOk()->assertSee('perbarui database dulu', false);
        $this->actingAs($admin)->put(route('admin.kegiatan.update', $k), [
            'judul' => $k->judul, 'tanggal' => $k->tanggal->toDateString(), 'gambar' => UploadedFile::fake()->image('a.jpg'),
        ])->assertRedirect();
        $this->assertNotNull($k->fresh()->gambar);

        // Cek Sistem menawarkan pembaruan, dan tombolnya membuat tabel kembali
        \Illuminate\Support\Facades\DB::table('migrations')->where('migration', '2026_10_19_000001_create_kegiatan_foto_table')->delete();
        $this->actingAs($admin)->get(route('admin.cek-sistem'))->assertOk()->assertSee('Perbarui database sekarang');
        $this->actingAs($admin)->post(route('admin.cek-sistem.database'))->assertRedirect(route('admin.cek-sistem'))->assertSessionHas('success');
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('kegiatan_foto'));
        $this->assertSame(1, \App\Models\KegiatanFoto::where('kegiatan_id', $k->id)->count(), 'Foto lama ikut masuk album');

        $this->actingAs($this->u('budi@contoh.com'))->post(route('admin.cek-sistem.database'))->assertForbidden();
    }

    public function test_pesan_sistem_berbahasa_indonesia(): void
    {
        $this->assertSame('id', app()->getLocale());

        // Pesan validasi
        $this->actingAs($this->u('admin@tkceria.sch.id'))->post(route('admin.kelas.store'), [])
            ->assertSessionHasErrors(['nama' => 'Nama wajib diisi.']);

        // Login gagal
        auth()->logout();
        $this->post(route('login.attempt'), ['email' => 'bukan-email', 'password' => 'x'])
            ->assertSessionHasErrors(['email' => 'Email harus berupa alamat email yang valid.']);

        // Navigasi halaman (pagination)
        foreach (range(1, 45) as $i) \App\Models\Pesan::create(['nama' => "Tamu $i", 'pesan' => 'Halo', 'telepon' => '08123']);
        $this->actingAs($this->u('admin@tkceria.sch.id'))->get(route('admin.pesan.index'))
            ->assertOk()->assertSee('Menampilkan')->assertSee('Berikutnya', false)->assertDontSee('Showing');
    }
}
