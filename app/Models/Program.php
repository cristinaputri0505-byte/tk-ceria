<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    /** warna => [latar kartu, latar+warna ikon] */
    public const KELAS_WARNA = [
        'rose' => ['bg-rose-50', 'bg-rose-100 text-rose-500'],
        'amber' => ['bg-amber-50', 'bg-amber-100 text-amber-500'],
        'violet' => ['bg-violet-50', 'bg-violet-100 text-violet-500'],
        'emerald' => ['bg-emerald-50', 'bg-emerald-100 text-emerald-600'],
        'sky' => ['bg-sky-50', 'bg-sky-100 text-sky-600'],
    ];

    public function getKelasWarnaAttribute(): array
    {
        return self::KELAS_WARNA[$this->warna] ?? self::KELAS_WARNA['sky'];
    }

    protected $fillable = ['nama', 'keterangan', 'deskripsi', 'detail', 'gambar', 'ikon', 'warna', 'urutan'];
}
