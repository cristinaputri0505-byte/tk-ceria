# TK Ceria — Aplikasi Akademik TK (Laravel 11)

Website sekolah (halaman visitor) + sistem akademik dengan 3 jenis login:

| Peran | Bisa melakukan |
|---|---|
| **Admin** | Ringkasan sekolah, kelola pendaftaran murid baru, data siswa, kelas & wali kelas, akun guru/orang tua/admin, kegiatan (galeri), berita |
| **Guru** | Isi & ubah absensi harian kelasnya, isi laporan perkembangan 6 aspek PAUD (BB/MB/BSH/BSB) |
| **Orang Tua** | Lihat anak, rekap kehadiran bulanan, rapor perkembangan per periode |

Pengunjung bisa melihat profil, program, kegiatan, guru, berita, dan **mendaftar online**.

## Instalasi

Folder ini berisi file aplikasi yang ditimpakan ke proyek Laravel 11 baru.

```bash
# 1. Buat proyek Laravel baru
composer create-project laravel/laravel tk-ceria
cd tk-ceria

# 2. Salin isi folder ini ke dalam proyek (timpa file yang sama)
#    app/, bootstrap/app.php, database/, resources/views/, routes/web.php, public/images/

# 3. Atur .env
#    APP_NAME="TK Ceria"
#    APP_LOCALE=id
#    APP_TIMEZONE=Asia/Jakarta     (di Laravel 11 atur di config/app.php -> 'timezone')
#    DB_CONNECTION=mysql
#    DB_DATABASE=tk_ceria
#    DB_USERNAME=root
#    DB_PASSWORD=

# 4. Migrasi + data contoh, lalu tautkan folder upload
php artisan migrate:fresh --seed
php artisan storage:link

# 5. Jalankan
php artisan serve
```

Buka http://127.0.0.1:8000 — tombol **Login** ada di kanan atas.

## Akun demo (kata sandi: `password`)

| Peran | Email |
|---|---|
| Admin | admin@tkceria.sch.id |
| Guru (wali A1) | rina@tkceria.sch.id |
| Guru (wali B1) | sari@tkceria.sch.id |
| Orang tua | budi@contoh.com |
| Orang tua | dewi@contoh.com |

Ganti semua kata sandi sebelum dipakai sungguhan.

## Portal orang tua

Orang tua melihat: beranda berisi ringkasan setiap anak (foto, kehadiran bulan ini, status SPP, rapor), pengumuman, agenda, dan dokumentasi; profil lengkap anak (biodata, data ayah/ibu, kesehatan); kalender kehadiran; rapor; tagihan dengan unggah bukti transfer; galeri dokumentasi; profil akun.

- Admin: **Tagihan & SPP** (buat tagihan massal per bulan/kelas/siswa, verifikasi bukti), **Pengumuman** (semua kelas atau per kelas, bisa jadi agenda), biodata lengkap di **Siswa**, info rekening di Pengaturan Halaman → Info Pembayaran.
- Guru: **Dokumentasi Kelas** untuk membagikan foto ke orang tua (seluruh kelas atau khusus satu anak).
- Foto dokumentasi dan bukti transfer disimpan privat di `storage/app/private` dan hanya bisa dibuka oleh pihak yang berhak.

Admin juga mengelola seluruh portal: **Pengaturan Portal** (teks sapaan, gambar banner, judul bagian, menu yang aktif, teks login), **Absensi**, **Perkembangan**, dan **Dokumentasi** untuk semua kelas, serta tombol **Masuk sebagai** di Akun Pengguna untuk melihat panel persis seperti orang tua/guru (ada tombol Kembali ke admin).

Data contoh portal: `php artisan db:seed --class=PortalDemoSeeder`

## Mengelola website (Admin)

Setiap menu website punya halaman sendiri: Home, Tentang Kami, Program, Kegiatan, Galeri, Guru & Staff, Berita, Kontak, dan Pendaftaran.

| Yang diubah | Menu admin |
|---|---|
| Teks, banner, gambar latar tiap halaman, logo, kontak, peta, media sosial | Website → **Pengaturan Halaman** (satu tab per halaman) |
| Kartu keunggulan berikon | Website → **Keunggulan** |
| Kartu & penjelasan program | Website → **Program** |
| Kegiatan (dengan halaman detail) | Website → **Kegiatan** |
| Foto galeri (unggah banyak sekaligus, per album) | Website → **Galeri** |
| Profil guru & staff | Website → **Guru & Staff** |
| Berita & pengumuman | Website → **Berita** |
| Pesan dari formulir Kontak | Website → **Pesan Masuk** |

Kolom teks yang dikosongkan kembali ke tulisan bawaan.

## Foto halaman depan

Taruh foto Anda di `public/images/`:

- `hero.jpg` — foto anak-anak di banner utama (rasio lebar, ±1400×800)
- `gedung.jpg` — foto gedung sekolah di bagian Tentang Kami

Jika belum ada, halaman memakai ilustrasi pengganti. Foto kegiatan, berita, siswa, dan guru diunggah lewat panel Admin.

## Alur penggunaan

1. Admin membuat **akun guru**, lalu **kelas** dan memilih wali kelasnya.
2. Pendaftar online masuk ke menu **Pendaftaran**; admin mengubah status (Baru → Diproses → Diterima).
3. Untuk anak yang diterima: admin membuat **akun orang tua**, lalu menambah **siswa** dan menghubungkannya ke kelas dan akun orang tua.
4. Guru mengisi **absensi** harian dan **laporan perkembangan** tiap semester.
5. Orang tua masuk dan melihat kehadiran serta rapor anaknya.

## Struktur penting

```
routes/web.php                      semua rute (publik, admin, guru, orangtua)
app/Http/Middleware/RoleMiddleware  pembatas akses per peran  -> middleware 'role:admin'
bootstrap/app.php                   registrasi alias middleware 'role'
app/Http/Controllers/{Admin,Guru,OrangTua}
app/Models                          User, Kelas, Siswa, Absensi, Perkembangan, Program, Kegiatan, Berita, Pendaftaran
resources/views/layouts/public      layout website visitor
resources/views/layouts/dashboard   layout panel (sidebar menyesuaikan peran)
```

Tampilan memakai Tailwind CDN agar langsung jalan tanpa `npm`. Untuk produksi, pindahkan konfigurasi warna di `resources/views/partials/head.blade.php` ke `tailwind.config.js` dan build dengan Vite.
