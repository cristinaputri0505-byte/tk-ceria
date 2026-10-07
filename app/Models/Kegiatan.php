<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    protected $table = 'kegiatan';
    protected $fillable = ['judul', 'tanggal', 'deskripsi', 'gambar'];
    protected $casts = ['tanggal' => 'date'];
}
