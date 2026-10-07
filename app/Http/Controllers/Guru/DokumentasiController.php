<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Dokumentasi;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DokumentasiController extends Controller
{
    public function index(Request $request)
    {
        $sumber = $request->user()->role === 'admin' ? Kelas::query() : $request->user()->kelasDiampu();
        $kelasList = $sumber->with(['siswa' => fn ($q) => $q->orderBy('nama')])->orderBy('nama')->get();
        $kelas = $kelasList->firstWhere('id', (int) $request->kelas_id) ?? $kelasList->first();

        return view('guru.dokumentasi', [
            'kelasList' => $kelasList,
            'kelas' => $kelas,
            'foto' => $kelas ? Dokumentasi::with('siswa')->where('kelas_id', $kelas->id)->latest('tanggal')->latest('id')->paginate(24)->withQueryString() : collect(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'siswa_id' => 'nullable|exists:siswa,id',
            'tanggal' => 'required|date|before_or_equal:today',
            'keterangan' => 'nullable|string|max:255',
            'gambar' => 'required|array|max:20',
            'gambar.*' => 'image|max:5120',
        ], [], ['gambar' => 'foto', 'gambar.*' => 'foto']);

        $kelas = ($request->user()->role === 'admin' ? Kelas::query() : $request->user()->kelasDiampu())->findOrFail($data['kelas_id']);
        if (! empty($data['siswa_id'])) abort_unless($kelas->siswa()->whereKey($data['siswa_id'])->exists(), 422);

        foreach ($request->file('gambar') as $file) {
            Dokumentasi::create([
                'kelas_id' => $kelas->id,
                'siswa_id' => $data['siswa_id'] ?? null,
                'guru_id' => $request->user()->id,
                'gambar' => $file->store('dokumentasi', 'local'),
                'keterangan' => $data['keterangan'] ?? null,
                'tanggal' => $data['tanggal'],
            ]);
        }

        return redirect()->route(($request->user()->role === 'admin' ? 'admin.' : 'guru.') . 'dokumentasi.index', ['kelas_id' => $kelas->id])
            ->with('success', count($request->file('gambar')) . ' foto dibagikan ke orang tua.');
    }

    public function destroy(Request $request, Dokumentasi $dokumentasi)
    {
        abort_unless($request->user()->role === 'admin' || (int) $dokumentasi->kelas->wali_guru_id === $request->user()->id, 403);
        Storage::disk('local')->delete($dokumentasi->gambar);
        $dokumentasi->delete();
        return back()->with('success', 'Foto dihapus.');
    }
}
