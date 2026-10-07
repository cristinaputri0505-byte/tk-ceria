<?php

namespace App\Http\Controllers;

use App\Models\Dokumentasi;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Menyajikan file privat (foto dokumentasi anak, bukti bayar) hanya kepada yang berhak. */
class FileController extends Controller
{
    public function dokumentasi(Request $request, Dokumentasi $dokumentasi)
    {
        abort_unless($dokumentasi->bolehDilihat($request->user()), 403);
        abort_unless(Storage::disk('local')->exists($dokumentasi->gambar), 404);

        return Storage::disk('local')->response($dokumentasi->gambar, null, ['Cache-Control' => 'private, max-age=86400']);
    }

    /** Foto anak: admin, wali kelasnya, dan orang tuanya sendiri. */
    public function fotoSiswa(Request $request, Siswa $siswa)
    {
        $u = $request->user();
        $boleh = $u->role === User::ADMIN
            || ($u->role === User::GURU && (int) optional($siswa->kelas)->wali_guru_id === $u->id)
            || (int) $siswa->orang_tua_id === $u->id;
        abort_unless($boleh, 403);
        abort_unless($siswa->foto && Storage::disk('local')->exists($siswa->foto), 404);

        return Storage::disk('local')->response($siswa->foto, null, ['Cache-Control' => 'private, max-age=604800']);
    }

    public function bukti(Request $request, Tagihan $tagihan)
    {
        $u = $request->user();
        abort_unless($u->role === User::ADMIN || (int) $tagihan->siswa->orang_tua_id === $u->id, 403);
        abort_unless($tagihan->bukti && Storage::disk('local')->exists($tagihan->bukti), 404);

        return Storage::disk('local')->response($tagihan->bukti, null, ['Cache-Control' => 'private, no-store']);
    }

    public function kuitansi(Request $request, Tagihan $tagihan)
    {
        $u = $request->user();
        abort_unless($u->role === User::ADMIN || (int) $tagihan->siswa->orang_tua_id === $u->id, 403);
        abort_unless($tagihan->status === 'lunas', 404);

        return view('kuitansi', ['t' => $tagihan->load('siswa.kelas', 'siswa.orangTua', 'penerima')]);
    }
}
