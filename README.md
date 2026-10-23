# BUKANWIL

Buku tamu digital dan CMS admin untuk PTSP Kantor Wilayah Kementerian Agama Provinsi Kalimantan Utara.

Situs: https://bukanwil.is-great.org

## Apa itu BUKANWIL

BUKANWIL (Buku Tamu Kanwil Kemenag Kaltara) menggantikan Google Form yang sebelumnya dipakai untuk mencatat pengunjung PTSP. Aplikasi ini punya dua antarmuka. Form publik yang bisa diisi siapa saja tanpa akun, dan panel admin tempat petugas membaca, menyaring, serta mengekspor data tamu.

## Fitur utama

### Form buku tamu (publik)

- Isian nama, jenis kelamin, asal (perorangan, instansi, lembaga), instansi, alamat, nomor HP, dan keperluan.
- Foto bersifat opsional.
- Tidak butuh akun atau login.

### Panel admin

- Login petugas dengan dua role: superadmin dan admin.
- Dashboard statistik: total tamu, tamu hari ini, tamu belum dibaca, rekap per jenis kelamin dan per asal, lima keperluan teratas, dan grafik tren 7 hari (Chart.js).
- Daftar tamu dengan pagination, pencarian (nama, nomor HP, instansi), filter (tanggal, jenis kelamin, asal, keperluan), dan pengurutan.
- Halaman detail tamu.
- Export CSV dengan BOM UTF-8, langsung terbaca benar di Excel.
- Notifikasi in-app berupa badge untuk tamu baru yang belum dibaca.
- Pengelolaan akun admin dan pengaturan aplikasi.

### Keamanan

- Proteksi CSRF (mode cookie di `app/Config/Security.php`).
- Honeypot dan rate limit form: 3 pengiriman per 10 menit.
- Rate limit login: 5 kali gagal per 15 menit.
- Validasi upload: MIME dicek dengan finfo (jpeg, png, webp), ekstensi whitelist, nama file diganti acak, ukuran maksimal 1 MB.
- Query memakai CI4 Query Builder (prepared statements).
- Password disimpan dengan bcrypt cost 12.
- Session hardening.

## Tech stack

- CodeIgniter 4.7.5 dengan PHP 8.3
- MySQL 8.0
- Tailwind CSS v4, hasil build di `public/css/app.css` dan ikut di-commit
- JavaScript native
- Chart.js, versi di-pin dan dimuat lewat CDN
- Composer dan npm

## Prasyarat

- PHP 8.2 atau lebih baru dengan ekstensi intl, mbstring, mysqli, fileinfo, dan json
- MySQL 8.0
- Composer
- Node.js dan npm, hanya bila ingin membangun ulang CSS

## Instalasi dan run lokal

1. Clone repo dan pasang dependensi.

```bash
git clone https://github.com/martinserafim93/bukanwil.git
cd bukanwil
composer install
```

2. Buat database lalu impor skema. Nama database bebas, contoh di bawah memakai `bukanwil`.

```bash
mysql -u root -p -e "CREATE DATABASE bukanwil CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysql -u root -p bukanwil < database/schema.sql
```

Skema menyertakan akun superadmin awal. Segera ganti password lewat menu Kelola Admin setelah login pertama.

3. Sesuaikan konfigurasi. Proyek ini tidak memakai file `.env`, semua nilai ditulis langsung di berkas config.

- `app/Config/Database.php`: hostname, username, password, dan nama database.
- `app/Config/App.php`: baseURL, misalnya `http://localhost:8080/`.

4. Jalankan server pengembangan.

```bash
php spark serve
```

Buka http://localhost:8080 di browser.

Environment bawaan adalah `production`. Untuk mode development, set variabel environment saat menjalankan server:

```bash
CI_ENVIRONMENT=development php spark serve
```

Bila ingin mengubah tampilan, bangun ulang CSS Tailwind lalu commit hasilnya:

```bash
npm run build:css
```

## Deploy

Aplikasi berjalan di InfinityFree (free tier) pada https://bukanwil.is-great.org. Batasan hosting ini membentuk beberapa keputusan desain: tanpa dotenv, Redis, dan queue, seluruh konfigurasi ada di `app/Config/*.php`.

Langkah umum:

1. Jalankan `composer install` di lokal agar folder `vendor/` tersedia.
2. Ubah baseURL di `app/Config/App.php` ke domain produksi, dan isi kredensial database hosting di `app/Config/Database.php`.
3. Impor `database/schema.sql` ke MySQL hosting lewat phpMyAdmin.
4. Unggah seluruh proyek lewat FTP, atau unggah satu file ZIP lalu ekstrak di server.
5. Arahkan domain ke folder `public/` lewat `.htaccess`. Entry point aplikasi adalah `public/index.php`.

Jangan sampai file `.env` atau kredensial produksi masuk ke repo.

## Struktur direktori

```text
app/               kode MVC CodeIgniter 4 (controller, model, view)
app/Config/        konfigurasi (App.php, Database.php, Security.php, dst.)
public/            entry point (index.php), aset, dan CSS hasil build
database/schema.sql  skema MySQL
writable/          log dan cache
```