<?php

namespace App\Http\Controllers;

use App\Models\ElearningHasil;
use App\Models\ElearningTugas;
use App\Models\Kelas;
use App\Models\User;
use App\Support\Unggah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * E-Learning: guru (kelas yang diampu) dan admin (semua kelas) membuat tugas interaktif,
 * lalu melihat rekap bintang & karya gambar anak.
 */
class ElearningController extends Controller
{
    /** Kelas yang boleh dikelola user ini. */
    private function kelasSaya(Request $request)
    {
        $u = $request->user();

        return $u->role === User::ADMIN ? Kelas::orderBy('nama')->get() : $u->kelasDiampu()->orderBy('nama')->get();
    }

    private function siapAtauPesan()
    {
        if (ElearningTugas::aktifDiDatabase()) return null;

        return view('elearning.belum-siap');
    }

    public function index(Request $request)
    {
        if ($v = $this->siapAtauPesan()) return $v;

        $kelasList = $this->kelasSaya($request);
        $kelasId = $kelasList->firstWhere('id', (int) $request->kelas_id)?->id;
        $isAdmin = $request->user()->role === User::ADMIN;

        $tugas = ElearningTugas::with('kelas')
            ->withCount(['hasil as selesai_count' => fn ($q) => $q->whereNotNull('selesai_pada')])
            ->when($kelasId, fn ($q) => $q->untukKelas($kelasId))
            ->when(! $kelasId && ! $isAdmin, fn ($q) => $q->where(fn ($w) => $w->whereNull('kelas_id')->orWhereIn('kelas_id', $kelasList->pluck('id'))))
            ->orderBy('urutan')->latest('id')->get();

        return view('elearning.index', [
            'tugas' => $tugas,
            'kelasList' => $kelasList,
            'kelasId' => $kelasId,
            'jumlahSiswa' => \App\Models\Siswa::whereNotNull('kelas_id')->selectRaw('kelas_id, count(*) as n')->groupBy('kelas_id')->pluck('n', 'kelas_id'),
        ]);
    }

    public function create(Request $request)
    {
        if ($v = $this->siapAtauPesan()) return $v;

        $tugas = new ElearningTugas([
            'tipe' => in_array($request->tipe, array_keys(ElearningTugas::TIPE), true) ? $request->tipe : 'kuis',
            'ikon' => '⭐', 'warna' => 'pink', 'bintang' => 3, 'aktif' => true,
            'kelas_id' => $request->integer('kelas_id') ?: null,
            'data' => self::contohData(),
        ]);

        return view('elearning.form', ['tugas' => $tugas, 'kelasList' => $this->kelasSaya($request)]);
    }

    public function store(Request $request)
    {
        if ($v = $this->siapAtauPesan()) return $v;

        $tugas = ElearningTugas::create($this->validated($request) + ['user_id' => $request->user()->id]);

        return redirect()->route('elearning.index', ['kelas_id' => $tugas->kelas_id])->with('success', "Tugas \"{$tugas->judul}\" ditambahkan.");
    }

    public function edit(Request $request, ElearningTugas $tugas)
    {
        abort_unless($tugas->bolehDiubah($request->user()), 403);

        return view('elearning.form', ['tugas' => $tugas, 'kelasList' => $this->kelasSaya($request)]);
    }

    public function update(Request $request, ElearningTugas $tugas)
    {
        abort_unless($tugas->bolehDiubah($request->user()), 403);
        $tugas->update($this->validated($request));

        return redirect()->route('elearning.index', ['kelas_id' => $tugas->kelas_id])->with('success', "Tugas \"{$tugas->judul}\" diperbarui.");
    }

    public function destroy(Request $request, ElearningTugas $tugas)
    {
        abort_unless($tugas->bolehDiubah($request->user()), 403);
        Unggah::hapus($tugas->hasil()->whereNotNull('gambar')->pluck('gambar')->all(), 'local');
        $tugas->delete();

        return back()->with('success', 'Tugas dihapus.');
    }

    /** Rekap per kelas: bintang dan tugas selesai tiap anak, beserta karya gambarnya. */
    public function rekap(Request $request)
    {
        if ($v = $this->siapAtauPesan()) return $v;

        $kelasList = $this->kelasSaya($request);
        $kelas = $kelasList->firstWhere('id', (int) $request->kelas_id) ?? $kelasList->first();
        $siswa = $kelas ? $kelas->siswa()->orderBy('nama')->get() : collect();
        $tugas = $kelas ? ElearningTugas::untukKelas($kelas->id)->where('aktif', true)->orderBy('urutan')->latest('id')->get() : collect();
        $hasil = $kelas ? ElearningHasil::whereIn('siswa_id', $siswa->pluck('id'))->whereIn('tugas_id', $tugas->pluck('id'))->get()->groupBy('siswa_id') : collect();

        return view('elearning.rekap', compact('kelasList', 'kelas', 'siswa', 'tugas', 'hasil'));
    }

    /** Karya gambar anak (privat): admin, wali kelasnya, dan orang tuanya. */
    public function karya(Request $request, ElearningHasil $hasil)
    {
        $u = $request->user();
        $s = $hasil->siswa;
        $boleh = $u->role === User::ADMIN
            || ($u->role === User::GURU && (int) optional($s->kelas)->wali_guru_id === $u->id)
            || (int) $s->orang_tua_id === $u->id;
        abort_unless($boleh && $hasil->gambar, $boleh ? 404 : 403);

        try {
            $isi = Storage::disk('local')->get($hasil->gambar);
        } catch (\Throwable) {
            $isi = null;
        }
        abort_unless($isi, 404);

        return response($isi, 200, ['Content-Type' => 'image/png', 'Cache-Control' => 'private, max-age=300']);
    }

