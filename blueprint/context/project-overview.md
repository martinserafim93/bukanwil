# BUKANWIL - Project Overview

<!-- blueprint:source-hash e41bb89cc2c0ebc4872985a07c9eeb232905efd16b7b3e423f3474c864966523 -->

> Buku tamu digital dan panel administrasi Pelayanan Terpadu Satu Pintu (PTSP) Kantor Wilayah Kementerian Agama Provinsi Kalimantan Utara.

## Problem

Pencatatan buku tamu konvensional (buku manual kertas atau Google Form generik) pada PTSP Kanwil Kemenag Kaltara rentan terhadap ketidakteraturan data, kendala privasi antar-pengunjung, dan lambatnya pelaporan rekapitulasi. BUKANWIL menyediakan solusi digital terintegrasi yang memungkinkan tamu mengisi data secara mandiri dan cepat, sekaligus memberi petugas antarmuka analitik, pencarian, dan pelaporan yang terstruktur.

## Users

- **Pengunjung PTSP**: Tamu masyarakat umum (perorangan), perwakilan instansi pemerintah, dan pengurus lembaga keagamaan/swasta. Mengisi formulir kedatangan mandiri di meja pelayanan tanpa perlu akun login.
- **Petugas PTSP (Admin)**: Front-office dan staf sekretariat penerima tamu yang memantau kehadiran, memeriksa keperluan, meninjau riwayat kunjungan, dan mengekspor rekap laporan.
- **Super Administrator**: Administrator sistem dengan wewenang penuh mengelola akun petugas CMS, hak akses, dan pengaturan identitas instansi.

## Usage model

- **Skala & Beban**: Operasional kantor wilayah tingkat provinsi dengan volume puluhan hingga ratusan tamu per hari.
- **Keamanan Batas Akses**: Formulir publik terbuka untuk umum dengan pengamanan honeypot anti-bot, rate limiting (3 submit / 10 menit), proteksi CSRF, dan validasi MIME berkas ketat. Area administrasi dibatasi autentikasi sesi dengan proteksi brute-force login.
- **Kepatuhan Waktu**: Seluruh pencatatan riwayat dan laporan statistik terikat zona waktu WITA (UTC+8).

## Features

1. **Formulir Tamu Publik Mandiri** - Pintu masuk utama bagi tamu untuk mencatat kehadiran lengkap dengan foto opsional.
2. **Autentikasi & Sesi Petugas CMS** - Pengamanan akses panel admin berbasis role (`superadmin` dan `admin`) dengan pembatasan percobaan login.
3. **Dashboard Statistik & Analitik Tren** - Halaman ringkasan metrik kehadiran harian, akumulasi total, dan grafik tren 7 hari (Chart.js).
4. **Manajemen & Penelusuran Riwayat Tamu** - Tabel data kunjungan dengan pencarian multifield, filter tanggal/kategori, penomoran halaman, dan status baca.
5. **Ekspor Laporan Tamu CSV** - Unduhan rekap data tamu dalam format CSV ber-BOM UTF-8 siap buka di Microsoft Excel.
6. **Manajemen Akun Petugas Admin (CRUD)** - Antarmuka kelola pengguna petugas admin khusus Superadmin.
7. **Konfigurasi Identitas Aplikasi & PTSP** - Panel konfigurasi dinamis nama instansi, judul aplikasi, dan teks sambutan.
8. **Pengerasan Keamanan & Kompatibilitas Produksi** - Mitigasi cache browser, validasi ketat, dan kompatibilitas PHP 8.2+/8.3 di lingkungan server Linux.
9. **Manajemen Arsip dan Pembersihan Data Tamu** - Pengelolaan siklus hidup tamu (soft delete/tong sampah, pemulihan, hapus permanen, dan arsip riwayat tahunan).
10. **Penyempurnaan & Pemeliharaan Berkala** - Optimasi performa kueri data tamu, pemantauan log aplikasi, serta penyesuaian kebutuhan operasional PTSP Kanwil Kemenag Kaltara.
11. **Redesign UI/UX Halaman Publik, Sukses, dan Login Admin dengan Tasteskill v2 (Fase 1)** - Perbaikan token Tailwind CSS v4 (`brand`, `brand-gold`, `.btn-danger`, `.choice-card`), komposisi editorial 2 kolom pada halaman buku tamu publik dengan jam WITA real-time dan kartu pilihan ramah sentuh, kartu bukti kunjungan pada halaman sukses, serta layout mandiri untuk login admin.
12. **Redesign UI/UX Shell dan Halaman Kerja Panel Admin dengan Tasteskill v2 (Fase 2)** - Penerapan kaidah App Screens Tasteskill v2 pada sidebar/shell admin (navigasi mobile, logo transparan, badge antrean tamu belum dibaca), penyegaran hierarki KPI dan grafik dashboard, toolbar filter ringkas dan pagination Tailwind pada daftar tamu, serta penyeragaman halaman detail, CRUD admin, dan pengaturan.

