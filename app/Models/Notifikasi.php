<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';
    protected $fillable = ['user_id', 'kategori', 'judul', 'isi', 'url', 'dibaca_at'];
    protected $casts = ['dibaca_at' => 'datetime'];

    /** kode => [label, ikon lucide] */
    public const KATEGORI = [
        'kegiatan' => ['Kegiatan', 'party-popper'],
        'pembayaran' => ['Pembayaran', 'wallet'],
        'pengumuman' => ['Pengumuman', 'megaphone'],
        'chat' => ['Chat', 'message-circle'],
        'akun' => ['Akun', 'lock-keyhole'],
    ];

    /** Kirim notifikasi ke banyak user. Notifikasi chat yang belum dibaca digabung agar tidak membanjiri. */
    public static function kirim($userIds, string $kategori, string $judul, ?string $isi = null, ?string $url = null): void
    {
        $userIds = collect($userIds)->filter()->unique()->values();
        if ($userIds->isEmpty()) return;
        $isi = $isi !== null ? Str::limit($isi, 250) : null;

        $judul = Str::limit($judul, 150, '');
        $now = now();

        if ($kategori === 'chat') {
            // Notifikasi chat yang belum dibaca cukup diperbarui (2 query untuk semua penerima,
            // bukan 2 query per orang tua seperti sebelumnya: pesan grup dikirim ke semua orang tua).
            $belumDibaca = fn () => static::whereIn('user_id', $userIds)->where('kategori', 'chat')->where('url', $url)->whereNull('dibaca_at');
            $sudahAda = $belumDibaca()->pluck('user_id')->map(fn ($id) => (int) $id);
            if ($sudahAda->isNotEmpty()) {
                $belumDibaca()->update(['judul' => $judul, 'isi' => $isi, 'created_at' => $now, 'updated_at' => $now]);
            }
            $userIds = $userIds->map(fn ($id) => (int) $id)->diff($sudahAda)->values();
            if ($userIds->isEmpty()) return;
        }

        foreach ($userIds->chunk(500) as $potongan) {
            static::insert($potongan->map(fn ($id) => [
                'user_id' => $id, 'kategori' => $kategori, 'judul' => $judul, 'isi' => $isi, 'url' => $url,
                'dibaca_at' => null, 'created_at' => $now, 'updated_at' => $now,
            ])->values()->all());
        }
    }
}
