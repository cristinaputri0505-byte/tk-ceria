<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Satu pintu untuk menyimpan & menghapus file unggahan (foto, bukti bayar) di seluruh aplikasi.
 *
 * - Bila penyimpanan (Supabase Storage) menolak, pengguna kembali ke formulir dengan pesan jelas,
 *   bukan halaman "500 Terjadi kesalahan di server". Admin juga melihat alasan teknisnya.
 * - Menghapus file lama tidak pernah menggagalkan penyimpanan data; kegagalan hanya dicatat di log.
 */
class Unggah
{
    /** Simpan file; lempar pesan validasi pada kolom $kolom bila gagal. Mengembalikan path file. */
    public static function simpan(UploadedFile $file, string $folder, string $disk, string $kolom): string
    {
        $alasan = null;
        try {
            $path = $file->store($folder, $disk);
        } catch (\Throwable $e) {
            $path = false;
            $alasan = $e->getMessage();
        }

        if (! $path) {
            $alasan ??= 'penyimpanan menolak file tanpa alasan';
            Log::error('Unggah file gagal', [
                'disk' => $disk, 'folder' => $folder, 'ukuran' => $file->getSize(),
                'jenis' => $file->getMimeType(), 'alasan' => mb_substr($alasan, 0, 500),
            ]);

            $pesan = 'File gagal disimpan ke penyimpanan. Silakan coba lagi beberapa saat lagi.';
            if (auth()->user()?->role === User::ADMIN) {
                $pesan .= ' Detail untuk admin: ' . mb_substr($alasan, 0, 250);
            }
            throw ValidationException::withMessages([$kolom => $pesan]);
        }

        return $path;
    }

    /** Hapus satu atau beberapa file. Tidak pernah melempar error. */
    public static function hapus(string|array|null $path, string $disk): void
    {
        $daftar = array_values(array_filter((array) $path));
        if (! $daftar) return;

        try {
            Storage::disk($disk)->delete($daftar);
        } catch (\Throwable $e) {
            Log::warning('Hapus file gagal', ['disk' => $disk, 'path' => $daftar, 'alasan' => mb_substr($e->getMessage(), 0, 300)]);
        }
    }
}
