<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesan extends Model
{
    protected $table = 'pesan';
    protected $fillable = ['nama', 'email', 'telepon', 'pesan', 'dibaca'];
    protected $casts = ['dibaca' => 'boolean'];
}
