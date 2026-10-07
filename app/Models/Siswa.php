<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswa';
    protected $fillable = [
        'nis', 'nama', 'panggilan', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'agama', 'anak_ke', 'alamat', 'kelas_id', 'orang_tua_id', 'foto',
        'golongan_darah', 'catatan_kesehatan', 'tanggal_masuk',
        'nama_ayah', 'pekerjaan_ayah', 'telepon_ayah', 'nama_ibu', 'pekerjaan_ibu', 'telepon_ibu',
    ];
    protected $casts = ['tanggal_lahir' => 'date', 'tanggal_masuk' => 'date'];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function orangTua()
    {
        return $this->belongsTo(User::class, 'orang_tua_id');
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }

    public function perkembangan()
    {
        return $this->hasMany(Perkembangan::class);
    }

    public function tagihan()
    {
        return $this->hasMany(Tagihan::class);
    }

    public function getNamaPanggilanAttribute(): string
    {
        return $this->panggilan ?: explode(' ', $this->nama)[0];
    }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? asset('storage/' . $this->foto) : null;
    }

    public function getUsiaAttribute(): string
    {
        if (! $this->tanggal_lahir) return '-';
        $d = $this->tanggal_lahir->diff(now());
        return "{$d->y} th {$d->m} bln";
    }
}
