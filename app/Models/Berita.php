<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Berita extends Model
{
    protected $table = 'berita';
    protected $fillable = ['judul', 'slug', 'ringkasan', 'isi', 'gambar', 'published_at'];
    protected $casts = ['published_at' => 'datetime'];

    protected static function booted(): void
    {
        static::saving(function (Berita $b) {
            if (! $b->slug || $b->isDirty('judul')) {
                $b->slug = Str::slug($b->judul) . '-' . Str::lower(Str::random(4));
            }
        });
    }

    public function scopeTerbit($q)
    {
        return $q->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
