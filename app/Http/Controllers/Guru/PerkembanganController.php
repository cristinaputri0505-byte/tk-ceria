<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Perkembangan;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PerkembanganController extends Controller
{
    public static function periodeAktif(): string
    {
        $tahun = now()->month >= 7 ? now()->year : now()->year - 1;
        $semester = now()->month >= 7 ? 1 : 2;
        return "Semester {$semester} {$tahun}/" . ($tahun + 1);
    }

    public function index(Request $request)
    {
        $periode = $request->get('periode', self::periodeAktif());
        $sumber = $request->user()->role === 'admin' ? Kelas::query() : $request->user()->kelasDiampu();
        $kelas = $sumber->orderBy('nama')->with(['siswa' => fn ($q) => $q->orderBy('nama')
            ->withCount(['perkembangan as terisi' => fn ($p) => $p->where('periode', $periode)])])->get();

        return view('guru.perkembangan.index', compact('kelas', 'periode'));
    }

    public function edit(Request $request, Siswa $siswa)
    {
        $this->authorizeSiswa($request, $siswa);
        $periode = $request->get('periode', self::periodeAktif());
        $nilai = $siswa->perkembangan()->where('periode', $periode)->get()->keyBy('aspek');

        return view('guru.perkembangan.edit', compact('siswa', 'periode', 'nilai'));
    }

    public function store(Request $request, Siswa $siswa)
    {
        $this->authorizeSiswa($request, $siswa);

        $data = $request->validate([
            'periode' => 'required|string|max:50',
            'nilai' => 'required|array',
            'nilai.*' => ['nullable', Rule::in(array_keys(Perkembangan::NILAI))],
            'catatan' => 'nullable|array',
            'catatan.*' => 'nullable|string|max:1000',
        ]);

        foreach (Perkembangan::ASPEK as $kode => $label) {
            if (empty($data['nilai'][$kode])) continue;

            Perkembangan::updateOrCreate(
                ['siswa_id' => $siswa->id, 'periode' => $data['periode'], 'aspek' => $kode],
                ['guru_id' => $request->user()->id, 'nilai' => $data['nilai'][$kode], 'catatan' => $data['catatan'][$kode] ?? null]
            );
        }

        return redirect()->route(($request->user()->role === 'admin' ? 'admin.' : 'guru.') . 'perkembangan.index', ['periode' => $data['periode']])
            ->with('success', "Laporan perkembangan {$siswa->nama} tersimpan.");
    }

    private function authorizeSiswa(Request $request, Siswa $siswa): void
    {
        if ($request->user()->role === 'admin') return;
        abort_unless($siswa->kelas && (int) $siswa->kelas->wali_guru_id === $request->user()->id, 403);
    }
}
