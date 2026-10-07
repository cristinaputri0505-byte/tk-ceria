<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesan;

class PesanController extends Controller
{
    public function index()
    {
        return view('admin.pesan.index', ['pesan' => Pesan::latest()->paginate(20)]);
    }

    public function update(Pesan $pesan)
    {
        $pesan->update(['dibaca' => ! $pesan->dibaca]);
        return back();
    }

    public function destroy(Pesan $pesan)
    {
        $pesan->delete();
        return back()->with('success', 'Pesan dihapus.');
    }
}