    /* ------------------------------------------------------------------ */

    private function validated(Request $request): array
    {
        $kelasBoleh = $this->kelasSaya($request)->pluck('id')->all();
        $isAdmin = $request->user()->role === User::ADMIN;
        $teks = 'nullable|string|max:40';

        $v = $request->validate([
            'tipe' => ['required', Rule::in(array_keys(ElearningTugas::TIPE))],
            'kelas_id' => [$isAdmin ? 'nullable' : 'required', 'integer', Rule::in($kelasBoleh)],
            'judul' => 'required|string|max:100',
            'instruksi' => 'required|string|max:300',
            'ikon' => 'required|string|max:16',
            'warna' => ['required', Rule::in(array_keys(ElearningTugas::WARNA))],
            'bintang' => 'required|integer|min:1|max:10',
            'urutan' => 'nullable|integer|min:0|max:999',
            // Tebak gambar
            'kuis_gambar' => 'required_if:tipe,kuis|nullable|string|max:40',
            'kuis_opsi' => 'required_if:tipe,kuis|array|size:4',
            'kuis_opsi.*' => 'required_if:tipe,kuis|nullable|string|max:40',
            'kuis_benar' => 'required_if:tipe,kuis|nullable|integer|min:0|max:3',
            // Berhitung
            'hitung_emoji' => 'required_if:tipe,hitung|nullable|string|max:16',
            'hitung_nama' => 'required_if:tipe,hitung|nullable|string|max:40',
            'hitung_jumlah' => 'required_if:tipe,hitung|nullable|integer|min:1|max:15',
            // Mencocokkan
            'cocok_kiri' => 'nullable|array|max:4',
            'cocok_kiri.*' => $teks,
            'cocok_kanan' => 'nullable|array|max:4',
            'cocok_kanan.*' => $teks,
            // Menggambar
            'gambar_stiker' => 'nullable|string|max:200',
        ], [
            'kelas_id.required' => 'Pilih kelas untuk tugas ini.',
            'kuis_opsi.*.required_if' => 'Keempat pilihan jawaban wajib diisi.',
        ], [
            'kuis_gambar' => 'gambar/emoji soal', 'kuis_benar' => 'kunci jawaban', 'hitung_emoji' => 'emoji benda',
            'hitung_nama' => 'nama benda', 'hitung_jumlah' => 'jumlah benda', 'gambar_stiker' => 'stiker',
        ]);

        $data = match ($v['tipe']) {
            'kuis' => ['gambar' => $v['kuis_gambar'], 'opsi' => array_values(array_map('trim', $v['kuis_opsi'])), 'benar' => (int) $v['kuis_benar']],
            'hitung' => ['emoji' => $v['hitung_emoji'], 'nama' => $v['hitung_nama'], 'jumlah' => (int) $v['hitung_jumlah']],
            'cocok' => ['pasangan' => $this->pasangan($request)],
            'gambar' => ['stiker' => $this->stiker($v['gambar_stiker'] ?? '')],
        };

        return [
            'tipe' => $v['tipe'], 'kelas_id' => $v['kelas_id'] ?? null, 'judul' => $v['judul'], 'instruksi' => $v['instruksi'],
            'ikon' => $v['ikon'], 'warna' => $v['warna'], 'bintang' => $v['bintang'], 'urutan' => $v['urutan'] ?? 0,
            'aktif' => $request->boolean('aktif'), 'data' => $data,
        ];
    }

    private function pasangan(Request $request): array
    {
        $kiri = (array) $request->input('cocok_kiri', []);
        $kanan = (array) $request->input('cocok_kanan', []);
        $hasil = [];
        foreach (range(0, 3) as $i) {
            $l = trim((string) ($kiri[$i] ?? ''));
            $r = trim((string) ($kanan[$i] ?? ''));
            if ($l !== '' && $r !== '') $hasil[] = ['kiri' => $l, 'kanan' => $r];
        }
        if (count($hasil) < 2) {
            throw \Illuminate\Validation\ValidationException::withMessages(['cocok_kiri' => 'Isi minimal 2 pasangan (kiri dan kanan).']);
        }

        return $hasil;
    }

    private function stiker(string $teks): array
    {
        $daftar = array_slice(array_values(array_filter(preg_split('/\s+/u', trim($teks)))), 0, 12);

        return $daftar ?: ['🏠', '🌳', '🌻', '☀️', '☁️', '🌈', '🦋', '🚗'];
    }

    /** Isian bawaan formulir agar guru tinggal mengubah. */
    public static function contohData(): array
    {
        return [
            'gambar' => '🌿🐘🥜', 'opsi' => ['🐘 Gajah', '🦒 Jerapah', '🐒 Monyet', '🦆 Bebek'], 'benar' => 0,
            'emoji' => '🍎', 'nama' => 'buah apel', 'jumlah' => 5,
            'pasangan' => [['kiri' => '🍓 Stroberi', 'kanan' => '🔴 Merah'], ['kiri' => '🍌 Pisang', 'kanan' => '🟡 Kuning'], ['kiri' => '🍃 Daun', 'kanan' => '🟢 Hijau']],
            'stiker' => ['🏠', '🌳', '🌻', '☀️', '☁️', '🌈', '🦋', '🚗'],
        ];
    }
}
