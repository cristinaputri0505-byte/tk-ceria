<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pendaftaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PendaftaranController extends Controller
{
    public function index(Request $request)
    {
        $pendaftaran = Pendaftaran::when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.pendaftaran.index', ['pendaftaran' => $pendaftaran]);
    }

    public function update(Request $request, Pendaftaran $pendaftaran)
    {
        $pendaftaran->update($request->validate([
            'status' => ['required', Rule::in(array_keys(Pendaftaran::STATUS))],
            'catatan' => 'nullable|string|max:255',
        ]));

        return back()->with('success', "Status pendaftaran {$pendaftaran->nama_anak} diubah menjadi " . Pendaftaran::STATUS[$pendaftaran->status] . '.');
    }

    public function destroy(Pendaftaran $pendaftaran)
    {
        $pendaftaran->delete();
        return back()->with('success', 'Data pendaftaran dihapus.');
    }
}
