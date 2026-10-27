# Project Plan

## 1. Problem - What problem are we solving?

Menggantikan pencatatan buku tamu manual (kertas/Google Form) pada Pelayanan Terpadu Satu Pintu (PTSP) Kantor Wilayah Kementerian Agama Provinsi Kalimantan Utara dengan sistem buku tamu digital mandiri yang cepat, terstruktur, aman, dan mudah direkapitulasi secara otomatis.

## 2. Users - Who is this for?

- **Pengunjung PTSP**: Tamu masyarakat umum, perorangan, perwakilan instansi pemerintah, dan pengurus lembaga keagamaan/swasta yang datang ke Kanwil Kemenag Kaltara. Mereka mengisi data kunjungan secara mandiri tanpa memerlukan registrasi atau login.
- **Petugas PTSP & Admin**: Petugas front-office dan staf sekretariat yang memantau kehadiran tamu, memeriksa keperluan kunjungan, serta mencetak atau mengekspor rekap kunjungan.
- **Super Administrator**: Administrator sistem yang mengelola akun petugas admin, hak akses, dan pengaturan identitas instansi.

## 3. Features - What does the MVP need?

- **Formulir Buku Tamu Mandiri (Publik)**: Pengisian data nama, jenis kelamin, asal pengunjung (perorangan/instansi/lembaga), instansi terkait, alamat, nomor HP, keperluan, dan unggahan foto tamu opsional.
- **Keamanan & Validasi Form**: Proteksi bot dengan honeypot tersembunyi, pembatasan laju pengisian (*rate limit* 3 submit / 10 menit), validasi MIME finfo berkas gambar (JPEG, PNG, WebP maksimal 1 MB), pengacakan nama berkas, dan proteksi CSRF.
- **Halaman Konfirmasi Sukses**: Umpan balik visual ramah bagi pengunjung setelah data berhasil tersimpan.
- **Autentikasi Petugas CMS**: Login admin dengan pembatasan laju gagal login (maksimal 5 kali / 15 menit), password hash Bcrypt cost 12, pemisahan hak akses (`superadmin` dan `admin`), serta modal dialog konfirmasi kustom saat logout.
- **Dashboard Statistik & Analitik**: Kartu metrik total tamu, kunjungan hari ini, tamu belum dibaca, grafik tren kunjungan 7 hari terakhir (Chart.js), komposisi pengunjung berdasarkan jenis kelamin dan asal pengunjung, serta 5 keperluan tamu terbanyak.
- **Manajemen & Penelusuran Data Tamu**: Tabel tamu terpadu dengan penomoran halaman (*pagination*), pencarian cepat (nama, no HP, instansi), penyaringan (*filter* tanggal, jenis kelamin, asal, keperluan), dan pengurutan data.
- **Detail Kunjungan & Status Baca**: Halaman riwayat lengkap tiap tamu termasuk pratinjau foto, indikator badge belum ditinjau, serta aksi tandai sudah dibaca (per tamu atau massal).
- **Ekspor Laporan CSV**: Fitur unduh berkas CSV dengan UTF-8 BOM agar langsung terbaca rapi dengan format karakter yang benar pada Microsoft Excel.
- **Manajemen Akun Petugas**: CRUD lengkap pengelolaan akun admin (tambah, edit, hapus, reset kata sandi) khusus untuk Superadmin.
- **Pengaturan Identitas Aplikasi**: Panel konfigurasi judul sistem, deskripsi, nama instansi PTSP, dan pesan sambutan pada header.
- **Manajemen Arsip & Siklus Hidup Tamu**: Pengelolaan data tamu dengan mekanisme soft delete (tong sampah, pemulihan, dan hapus permanen beserta berkas foto fisik) khusus Superadmin, pemisahan tab antarmuka (Aktif, Arsip, Tong Sampah), serta pengarsipan data riwayat tahunan (satuan maupun massal).

## 4. Data - What are we storing?

