<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Pengumuman;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    public function index()
    {
        return view('admin.pengumuman.index', ['pengumuman' => Pengumuman::with('kelas', 'penulis')->latest()->paginate(15)]);
    }

    public function create()
    {
        return view('admin.pengumuman.form', ['pengumuman' => new Pengumuman, 'kelas' => Kelas::orderBy('nama')->get()]);
    }

    public function store(Request $request)
    {
        Pengumuman::create($this->validated($request) + ['user_id' => $request->user()->id]);
        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman dikirim ke panel orang tua.');
    }

    public function edit(Pengumuman $pengumuman)
    {
        return view('admin.pengumuman.form', ['pengumuman' => $pengumuman, 'kelas' => Kelas::orderBy('nama')->get()]);
    }

    public function update(Request $request, Pengumuman $pengumuman)
    {
        $pengumuman->update($this->validated($request));
        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman)
    {
        $pengumuman->delete();
        return back()->with('success', 'Pengumuman dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'judul' => 'required|string|max:150',
            'isi' => 'required|string|max:5000',
            'kelas_id' => 'nullable|exists:kelas,id',
            'tanggal_acara' => 'nullable|date',
        ]);
        $data['penting'] = $request->boolean('penting');
        return $data;
    }
}
