<?php

namespace App\Http\Controllers;

use App\Models\Galeri;
use App\Models\Kegiatan;
use App\Models\Keunggulan;
use App\Models\Pesan;
use App\Models\Program;
use App\Models\Staff;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function tentang()
    {
        return view('pages.tentang', [
            'keunggulan' => Keunggulan::orderBy('urutan')->get(),
            'jumlahGuru' => Staff::count(),
            'jumlahProgram' => Program::count(),
        ]);
    }

    public function program()
    {
        return view('pages.program', ['programs' => Program::orderBy('urutan')->get()]);
    }

    public function kegiatan()
    {
        return view('pages.kegiatan', ['kegiatan' => Kegiatan::latest('tanggal')->paginate(12)]);
    }

    public function kegiatanShow(Kegiatan $kegiatan)
    {
        return view('pages.kegiatan-show', [
            'kegiatan' => $kegiatan,
            'lainnya' => Kegiatan::whereKeyNot($kegiatan->id)->latest('tanggal')->take(4)->get(),
        ]);
    }

    public function galeri(Request $request)
    {
        $album = $request->query('album');

        return view('pages.galeri', [
            'albums' => Galeri::whereNotNull('album')->where('album', '!=', '')->select('album')->distinct()->orderBy('album')->pluck('album'),
            'album' => $album,
            'foto' => Galeri::when($album, fn ($q) => $q->where('album', $album))->orderBy('urutan')->latest('id')->paginate(24)->withQueryString(),
        ]);
    }

    public function guru()
    {
        return view('pages.guru', ['staff' => Staff::orderBy('urutan')->orderBy('nama')->get()]);
    }

    public function privasi()
    {
        return view('pages.privasi');
    }

    public function kontak()
    {
        return view('pages.kontak');
    }

    public function kontakKirim(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'telepon' => 'nullable|string|max:20|required_without:email',
            'email' => 'nullable|email|max:100',
            'pesan' => 'required|string|max:2000',
        ], ['telepon.required_without' => 'Isi nomor WhatsApp atau email agar kami bisa membalas.']);

        Pesan::create($data);

        return redirect()->route('kontak')->with('success', 'Pesan terkirim. Kami akan membalas secepatnya.');
    }
}
