<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\GuruKehadiran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Guru mencatat kehadirannya sendiri hari ini: absen masuk, absen pulang, atau tidak hadir (izin/sakit/dinas). */
class KehadiranSayaController extends Controller
{
    public function store(Request $request)
    {
        abort_unless(GuruKehadiran::aktif(), 404);
        $data = $request->validate([
            'aksi' => ['required', Rule::in(['masuk', 'pulang', 'izin', 'sakit', 'dinas'])],
            'keterangan' => 'nullable|string|max:255',
        ]);

        $user = $request->user();
        $hariIni = today()->toDateString();
        $jam = now()->timezone('Asia/Jakarta')->format('H:i');
        $k = GuruKehadiran::where('user_id', $user->id)->where('tanggal', $hariIni)->first();

        switch ($data['aksi']) {
            case 'masuk':
                if ($k && $k->status === 'hadir' && $k->jam_masuk) return back()->with('error', 'Anda sudah absen masuk hari ini pukul ' . $k->jam_masuk . '.');
                $k ??= new GuruKehadiran(['user_id' => $user->id, 'tanggal' => $hariIni]);
                $k->fill(['status' => 'hadir', 'jam_masuk' => $jam, 'keterangan' => $data['keterangan'] ?? ($k->status === 'hadir' ? $k->keterangan : null), 'dicatat_oleh' => $user->id])->save();
                $pesan = 'Absen masuk tercatat pukul ' . str_replace(':', '.', $jam) . ' WIB. Selamat mengajar!';
                break;

            case 'pulang':
                if (! $k || $k->status !== 'hadir' || ! $k->jam_masuk) return back()->with('error', 'Absen masuk dulu sebelum absen pulang.');
                if ($k->jam_pulang) return back()->with('error', 'Anda sudah absen pulang hari ini pukul ' . $k->jam_pulang . '.');
                $k->update(['jam_pulang' => $jam]);
                $pesan = 'Absen pulang tercatat pukul ' . str_replace(':', '.', $jam) . ' WIB. Terima kasih!';
                break;

            default: // izin, sakit, dinas luar
                if ($k && $k->status === 'hadir' && $k->jam_masuk) return back()->with('error', 'Anda sudah absen masuk hari ini. Hubungi admin bila perlu diubah.');
                GuruKehadiran::updateOrCreate(['user_id' => $user->id, 'tanggal' => $hariIni], [
                    'status' => $data['aksi'], 'jam_masuk' => null, 'jam_pulang' => null,
                    'keterangan' => $data['keterangan'] ?? null, 'dicatat_oleh' => $user->id,
                ]);
                $pesan = 'Tercatat: ' . GuruKehadiran::STATUS[$data['aksi']][0] . ' hari ini.';
        }

        return back()->with('success', $pesan);
    }
}
