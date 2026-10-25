<?php

namespace App\Http\Controllers\Admin;

use App\Support\Unggah;
use App\Http\Controllers\Controller;
use App\Support\Basisdata;
use App\Support\CekPenyimpanan;
use App\Support\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PengaturanController extends Controller
{
    public function index(Request $request)
    {
        $tabs = Pengaturan::tabs();
        $tab = array_key_exists((string) $request->tab, $tabs) ? $request->tab : array_key_first($tabs);

        return view('admin.pengaturan.index', [
            'tabs' => $tabs,
            'tab' => $tab,
            'nilai' => Pengaturan::semua(),
        ]);
    }

    public function update(Request $request, string $tab)
    {
        $fields = Pengaturan::fields($tab);
        abort_if(empty($fields), 404);

        $rules = [];
        foreach ($fields as $key => $f) {
            $tipe = $f[1];
            $rules[$key] = match ($tipe) {
                'pilihan' => 'nullable|in:' . implode(',', array_keys($f[4] ?? [])),
                'image' => 'nullable|image|max:4096',
                'url' => 'nullable|url|max:255',
                'textarea', 'lines', 'embed' => 'nullable|string|max:3000',
                'ikon' => 'nullable|in:' . implode(',', array_keys(Pengaturan::IKON)),
                'toggle' => 'nullable|in:0,1',
                default => 'nullable|string|max:255',
            };
        }
        $request->validate($rules, [], array_map(fn ($f) => strtolower($f[0]), $fields));

        $simpan = [];
        foreach ($fields as $key => [, $tipe]) {
            if ($tipe === 'image') {
                $lama = Pengaturan::mentah($key);
                if ($request->hasFile($key)) {
                    $simpan[$key] = Unggah::simpan($request->file($key), 'website', 'public', $key);
                    Unggah::hapus($lama, 'public');
                } elseif ($request->boolean("hapus_$key") && $lama) {
                    Unggah::hapus($lama, 'public');
                    $simpan[$key] = null;
                }
                continue;
            }
            $simpan[$key] = $tipe === 'toggle' ? ($request->boolean($key) ? '1' : '0') : $request->input($key);
        }

        Pengaturan::simpan($simpan);

        return redirect()->route('admin.pengaturan.index', ['tab' => $tab])
            ->with('success', 'Perubahan tersimpan dan sudah tampil di website.');
    }

    /** Admin → Cek Sistem: penyimpanan file (Supabase Storage) dan struktur database. */
    public function cekSistem()
    {
        return view('admin.cek-sistem', [
            'hasil' => CekPenyimpanan::jalankan(),
            'tertunda' => rescue(fn () => Basisdata::migrasiTertunda(), ['(tidak bisa membaca daftar migrasi)'], false),
        ]);
    }

    /** Menjalankan migrasi database yang belum dijalankan (Vercel tidak menjalankannya otomatis). */
    public function perbaruiDatabase()
    {
        [$berhasil, $keluaran] = Basisdata::perbarui();
        Log::info('Admin memperbarui database', ['berhasil' => $berhasil, 'keluaran' => mb_substr($keluaran, 0, 1000)]);

        return redirect()->route('admin.cek-sistem')
            ->with($berhasil ? 'success' : 'error', $berhasil ? 'Database berhasil diperbarui.' : 'Database gagal diperbarui: ' . mb_substr($keluaran, 0, 300));
    }
}
