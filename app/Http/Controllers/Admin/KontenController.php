<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * CRUD generik untuk konten website berbentuk kartu (keunggulan, galeri, guru & staff).
 * Turunan cukup mendefinisikan model, nama route, judul, dan daftar field.
 */
abstract class KontenController extends Controller
{
    /** @var class-string<\Illuminate\Database\Eloquent\Model> */
    protected string $model;
    protected string $route;      // contoh: 'admin.staff'
    protected string $judul;      // judul halaman
    protected string $satuan;     // contoh: 'guru/staff'
    protected string $keterangan = '';
    protected string $folder = 'konten';
    protected int $perHalaman = 24;

    /**
     * key => [label, tipe, rules, opsi]
     * tipe: text, textarea, image, ikon, number
     * opsi: hint, wide, judul (dipakai sbg judul kartu), sub (subjudul kartu), wajib_baru (gambar wajib saat tambah)
     */
    abstract protected function fields(): array;

    protected function meta(): array
    {
        return [
            'judul' => $this->judul, 'satuan' => $this->satuan, 'keterangan' => $this->keterangan,
            'route' => $this->route, 'fields' => $this->fields(),
        ];
    }

    protected function query()
    {
        return $this->model::query()->orderBy('urutan')->orderBy('id');
    }

    public function index(Request $request)
    {
        return view('admin.konten.index', ['items' => $this->query()->paginate($this->perHalaman), 'm' => $this->meta()]);
    }

    public function create()
    {
        $item = new $this->model(['urutan' => (int) $this->model::max('urutan') + 1]);
        return view('admin.konten.form', ['item' => $item, 'm' => $this->meta()]);
    }

    public function store(Request $request)
    {
        $this->model::create($this->data($request));
        return redirect()->route("{$this->route}.index")->with('success', ucfirst($this->satuan) . ' ditambahkan.');
    }

    public function edit(string $id)
    {
        return view('admin.konten.form', ['item' => $this->model::findOrFail($id), 'm' => $this->meta()]);
    }

    public function update(Request $request, string $id)
    {
        $item = $this->model::findOrFail($id);
        $item->update($this->data($request, $item));
        return redirect()->route("{$this->route}.index")->with('success', ucfirst($this->satuan) . ' diperbarui.');
    }

    public function destroy(string $id)
    {
        $item = $this->model::findOrFail($id);
        foreach ($this->fields() as $key => $f) {
            if ($f[1] === 'image' && $item->$key) Storage::disk('public')->delete($item->$key);
        }
        $item->delete();
        return back()->with('success', ucfirst($this->satuan) . ' dihapus.');
    }

    protected function data(Request $request, $item = null): array
    {
        $rules = [];
        $labels = [];
        foreach ($this->fields() as $key => $f) {
            $rules[$key] = $f[2];
            if ($f[1] === 'image') {
                $wajib = ! $item && ($f[3]['wajib_baru'] ?? false);
                $rules[$key] = ($wajib ? 'required' : 'nullable') . '|image|max:4096';
            }
            $labels[$key] = strtolower($f[0]);
        }
        $data = $request->validate($rules, [], $labels);

        foreach ($this->fields() as $key => $f) {
            if ($f[1] !== 'image') continue;
            unset($data[$key]);
            if ($request->hasFile($key)) {
                if ($item?->$key) Storage::disk('public')->delete($item->$key);
                $data[$key] = $request->file($key)->store($this->folder, 'public');
            } elseif ($item && $request->boolean("hapus_$key") && ! ($f[3]['wajib_baru'] ?? false)) {
                if ($item->$key) Storage::disk('public')->delete($item->$key);
                $data[$key] = null;
            }
        }

        return $data;
    }
}
