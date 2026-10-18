<?php

namespace App\Http\Controllers;

use App\Models\Dokumentasi;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\MimeTypeDetection\ExtensionMimeTypeDetector;

/** Menyajikan file privat (foto dokumentasi anak, bukti bayar) hanya kepada yang berhak. */
class FileController extends Controller
{
    public function dokumentasi(Request $request, Dokumentasi $dokumentasi)
    {
        abort_unless($dokumentasi->bolehDilihat($request->user()), 403);

        return $this->kirim($dokumentasi->gambar, 'private, max-age=86400');
    }

    /** Foto anak: admin, wali kelasnya, dan orang tuanya sendiri. */
    public function fotoSiswa(Request $request, Siswa $siswa)
    {
        $u = $request->user();
        $boleh = $u->role === User::ADMIN
            || ($u->role === User::GURU && (int) optional($siswa->kelas)->wali_guru_id === $u->id)
            || (int) $siswa->orang_tua_id === $u->id;
        abort_unless($boleh, 403);

        return $this->kirim($siswa->foto, 'private, max-age=604800');
    }

    public function bukti(Request $request, Tagihan $tagihan)
    {
        $u = $request->user();
        abort_unless($u->role === User::ADMIN || (int) $tagihan->siswa->orang_tua_id === $u->id, 403);

        return $this->kirim($tagihan->bukti, 'private, no-store');
    }

    public function kuitansi(Request $request, Tagihan $tagihan)
    {
        $u = $request->user();
        abort_unless($u->role === User::ADMIN || (int) $tagihan->siswa->orang_tua_id === $u->id, 403);
        abort_unless($tagihan->status === 'lunas', 404);

        return view('kuitansi', ['t' => $tagihan->load('siswa.kelas', 'siswa.orangTua', 'penerima')]);
    }

    /**
     * Kirim file privat dengan SATU permintaan baca ke penyimpanan.
     *
     * Sebelumnya dipakai exists() + response(): di Supabase Storage itu berarti 3 permintaan
     * (HEAD cek ada, HEAD ukuran, GET isi) dan ukuran gambar diambil dari header HEAD; satu saja
     * yang gagal membuat gambar tampil rusak. Sekarang isi dibaca sekali, jenis file ditentukan dari
     * ekstensi, ukuran dihitung dari isi yang benar-benar diterima, dan alasan gagal dicatat di log
     * (Vercel → Logs, cari "File privat").
     */
    private function kirim(?string $path, string $cache)
    {
        abort_unless($path, 404);

        try {
            $isi = Storage::disk('local')->get($path);
        } catch (\Throwable $e) {
            $isi = null;
            Log::warning('File privat gagal dibaca', ['path' => $path, 'alasan' => mb_substr($e->getMessage(), 0, 300)]);
        }
        if ($isi === null || $isi === '') {
            Log::warning('File privat tidak ditemukan atau kosong', ['path' => $path]);
            abort(404);
        }

        return response($isi, 200, [
            'Content-Type' => (new ExtensionMimeTypeDetector)->detectMimeTypeFromPath($path) ?? 'application/octet-stream',
            'Content-Length' => strlen($isi),
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            'Cache-Control' => $cache,
        ]);
    }
}
