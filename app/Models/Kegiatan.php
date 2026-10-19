<?php

namespace App\Models;

use App\Support\Basisdata;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Kegiatan extends Model
{
    protected $table = 'kegiatan';
    protected $fillable = ['judul', 'tanggal', 'deskripsi', 'gambar'];
    protected $casts = ['tanggal' => 'date'];

    /** Semua foto kegiatan (album). Kolom 'gambar' menyimpan foto sampul, salah satu dari album ini. */
    public function foto()
    {
        return $this->hasMany(KegiatanFoto::class)->orderBy('urutan')->orderBy('id');
    }

    /** true bila tabel album sudah dibuat (migrasi sudah dijalankan di database). */
    public static function albumAktif(): bool
    {
        return Basisdata::ada('kegiatan_foto');
    }

    /** Query daftar kegiatan + jumlah foto (aman dipakai sebelum migrasi dijalankan). */
    public static function denganJumlahFoto()
    {
        return static::query()->when(static::albumAktif(), fn ($q) => $q->withCount('foto'));
    }

    /** URL semua foto untuk ditampilkan: album bila ada, atau foto sampul lama. */
    public function daftarFoto(): array
    {
        if (static::albumAktif() && ($album = $this->foto)->isNotEmpty()) {
            return $album->map(fn ($f) => $f->url)->all();
        }

        return $this->gambar ? [Storage::disk('public')->url($this->gambar)] : [];
    }

    public function getSampulUrlAttribute(): ?string
    {
        return $this->gambar ? Storage::disk('public')->url($this->gambar) : null;
    }
}
