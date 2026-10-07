<?php

namespace App\Providers;

use App\Support\Pengaturan;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
