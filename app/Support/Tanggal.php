<?php

namespace App\Support;

use Illuminate\Http\Request;

/** Pilihan tanggal berupa tiga daftar (tanggal, bulan, tahun) — lebih jelas daripada kalender bawaan browser untuk tanggal lahir. */
class Tanggal
{
    public const BULAN = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    /**
     * Gabungkan {nama}_hari, {nama}_bulan, {nama}_tahun menjadi {nama} (Y-m-d) sebelum validasi.
     * Bila sebagian kosong → kosong (kena aturan "required"); bila tidak ada (mis. 31 Februari) → teks tidak valid (kena aturan "date").
     */
    public static function gabung(Request $request, string $nama): void
    {
        $bagian = [$request->input($nama.'_hari'), $request->input($nama.'_bulan'), $request->input($nama.'_tahun')];
        if ($bagian === [null, null, null]) return;     // formulir lama / API: pakai {nama} apa adanya

        [$h, $b, $t] = array_map(fn ($x) => (int) $x, $bagian);
        if (! $h || ! $b || ! $t) {
            $request->merge([$nama => null]);
        } elseif (! checkdate($b, $h, $t)) {
            $request->merge([$nama => 'tanggal-tidak-ada']);
        } else {
            $request->merge([$nama => sprintf('%04d-%02d-%02d', $t, $b, $h)]);
        }
    }
}
