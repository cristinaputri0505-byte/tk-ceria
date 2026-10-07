<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $siswa = Siswa::with(['kelas', 'orangTua'])
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('nama', 'like', "%$s%")->orWhere('nis', 'like', "%$s%")))
            ->when($request->kelas_id, fn ($q, $k) => $q->where('kelas_id', $k))
            ->orderBy('nama')->paginate(15)->withQueryString();

        return view('admin.siswa.index', ['siswa' => $siswa, 'kelas' => Kelas::orderBy('nama')->get()]);
    }

    public function create()
    {
        return view('admin.siswa.form', $this->formData(new Siswa));
    }

    public function store(Request $request)
    {
        Siswa::create($this->validated($request));
        return redirect()->route('admin.siswa.index')->with('success', 'Data siswa ditambahkan.');
    }

    public function edit(Siswa $siswa)
    {
        return view('admin.siswa.form', $this->formData($siswa));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $siswa->update($this->validated($request, $siswa));
        return redirect()->route('admin.siswa.index')->with('success', 'Data siswa diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        if ($siswa->foto) Storage::disk('public')->delete($siswa->foto);
        $siswa->delete();
        return back()->with('success', 'Data siswa dihapus.');
    }

    private function formData(Siswa $siswa): array
    {
        return [
            'siswa' => $siswa,
            'kelas' => Kelas::orderBy('nama')->get(),
            'orangTua' => User::where('role', User::ORANG_TUA)->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request, ?Siswa $siswa = null): array
    {
        $data = $request->validate([
            'nis' => ['required', 'string', 'max:20', Rule::unique('siswa', 'nis')->ignore($siswa)],
            'nama' => 'required|string|max:100',
            'jenis_kelamin' => 'required|in:L,P',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'nullable|string|max:255',
            'kelas_id' => 'nullable|exists:kelas,id',
            'orang_tua_id' => 'nullable|exists:users,id',
            'foto' => 'nullable|image|max:2048',
            'panggilan' => 'nullable|string|max:50',
            'tempat_lahir' => 'nullable|string|max:100',
            'agama' => 'nullable|string|max:30',
            'anak_ke' => 'nullable|integer|min:1|max:20',
            'golongan_darah' => 'nullable|in:A,B,AB,O',
            'catatan_kesehatan' => 'nullable|string|max:1000',
            'tanggal_masuk' => 'nullable|date',
            'nama_ayah' => 'nullable|string|max:100',
            'pekerjaan_ayah' => 'nullable|string|max:100',
            'telepon_ayah' => 'nullable|string|max:20',
            'nama_ibu' => 'nullable|string|max:100',
            'pekerjaan_ibu' => 'nullable|string|max:100',
            'telepon_ibu' => 'nullable|string|max:20',
        ]);
        unset($data['foto']);

        if ($request->hasFile('foto')) {
            if ($siswa?->foto) Storage::disk('public')->delete($siswa->foto);
            $data['foto'] = $request->file('foto')->store('siswa', 'public');
        }

        return $data;
    }
}
