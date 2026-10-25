<?php

namespace App\Models;

use App\Support\Basisdata;
use Illuminate\Database\Eloquent\Model;

class ElearningTugas extends Model
{
    protected $table = 'elearning_tugas';
    protected $fillable = ['kelas_id', 'user_id', 'tipe', 'judul', 'instruksi', 'ikon', 'warna', 'bintang', 'data', 'aktif', 'urutan'];
    protected $casts = ['data' => 'array', 'aktif' => 'boolean', 'bintang' => 'integer'];

    /** kode => [label, emoji] */
    public const TIPE = [
        'kuis' => ['Tebak Gambar', '❓'],
        'hitung' => ['Berhitung Ceria', '🔢'],
        'cocok' => ['Mencocokkan', '🧩'],
        'gambar' => ['Menggambar', '🎨'],
    ];

    /** kode => [label, emoji] */
    public const WARNA = [
        'pink' => ['Merah muda', '🌸'], 'biru' => ['Biru', '💙'], 'hijau' => ['Hijau', '🍏'],
        'kuning' => ['Kuning', '🌻'], 'ungu' => ['Ungu', '🍇'], 'oranye' => ['Oranye', '🍊'],
    ];

    public static function aktifDiDatabase(): bool
    {
        return Basisdata::ada('elearning_tugas') && Basisdata::ada('elearning_hasil');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function hasil()
    {
        return $this->hasMany(ElearningHasil::class, 'tugas_id');
    }

    /** Tugas yang tampil untuk satu kelas: khusus kelas itu + untuk semua kelas. */
    public function scopeUntukKelas($q, ?int $kelasId)
    {
        return $q->where(fn ($w) => $w->whereNull('kelas_id')->when($kelasId, fn ($x) => $x->orWhere('kelas_id', $kelasId)));
    }

    /** Tugas yang boleh diubah user ini: admin semua, guru hanya kelas yang diampunya. */
    public function bolehDiubah(User $u): bool
    {
        if ($u->role === User::ADMIN) return true;
        if ($u->role !== User::GURU || ! $this->kelas_id) return false;

        return $u->kelasDiampu()->whereKey($this->kelas_id)->exists();
    }

    public function getLabelTipeAttribute(): string
    {
        return self::TIPE[$this->tipe][1] . ' ' . self::TIPE[$this->tipe][0];
    }

    /** Bentuk data yang dikirim ke halaman bermain anak (tanpa data internal). */
    public function untukPemain(): array
    {
        return [
            'id' => $this->id, 'tipe' => $this->tipe, 'judul' => $this->judul, 'instruksi' => $this->instruksi,
            'ikon' => $this->ikon, 'warna' => $this->warna, 'bintang' => $this->bintang, 'data' => $this->data,
        ];
    }
}
