<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermintaanReset extends Model
{
    protected $table = 'permintaan_reset';
    protected $fillable = ['user_id', 'telepon_kontak', 'catatan', 'status', 'diproses_oleh', 'diproses_pada', 'ip'];
    protected $casts = ['diproses_pada' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prosesor()
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }
}
