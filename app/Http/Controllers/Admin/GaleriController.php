<?php

namespace App\Http\Controllers\Admin;

use App\Models\Galeri;
use Illuminate\Http\Request;

class GaleriController extends KontenController
{
    protected string $model = Galeri::class;
    protected string $route = 'admin.galeri';
    protected string $judul = 'Galeri Foto';
    protected string $satuan = 'foto';
    protected string $keterangan = 'Foto yang tampil di halaman Galeri. Gunakan album untuk mengelompokkan foto.';
    protected string $folder = 'galeri';
    protected int $perHalaman = 36;

    protected function fields(): array
    {
        return [
            'gambar' => ['Foto', 'image', '', ['wide' => true, 'wajib_baru' => true, 'multiple' => true, 'hint' => 'Saat menambah, Anda bisa memilih beberapa foto sekaligus.']],
            'judul' => ['Keterangan foto', 'text', 'nullable|string|max:150', ['judul' => true]],
            'album' => ['Album', 'text', 'nullable|string|max:100', ['sub' => true, 'hint' => 'Contoh: Pentas Seni 2026. Foto dengan album sama dikelompokkan.']],
            'urutan' => ['Nomor urut', 'number', 'required|integer|min:0|max:999'],
        ];
    }

    protected function query()
    {
        return Galeri::query()->latest('id');
    }

    /** Tambah banyak foto sekaligus. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'gambar' => 'required|array|max:30',
            'gambar.*' => 'image|max:4096',
            'judul' => 'nullable|string|max:150',
            'album' => 'nullable|string|max:100',
            'urutan' => 'required|integer|min:0|max:999',
        ], [], ['gambar' => 'foto', 'gambar.*' => 'foto']);

        foreach ($request->file('gambar') as $i => $file) {
            Galeri::create([
                'gambar' => $file->store($this->folder, 'public'),
                'judul' => $data['judul'] ?? null,
                'album' => $data['album'] ?? null,
                'urutan' => $data['urutan'] + $i,
            ]);
        }

        return redirect()->route('admin.galeri.index')->with('success', count($request->file('gambar')) . ' foto ditambahkan ke galeri.');
    }
}
