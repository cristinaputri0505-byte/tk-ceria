<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class BuatAdmin extends Command
{
    protected $signature = 'tk:buat-admin';
    protected $description = 'Membuat akun admin baru (dipakai saat pertama kali online, tanpa data contoh)';

    public function handle(): int
    {
        $this->info('Membuat akun admin TK Ceria');
        $nama = $this->ask('Nama lengkap');
        $email = $this->ask('Email (dipakai untuk login)');
        $pw = $this->secret('Kata sandi (minimal 10 karakter, campuran huruf besar, kecil & angka)');
        $pw2 = $this->secret('Ulangi kata sandi');

        $v = Validator::make(
            ['name' => $nama, 'email' => $email, 'password' => $pw, 'password_confirmation' => $pw2],
            ['name' => 'required|string|max:100', 'email' => 'required|email|max:100|unique:users,email',
             'password' => ['required', 'confirmed', Password::min(10)->mixedCase()->numbers()]],
            [], ['name' => 'nama', 'password' => 'kata sandi']
        );
        if ($v->fails()) {
            foreach ($v->errors()->all() as $e) $this->error($e);
            return self::FAILURE;
        }

        User::create(['name' => $nama, 'email' => $email, 'password' => $pw, 'role' => User::ADMIN]);
        $this->info("Akun admin {$email} berhasil dibuat. Silakan login.");
        return self::SUCCESS;
    }
}