- **Tabel `tamu`**: `id`, `nama`, `jenis_kelamin` (Laki-laki / Perempuan), `asal_pengunjung` (perorangan / instansi / lembaga), `nama_instansi`, `alamat`, `no_hp`, `keperluan`, `foto` (path berkas foto di `public/uploads/foto/`), `is_read` (status sudah/belum dibaca), `is_archived` (status aktif/arsip), `deleted_at` (timestamp soft delete), dan `created_at`.
- **Tabel `admin`**: `id`, `nama`, `username`, `password` (hash Bcrypt), `role` (superadmin / admin), `created_at`, dan `updated_at`.
- **Tabel `pengaturan`**: Key-value store untuk `judul_aplikasi`, `deskripsi_aplikasi`, `nama_instansi_ptsp`, dan `teks_header`.

## 5. Tech - What stack are we using?

- **Backend**: PHP ^8.2 (tested on PHP 8.3) dengan framework CodeIgniter 4.7 (`codeigniter4/framework: ^4.7`).
- **Frontend**: PHP View Templates dengan layout modular (`app/Views/layouts/publik.php`, `app/Views/layouts/admin.php`), Tailwind CSS v4 (`@tailwindcss/cli: ^4.3.3`, `themes/app.css` dikompilasi ke `public/css/app.css`), Chart.js untuk grafik tren, dan Vanilla JavaScript untuk micro-interaksi.
- **Database**: MySQL 8.0 / MariaDB (skema pada `database/schema.sql`, charset `utf8mb4_unicode_ci`, zona waktu WITA `+08:00`).
- **Web Server & Lingkungan**: Apache / Nginx / PHP built-in server (`php spark serve`), Laragon / cPanel hosting.
- **Paket & Ketergantungan**: Composer untuk pustaka PHP dan npm untuk build asset Tailwind CSS v4.

## 6. Monetize - How will this make money?

Layanan publik internal instansi pemerintah non-komersial (didukung DIPA Kanwil Kementerian Agama Provinsi Kalimantan Utara).

## 7. UI/UX - How should this look and feel?

- **Palet Warna & Identitas**: Memakai nuansa warna hijau resmi Kementerian Agama (`#006B2E`) dan aksen emas (`#E5A400`), dipadukan dengan sistem desain flat Tailwind CSS v4 dan kaidah Tasteskill v2 (`brand`, `brand-gold`, tombol pil, serta permukaan siku bebas gradasi).
- **Tampilan Publik & Login Admin**: Komposisi editorial 2 kolom pada layar lebar (panel identitas instansi Kemenag Kaltara dengan logo transparan dan jam layanan WITA real-time di sisi kiri, serta lembar kerja formulir buku tamu dengan kartu pilihan ramah sentuh dan dropzone unggah foto di sisi kanan), halaman sukses berbentuk kartu bukti pencatatan kunjungan PTSP, serta halaman login petugas yang fokus dan terpisah dari banner buku tamu publik.
- **Tampilan Panel Admin**: Mengadopsi kaidah App Screens Tasteskill v2 berbasis sidebar responsif (drawer mobile, logo transparan, indikator menu aktif, dan badge jumlah antrean belum dibaca pada menu Daftar Tamu), kartu KPI bertipografi tabular tegas tanpa label huruf kapital renggang, toolbar pencarian/filter ringkas agar tabel tampil di layar pertama (*above the fold*), pagination ber-style Tailwind, modal dialog konfirmasi kustom, serta notifikasi toast in-app.

## 8. Deployment - Where and how will this ship?

- **Target Hosting**: Web hosting cPanel / VPS Linux (live demo pada `https://bukanwil.is-great.org`).
- **Konfigurasi Lingkungan**: File `.env` berbasis template `env` dengan pengaturan `CI_ENVIRONMENT = production`, `app.baseURL`, konfigurasi koneksi MySQL `database.default.*`, dan zona waktu WITA.
- **Direktori Berkas**: Direktori `public/uploads/foto/` dan direktori `writable/` harus memiliki izin tulis (*writeable*) oleh web server.

## 9. Usage model and constraints (optional)

- **Skala Penggunaan**: Digunakan aktif harian pada jam kerja PTSP (perkiraan puluhan hingga ratusan pengunjung per hari).
- **Batas Keamanan & Kepercayaan**: Pengunjung umum tidak terotentikasi dibatasi oleh *rate limiting* dan validasi berkas ketat. Petugas admin diautentikasi dengan sesi aman dan pembatasan percobaan login.
- **Kepatuhan Waktu**: Seluruh pencatatan timestamp dan pelaporan menggunakan waktu Indonesia bagian Tengah (WITA, UTC+8).
