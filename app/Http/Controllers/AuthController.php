<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau kata sandi salah. Periksa kembali lalu coba lagi.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route(Auth::user()->dashboardRoute()));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /** Kembali ke akun admin setelah "Masuk sebagai". */
    public function kembaliAdmin(Request $request)
    {
        $admin = User::where('role', User::ADMIN)->find($request->session()->pull('admin_asli'));
        abort_unless($admin, 403);

        Auth::login($admin);
        $request->session()->regenerate();

        return redirect()->route('admin.pengguna.index', ['role' => 'orangtua'])->with('success', 'Anda kembali ke akun admin.');
    }
}
