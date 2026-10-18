<?php

namespace App\Http\Controllers\Admin;

use App\Support\Unggah;
use App\Http\Controllers\Controller;
use App\Models\Berita;
use Illuminate\Http\Request;

class BeritaController extends Controller
{
    public function index()
    {
        return view('admin.berita.index', ['berita' => Berita::latest()->paginate(15)]);
    }

    public function create()
    {
        return view('admin.berita.form', ['berita' => new Berita]);
    }

    public function store(Request $request)
    {
        Berita::create($this->validated($request));
        return redirect()->route('admin.berita.index')->with('success', 'Berita disimpan.');
    }

    public function edit(Berita $berita)
    {
        return view('admin.berita.form', ['berita' => $berita]);
    }

    public function update(Request $request, Berita $berita)
    {
        $berita->update($this->validated($request, $berita));
        return redirect()->route('admin.berita.index')->with('success', 'Berita diperbarui.');
    }

    public function destroy(Berita $berita)
    {
        Unggah::hapus($berita->gambar, 'public');
        $berita->delete();
        return back()->with('success', 'Berita dihapus.');
    }

    private function validated(Request $request, ?Berita $berita = null): array
    {
        $data = $request->validate([
            'judul' => 'required|string|max:150',
            'ringkasan' => 'nullable|string|max:255',
            'isi' => 'required|string',
            'gambar' => 'nullable|image|max:4096',
            'terbitkan' => 'nullable|boolean',
        ]);

        $data['published_at'] = $request->boolean('terbitkan') ? ($berita?->published_at ?? now()) : null;
        unset($data['terbitkan']);

        if ($request->hasFile('gambar')) {
            $data['gambar'] = Unggah::simpan($request->file('gambar'), 'berita', 'public', 'gambar');
            Unggah::hapus($berita?->gambar, 'public');
        }

        return $data;
    }
}
