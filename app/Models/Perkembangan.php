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

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }
}
