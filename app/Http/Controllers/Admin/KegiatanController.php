<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Models\KegiatanFoto;
use App\Support\Unggah;
use Illuminate\Http\Request;

class KegiatanController extends Controller
{
    /** Batas foto per sekali simpan, agar kiriman tidak melebihi batas ukuran server. Tambah lagi setelah disimpan. */
    public const MAKS_FOTO_SEKALI = 10;

    public function index()
    {
        return view('admin.kegiatan.index', ['kegiatan' => Kegiatan::denganJumlahFoto()->latest('tanggal')->paginate(12)]);
    }

    public function create()
    {
        return view('admin.kegiatan.form', ['kegiatan' => new Kegiatan, 'album' => collect()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $kegiatan = Kegiatan::create($data);
        $this->simpanFoto($request, $kegiatan);

        return redirect()->route('admin.kegiatan.index')->with('success', 'Kegiatan ditambahkan.' . $this->ringkas($kegiatan));
    }

    public function edit(Kegiatan $kegiatan)
    {
        return view('admin.kegiatan.form', [
            'kegiatan' => $kegiatan,
            'album' => Kegiatan::albumAktif() ? $kegiatan->foto : collect(),
        ]);
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $kegiatan->update($this->validated($request, $kegiatan));
        $this->simpanFoto($request, $kegiatan);

        return redirect()->route('admin.kegiatan.edit', $kegiatan)->with('success', 'Kegiatan diperbarui.' . $this->ringkas($kegiatan));
    }

    public function destroy(Kegiatan $kegiatan)
    {
        $file = [$kegiatan->gambar];
        if (Kegiatan::albumAktif()) $file = array_merge($file, $kegiatan->foto()->pluck('gambar')->all());
        Unggah::hapus(array_unique(array_filter($file)), 'public');
        $kegiatan->delete();

        return back()->with('success', 'Kegiatan dihapus.');
    }

    private function validated(Request $request, ?Kegiatan $kegiatan = null): array
    {
        $data = $request->validate([
            'judul' => 'required|string|max:100',
            'tanggal' => 'required|date',
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|array|max:' . self::MAKS_FOTO_SEKALI,
            'foto.*' => 'image|max:4096',
            'hapus_foto' => 'nullable|array',
            'hapus_foto.*' => 'integer',
            'sampul' => 'nullable|integer',
            'gambar' => 'nullable|image|max:4096',
        ], [
            'foto.max' => 'Maksimal ' . self::MAKS_FOTO_SEKALI . ' foto sekali simpan. Simpan dulu, lalu tambahkan foto berikutnya.',
        ], ['foto' => 'foto', 'foto.*' => 'foto']);

        return array_intersect_key($data, array_flip(['judul', 'tanggal', 'deskripsi']));
    }

    /** Tambah/hapus foto album dan atur foto sampul (kolom 'gambar'). */
    private function simpanFoto(Request $request, Kegiatan $kegiatan): void
    {
        // Database belum diperbarui: tetap satu foto seperti sebelumnya.
        if (! Kegiatan::albumAktif()) {
            $file = $request->file('gambar') ?? ($request->file('foto')[0] ?? null);
            if ($file) {
                $baru = Unggah::simpan($file, 'kegiatan', 'public', 'foto');
                Unggah::hapus($kegiatan->gambar, 'public');
                $kegiatan->update(['gambar' => $baru]);
            }
            return;
        }

        // 1. Hapus foto yang dicentang
        $hapus = $kegiatan->foto()->whereIn('id', (array) $request->input('hapus_foto', []))->get();
        if ($hapus->isNotEmpty()) {
            Unggah::hapus($hapus->pluck('gambar')->all(), 'public');
            KegiatanFoto::whereIn('id', $hapus->pluck('id'))->delete();
        }

        // 2. Tambah foto baru
        $urutan = (int) $kegiatan->foto()->max('urutan');
        foreach ((array) $request->file('foto', []) as $file) {
            $kegiatan->foto()->create([
                'gambar' => Unggah::simpan($file, 'kegiatan', 'public', 'foto'),
                'urutan' => ++$urutan,
            ]);
        }

        // 3. Foto sampul: pilihan admin, atau tetap yang lama bila masih ada, atau foto pertama.
        $album = $kegiatan->foto()->get();
        $sampul = $album->firstWhere('id', (int) $request->input('sampul'))
            ?? $album->firstWhere('gambar', $kegiatan->gambar)
            ?? $album->first();
        $kegiatan->update(['gambar' => $sampul?->gambar]);
    }

    private function ringkas(Kegiatan $kegiatan): string
    {
        if (! Kegiatan::albumAktif()) return '';
        $n = $kegiatan->foto()->count();

        return $n ? " Album berisi {$n} foto." : ' Belum ada foto, tambahkan agar kartu kegiatan tidak kosong.';
    }
}
