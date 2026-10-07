<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ChatPesan extends Model
{
    protected $table = 'chat_pesan';
    protected $fillable = ['jenis', 'orang_tua_id', 'pengirim_id', 'isi', 'dibaca'];
    protected $casts = ['dibaca' => 'boolean'];

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }

    /** Jumlah pesan pribadi yang belum dibaca oleh $u. */
    public static function belumPribadi(User $u): int
    {
        $q = static::where('jenis', 'pribadi')->where('dibaca', false);

        return $u->role === User::ORANG_TUA
            ? $q->where('orang_tua_id', $u->id)->where('pengirim_id', '!=', $u->id)->count()
            : $q->whereColumn('pengirim_id', 'orang_tua_id')->count();
    }

    /** Jumlah pesan grup yang belum dibaca oleh $u. */
    public static function belumGrup(User $u): int
    {
        $baca = (int) DB::table('chat_grup_baca')->where('user_id', $u->id)->value('terakhir_id');

        return static::where('jenis', 'grup')->where('id', '>', $baca)->where('pengirim_id', '!=', $u->id)->count();
    }
}
