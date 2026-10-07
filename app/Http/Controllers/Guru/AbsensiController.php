<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $kelasList = $this->kelasUntuk($request);
        $kelas = $kelasList->firstWhere('id', (int) $request->kelas_id) ?? $kelasList->first();
        $tanggal = $request->date('tanggal') ?? today();

        $siswa = $kelas ? $kelas->siswa()->orderBy('nama')->get() : collect();
        $absensi = $kelas
            ? Absensi::where('kelas_id', $kelas->id)->whereDate('tanggal', $tanggal)->get()->keyBy('siswa_id')
            : collect();

        return view('guru.absensi', compact('kelasList', 'kelas', 'tanggal', 'siswa', 'absensi'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tanggal' => 'required|date|before_or_equal:today',
            'status' => 'required|array',
            'status.*' => ['required', Rule::in(array_keys(Absensi::STATUS))],
            'keterangan' => 'nullable|array',
        ]);

        $kelas = Kelas::findOrFail($data['kelas_id']);
        abort_unless($this->bolehKelola($request, $kelas), 403);

        $siswaIds = $kelas->siswa()->pluck('id')->all();

        foreach ($data['status'] as $siswaId => $status) {
            if (! in_array((int) $siswaId, $siswaIds, true)) continue;

            // whereDate agar aman di MySQL maupun SQLite
            $absen = Absensi::where('siswa_id', $siswaId)->whereDate('tanggal', $data['tanggal'])->first()
                ?? new Absensi(['siswa_id' => $siswaId, 'tanggal' => $data['tanggal']]);

            $absen->fill([
                'kelas_id' => $kelas->id,
                'guru_id' => $request->user()->id,
                'status' => $status,
                'keterangan' => $data['keterangan'][$siswaId] ?? null,
            ])->save();
        }

        return redirect()->route($this->rp($request) . 'absensi.index', ['kelas_id' => $kelas->id, 'tanggal' => $data['tanggal']])
            ->with('success', 'Absensi tersimpan.');
    }

    // ---- Dipakai bersama oleh panel Guru (kelas sendiri) dan Admin (semua kelas) ----
    private function rp(Request $request): string
    {
        return $request->user()->role === 'admin' ? 'admin.' : 'guru.';
    }

    private function kelasUntuk(Request $request)
    {
        return $request->user()->role === 'admin'
            ? Kelas::orderBy('nama')->get()
            : $request->user()->kelasDiampu()->orderBy('nama')->get();
    }

    private function bolehKelola(Request $request, Kelas $kelas): bool
    {
        return $request->user()->role === 'admin' || (int) $kelas->wali_guru_id === $request->user()->id;
    }
}
