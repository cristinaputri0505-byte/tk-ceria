<?php

namespace App\Providers;

use App\Support\Pengaturan;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Driver penyimpanan Supabase Storage (lihat config/filesystems.php)
        Storage::extend('supabase', function ($app, array $config) {
            $adapter = new \App\Support\SupabaseStorageAdapter(
                (string) $config['url_project'], (string) $config['key'], (string) $config['bucket'], (bool) ($config['public'] ?? false)
            );
            return new FilesystemAdapter(new Filesystem($adapter, $config), $adapter, $config);
        });

        // Tanggal tampil dalam Bahasa Indonesia (Senin, 7 Oktober 2026)
        Carbon::setLocale(config('app.locale', 'id'));

        // Batas percobaan login: 5x/menit per akun (email + IP), dan 30x/menit per IP.
        // Dengan begitu banyak orang tua yang memakai Wi-Fi sekolah yang sama tidak saling terkunci.
        RateLimiter::for('login', fn (Request $r) => [
            Limit::perMinute(5)->by(mb_strtolower((string) $r->input('email')) . '|' . $r->ip()),
            Limit::perMinute(30)->by($r->ip()),
        ]);

        // Formulir publik (pendaftaran, kontak): 5 kiriman per 10 menit per IP, dihitung terpisah per formulir.
        // Pembatas tanpa nama (throttle:5,1) di Laravel memakai satu penghitung bersama untuk semua route.
        RateLimiter::for('formulir', fn (Request $r) => Limit::perMinutes(10, 5)->by('formulir|' . $r->route()?->getName() . '|' . $r->ip()));

        // $site tersedia di semua view: teks & gambar dari menu Pengaturan Website
        View::composer('*', fn ($view) => $view->with('site', Pengaturan::semua()));
    }
}
