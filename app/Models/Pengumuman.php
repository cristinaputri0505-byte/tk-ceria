<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    protected $table = 'pengumuman';
    protected $fillable = ['judul', 'isi', 'kelas_id', 'penting', 'tanggal_acara', 'user_id'];
    protected $casts = ['penting' => 'boolean', 'tanggal_acara' => 'date'];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function penulis()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Pengumuman untuk semua + untuk kelas tertentu. */
    public function scopeUntukKelas($q, $kelasIds)
    {
        return $q->where(fn ($w) => $w->whereNull('kelas_id')->orWhereIn('kelas_id', collect($kelasIds)->filter()->all()));
    }
}
