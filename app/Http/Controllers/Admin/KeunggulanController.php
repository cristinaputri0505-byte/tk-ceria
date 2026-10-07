<?php

namespace App\Http\Controllers\Admin;

use App\Models\Keunggulan;
use App\Support\Pengaturan;

class KeunggulanController extends KontenController
{
    protected string $model = Keunggulan::class;
    protected string $route = 'admin.keunggulan';
    protected string $judul = 'Kartu Keunggulan';
    protected string $satuan = 'kartu keunggulan';
    protected string $keterangan = 'Kartu berikon yang tampil di bawah banner Home dan di halaman Tentang Kami. Disarankan 6 kartu.';

    protected function fields(): array
    {
        return [
            'ikon' => ['Ikon', 'ikon', 'required|in:' . implode(',', array_keys(Pengaturan::IKON))],
            'judul' => ['Judul', 'text', 'required|string|max:100', ['judul' => true]],
            'deskripsi' => ['Deskripsi', 'text', 'nullable|string|max:255', ['sub' => true, 'wide' => true]],
            'urutan' => ['Nomor urut', 'number', 'required|integer|min:0|max:999'],
        ];
    }
}
