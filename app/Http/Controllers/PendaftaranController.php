<?php

namespace App\Http\Controllers;

use App\Models\Pendaftaran;
use App\Models\Program;
use Illuminate\Http\Request;

class PendaftaranController extends Controller
{
    public function create()
    {
        return view('pendaftaran.create', ['programs' => Program::orderBy('urutan')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_anak' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date|before:today',
            'jenis_kelamin' => 'required|in:L,P',
            'program' => 'required|string|max:100',
            'nama_orang_tua' => 'required|string|max:100',
            'telepon' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'alamat' => 'required|string|max:255',
            'setuju_privasi' => 'accepted',
            'izin_foto_publik' => 'nullable|boolean',
        ], ['setuju_privasi.accepted' => 'Pendaftaran memerlukan persetujuan Kebijakan Privasi.']);

        $data['setuju_privasi_pada'] = now();
        $data['izin_foto_publik'] = $request->boolean('izin_foto_publik');
        unset($data['setuju_privasi']);

        Pendaftaran::create($data + ['status' => 'baru']);

        return redirect()->route('pendaftaran.create')
            ->with('success', 'Pendaftaran terkirim. Tim kami akan menghubungi Anda melalui telepon dalam 2–3 hari kerja.');
    }
}
