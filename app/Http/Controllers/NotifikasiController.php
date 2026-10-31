<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $uid = $request->user()->id;
        $kategori = array_key_exists((string) $request->kategori, Notifikasi::KATEGORI) ? $request->kategori : null;

        return view('notifikasi.index', [
            'kategori' => $kategori,
            'belum' => Notifikasi::where('user_id', $uid)->whereNull('dibaca_at')->selectRaw('kategori, count(*) as n')->groupBy('kategori')->pluck('n', 'kategori'),
            'daftar' => Notifikasi::where('user_id', $uid)->when($kategori, fn ($q, $k) => $q->where('kategori', $k))
                ->latest()->latest('id')->paginate(20)->withQueryString(),
        ]);
    }

    /** Dipanggil berkala oleh header untuk memperbarui angka lonceng. */
    public function ringkas(Request $request)
    {
        return response()->json([
            'notif' => Notifikasi::where('user_id', $request->user()->id)->whereNull('dibaca_at')->count(),
        ]);
    }

    public function buka(Request $request, Notifikasi $notifikasi)
    {
        abort_unless((int) $notifikasi->user_id === (int) $request->user()->id, 403);
        if (! $notifikasi->dibaca_at) $notifikasi->update(['dibaca_at' => now()]);

        // Fitur portal yang sudah dimatikan admin: jangan arahkan ke halaman yang kini 404.
        $fitur = $this->fiturDariUrl((string) $notifikasi->url);
        if ($fitur && ! \App\Support\Pengaturan::fitur($fitur)) {
            return redirect()->route('notifikasi.index')->with('error', 'Halaman untuk notifikasi ini sedang tidak tersedia karena fiturnya dinonaktifkan sekolah.');
        }

        return redirect($notifikasi->url ?: route('notifikasi.index'));
    }

    public function bacaSemua(Request $request)
    {
        Notifikasi::where('user_id', $request->user()->id)->whereNull('dibaca_at')
            ->when(array_key_exists((string) $request->kategori, Notifikasi::KATEGORI), fn ($q) => $q->where('kategori', $request->kategori))
            ->update(['dibaca_at' => now()]);

        return back();
    }

    /** Nama fitur portal (Pengaturan::fitur) yang dibutuhkan alamat ini, atau null. */
    private function fiturDariUrl(string $url): ?string
    {
        $path = '/' . ltrim((string) parse_url($url, PHP_URL_PATH), '/');
        foreach (['/orangtua/pembayaran' => 'pembayaran', '/orangtua/pengumuman' => 'pengumuman', '/orangtua/dokumentasi' => 'dokumentasi', '/belajar' => 'belajar'] as $awal => $fitur) {
            if ($path === $awal || str_starts_with($path, $awal . '/')) return $fitur;
        }

        return null;
    }
}