## Data model

### `tamu`

Menyimpan setiap entri kunjungan tamu dari formulir publik.

- `id` (BIGINT UNSIGNED, Primary Key, AUTO_INCREMENT) - Pengidentifikasi unik entri tamu
- `nama` (VARCHAR(255), NOT NULL) - Nama lengkap pengunjung
- `jenis_kelamin` (ENUM('Laki-laki', 'Perempuan'), NOT NULL) - Jenis kelamin
- `asal_pengunjung` (ENUM('perorangan', 'instansi', 'lembaga'), NOT NULL) - Kategori asal
- `nama_instansi` (VARCHAR(255), NULL) - Wajib diisi jika asal `instansi` atau `lembaga`
- `alamat` (TEXT, NOT NULL) - Alamat tempat tinggal atau instansi tamu
- `no_hp` (VARCHAR(20), NOT NULL) - Nomor kontak / WhatsApp
- `keperluan` (TEXT, NOT NULL) - Maksud atau tujuan kedatangan
- `foto` (VARCHAR(500), NULL) - Path berkas foto tersimpan (relatif terhadap `public/uploads/foto/`)
- `is_read` (TINYINT(1) UNSIGNED, NOT NULL, DEFAULT 0) - Status review petugas (0 = belum dibaca, 1 = sudah dibaca)
- `is_archived` (TINYINT(1) UNSIGNED, NOT NULL, DEFAULT 0) - Status arsip (0 = aktif, 1 = diarsipkan)
- `created_at` (DATETIME, NOT NULL, DEFAULT CURRENT_TIMESTAMP) - Timestamp kunjungan dalam waktu WITA
- `deleted_at` (DATETIME, NULL, DEFAULT NULL) - Timestamp soft delete ke Tong Sampah (NULL = aktif/arsip sah)
- Indeks: `idx_tamu_created_at`, `idx_tamu_is_read`, `idx_tamu_jenis_kelamin`, `idx_tamu_asal`, `idx_tamu_archive`, `idx_tamu_deleted`

### `admin`

Menyimpan data kredensial dan hak akses petugas pengelola CMS.

- `id` (INT UNSIGNED, Primary Key, AUTO_INCREMENT) - Pengidentifikasi unik admin
- `nama` (VARCHAR(255), NOT NULL) - Nama lengkap petugas
- `username` (VARCHAR(100), NOT NULL, UNIQUE) - Username unik untuk login
- `password` (VARCHAR(255), NOT NULL) - Hash kata sandi menggunakan Bcrypt (cost 12)
- `role` (ENUM('superadmin', 'admin'), NOT NULL, DEFAULT 'admin') - Tingkat hak akses
- `created_at` (DATETIME, NOT NULL, DEFAULT CURRENT_TIMESTAMP)
- `updated_at` (DATETIME, NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)

### `pengaturan`

