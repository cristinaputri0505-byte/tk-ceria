<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $kelas = $request->user()->kelasDiampu()->withCount('siswa')->get();

        $absenHariIni = Absensi::whereIn('kelas_id', $kelas->pluck('id'))->whereDate('tanggal', today())
            ->selectRaw('kelas_id, status, count(*) as total')->groupBy('kelas_id', 'status')->get()
            ->groupBy('kelas_id');

        return view('guru.dashboard', compact('kelas', 'absenHariIni'));
    }
}
