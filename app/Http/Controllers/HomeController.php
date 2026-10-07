<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use App\Models\Kegiatan;
use App\Models\Program;
use App\Models\Keunggulan;
use App\Models\Staff;

class HomeController extends Controller
{
    public function index()
    {
        return view('home', [
            'programs' => Program::orderBy('urutan')->get(),
            'kegiatan' => Kegiatan::latest('tanggal')->take(5)->get(),
            'guru' => Staff::orderBy('urutan')->take(8)->get(),
            'keunggulan' => Keunggulan::orderBy('urutan')->take(6)->get(),
            'berita' => Berita::terbit()->latest('published_at')->take(3)->get(),
        ]);
    }

    public function berita()
    {
        return view('berita.index', [
            'berita' => Berita::terbit()->latest('published_at')->paginate(9),
        ]);
    }

    public function beritaShow(Berita $berita)
    {
        abort_unless($berita->published_at && $berita->published_at->isPast(), 404);

        return view('berita.show', [
            'berita' => $berita,
            'lainnya' => Berita::terbit()->whereKeyNot($berita->id)->latest('published_at')->take(3)->get(),
        ]);
    }
}
