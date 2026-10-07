<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KegiatanController extends Controller
{
    public function index()
    {
        return view('admin.kegiatan.index', ['kegiatan' => Kegiatan::latest('tanggal')->paginate(12)]);
    }

    public function create()
    {
        return view('admin.kegiatan.form', ['kegiatan' => new Kegiatan]);
    }

    public function store(Request $request)
    {
        Kegiatan::create($this->validated($request));
        return redirect()->route('admin.kegiatan.index')->with('success', 'Kegiatan ditambahkan.');
    }

    public function edit(Kegiatan $kegiatan)
    {
        return view('admin.kegiatan.form', ['kegiatan' => $kegiatan]);
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $kegiatan->update($this->validated($request, $kegiatan));
        return redirect()->route('admin.kegiatan.index')->with('success', 'Kegiatan diperbarui.');
    }

    public function destroy(Kegiatan $kegiatan)
    {
        if ($kegiatan->gambar) Storage::disk('public')->delete($kegiatan->gambar);
        $kegiatan->delete();
        return back()->with('success', 'Kegiatan dihapus.');
    }

    private function validated(Request $request, ?Kegiatan $kegiatan = null): array
    {
        $data = $request->validate([
            'judul' => 'required|string|max:100',
            'tanggal' => 'required|date',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|max:4096',
        ]);

        if ($request->hasFile('gambar')) {
            if ($kegiatan?->gambar) Storage::disk('public')->delete($kegiatan->gambar);
            $data['gambar'] = $request->file('gambar')->store('kegiatan', 'public');
        }

        return $data;
    }
}
