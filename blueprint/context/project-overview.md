# Project Overview — SANTRI

**Sistem Aplikasi Notifikasi dan Tamu Religius Integritas**  
Buku Tamu Digital + CMS Admin untuk PTSP Kanwil Kemenag Kalimantan Utara

---

## Problem Statement

PTSP (Pelayanan Terpadu Satu Pintu) Kanwil Kemenag Kaltara saat ini menggunakan Google Form sebagai buku tamu digital. Pendekatan ini memiliki keterbatasan kritis:

- **Data tersimpan di platform pihak ketiga** — tidak ada kepemilikan penuh atas data sensitif pengunjung.
- **Tidak ada CMS terpusat** — admin tidak dapat mengelola, mencari, atau mengekspor data secara fleksibel.
- **Tidak ada notifikasi in-app** — operator tidak tahu kapan ada tamu baru tanpa membuka spreadsheet.
- **Tampilan generik** — tidak mencerminkan identitas instansi Kemenag.

SANTRI hadir sebagai solusi pengganti: aplikasi buku tamu self-hosted yang tetap ringan, mudah di-deploy ke hosting shared (InfinityFree free tier), dan tidak bergantung pada layanan eksternal untuk fungsi inti.

---

## Solution

Aplikasi web full-stack sederhana dengan dua interface:

1. **Form buku tamu publik** — pengunjung PTSP mengisi data (nama, jenis kelamin, asal, instansi, alamat, no HP, keperluan, foto, koordinat lokasi) sekali per kunjungan, tanpa akun.
2. **CMS admin** — operator/admin PTSP login, melihat dashboard statistik, menelusuri daftar tamu dengan filter/pencarian/pagination, membuka detail lengkap (termasuk peta lokasi via Leaflet + OpenStreetMap), dan mengekspor data ke CSV.

Fitur keamanan built-in: anti-spam (honeypot + rate limit), CSRF protection, upload file validation, session hardening, prepared statements.

Notifikasi in-app: badge jumlah tamu baru belum dibaca di topbar admin, otomatis berkurang saat admin membuka detail tamu.

---

## User Personas

### 1. Pengunjung PTSP (Tamu)
- Masyarakat umum atau perwakilan instansi yang datang ke kantor PTSP.
- Mengakses halaman publik via perangkat mobile/desktop.
- **Tidak perlu akun**. Mengisi form satu kali per kunjungan.
- Tingkat literasi digital bervariasi — form harus sesederhana mungkin, mobile-first.

### 2. Operator / Admin
- Staff atau operator PTSP Kanwil Kemenag Kaltara.
- Mengelola data tamu, melihat statistik, mengekspor laporan.
- Mengakses CMS dari browser desktop/laptop di jaringan kantor.

### 3. Superadmin
- Pemegang akun admin dengan hak penuh: CRUD admin lain + ubah pengaturan aplikasi (judul, nama instansi, teks header).
- Minimal 1 akun; strukturnya extensible untuk penambahan admin di kemudian hari.

---

## Tech Stack

| Layer                | Teknologi                           | Alasan Pilihan                                      |
|----------------------|-------------------------------------|-----------------------------------------------------|
| **Backend**          | CodeIgniter 4.7.5 + PHP 8.3         | Framework MVC ringan, kompatibel hosting shared     |
| **Database**         | MySQL 8.0                           | Standar InfinityFree                                |
| **Frontend (CSS)**   | Tailwind CSS v4.3.3 (CSS-first `@theme`) | Design tokens eksplisit, anti-slop, mobile-first |
| **Frontend (JS)**    | JavaScript native                   | No build step di production (Leaflet + Chart.js CDN) |
| **Peta**             | Leaflet + OpenStreetMap tile        | Open-source, no API key                             |
| **Chart**            | Chart.js 4.4.x (CDN)                | Dashboard statistik sederhana                       |
| **Dependency Mgmt**  | Composer 2.x + npm                  | CI4 via Composer, Tailwind CLI via npm              |

