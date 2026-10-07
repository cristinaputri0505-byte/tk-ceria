<?php

namespace App\Providers;

use App\Models\Kegiatan;
use App\Models\Notifikasi;
use App\Models\Pengumuman;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

/** Membuat notifikasi otomatis saat admin menambah kegiatan, pengumuman, atau mengubah tagihan. */
class NotifikasiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Lewati saat artisan (migrate, seeder, dll.)
        if ($this->app->runningInConsole()) return;

        // Kegagalan membuat notifikasi tidak boleh menggagalkan penyimpanan data utama.
        $aman = fn (callable $f) => function (...$a) use ($f) {
            try { $f(...$a); } catch (\Throwable $e) { report($e); }
        };

        $semuaOrtu = fn () => User::where('role', User::ORANG_TUA)->pluck('id');
        $semuaAdmin = fn () => User::where('role', User::ADMIN)->pluck('id');

        Kegiatan::created($aman(function (Kegiatan $k) use ($semuaOrtu) {
            Notifikasi::kirim($semuaOrtu(), 'kegiatan', 'Kegiatan baru: ' . $k->judul,
                $k->tanggal?->translatedFormat('l, d F Y'), route('kegiatan.show', $k, false));
        }));

        Pengumuman::created($aman(function (Pengumuman $p) {
            $ids = User::where('role', User::ORANG_TUA)
                ->when($p->kelas_id, fn ($q) => $q->whereHas('anak', fn ($a) => $a->where('kelas_id', $p->kelas_id)))
                ->pluck('id');
            Notifikasi::kirim($ids, 'pengumuman', ($p->penting ? '[Penting] ' : '') . $p->judul,
                Str::limit($p->isi, 120), route('orangtua.pengumuman', [], false));
        }));

        Tagihan::created($aman(function (Tagihan $t) {
            $t->loadMissing('siswa');
            Notifikasi::kirim([$t->siswa?->orang_tua_id], 'pembayaran', "Tagihan baru: {$t->jenis} {$t->periode}",
                "{$t->rupiah} untuk {$t->siswa?->nama_panggilan}" . ($t->jatuh_tempo ? ', jatuh tempo ' . $t->jatuh_tempo->translatedFormat('d F Y') : ''),
                route('orangtua.pembayaran', [], false));
        }));

        Tagihan::updated($aman(function (Tagihan $t) use ($semuaAdmin) {
            if (! $t->wasChanged('status')) return;
            $t->loadMissing('siswa');
            $ortu = $t->siswa?->orang_tua_id;
            $lama = $t->getOriginal('status');

            if ($t->status === 'menunggu') {
                Notifikasi::kirim($semuaAdmin(), 'pembayaran', 'Bukti pembayaran baru',
                    "{$t->jenis} {$t->periode} ({$t->siswa?->nama_panggilan}) menunggu verifikasi.",
                    route('admin.tagihan.index', ['status' => 'menunggu'], false));
            } elseif ($t->status === 'lunas') {
                Notifikasi::kirim([$ortu], 'pembayaran', 'Pembayaran diterima',
                    "{$t->jenis} {$t->periode} untuk {$t->siswa?->nama_panggilan} sudah lunas. No. kuitansi {$t->no_kuitansi}.",
                    route('orangtua.pembayaran', [], false));
            } elseif ($t->status === 'belum' && $lama === 'menunggu') {
                Notifikasi::kirim([$ortu], 'pembayaran', 'Bukti pembayaran perlu diperiksa ulang',
                    "Bukti {$t->jenis} {$t->periode} belum dapat diverifikasi. Silakan cek catatan atau unggah ulang.",
                    route('orangtua.pembayaran', [], false));
            }
        }));
    }
}
