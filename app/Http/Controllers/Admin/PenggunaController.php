<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Mengelola akun Guru, Orang Tua, dan Admin.
 */
class PenggunaController extends Controller
{
    private const ROLES = ['guru' => 'Guru', 'orangtua' => 'Orang Tua', 'admin' => 'Admin'];

    public function index(Request $request)
    {
        $role = array_key_exists((string) $request->role, self::ROLES) ? $request->role : 'guru';

        $pengguna = User::where('role', $role)
            ->withCount('anak')
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->whereLike('name', "%$s%")->orWhereLike('email', "%$s%")))
            ->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.pengguna.index', ['pengguna' => $pengguna, 'role' => $role, 'roles' => self::ROLES]);
    }

    public function create(Request $request)
    {
        $user = new User(['role' => $request->get('role', 'guru')]);
        return view('admin.pengguna.form', ['pengguna' => $user, 'roles' => self::ROLES]);
    }

    public function store(Request $request)
    {
        $user = User::create($this->validated($request));
        return redirect()->route('admin.pengguna.index', ['role' => $user->role])->with('success', 'Akun dibuat. Bagikan email dan kata sandi kepada pemilik akun.');
    }

    public function edit(User $pengguna)
    {
        return view('admin.pengguna.form', ['pengguna' => $pengguna, 'roles' => self::ROLES]);
    }

    public function update(Request $request, User $pengguna)
    {
        $pengguna->update($this->validated($request, $pengguna));
        return redirect()->route('admin.pengguna.index', ['role' => $pengguna->role])->with('success', 'Akun diperbarui.');
    }

    public function destroy(Request $request, User $pengguna)
    {
        if ($pengguna->is($request->user())) {
            return back()->with('error', 'Anda tidak dapat menghapus akun yang sedang dipakai.');
        }
        if ($pengguna->foto) Storage::disk('public')->delete($pengguna->foto);
        $pengguna->delete();
        return back()->with('success', 'Akun dihapus.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'email', 'max:100', Rule::unique('users')->ignore($user)],
            'role' => ['required', Rule::in(array_keys(self::ROLES))],
            'telepon' => 'nullable|string|max:20',
            'jabatan' => 'nullable|string|max:100',
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'foto' => 'nullable|image|max:2048',
        ]);

        if (empty($data['password'])) unset($data['password']);

        if ($request->hasFile('foto')) {
            if ($user?->foto) Storage::disk('public')->delete($user->foto);
            $data['foto'] = $request->file('foto')->store('pengguna', 'public');
        }

        return $data;
    }

    /** Admin melihat panel persis seperti yang dilihat guru / orang tua. */
    public function masukSebagai(Request $request, User $pengguna)
    {
        abort_if($pengguna->role === User::ADMIN, 403, 'Tidak bisa masuk sebagai admin lain.');

        $adminId = $request->user()->id;
        Auth::login($pengguna);
        $request->session()->regenerate();
        $request->session()->put('admin_asli', $adminId);

        return redirect()->route($pengguna->dashboardRoute());
    }
}
