<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staff';
    protected $fillable = ['nama', 'jabatan', 'pendidikan', 'foto', 'urutan'];

    public function getInisialAttribute(): string
    {
        $kata = array_values(array_diff(explode(' ', $this->nama), ['Bu', 'Ibu', 'Pak', 'Bapak']));
        return mb_strtoupper(mb_substr($kata[0] ?? $this->nama, 0, 1));
    }
}
