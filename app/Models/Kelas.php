<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';
    protected $fillable = ['nama', 'kelompok', 'tahun_ajaran', 'wali_guru_id'];

    public function wali()
    {
        return $this->belongsTo(User::class, 'wali_guru_id');
    }

    public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }
}
