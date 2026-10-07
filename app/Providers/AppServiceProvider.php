<?php

namespace App\Providers;

use App\Support\Pengaturan;
use Carbon\Carbon;
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

        // $site tersedia di semua view: teks & gambar dari menu Pengaturan Website
        View::composer('*', fn ($view) => $view->with('site', Pengaturan::semua()));
    }
}
