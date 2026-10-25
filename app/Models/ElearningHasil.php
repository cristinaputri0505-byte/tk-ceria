<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ElearningHasil extends Model
{
    protected $table = 'elearning_hasil';
    protected $fillable = ['tugas_id', 'siswa_id', 'bintang', 'dimainkan', 'gambar', 'selesai_pada'];
    protected $casts = ['selesai_pada' => 'datetime'];

    public function tugas()
    {
        return $this->belongsTo(ElearningTugas::class, 'tugas_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function getGambarUrlAttribute(): ?string
    {
        return $this->gambar ? route('belajar.karya', $this) : null;
    }
}
