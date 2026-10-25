<?php

namespace App\Http\Controllers;

use App\Models\ElearningHasil;
use App\Models\ElearningTugas;
use App\Models\Siswa;
use App\Support\Pengaturan;
use App\Support\Suara;
use App\Support\Unggah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Ruang Belajar anak: dibuka lewat akun orang tua, hasil tersimpan per anak. */
class BelajarController extends Controller
{
    private function cekFitur(): void
    {
        abort_unless(Pengaturan::fitur('belajar') && ElearningTugas::aktifDiDatabase(), 404);
    }

    private function pastikanAnak(Request $request, Siswa $siswa): void
    {
        abort_unless((int) $siswa->orang_tua_id === $request->user()->id, 403);
    }

    /** Pilih anak. Bila hanya satu anak, langsung masuk. */
    public function index(Request $request)
    {
        $this->cekFitur();
        $anak = $request->user()->anak()->with('kelas')->orderBy('nama')->get();
        if ($anak->count() === 1) return redirect()->route('belajar.main', $anak->first());

        $bintang = ElearningHasil::whereIn('siswa_id', $anak->pluck('id'))->selectRaw('siswa_id, sum(bintang) as n')->groupBy('siswa_id')->pluck('n', 'siswa_id');

        return view('belajar.pilih', compact('anak', 'bintang'));
    }

    public function main(Request $request, Siswa $siswa)
    {
        $this->cekFitur();
        $this->pastikanAnak($request, $siswa);

        $tugas = ElearningTugas::untukKelas($siswa->kelas_id)->where('aktif', true)->orderBy('urutan')->latest('id')->get();
        $hasil = ElearningHasil::where('siswa_id', $siswa->id)->whereIn('tugas_id', $tugas->pluck('id'))->get()->keyBy('tugas_id');

        return view('belajar.main', [
            'siswa' => $siswa->load('kelas.wali'),
            'semuaAnak' => $request->user()->anak()->orderBy('nama')->get(),
            'muatan' => [
                'anak' => ['id' => $siswa->id, 'nama' => $siswa->nama_panggilan, 'foto' => $siswa->foto_url, 'inisial' => mb_substr($siswa->nama, 0, 1)],
                'sambutan' => Pengaturan::isi('belajar_sambutan', ['{anak}' => $siswa->nama_panggilan]),
                'tugas' => $tugas->map->untukPemain()->values(),
                'hasil' => $hasil->map(fn ($h) => ['selesai' => (bool) $h->selesai_pada, 'bintang' => $h->bintang, 'gambar' => $h->gambar_url])->all(),
                'kirim' => str_replace('999999999', '__ID__', route('belajar.selesai', ['siswa' => $siswa->id, 'tugas' => 999999999])),
                'suara' => Suara::tersedia() ? route('belajar.suara') : null,
                'csrf' => csrf_token(),
            ],
        ]);
    }

    /** Dipanggil saat anak menyelesaikan tugas. Bintang hanya dihitung sekali per tugas. */
    public function selesai(Request $request, Siswa $siswa, ElearningTugas $tugas)
    {
        $this->cekFitur();
        $this->pastikanAnak($request, $siswa);
        abort_unless($tugas->aktif && ($tugas->kelas_id === null || (int) $tugas->kelas_id === (int) $siswa->kelas_id), 404);

        $request->validate(['gambar' => 'nullable|string|max:3000000']);

        $hasil = ElearningHasil::firstOrNew(['tugas_id' => $tugas->id, 'siswa_id' => $siswa->id]);
        $baru = ! $hasil->selesai_pada;
        $hasil->dimainkan = $hasil->exists ? $hasil->dimainkan + 1 : 1;
        $hasil->bintang = $tugas->bintang;
        $hasil->selesai_pada ??= now();

        if ($tugas->tipe === 'gambar' && $request->filled('gambar')) {
            $png = $this->bacaPng((string) $request->input('gambar'));
            if ($png === null) return response()->json(['ok' => false, 'pesan' => 'Gambar tidak valid.'], 422);

            $path = 'elearning/' . $siswa->id . '/' . $tugas->id . '-' . Str::random(8) . '.png';
            try {
                Storage::disk('local')->put($path, $png);
            } catch (\Throwable $e) {
                Log::error('Simpan karya e-learning gagal', ['alasan' => mb_substr($e->getMessage(), 0, 300)]);
                return response()->json(['ok' => false, 'pesan' => 'Gambar gagal disimpan. Coba lagi.'], 500);
            }
            $lama = $hasil->gambar;
            $hasil->gambar = $path;
            $hasil->save();
            Unggah::hapus($lama, 'local');
        } else {
            $hasil->save();
        }

        return response()->json([
            'ok' => true,
            'baru' => $baru,
            'bintang' => $tugas->bintang,
            'total' => (int) ElearningHasil::where('siswa_id', $siswa->id)->sum('bintang'),
            'gambar' => $hasil->gambar_url,
        ]);
    }

    /** Suara Bahasa Indonesia dari server (WAV). 503 bila server tidak punya mesin suara. */
    public function suara(Request $request)
    {
        $this->cekFitur();
        $request->validate(['teks' => 'required|string|max:600']);

        $wav = Suara::buat((string) $request->query('teks'));
        if ($wav === null) return response()->json(['ok' => false], 503);

        return response($wav, 200, [
            'Content-Type' => 'audio/wav',
            'Content-Length' => strlen($wav),
            'Cache-Control' => 'private, max-age=604800',
        ]);
    }

    /** data:image/png;base64,... → isi PNG, atau null bila bukan PNG yang wajar. */
    private function bacaPng(string $dataUrl): ?string
    {
        if (! str_starts_with($dataUrl, 'data:image/png;base64,')) return null;
        $isi = base64_decode(substr($dataUrl, 22), true);
        if ($isi === false || strlen($isi) > 2_000_000 || ! str_starts_with($isi, "\x89PNG\r\n\x1a\n")) return null;
        $ukuran = @getimagesizefromstring($isi);

        return $ukuran && $ukuran[0] <= 2000 && $ukuran[1] <= 2000 ? $isi : null;
    }
}
