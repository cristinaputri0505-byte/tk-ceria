<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    protected $table = 'tagihan';
    protected $fillable = ['siswa_id', 'jenis', 'periode', 'bulan', 'jumlah', 'jatuh_tempo', 'status', 'metode', 'no_kuitansi', 'diterima_oleh', 'bukti', 'dibayar_pada', 'catatan'];
    protected $casts = ['bulan' => 'date', 'jatuh_tempo' => 'date', 'dibayar_pada' => 'datetime'];

    public const STATUS = ['belum' => 'Belum dibayar', 'menunggu' => 'Menunggu verifikasi', 'lunas' => 'Lunas'];
    public const METODE = ['transfer' => 'Transfer bank', 'tunai' => 'Tunai'];
    public const JENIS = ['SPP', 'Uang Pangkal', 'Seragam', 'Buku & Alat Tulis', 'Kegiatan', 'Lainnya'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function penerima()
    {
        return $this->belongsTo(User::class, 'diterima_oleh');
    }

    /** Tandai lunas + beri nomor kuitansi (KW-202610-0001). */
    public function lunasi(string $metode, ?User $petugas = null, $tanggal = null): void
    {
        $tanggal = $tanggal ? \Carbon\Carbon::parse($tanggal) : now();
        $prefix = 'KW-' . $tanggal->format('Ym') . '-';
        $no = $this->no_kuitansi;
        if (! $no) {
            $terakhir = static::where('no_kuitansi', 'like', $prefix . '%')->max('no_kuitansi');
            $no = $prefix . str_pad((string) ((int) substr((string) $terakhir, -4) + 1), 4, '0', STR_PAD_LEFT);
        }
        $this->update([
            'status' => 'lunas', 'metode' => $metode, 'no_kuitansi' => $no,
            'diterima_oleh' => $petugas?->id, 'dibayar_pada' => $tanggal,
        ]);
    }

    public function getRupiahAttribute(): string
    {
        return 'Rp ' . number_format($this->jumlah, 0, ',', '.');
    }

    public function getTerlambatAttribute(): bool
    {
        return $this->status === 'belum' && $this->jatuh_tempo && $this->jatuh_tempo->isPast() && ! $this->jatuh_tempo->isToday();
    }

    public static function rupiah(int $n): string
    {
        return 'Rp ' . number_format($n, 0, ',', '.');
    }

    /** Angka ke kata Bahasa Indonesia, untuk kuitansi. */
    public static function terbilang(int $n): string
    {
        $s = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        if ($n < 12) return $s[$n];
        if ($n < 20) return static::terbilang($n - 10) . ' belas';
        if ($n < 100) return trim(static::terbilang(intdiv($n, 10)) . ' puluh ' . static::terbilang($n % 10));
        if ($n < 200) return trim('seratus ' . static::terbilang($n - 100));
        if ($n < 1000) return trim(static::terbilang(intdiv($n, 100)) . ' ratus ' . static::terbilang($n % 100));
        if ($n < 2000) return trim('seribu ' . static::terbilang($n - 1000));
        if ($n < 1000000) return trim(static::terbilang(intdiv($n, 1000)) . ' ribu ' . static::terbilang($n % 1000));
        if ($n < 1000000000) return trim(static::terbilang(intdiv($n, 1000000)) . ' juta ' . static::terbilang($n % 1000000));
        return trim(static::terbilang(intdiv($n, 1000000000)) . ' miliar ' . static::terbilang($n % 1000000000));
    }
}
