<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    /** warna => [latar kartu, latar+warna ikon] */
    public const KELAS_WARNA = [
        'rose' => ['bg-permen-soft', 'bg-permen text-navy border-2 border-navy'],
        'amber' => ['bg-sun-soft', 'bg-sun text-navy border-2 border-navy'],
        'violet' => ['bg-anggur-soft', 'bg-anggur text-navy border-2 border-navy'],
        'emerald' => ['bg-daun-soft', 'bg-daun text-navy border-2 border-navy'],
        'sky' => ['bg-langit-soft', 'bg-langit text-navy border-2 border-navy'],
    ];

    public function getKelasWarnaAttribute(): array
    {
        return self::KELAS_WARNA[$this->warna] ?? self::KELAS_WARNA['sky'];
    }

    protected $fillable = ['nama', 'keterangan', 'deskripsi', 'detail', 'gambar', 'ikon', 'warna', 'urutan'];
}
