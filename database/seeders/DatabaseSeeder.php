<?php

namespace Database\Seeders;

use App\Models\Absensi;
use App\Models\Berita;
use App\Models\Kegiatan;
use App\Models\Kelas;
use App\Models\Perkembangan;
use App\Models\Program;
use App\Models\Siswa;
use App\Models\Staff;
use App\Models\User;
use App\Http\Controllers\Guru\PerkembanganController;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Data contoh memakai password "password" yang tertulis di README. Jangan pernah masuk ke server.
        if (app()->isProduction()) {
            $this->command?->error('Seeder data contoh DIBATALKAN: aplikasi berjalan di mode produksi. Buat admin dengan: php artisan tk:buat-admin');
            return;
        }

        // ---- Akun demo (kata sandi semua: password) ----
        User::create(['name' => 'Admin TK Ceria', 'email' => 'admin@tkceria.sch.id', 'password' => 'password', 'role' => 'admin', 'jabatan' => 'Kepala Sekolah']);

        $guruA = User::create(['name' => 'Bu Rina Wulandari', 'email' => 'rina@tkceria.sch.id', 'password' => 'password', 'role' => 'guru', 'jabatan' => 'Wali Kelas A1', 'telepon' => '081234560001']);
        $guruB = User::create(['name' => 'Bu Sari Lestari', 'email' => 'sari@tkceria.sch.id', 'password' => 'password', 'role' => 'guru', 'jabatan' => 'Wali Kelas B1', 'telepon' => '081234560002']);
        User::create(['name' => 'Pak Dimas Pratama', 'email' => 'dimas@tkceria.sch.id', 'password' => 'password', 'role' => 'guru', 'jabatan' => 'Guru Olahraga & Musik']);

        // ---- Profil Guru & Staff untuk website (terpisah dari akun login) ----
        if (Staff::count() === 0) {
            foreach ([
                ['Ibu Siti Rahmawati, S.Pd.', 'Kepala Sekolah', 'S1 PG-PAUD'],
                ['Bu Rina Wulandari, S.Pd.', 'Wali Kelas A1', 'S1 PG-PAUD'],
                ['Bu Sari Lestari, S.Pd.', 'Wali Kelas B1', 'S1 PG-PAUD'],
                ['Pak Dimas Pratama', 'Guru Olahraga & Musik', 'S1 Pendidikan Jasmani'],
                ['Bu Maya Anggraini', 'Staf Tata Usaha', 'D3 Administrasi'],
            ] as $i => [$nama, $jabatan, $pend]) {
                Staff::create(['nama' => $nama, 'jabatan' => $jabatan, 'pendidikan' => $pend, 'urutan' => $i + 1]);
            }
        }

        $ortu1 = User::create(['name' => 'Budi Santoso', 'email' => 'budi@contoh.com', 'password' => 'password', 'role' => 'orangtua', 'telepon' => '081298760001']);
        $ortu2 = User::create(['name' => 'Dewi Anggraini', 'email' => 'dewi@contoh.com', 'password' => 'password', 'role' => 'orangtua', 'telepon' => '081298760002']);

        // ---- Kelas ----
        $tahun = now()->month >= 7 ? now()->year : now()->year - 1;
        $ta = $tahun . '/' . ($tahun + 1);
        $a1 = Kelas::create(['nama' => 'A1 Matahari', 'kelompok' => 'A', 'tahun_ajaran' => $ta, 'wali_guru_id' => $guruA->id]);
        $b1 = Kelas::create(['nama' => 'B1 Pelangi', 'kelompok' => 'B', 'tahun_ajaran' => $ta, 'wali_guru_id' => $guruB->id]);

        // ---- Siswa ----
        $data = [
            ['Alya Putri', 'P', '2021-03-12', $a1, $ortu1], ['Raka Aditya', 'L', '2021-07-02', $a1, $ortu2],
            ['Nadia Khairunnisa', 'P', '2021-01-20', $a1, null], ['Fajar Ramadhan', 'L', '2021-05-08', $a1, null],
            ['Kenzo Mahendra', 'L', '2020-02-14', $b1, $ortu1], ['Salsabila Azzahra', 'P', '2020-09-30', $b1, null],
            ['Gilang Saputra', 'L', '2020-11-11', $b1, null], ['Zahra Amelia', 'P', '2020-04-25', $b1, $ortu2],
        ];
        foreach ($data as $i => [$nama, $jk, $lahir, $kelas, $ortu]) {
            Siswa::create([
                'nis' => sprintf('%d%03d', $tahun, $i + 1), 'nama' => $nama, 'jenis_kelamin' => $jk,
                'tanggal_lahir' => $lahir, 'alamat' => 'Jakarta', 'kelas_id' => $kelas->id, 'orang_tua_id' => $ortu?->id,
            ]);
        }

        // ---- Absensi 10 hari sekolah terakhir & perkembangan ----
        $periode = PerkembanganController::periodeAktif();
        foreach (Siswa::with('kelas')->get() as $s) {
            $hari = now()->copy();
            for ($n = 0; $n < 10; $hari->subDay()) {
                if ($hari->isWeekend()) continue;
                Absensi::create([
                    'siswa_id' => $s->id, 'kelas_id' => $s->kelas_id, 'guru_id' => $s->kelas->wali_guru_id,
                    'tanggal' => $hari->toDateString(), 'status' => fake()->randomElement(['hadir', 'hadir', 'hadir', 'hadir', 'hadir', 'sakit', 'izin']),
                ]);
                $n++;
            }
            foreach (array_keys(Perkembangan::ASPEK) as $aspek) {
                Perkembangan::create([
                    'siswa_id' => $s->id, 'guru_id' => $s->kelas->wali_guru_id, 'periode' => $periode, 'aspek' => $aspek,
                    'nilai' => fake()->randomElement(['MB', 'BSH', 'BSH', 'BSB']),
                    'catatan' => 'Ananda menunjukkan kemajuan yang baik dan antusias mengikuti kegiatan kelas.',
                ]);
            }
        }

        // ---- Konten halaman visitor ----
        $programs = [
            ['Kelompok A', 'Usia 4–5 tahun', 'Fokus pada kemandirian dan sosialisasi.', 'school', 'rose'],
            ['Kelompok B', 'Usia 5–6 tahun', 'Meningkatkan kreativitas dan kemampuan berpikir.', 'star', 'amber'],
            ['Program Plus', 'Seni, Bahasa, Agama', 'Mengembangkan bakat dan minat anak.', 'palette', 'violet'],
            ['Ekstrakurikuler', 'Menari, Musik, Olahraga', 'Melatih keterampilan dan percaya diri.', 'trophy', 'emerald'],
        ];
        foreach ($programs as $i => [$nama, $ket, $desk, $ikon, $warna]) {
            Program::create(compact('nama', 'ikon', 'warna') + ['keterangan' => $ket, 'deskripsi' => $desk, 'urutan' => $i]);
        }

        foreach (['Kegiatan Seni', 'Outbound', 'Membuat Karya', 'Pentas Seni', 'Field Trip'] as $i => $judul) {
            Kegiatan::create(['judul' => $judul, 'tanggal' => now()->subWeeks($i + 1)->toDateString(), 'deskripsi' => "Dokumentasi {$judul} bersama anak-anak TK Ceria."]);
        }

        Berita::create([
            'judul' => "Pendaftaran Murid Baru Tahun Ajaran " . ($tahun + 1) . '/' . ($tahun + 2) . ' Dibuka',
            'ringkasan' => 'Kuota terbatas untuk Kelompok A dan Kelompok B. Daftar online lewat website.',
            'isi' => "TK Ceria membuka pendaftaran murid baru. Orang tua dapat mengisi formulir pendaftaran secara online, kemudian tim kami akan menghubungi untuk jadwal observasi anak.\n\nSyarat: fotokopi akta kelahiran, kartu keluarga, dan pas foto anak.",
            'published_at' => now(),
        ]);
        Berita::create([
            'judul' => 'Keseruan Field Trip ke Kebun Binatang',
            'ringkasan' => 'Anak-anak belajar mengenal hewan secara langsung.',
            'isi' => 'Kegiatan field trip kali ini mengajak anak-anak mengenal berbagai jenis hewan dan cara merawatnya.',
            'published_at' => now()->subDays(5),
        ]);

        $this->call(PortalDemoSeeder::class);
    }
}
