<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dokumentasi extends Model
{
    protected $table = 'dokumentasi';
    protected $fillable = ['kelas_id', 'siswa_id', 'guru_id', 'gambar', 'keterangan', 'tanggal'];
    protected $casts = ['tanggal' => 'date'];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    /** Foto kelas (untuk semua anak) + foto khusus anak ini. */
    public function scopeUntukSiswa($q, Siswa $s)
    {
        return $q->where('kelas_id', $s->kelas_id)->where(fn ($w) => $w->whereNull('siswa_id')->orWhere('siswa_id', $s->id));
    }

    /** Foto anak bersifat privat: hanya admin, guru kelasnya, dan orang tua anak terkait. */
    public function bolehDilihat(User $u): bool
    {
        if ($u->role === User::ADMIN) return true;
        if ($u->role === User::GURU) return (int) optional($this->kelas)->wali_guru_id === $u->id;

        return $u->anak()
            ->where('kelas_id', $this->kelas_id)
            ->when($this->siswa_id, fn ($q) => $q->where('id', $this->siswa_id))
            ->exists();
    }

    public function getUrlAttribute(): string
    {
        return route('dokumentasi.foto', $this);
    }
}
