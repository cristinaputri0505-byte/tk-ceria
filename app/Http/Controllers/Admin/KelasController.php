<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index()
    {
        return view('admin.kelas.index', ['kelas' => Kelas::with('wali')->withCount('siswa')->orderBy('nama')->get()]);
    }

    public function create()
    {
        return view('admin.kelas.form', ['kelas' => new Kelas, 'guru' => $this->guru()]);
    }

    public function store(Request $request)
    {
        Kelas::create($this->validated($request));
        return redirect()->route('admin.kelas.index')->with('success', 'Kelas ditambahkan.');
    }

    public function edit(Kelas $kelas)
    {
        return view('admin.kelas.form', ['kelas' => $kelas, 'guru' => $this->guru()]);
    }

    public function update(Request $request, Kelas $kelas)
    {
        $kelas->update($this->validated($request));
        return redirect()->route('admin.kelas.index')->with('success', 'Kelas diperbarui.');
    }

    public function destroy(Kelas $kelas)
    {
        if ($kelas->siswa()->exists()) {
            return back()->with('error', 'Kelas masih memiliki siswa. Pindahkan siswa ke kelas lain sebelum menghapus.');
        }
        $kelas->delete();
        return back()->with('success', 'Kelas dihapus.');
    }

    private function guru()
    {
        return User::where('role', User::GURU)->orderBy('name')->get();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nama' => 'required|string|max:50',
            'kelompok' => 'required|in:A,B',
            'tahun_ajaran' => 'required|string|max:20',
            'wali_guru_id' => 'nullable|exists:users,id',
        ]);
    }
}
