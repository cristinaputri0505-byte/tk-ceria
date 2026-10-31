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
            'hadirPerKelas' => (clone $hariIni)->where('status', 'hadir')->selectRaw('kelas_id, count(*) as n')->groupBy('kelas_id')->pluck('n', 'kelas_id'),
            // Hal yang menunggu tindakan admin (sama dengan angka di menu samping)
            'perlu' => [
                ['Pendaftar baru menunggu diverifikasi', Pendaftaran::where('status', 'baru')->count(), route('admin.pendaftaran.index', ['status' => 'baru']), 'clipboard-list', 'bg-permen-soft'],
                ['Bukti bayar menunggu dicek', \App\Models\Tagihan::where('status', 'menunggu')->count(), route('admin.tagihan.index'), 'wallet', 'bg-sun-soft'],
                ['Permintaan reset kata sandi', \App\Models\PermintaanReset::where('status', 'menunggu')->count(), route('admin.reset.index'), 'lock-keyhole', 'bg-anggur-soft'],
                ['Pesan masuk belum dibaca', \App\Models\Pesan::where('dibaca', false)->count(), route('admin.pesan.index'), 'inbox', 'bg-langit-soft'],
            ],
        ]);
    }
}
