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

        if ($kategori === 'chat') {
            foreach ($userIds as $id) {
                $ada = static::where('user_id', $id)->where('kategori', 'chat')->where('url', $url)->whereNull('dibaca_at')->first();
                if ($ada) {
                    $ada->update(['judul' => $judul, 'isi' => $isi, 'created_at' => now()]);
                } else {
                    static::create(['user_id' => $id, 'kategori' => 'chat', 'judul' => $judul, 'isi' => $isi, 'url' => $url]);
                }
            }
            return;
        }

        $now = now();
        static::insert($userIds->map(fn ($id) => [
            'user_id' => $id, 'kategori' => $kategori, 'judul' => Str::limit($judul, 150, ''), 'isi' => $isi, 'url' => $url,
            'dibaca_at' => null, 'created_at' => $now, 'updated_at' => $now,
        ])->all());
    }
}
