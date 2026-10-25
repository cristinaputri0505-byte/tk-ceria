<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * Suara Bahasa Indonesia yang natural dan ceria untuk Ruang Belajar, dibuat di SERVER sehingga
 * HP/laptop orang tua tidak perlu diatur apa pun.
 *
 * Mesin: sherpa-onnx + model Piper "id_ID-news_tts-medium" (suara perempuan Indonesia),
 * dipasang oleh Dockerfile.vercel di folder /opt/suara.
 * Nada dibuat lebih tinggi (ceria, cocok untuk anak) tanpa mengubah kecepatan bicara.
 * Di laptop (Laragon) biasanya mesin ini tidak ada: halaman lalu memakai suara browser.
 */
class Suara
{
    public const MAKS_KARAKTER = 300;

    /** kode => [label, faktor nada]. Faktor > 1 = nada lebih tinggi. */
    public const NADA = [
        'normal' => ['Normal (suara ibu guru)', 1.0],
        'ceria' => ['Ceria (lebih tinggi, ramah anak)', 1.2],
        'lucu' => ['Lucu sekali (seperti tokoh kartun)', 1.3],
    ];

    private static function folder(): string
    {
        return rtrim((string) env('SUARA_DIR', '/opt/suara'), '/');
    }

    public static function tersedia(): bool
    {
        $d = self::folder();

        return is_executable("$d/bin/sherpa-onnx-offline-tts") && is_file("$d/model/model.onnx") && is_file("$d/model/tokens.txt");
    }

    public static function faktorNada(): float
    {
        $kode = (string) (Pengaturan::semua()['belajar_nada'] ?? 'ceria');

        return self::NADA[$kode][1] ?? self::NADA['ceria'][1];
    }

    /** Teks -> isi file WAV, atau null bila mesin tidak ada / gagal. Hasil disimpan sementara (cache). */
    public static function buat(string $teks): ?string
    {
        $teks = self::bersihkan($teks);
        if ($teks === '' || ! self::tersedia()) return null;

        $nada = self::faktorNada();
        $cache = storage_path('framework/cache/suara/' . sha1($nada . '|' . $teks) . '.wav');
        if (is_file($cache) && filesize($cache) > 44) return file_get_contents($cache);

        $wav = self::sintesis($teks, $nada);
        if ($wav === null) return null;

        try {
            if (! is_dir(dirname($cache))) mkdir(dirname($cache), 0775, true);
            file_put_contents($cache, $wav);
        } catch (\Throwable) {
            // cache hanya penghemat waktu; boleh gagal
        }

        return $wav;
    }

    /** Buang emoji & simbol agar tidak dibacakan; batasi panjang. */
    public static function bersihkan(string $teks): string
    {
        $teks = preg_replace('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{FE0F}\x{200D}\x{20E3}]/u', ' ', $teks) ?? '';
        $teks = preg_replace('/[^\p{L}\p{N}\s.,!?\'"\-:;()%]/u', ' ', $teks) ?? '';
        $teks = trim(preg_replace('/\s+/u', ' ', $teks) ?? '');

        return mb_substr($teks, 0, self::MAKS_KARAKTER);
    }

    private static function sintesis(string $teks, float $nada): ?string
    {
        $d = self::folder();
        $keluar = tempnam(sys_get_temp_dir(), 'suara') . '.wav';

        // Nada dinaikkan dengan memutar audio lebih cepat (sample rate x faktor). Agar kecepatan bicara
        // tetap enak didengar anak, ucapan dibuat lebih panjang dengan faktor yang sama.
        $p = new Process([
            "$d/bin/sherpa-onnx-offline-tts",
            "--vits-model=$d/model/model.onnx",
            "--vits-tokens=$d/model/tokens.txt",
            "--vits-data-dir=$d/model/espeak-ng-data",
            '--vits-length-scale=' . round(1.05 * $nada, 3),
            '--vits-noise-scale=0.75',
            '--num-threads=2',
            "--output-filename=$keluar",
            $teks,
        ], $d, ['LD_LIBRARY_PATH' => "$d/lib"], null, 30);

        try {
            $p->run();
            $wav = is_file($keluar) ? file_get_contents($keluar) : '';
        } catch (\Throwable $e) {
            $wav = '';
            Log::warning('Mesin suara gagal dijalankan', ['alasan' => mb_substr($e->getMessage(), 0, 300)]);
        } finally {
            @unlink($keluar);
            @unlink(substr($keluar, 0, -4));
        }

        if (! $p->isSuccessful() || ! str_starts_with($wav, 'RIFF') || strlen($wav) <= 44) {
            Log::warning('Mesin suara gagal', ['kode' => $p->getExitCode(), 'galat' => mb_substr($p->getErrorOutput(), -300)]);
            return null;
        }

        return $nada === 1.0 ? $wav : self::naikkanNada($wav, $nada);
    }

    /** Ubah sample rate di header WAV (byte 24-31) sehingga suara diputar lebih tinggi. */
    public static function naikkanNada(string $wav, float $faktor): string
    {
        if (substr($wav, 0, 4) !== 'RIFF' || substr($wav, 12, 4) !== 'fmt ') return $wav;
        $f = unpack('vformat/vchannels/Vrate/VbyteRate/vblockAlign/vbits', substr($wav, 20, 16));
        $rate = (int) round($f['rate'] * $faktor);
        $byteRate = $rate * $f['blockAlign'];

        return substr($wav, 0, 24) . pack('V', $rate) . pack('V', $byteRate) . substr($wav, 32);
    }
}
