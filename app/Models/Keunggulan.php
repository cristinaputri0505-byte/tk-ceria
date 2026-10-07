<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keunggulan extends Model
{
    protected $table = 'keunggulan';
    protected $fillable = ['ikon', 'judul', 'deskripsi', 'urutan'];
}
