<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\Guru;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrangTua;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PendaftaranController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman Visitor (publik)
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tentang-kami', [PageController::class, 'tentang'])->name('tentang');
Route::get('/program', [PageController::class, 'program'])->name('program');
Route::get('/kegiatan', [PageController::class, 'kegiatan'])->name('kegiatan.index');
Route::get('/kegiatan/{kegiatan}', [PageController::class, 'kegiatanShow'])->name('kegiatan.show');
Route::get('/galeri', [PageController::class, 'galeri'])->name('galeri');
Route::get('/guru-staff', [PageController::class, 'guru'])->name('guru');
Route::get('/kontak', [PageController::class, 'kontak'])->name('kontak');
Route::post('/kontak', [PageController::class, 'kontakKirim'])->middleware('throttle:formulir')->name('kontak.kirim');
Route::get('/berita', [HomeController::class, 'berita'])->name('berita.index');
Route::get('/berita/{berita}', [HomeController::class, 'beritaShow'])->name('berita.show');
Route::get('/pendaftaran', [PendaftaranController::class, 'create'])->name('pendaftaran.create');
Route::post('/pendaftaran', [PendaftaranController::class, 'store'])->middleware('throttle:formulir')->name('pendaftaran.store');

/*
|--------------------------------------------------------------------------
| Login (satu pintu untuk Admin, Guru, Orang Tua)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.attempt');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::post('/kembali-ke-admin', [AuthController::class, 'kembaliAdmin'])->middleware('auth')->name('kembali.admin');

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('siswa', Admin\SiswaController::class)->except('show')->parameters(['siswa' => 'siswa']);
    Route::resource('kelas', Admin\KelasController::class)->except('show')->parameters(['kelas' => 'kelas']);
    Route::resource('pengguna', Admin\PenggunaController::class)->except('show')->parameters(['pengguna' => 'pengguna']);
    Route::resource('kegiatan', Admin\KegiatanController::class)->except('show')->parameters(['kegiatan' => 'kegiatan']);
    Route::resource('berita', Admin\BeritaController::class)->except('show')->parameters(['berita' => 'berita']);
    Route::get('pendaftaran', [Admin\PendaftaranController::class, 'index'])->name('pendaftaran.index');
    Route::patch('pendaftaran/{pendaftaran}', [Admin\PendaftaranController::class, 'update'])->name('pendaftaran.update');
    Route::delete('pendaftaran/{pendaftaran}', [Admin\PendaftaranController::class, 'destroy'])->name('pendaftaran.destroy');
    Route::resource('program', Admin\ProgramController::class)->except('show');
    Route::resource('keunggulan', Admin\KeunggulanController::class)->except('show');
    Route::resource('galeri', Admin\GaleriController::class)->except('show');
    Route::resource('staff', Admin\StaffController::class)->except('show');
    // Data portal orang tua: admin mengelola semua kelas (controller dipakai bersama dengan guru)
    Route::get('absensi', [Guru\AbsensiController::class, 'index'])->name('absensi.index');
    Route::post('absensi', [Guru\AbsensiController::class, 'store'])->name('absensi.store');
    Route::get('perkembangan', [Guru\PerkembanganController::class, 'index'])->name('perkembangan.index');
    Route::get('perkembangan/{siswa}', [Guru\PerkembanganController::class, 'edit'])->name('perkembangan.edit');
    Route::post('perkembangan/{siswa}', [Guru\PerkembanganController::class, 'store'])->name('perkembangan.store');
    Route::get('dokumentasi', [Guru\DokumentasiController::class, 'index'])->name('dokumentasi.index');
    Route::post('dokumentasi', [Guru\DokumentasiController::class, 'store'])->name('dokumentasi.store');
    Route::delete('dokumentasi/{dokumentasi}', [Guru\DokumentasiController::class, 'destroy'])->name('dokumentasi.destroy');
    Route::post('pengguna/{pengguna}/masuk', [Admin\PenggunaController::class, 'masukSebagai'])->name('pengguna.masuk');
    Route::resource('tagihan', Admin\TagihanController::class)->only(['index', 'create', 'store', 'update', 'destroy']);
    Route::resource('pengumuman', Admin\PengumumanController::class)->except('show')->parameters(['pengumuman' => 'pengumuman']);
    Route::get('pesan', [Admin\PesanController::class, 'index'])->name('pesan.index');
    Route::patch('pesan/{pesan}', [Admin\PesanController::class, 'update'])->name('pesan.update');
    Route::delete('pesan/{pesan}', [Admin\PesanController::class, 'destroy'])->name('pesan.destroy');
    Route::get('pengaturan', [Admin\PengaturanController::class, 'index'])->name('pengaturan.index');
    Route::post('pengaturan/{tab}', [Admin\PengaturanController::class, 'update'])->name('pengaturan.update');
});

/*
|--------------------------------------------------------------------------
| Guru
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/', [Guru\DashboardController::class, 'index'])->name('dashboard');
    Route::get('absensi', [Guru\AbsensiController::class, 'index'])->name('absensi.index');
    Route::post('absensi', [Guru\AbsensiController::class, 'store'])->name('absensi.store');
    Route::get('perkembangan', [Guru\PerkembanganController::class, 'index'])->name('perkembangan.index');
    Route::get('perkembangan/{siswa}', [Guru\PerkembanganController::class, 'edit'])->name('perkembangan.edit');
    Route::post('perkembangan/{siswa}', [Guru\PerkembanganController::class, 'store'])->name('perkembangan.store');
    Route::get('dokumentasi', [Guru\DokumentasiController::class, 'index'])->name('dokumentasi.index');
    Route::post('dokumentasi', [Guru\DokumentasiController::class, 'store'])->name('dokumentasi.store');
    Route::delete('dokumentasi/{dokumentasi}', [Guru\DokumentasiController::class, 'destroy'])->name('dokumentasi.destroy');
});

/*
|--------------------------------------------------------------------------
| Orang Tua
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:orangtua'])->prefix('orangtua')->name('orangtua.')->group(function () {
    Route::get('/', [OrangTua\DashboardController::class, 'index'])->name('dashboard');
    Route::get('anak/{siswa}', [OrangTua\DashboardController::class, 'anak'])->name('anak');
    Route::get('pembayaran', [OrangTua\DashboardController::class, 'pembayaran'])->name('pembayaran');
    Route::post('pembayaran/{tagihan}', [OrangTua\DashboardController::class, 'bayar'])->name('bayar');
    Route::get('pengumuman', [OrangTua\DashboardController::class, 'pengumuman'])->name('pengumuman');
    Route::get('dokumentasi', [OrangTua\DashboardController::class, 'dokumentasi'])->name('dokumentasi');
    Route::get('profil', [OrangTua\DashboardController::class, 'profil'])->name('profil');
    Route::put('profil', [OrangTua\DashboardController::class, 'profilUpdate'])->name('profil.update');
});

/*
|--------------------------------------------------------------------------
| File privat (dicek hak aksesnya di FileController)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('berkas/dokumentasi/{dokumentasi}', [FileController::class, 'dokumentasi'])->name('dokumentasi.foto');
    Route::get('berkas/foto-siswa/{siswa}', [FileController::class, 'fotoSiswa'])->name('siswa.foto');
    Route::get('berkas/bukti/{tagihan}', [FileController::class, 'bukti'])->name('tagihan.bukti');
    Route::get('kuitansi/{tagihan}', [FileController::class, 'kuitansi'])->name('tagihan.kuitansi');
});
