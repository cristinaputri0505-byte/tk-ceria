<?php

namespace App\Http\Controllers\Admin;

use App\Models\Staff;

class StaffController extends KontenController
{
    protected string $model = Staff::class;
    protected string $route = 'admin.staff';
    protected string $judul = 'Guru & Staff';
    protected string $satuan = 'guru/staff';
    protected string $keterangan = 'Profil yang tampil di halaman Guru & Staff dan di Home. Ini terpisah dari akun login guru.';
    protected string $folder = 'staff';

    protected function fields(): array
    {
        return [
            'nama' => ['Nama lengkap', 'text', 'required|string|max:100', ['judul' => true]],
            'jabatan' => ['Jabatan', 'text', 'nullable|string|max:100', ['sub' => true, 'hint' => 'Contoh: Wali Kelas A1, Kepala Sekolah, Staf TU']],
            'pendidikan' => ['Pendidikan', 'text', 'nullable|string|max:150', ['hint' => 'Contoh: S1 PG-PAUD Universitas Negeri Jakarta']],
            'urutan' => ['Nomor urut', 'number', 'required|integer|min:0|max:999'],
            'foto' => ['Foto', 'image', '', ['wide' => true, 'hint' => 'Foto potret, disarankan persegi.']],
        ];
    }
}
