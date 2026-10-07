<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\PermintaanReset;
use App\Models\User;
use Illuminate\Http\Request;

/** Permintaan reset password dari halaman login. Password baru hanya dirilis oleh admin. */
class LupaPasswordController extends Controller
{
    public function form()
    {
        return view('auth.lupa');
    }

    public function kirim(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|max:150',
            'telepon' => 'nullable|string|max:20',
            'catatan' => 'nullable|string|max:255',
        ], [], ['email' => 'email', 'telepon' => 'nomor WhatsApp', 'catatan' => 'catatan']);

        // Akun admin tidak bisa meminta reset lewat formulir ini.
        $user = User::where('email', $data['email'])->whereIn('role', [User::ORANG_TUA, User::GURU])->first();

        if ($user && ! PermintaanReset::where('user_id', $user->id)->where('status', 'menunggu')->exists()) {
            PermintaanReset::create([
                'user_id' => $user->id,
                'telepon_kontak' => isset($data['telepon']) ? preg_replace('/[^\d+]/', '', $data['telepon']) : null,
                'catatan' => $data['catatan'] ?? null,
                'ip' => $request->ip(),
            ]);

            Notifikasi::kirim(User::where('role', User::ADMIN)->pluck('id'), 'akun', 'Permintaan reset password',
                "{$user->name} ({$user->roleLabel()}) lupa password dan meminta password baru.",
                route('admin.reset.index', [], false));
        }

        // Pesan selalu sama, supaya orang lain tidak bisa menebak email mana yang terdaftar.
        return redirect()->route('lupa.form')->with('terkirim', true);
    }
}
