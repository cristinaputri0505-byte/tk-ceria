<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ADMIN = 'admin';
    public const GURU = 'guru';
    public const ORANG_TUA = 'orangtua';

    protected $fillable = ['name', 'email', 'password', 'role', 'telepon', 'jabatan', 'foto'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function anak()
    {
        return $this->hasMany(Siswa::class, 'orang_tua_id');
    }

    public function kelasDiampu()
    {
        return $this->hasMany(Kelas::class, 'wali_guru_id');
    }

    public function dashboardRoute(): string
    {
        return match ($this->role) {
            self::ADMIN => 'admin.dashboard',
            self::GURU => 'guru.dashboard',
            default => 'orangtua.dashboard',
        };
    }

    public function getInisialAttribute(): string
    {
        $kata = array_values(array_diff(explode(' ', $this->name), ['Bu', 'Ibu', 'Pak', 'Bapak']));
        return mb_strtoupper(mb_substr($kata[0] ?? $this->name, 0, 1));
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ADMIN => 'Administrator',
            self::GURU => 'Guru',
            default => 'Orang Tua',
        };
    }
}
