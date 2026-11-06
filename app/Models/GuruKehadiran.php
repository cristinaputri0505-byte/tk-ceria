<?php

namespace App\Models;

use App\Support\Basisdata;
use App\Support\Pengaturan;
use Illuminate\Database\Eloquent\Model;

/** Kehadiran guru per hari. Kolom tanggal disimpan sebagai teks Y-m-d, jam sebagai HH:MM (WIB). */
class GuruKehadiran extends Model
{
    protected $table = 'guru_kehadiran';
    protected $fillable = ['user_id', 'tanggal', 'status', 'jam_masuk', 'jam_pulang', 'keterangan', 'dicatat_oleh'];

    /** kode => [label, warna latar] */
    public const STATUS = [
        'hadir' => ['Hadir', 'bg-daun'],
        'izin' => ['Izin', 'bg-langit'],
        'sakit' => ['Sakit', 'bg-sun'],
        'dinas' => ['Dinas luar', 'bg-anggur'],
        'alpa' => ['Tanpa keterangan', 'bg-permen'],
    ];

    /** Pilihan batas jam masuk (Pengaturan Portal). */
    public const JAM_MASUK = ['06:30' => '06.30', '06:45' => '06.45', '07:00' => '07.00', '07:15' => '07.15', '07:30' => '07.30', '07:45' => '07.45', '08:00' => '08.00', '08:30' => '08.30'];

    public static function aktif(): bool
    {
        return Basisdata::ada('guru_kehadiran');
    }

    public static function batasMasuk(): string
    {
        $j = (string) (Pengaturan::semua()['guru_jam_masuk'] ?? '07:30');

        return array_key_exists($j, self::JAM_MASUK) ? $j : '07:30';
    }

    public function getTerlambatAttribute(): bool
    {
        return $this->status === 'hadir' && $this->jam_masuk && $this->jam_masuk > self::batasMasuk();
    }

    public function guru()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}