Penyimpanan key-value untuk konfigurasi teks dan identitas instansi.

- `id` (INT UNSIGNED, Primary Key, AUTO_INCREMENT)
- `key` (VARCHAR(100), NOT NULL, UNIQUE) - Kunci konfigurasi (`judul_aplikasi`, `deskripsi_aplikasi`, `nama_instansi_ptsp`, `teks_header`)
- `value` (TEXT, NOT NULL) - Nilai teks konfigurasi

## Tech stack

- **CodeIgniter 4.7**: Framework MVC backend PHP (^8.2 / 8.3) untuk routing, controller, model query builder, dan session management.
- **Tailwind CSS v4**: Utility-first CSS framework via `@tailwindcss/cli`, dipusatkan pada `themes/app.css` dan dikompilasi ke `public/css/app.css`.
- **MySQL 8.0 / MariaDB**: Penyimpanan data relasional (`utf8mb4_unicode_ci`), skema autoritatif di `database/schema.sql`.
- **Chart.js**: Visualisasi data grafik garis dan tren kunjungan mingguan pada dashboard admin.
- **PHPUnit 10.5**: Pengujian unit dan integrasi berbasis `composer test` dan `phpunit.dist.xml`.

## Monetization

Tidak berlaku (Non-komersial). Aplikasi layanan publik pemerintahan Kanwil Kementerian Agama Provinsi Kalimantan Utara.

## UI/UX

Identitas visual mengusung warna hijau resmi Kementerian Agama (`#006B2E`) dan aksen emas (`#E5A400`) dengan sistem desain flat Tailwind CSS v4 serta kaidah Tasteskill v2 (permukaan bertingkat bebas gradasi, tombol pil, komposisi editorial 2 kolom pada layar lebar dengan penunjuk waktu WITA real-time, kartu pilihan ramah sentuh, halaman login admin mandiri, serta panel admin berbasis kaidah App Screens).

- `/` - Formulir publik buku tamu mandiri (komposisi editorial 2 kolom: panel identitas PTSP & waktu WITA di kiri, lembar formulir interaktif di kanan)
- `/tamu/submit` - Endpoint POST penerimaan tamu (CSRF, honeypot, rate limiting)
- `/tamu/sukses` - Halaman konfirmasi berbentuk kartu bukti pencatatan kunjungan PTSP
- `/admin/login` - Antarmuka login petugas admin mandiri (split/focused panel terpisah dari banner buku tamu publik)
- `/admin/logout` - Endpoint logout dengan modal konfirmasi dialog kustom
- `/admin` - Dashboard statistik dengan KPI bertipografi tabular tegas, grafik tren kunjungan, serta bar proporsional keperluan dan asal pengunjung
- `/admin/tamu` - Antarmuka manajemen tamu dengan bilah tab (Aktif, Arsip, Tong Sampah), toolbar pencarian/filter ringkas (*above the fold*), pagination ber-style Tailwind, dan status baca
- `/admin/tamu/(:num)` - Halaman rincian riwayat lengkap kunjungan tamu beserta penampil foto
- `/admin/tamu/export` - Endpoint unduhan berkas CSV ber-BOM UTF-8
- `/admin/admin-user` - Halaman manajemen akun petugas admin (CRUD khusus Superadmin)
- `/admin/pengaturan` - Halaman konfigurasi identitas instansi dan salam aplikasi

## Deployment

- **Lingkungan Produksi**: Server web Linux Apache/Nginx (hosting cPanel / VPS) pada domain `https://bukanwil.is-great.org`.
- **Konfigurasi Lingkungan**: Berkas `.env` dengan `CI_ENVIRONMENT = production`, konfigurasi database MySQL `database.default.*`, dan pengaturan zona waktu `+08:00` (WITA).
- **Izin Direktori**: Direktori `public/uploads/foto/` dan `writable/` wajib dapat ditulis (*writeable*) oleh pengguna server web.
