<?php

namespace Database\Seeders;

use App\Models\Kegiatan;
use App\Models\Kelas;
use App\Models\Pengumuman;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Data contoh untuk portal orang tua (aman dijalankan pada database yang sudah berisi data):
 *   php artisan db:seed --class=PortalDemoSeeder
 */
class PortalDemoSeeder extends Seeder
{
    public function run(): void
    {
        $ayah = ['Budi Santoso', 'Agus Wijaya', 'Hendra Kurniawan', 'Rizky Ramadhan', 'Andi Pratama', 'Yusuf Hidayat', 'Fajar Nugroho', 'Dedi Saputra'];
        $ibu = ['Dewi Anggraini', 'Ratna Sari', 'Lina Marlina', 'Fitri Handayani', 'Nur Aini', 'Siti Aminah', 'Maya Puspita', 'Indah Permata'];
        $kerja = ['Karyawan swasta', 'Wiraswasta', 'PNS', 'Guru', 'Dokter', 'Ibu rumah tangga'];

        // ---- Biodata siswa yang masih kosong ----
        foreach (Siswa::orderBy('id')->get() as $i => $s) {
            $s->fill(array_filter([
                'panggilan' => $s->panggilan ?: explode(' ', $s->nama)[0],
                'tempat_lahir' => $s->tempat_lahir ?: 'Jakarta',
                'agama' => $s->agama ?: 'Islam',
                'anak_ke' => $s->anak_ke ?: ($i % 3) + 1,
                'golongan_darah' => $s->golongan_darah ?: ['A', 'B', 'O', 'AB'][$i % 4],
                'catatan_kesehatan' => $s->catatan_kesehatan ?: ($i % 4 === 1 ? 'Alergi kacang' : null),
                'tanggal_masuk' => $s->tanggal_masuk ?: (now()->month >= 7 ? now()->setMonth(7)->setDay(14) : now()->subYear()->setMonth(7)->setDay(14))->toDateString(),
                'nama_ayah' => $s->nama_ayah ?: ($s->orangTua && $s->orangTua->name === 'Budi Santoso' ? 'Budi Santoso' : $ayah[$i % 8]),
                'pekerjaan_ayah' => $s->pekerjaan_ayah ?: $kerja[$i % 5],
                'telepon_ayah' => $s->telepon_ayah ?: '0812' . str_pad((string) (3000000 + $i * 1111), 8, '0', STR_PAD_LEFT),
                'nama_ibu' => $s->nama_ibu ?: ($s->orangTua && $s->orangTua->name === 'Dewi Anggraini' ? 'Dewi Anggraini' : $ibu[$i % 8]),
                'pekerjaan_ibu' => $s->pekerjaan_ibu ?: $kerja[($i + 2) % 6],
                'telepon_ibu' => $s->telepon_ibu ?: '0813' . str_pad((string) (5000000 + $i * 2222), 8, '0', STR_PAD_LEFT),
            ], fn ($v) => $v !== null));
            $s->save();
        }

        // ---- Tagihan SPP 3 bulan terakhir + bulan ini ----
        $nominal = 350000;
        foreach (Siswa::whereNotNull('kelas_id')->get() as $i => $s) {
            for ($m = 3; $m >= 0; $m--) {
                $bulan = now()->startOfMonth()->subMonths($m);
                $lunas = $m >= 2 || ($m === 1 && $i % 3 !== 0);
                $t = Tagihan::firstOrCreate(
                    ['siswa_id' => $s->id, 'jenis' => 'SPP', 'periode' => $bulan->translatedFormat('F Y')],
                    ['bulan' => $bulan->toDateString(), 'jumlah' => $nominal, 'jatuh_tempo' => $bulan->copy()->addDays(9)->toDateString(), 'status' => 'belum']
                );
                if ($t->wasRecentlyCreated && $lunas) {
                    $t->lunasi(($i + $m) % 2 ? 'tunai' : 'transfer', User::where('role', 'admin')->first(), $bulan->copy()->addDays(5));
                }
            }
        }

        // ---- Pengumuman & agenda ----
        $admin = User::where('role', 'admin')->first();
        if (Pengumuman::count() === 0) {
            $kelasA = Kelas::where('kelompok', 'A')->first();
            Pengumuman::create(['judul' => 'Libur Hari Guru', 'isi' => "Sekolah libur pada hari tersebut untuk memperingati Hari Guru Nasional.\nKegiatan belajar kembali normal keesokan harinya.", 'penting' => true, 'tanggal_acara' => now()->addDays(10)->toDateString(), 'user_id' => $admin?->id]);
            Pengumuman::create(['judul' => 'Pembagian Rapor Semester', 'isi' => 'Orang tua dimohon hadir untuk menerima laporan perkembangan anak dan berdiskusi dengan wali kelas. Mohon konfirmasi kehadiran kepada wali kelas.', 'tanggal_acara' => now()->addDays(21)->toDateString(), 'user_id' => $admin?->id]);
            if ($kelasA) {
                Pengumuman::create(['judul' => 'Bawa bekal buah untuk kelas ' . $kelasA->nama, 'isi' => 'Hari Jumat kita belajar mengenal buah. Mohon bawakan satu buah kesukaan anak dalam wadah bertuliskan nama.', 'kelas_id' => $kelasA->id, 'tanggal_acara' => now()->next('Friday')->toDateString(), 'user_id' => $admin?->id]);
            }
            Pengumuman::create(['judul' => 'Pembayaran SPP kini bisa lewat aplikasi', 'isi' => 'Orang tua dapat mengunggah bukti transfer SPP langsung dari menu Pembayaran. Status akan berubah menjadi Lunas setelah diverifikasi admin.', 'user_id' => $admin?->id]);
        }

        if (Kegiatan::whereDate('tanggal', '>=', today())->doesntExist()) {
            Kegiatan::create(['judul' => 'Outbound ke Taman Kota', 'tanggal' => now()->addDays(14)->toDateString(), 'deskripsi' => 'Kegiatan luar kelas bersama seluruh siswa. Anak memakai baju olahraga dan membawa topi.']);
            Kegiatan::create(['judul' => 'Lomba Mewarnai', 'tanggal' => now()->addDays(28)->toDateString(), 'deskripsi' => 'Lomba mewarnai antar kelas dalam rangka ulang tahun sekolah.']);
        }
    }
}
