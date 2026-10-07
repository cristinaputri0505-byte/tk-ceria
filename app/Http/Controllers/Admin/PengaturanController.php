<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        foreach ($fields as $key => [, $tipe]) {
            $rules[$key] = match ($tipe) {
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
                    if ($lama) Storage::disk('public')->delete($lama);
                    $simpan[$key] = $request->file($key)->store('website', 'public');
                } elseif ($request->boolean("hapus_$key") && $lama) {
                    Storage::disk('public')->delete($lama);
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
}
