<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Support\Pengaturan;

class ProgramController extends Controller
{
    public const WARNA = ['rose' => 'Merah muda', 'amber' => 'Kuning', 'violet' => 'Ungu', 'emerald' => 'Hijau', 'sky' => 'Biru'];

    public function index()
    {
        return view('admin.program.index', ['programs' => Program::orderBy('urutan')->get()]);
    }

    public function create()
    {
        return view('admin.program.form', ['program' => new Program(['urutan' => Program::max('urutan') + 1])]);
    }

    public function store(Request $request)
    {
        Program::create($this->validated($request));
        return redirect()->route('admin.program.index')->with('success', 'Program ditambahkan.');
    }

    public function edit(Program $program)
    {
        return view('admin.program.form', ['program' => $program]);
    }

    public function update(Request $request, Program $program)
    {
        $program->update($this->validated($request, $program));
        return redirect()->route('admin.program.index')->with('success', 'Program diperbarui.');
    }

    public function destroy(Program $program)
    {
        if ($program->gambar) Storage::disk('public')->delete($program->gambar);
        $program->delete();
        return back()->with('success', 'Program dihapus.');
    }

    private function validated(Request $request, ?Program $program = null): array
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:100',
            'deskripsi' => 'nullable|string|max:255',
            'detail' => 'nullable|string|max:5000',
            'ikon' => ['required', Rule::in(array_keys(Pengaturan::IKON))],
            'warna' => ['required', Rule::in(array_keys(self::WARNA))],
            'urutan' => 'required|integer|min:0|max:999',
            'gambar' => 'nullable|image|max:4096',
        ]);

        unset($data['gambar']);
        if ($request->hasFile('gambar')) {
            if ($program?->gambar) Storage::disk('public')->delete($program->gambar);
            $data['gambar'] = $request->file('gambar')->store('program', 'public');
        } elseif ($program && $request->boolean('hapus_gambar') && $program->gambar) {
            Storage::disk('public')->delete($program->gambar);
            $data['gambar'] = null;
        }

        return $data;
    }
}
