<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pendaftaran extends Model
{
    protected $table = 'pendaftaran';
    protected $fillable = ['nama_anak', 'tanggal_lahir', 'jenis_kelamin', 'program', 'nama_orang_tua', 'telepon', 'email', 'alamat', 'status', 'catatan', 'setuju_privasi_pada', 'izin_foto_publik'];
    protected $casts = ['tanggal_lahir' => 'date', 'setuju_privasi_pada' => 'datetime', 'izin_foto_publik' => 'boolean'];

    public const STATUS = ['baru' => 'Baru', 'diproses' => 'Diproses', 'diterima' => 'Diterima', 'ditolak' => 'Ditolak'];
}
