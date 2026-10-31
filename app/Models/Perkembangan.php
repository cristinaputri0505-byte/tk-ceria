<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perkembangan extends Model
{
    protected $table = 'perkembangan';
    protected $fillable = ['siswa_id', 'guru_id', 'periode', 'aspek', 'nilai', 'catatan'];

    // Aspek perkembangan sesuai Kurikulum PAUD
    public const ASPEK = [
        'agama_moral' => 'Nilai Agama & Moral',
        'fisik_motorik' => 'Fisik Motorik',
        'kognitif' => 'Kognitif',
        'bahasa' => 'Bahasa',
        'sosial_emosional' => 'Sosial Emosional',
        'seni' => 'Seni',
    ];

    public const NILAI = [
        'BB' => 'Belum Berkembang',
        'MB' => 'Mulai Berkembang',
        'BSH' => 'Berkembang Sesuai Harapan',
        'BSB' => 'Berkembang Sangat Baik',
    ];

    /** Nama yang mudah dimengerti untuk setiap nilai (kode resmi di atas tetap yang disimpan). */
    public const NILAI_MUDAH = [
        'BB' => 'Perlu Bantuan',
        'MB' => 'Mulai Bisa',
        'BSH' => 'Sudah Bisa',
        'BSB' => 'Sangat Baik',
    ];

    /** Penjelasan singkat untuk guru & orang tua. */
    public const NILAI_PENJELASAN = [
        'BB' => 'Anak belum bisa melakukannya dan masih perlu banyak bantuan.',
        'MB' => 'Anak mulai bisa, tetapi masih perlu diingatkan atau dibantu.',
        'BSH' => 'Anak sudah bisa melakukannya sendiri sesuai usianya.',
        'BSB' => 'Anak sudah sangat mahir, bahkan bisa membantu temannya.',
    ];

    /** Nama aspek yang pendek & mudah dimengerti (untuk tabel). */
    public const ASPEK_MUDAH = [
        'agama_moral' => 'Agama & Moral',
        'fisik_motorik' => 'Gerak Tubuh',
        'kognitif' => 'Daya Pikir',
        'bahasa' => 'Bahasa',
        'sosial_emosional' => 'Sosial & Emosi',
        'seni' => 'Seni',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }
}