---

## Hosting Constraints (InfinityFree Free Tier)

Batasan yang harus diakomodasi:

1. **TANPA dotenv** — file `.env` tidak bisa dibaca karena keamanan IonCube. Semua konfigurasi (baseURL, database, CSRF, encryption key) ditulis langsung di `app/Config/*.php`.
2. **Tidak ada Redis/Memcached** — rate limiting menggunakan session PHP (FileHandler).
3. **Tidak ada queue/worker** — semua proses sinkron.
4. **Upload file** disimpan di filesystem (`public/uploads/foto/`), bukan blob DB.
5. **CDN eksternal** — Chart.js dan Leaflet dimuat dari CDN (versi di-pin untuk stabilitas).
6. **Entry point** `public/index.php` — domain diarahkan ke folder `public/` via `.htaccess`.

---

## Features (MVP / v1.0)

### Must-Have (P1) — 11 Fitur Inti

| Kode  | Fitur                                                                                      |
|-------|--------------------------------------------------------------------------------------------|
| F-01  | Form buku tamu publik dengan 10 field (termasuk foto dan koordinat lokasi)               |
| F-02  | Anti-spam: honeypot field + rate limit form publik (3 submit per 10 menit)               |
| F-03  | Login/logout admin dengan proteksi CSRF, rate limit login (5 gagal per 15 menit), session hardening |
| F-04  | Dashboard admin: statistik ringkas (total tamu, hari ini, belum dibaca, per jenis kelamin, per asal, top 5 keperluan) + grafik tren 7 hari (Chart.js) |
| F-05  | Daftar tamu: pagination (20/halaman), pencarian (nama/no_hp/instansi), filter (tanggal range, jenis_kelamin, asal, keperluan), urutkan (created_at/nama asc/desc) |
| F-06  | Detail tamu: semua field + foto + peta lokasi (Leaflet + OpenStreetMap marker)           |
| F-07  | Export daftar tamu ke CSV sesuai filter aktif (BOM UTF-8 untuk Excel)                    |
| F-08  | Notifikasi in-app: badge tamu baru belum dibaca + tandai dibaca (otomatis saat buka detail) + tandai semua dibaca |
| F-09  | Kelola admin: CRUD user admin, ubah password, role superadmin/admin (hapus hanya superadmin) |
| F-10  | Pengaturan: judul aplikasi, nama instansi PTSP, teks header (tabel pengaturan key-value) |
| F-11  | Checklist keamanan lengkap (lihat SPEC.md §7): prepared statements, bcrypt cost-12, CSRF, upload validation, XSS escaping, honeypot, rate limit, .htaccess hardening, mode produksi |

### Out of Scope (P2 — iterasi berikutnya)

- Notifikasi email/Telegram saat ada tamu baru.
- QR code untuk akses form publik.
- Multi-bahasa (Indonesia/Inggris).
- Fitur print kartu tamu.

---

## Success Criteria (Definition of Done v1)

Aplikasi dinyatakan selesai bila:

1. ✅ Tamu dapat mengisi dan mengirim form dari perangkat mobile tanpa error.
2. ✅ Admin dapat login, melihat dashboard, menelusuri dan mengekspor data tamu.
3. ✅ Notifikasi badge tamu baru tampil dan bisa di-dismiss (berkurang saat buka detail).
4. ✅ Semua 18 item checklist keamanan di SPEC.md §7 terpenuhi (diverifikasi oleh agen `koding`).
5. ✅ Tidak ada query tanpa prepared statement — semua via CI4 Model QueryBuilder.
6. ✅ Upload foto berjalan: validasi mime (`finfo_file`), ekstensi whitelist, ukuran max 2MB, rename acak, folder `.htaccess` terkunci (eksekusi PHP ditolak tapi foto tetap bisa disajikan).
7. ✅ Mode produksi aktif: `CI_ENVIRONMENT = 'production'`, `DBDebug = false` di Database.php.
8. ✅ Tidak ada file `.env` di root proyek (semua config di `app/Config/*.php`).
9. ✅ Aplikasi bisa di-deploy ke InfinityFree tanpa modifikasi: commit file CSS yang sudah ter-build, tidak ada dependency pada Node.js atau Redis di server.

