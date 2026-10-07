<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensi';
    protected $fillable = ['siswa_id', 'kelas_id', 'guru_id', 'tanggal', 'status', 'keterangan'];
    protected $casts = ['tanggal' => 'date'];

    public const STATUS = ['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
