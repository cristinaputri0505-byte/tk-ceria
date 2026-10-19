<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class KegiatanFoto extends Model
{
    protected $table = 'kegiatan_foto';
    protected $fillable = ['kegiatan_id', 'gambar', 'urutan'];

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->gambar);
    }
}