---

## Design Principles (Anti-Slop)

Skill `ui-design-standards` menjadi acuan:

- **Warna solid flat** — tidak ada `bg-gradient-*` pada elemen diam.
- **Border-radius konsisten** — pilih siku (0) atau pil (9999px), terapkan satu pilihan.
- **Button primary** — `bg-brand-green text-white` tanpa gradient.
- **Surface card** — `bg-surface-raised border border-border` bukan `shadow-lg rounded-lg`.
- **Tidak ada eyebrow label, tiny-uppercase, atau deretan ikon-card identik**.

Warna brand Kemenag:
- Hijau primer: `#006B2E`
- Emas aksen: `#E5A400`

---

## Open Questions (PERTANYAAN UNTUK LEAD)

Dicatat di SPEC.md §9 — belum ada jawaban final:

1. **Koordinat wajib atau opsional?** Saat ini `lat`/`lng` nullable di DB. Jika wajib, form tidak bisa disubmit bila geolocation ditolak dan user tidak isi manual.
2. **HTTPS di InfinityFree?** Bila domain sudah dikonfigurasi dengan SSL, set `$secure = true` di Cookie.php. Konfirmasi status HTTPS.
3. **Foto wajib atau opsional?** Saat ini diperlakukan sebagai WAJIB di aturan validasi. Konfirmasi apakah foto boleh dilewati.
4. **Rate limit berbasis session vs. IP?** Session PHP (tidak perlu Redis) — pengguna yang membersihkan cookie bisa melewatinya. Terima batasan ini?
5. **Tandai dibaca: otomatis saat buka detail atau tombol manual?** Saat ini spec: otomatis set `is_read=1` saat admin membuka halaman detail. Alternatif: tombol eksplisit.

---

## Deployment Notes

- **Hosting:** InfinityFree free tier (PHP + MySQL + .htaccess).
- **Repo:** https://github.com/martinserafim93/santri (sudah terbootstrap).
- **Build command (lokal/dev):** `npx @tailwindcss/cli -i public/assets/css/app.css -o public/assets/css/app.compiled.css --minify`
- **Deploy:** Commit file `app.compiled.css` yang sudah ter-build, upload seluruh repo ke `htdocs/` InfinityFree (atau subdirektori), arahkan domain ke folder `public/`.
- **Database setup:** Import `database/schema.sql`, generate hash password superadmin via `php -r "echo password_hash('Admin@2026', PASSWORD_BCRYPT, ['cost'=>12]);"`, UPDATE tabel admin.
- **Encryption key:** Generate via `php spark key:generate --show`, paste ke `app/Config/Encryption.php`.
- **Health check:** GET `/` (form publik), GET `/admin` (harus redirect ke `/admin/login` bila belum login).

---

## Maintenance & Extensibility

- **Logging:** CI4 log threshold 4 (warning ke atas), writable/logs/ — monitor error log setelah deploy.
- **Backup:** Export MySQL via phpMyAdmin InfinityFree, backup folder `public/uploads/foto/` berkala.
- **Skalabilitas:** Single-tenant, target 50–200 tamu per bulan (PTSP Kanwil), tidak ada rencana multi-tenant.
- **Extensibility:** Tabel `pengaturan` key-value store — fitur baru bisa menambah key tanpa ALTER TABLE. Tabel `admin` sudah mendukung role superadmin/admin (siap untuk role tambahan di kemudian hari).

---

**Versi:** 1.0.0  
**Tanggal:** 2026-10-18  
**Pemilik:** Kantor Wilayah Kementerian Agama Provinsi Kalimantan Utara
