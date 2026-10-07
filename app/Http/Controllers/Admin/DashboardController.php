<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\Pendaftaran;
use App\Models\Siswa;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $hariIni = Absensi::whereDate('tanggal', today());

        return view('admin.dashboard', [
            'stat' => [
                'siswa' => Siswa::count(),
                'guru' => User::where('role', User::GURU)->count(),
                'kelas' => Kelas::count(),
                'pendaftar_baru' => Pendaftaran::where('status', 'baru')->count(),
            ],
            'hadirHariIni' => (clone $hariIni)->where('status', 'hadir')->count(),
            'absenTercatat' => $hariIni->count(),
            'pendaftaranTerbaru' => Pendaftaran::latest()->take(5)->get(),
            'kelas' => Kelas::with('wali')->withCount('siswa')->get(),
        ]);
    }
}
