<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Hasil "Soal Hari Ini" (lihat App\Support\SoalHarian). Kolom tanggal disimpan sebagai teks Y-m-d. */
class ElearningHarian extends Model
{
    protected $table = 'elearning_harian';
    protected $fillable = ['siswa_id', 'tanggal', 'nomor', 'bintang', 'dimainkan'];
    protected $casts = ['bintang' => 'integer', 'nomor' => 'integer'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
