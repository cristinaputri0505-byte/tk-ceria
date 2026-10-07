<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PermintaanReset;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    public function index()
    {
        return view('admin.reset.index', [
            'permintaan' => PermintaanReset::with('user', 'prosesor')
                ->orderByRaw("case status when 'menunggu' then 0 else 1 end")
                ->latest()->paginate(20),
        ]);
    }

    /** Admin merilis password baru: dibuat acak, atau diketik sendiri oleh admin. */
    public function rilis(Request $request, PermintaanReset $permintaan)
    {
        if ($permintaan->status !== 'menunggu') {
            return redirect()->route('admin.reset.index')->with('success', 'Permintaan ini sudah diproses sebelumnya.');
        }

        $data = $request->validate(['password' => 'nullable|string|min:8|max:50'], [], ['password' => 'password baru']);
        $plain = $data['password'] ?? $this->buatPassword();

        $user = $permintaan->user;
        $user->password = $plain;                       // di-hash otomatis oleh cast 'hashed'
        $user->setRememberToken(Str::random(60));       // login "ingat saya" lama tidak berlaku lagi
        $user->save();

        $permintaan->update(['status' => 'selesai', 'diproses_oleh' => $request->user()->id, 'diproses_pada' => now()]);

        // Password hanya dikirim lewat flash session: tampil sekali, tidak disimpan di database.
        return redirect()->route('admin.reset.index')->with('password_baru', [
            'nama' => $user->name, 'email' => $user->email, 'telepon' => $user->telepon, 'password' => $plain,
        ]);
    }

    public function tolak(Request $request, PermintaanReset $permintaan)
    {
        if ($permintaan->status === 'menunggu') {
            $permintaan->update(['status' => 'ditolak', 'diproses_oleh' => $request->user()->id, 'diproses_pada' => now()]);
        }

        return redirect()->route('admin.reset.index')->with('success', 'Permintaan ditolak.');
    }

    /** 10 karakter acak tanpa huruf/angka yang mirip (0/O, 1/l/I). */
    private function buatPassword(int $panjang = 10): string
    {
        $abjad = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $hasil = '';
        for ($i = 0; $i < $panjang; $i++) $hasil .= $abjad[random_int(0, strlen($abjad) - 1)];

        return $hasil;
    }
}
