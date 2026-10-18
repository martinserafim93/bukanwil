# Project Plan — SANTRI v1.0

**Sistem Aplikasi Notifikasi dan Tamu Religius Integritas**
PTSP Kanwil Kemenag Kalimantan Utara

Sumber kebenaran: `docs/PRD.md`, `docs/SPEC.md`, `docs/TASKS.md`, `database/schema.sql`.
Urutan task di sini mengikuti urutan eksekusi di TASKS.md (Task-01 s/d Task-18).

---

## 1. Problem

PTSP Kanwil Kemenag Kaltara menggunakan Google Form sebagai buku tamu digital.
Masalah utama: data di platform pihak ketiga, tidak ada CMS terpusat, tidak ada notifikasi in-app,
tampilan tidak mencerminkan identitas instansi.

SANTRI menggantikan Google Form dengan aplikasi self-hosted: form publik + CMS admin,
di-deploy ke InfinityFree free tier (PHP + MySQL, tanpa dotenv, tanpa Redis).

---

## 2. Users

Tiga persona:

Pengunjung PTSP (Tamu): masyarakat umum atau perwakilan instansi yang datang ke kantor.
Mengisi form satu kali per kunjungan. Tanpa akun. Mobile-first, literasi digital bervariasi.

Operator / Admin: staff PTSP yang mengelola data, lihat statistik, ekspor laporan.
Akses CMS dari desktop/laptop di jaringan kantor.

Superadmin: hak penuh — CRUD admin lain + ubah pengaturan aplikasi. Minimal 1 akun.

---

## 3. Features (MVP v1.0)

### P1 — Must Have (11 fitur inti, semua wajib selesai sebelum v1 dinyatakan done)

- [ ] F-01  Form buku tamu publik (10 field: nama, jenis kelamin, asal pengunjung, nama instansi, alamat, no HP, keperluan, foto, lat, lng) — mobile-first, honeypot tersembunyi
- [ ] F-02  Anti-spam: honeypot field + rate limit form publik (max 3 submit per 10 menit via session)
- [ ] F-03  Login/logout admin: CSRF, rate limit login (5 gagal per 15 menit), session hardening (regenerate, destroy, httponly cookie)
- [ ] F-04  Dashboard admin: 4 kartu statistik (total, hari ini, belum dibaca, per jenis kelamin) + Chart.js Line tren 7 hari + Doughnut per jenis kelamin + list top 5 keperluan
- [ ] F-05  Daftar tamu: pagination 20/hal, pencarian (nama/no_hp/instansi), filter (tanggal range, jenis_kelamin, asal, keperluan), sort (created_at/nama asc/desc), filter persisten di URL
- [ ] F-06  Detail tamu: semua field + foto (fallback teks) + peta Leaflet + OpenStreetMap (marker bila koordinat ada, teks "tidak tersedia" bila null)
- [ ] F-07  Export CSV: filter aktif diteruskan, BOM UTF-8 (kompatibel Excel), semua 12 kolom
- [ ] F-08  Notifikasi in-app: badge unread count di topbar, otomatis tandai dibaca saat buka detail, tombol "tandai semua dibaca"
- [ ] F-09  Kelola admin: CRUD user admin, ubah password (kosong = tidak ubah), role superadmin/admin, hapus hanya oleh superadmin, tidak bisa hapus diri sendiri
- [ ] F-10  Pengaturan: edit judul_aplikasi, nama_instansi_ptsp, teks_header; perubahan langsung terlihat di layout; hanya superadmin
- [ ] F-11  Checklist keamanan: semua item SPEC.md §7 terpenuhi (prepared statements, bcrypt cost-12, CSRF, upload validation mime+ekstensi+ukuran+rename, XSS esc(), honeypot, rate limit, .htaccess hardening, mode produksi)

### P2 — Out of Scope v1 (iterasi berikutnya)

- Notifikasi email/Telegram saat ada tamu baru
- QR code untuk akses form publik
- Multi-bahasa Indonesia/Inggris
- Print kartu tamu

---

## 4. Data

Tiga tabel MySQL (DDL lengkap di `database/schema.sql`):

tamu: id, nama, jenis_kelamin, asal_pengunjung, nama_instansi, lat, lng, alamat, no_hp, keperluan, foto, is_read, created_at
admin: id, nama, username, password (bcrypt), role, created_at, updated_at
pengaturan: id, key (UNIQUE), value — key-value store untuk judul_aplikasi, nama_instansi_ptsp, teks_header

---

## 5. Tech Stack

Backend:   CodeIgniter 4.7.5 + PHP 8.3 (Composer)
Database:  MySQL 8.0
CSS:       Tailwind CSS v4.3.3 (CSS-first @theme, dikompilasi via npx @tailwindcss/cli)
JS:        JavaScript native
Peta:      Leaflet + OpenStreetMap (CDN)
Chart:     Chart.js 4.4.x (CDN, versi di-pin)
Hosting:   InfinityFree free tier (tidak ada dotenv, tidak ada Redis, tidak ada Node.js di server)
Repo:      https://github.com/martinserafim93/santri

Constraint kritis:
- TANPA .env — semua config di app/Config/*.php
- CSS dikompilasi lokal, file app.compiled.css di-commit ke repo
- Rate limiting via session PHP (bukan Redis)
- Upload foto ke filesystem public/uploads/foto/ (bukan blob DB)

---

## 6. Monetize

Tidak ada — aplikasi internal instansi pemerintah (Kanwil Kemenag Kaltara).
Tidak ada rencana komersialisasi.

---

## 7. UI/UX

Warna brand Kemenag:
- Hijau primer: #006B2E
- Emas aksen: #E5A400

Prinsip anti-slop (wajib dipatuhi agen koding saat menulis view):
- Warna solid flat — tidak ada bg-gradient-* pada elemen diam
- Border-radius konsisten — pilih siku (0) ATAU pil (9999px), satu pilihan, terapkan konsisten
- Button primary: bg-brand-green text-white tanpa gradient
- Surface card: bg-surface-raised border border-border (bukan shadow-lg rounded-lg)
- Tidak ada eyebrow label, tiny-uppercase, atau deretan ikon-card identik

Form publik: mobile-first (375px ke atas), satu kolom, field lebar penuh, label di atas field.
CMS admin: sidebar kiri tetap (desktop), topbar dengan badge notif, konten utama dengan tabel + filter.

---

## 8. Deployment

Target host: InfinityFree free tier
- Upload seluruh repo ke htdocs/ (atau subdirektori), domain diarahkan ke folder public/
- Tidak perlu Node.js atau Redis di server
- File app.compiled.css sudah ter-build dan di-commit sebelum deploy

Setup awal:
1. Import database/schema.sql ke MySQL InfinityFree (via phpMyAdmin)
2. Generate encryption key: php spark key:generate --show, paste ke app/Config/Encryption.php
3. Generate hash password superadmin: php -r "echo password_hash('Admin@2026', PASSWORD_BCRYPT, ['cost'=>12]);"
4. UPDATE admin SET password='<hash>' WHERE username='superadmin'
5. Update app/Config/App.php (baseURL), app/Config/Database.php (hostname/user/pass/database)
6. Upload — akses /admin/login untuk verifikasi

Health check path: GET / (form publik tampil), GET /admin (redirect ke /admin/login bila belum login)

---

## 9. Constraints & Scale

- Single-tenant: satu instalasi untuk satu instansi (PTSP Kanwil Kemenag Kaltara)
- Internal + internet-facing: form publik bisa diakses siapa pun; CMS admin di jaringan kantor
- Skala rendah: estimasi 50–200 tamu per bulan — tidak perlu caching atau optimasi lanjutan
- Tidak ada queue/worker: semua proses sinkron
- Rate limit berbasis session (bukan IP) — pengguna yang clear cookie bisa bypass; ini batasan yang disadari untuk hosting shared
- Keputusan terbuka (menunggu Lead): koordinat wajib/opsional, foto wajib/opsional, status HTTPS domain, rate limit session vs IP

---

## 10. Roadmap & Milestones

### Fase 1 — Fondasi (Task-01 s/d Task-04)
Selesai bila: CI4 bisa serve halaman, DB terkoneksi, skema terpasang, filter auth berfungsi.

- [ ] Task-01: Inisialisasi CI4 + konfigurasi (App, Database, Session, Cookie, Encryption, .htaccess)
- [ ] Task-02: Import schema.sql + seed admin superadmin dengan hash bcrypt
- [ ] Task-03: Model layer (TamuModel, AdminModel, PengaturanModel) + semua method
- [ ] Task-04: AuthFilter + BaseController (inject $pengaturan + $unreadCount ke semua controller)

### Fase 2 — Auth + Skeleton CMS (Task-05 s/d Task-06)
Selesai bila: admin bisa login, dashboard tampil dengan data aktual (walaupun kosong), layout konsisten.

- [ ] Task-05: Auth admin (login form, doLogin dengan rate limit, logout)
- [ ] Task-06: Layout admin (sidebar, topbar, badge notif) + Dashboard (4 kartu + Chart.js)

### Fase 3 — Fitur Tamu di CMS (Task-07 s/d Task-10)
Selesai bila: admin bisa lihat, cari, filter, ekspor data tamu; peta dan badge berfungsi.

- [ ] Task-07: Daftar tamu (list, pencarian, filter, pagination, sort)
- [ ] Task-08: Detail tamu (semua field + foto + Leaflet map)
- [ ] Task-09: Export CSV (filter aktif + BOM UTF-8)
- [ ] Task-10: Notifikasi in-app (badge + tandai dibaca + tandai semua dibaca)

### Fase 4 — Manajemen Admin + Pengaturan (Task-11 s/d Task-12)
Selesai bila: superadmin bisa CRUD admin, ubah pengaturan; role check berfungsi.

- [ ] Task-11: Kelola admin (CRUD, ubah password, role check, hapus dengan konfirmasi)
- [ ] Task-12: Halaman pengaturan (edit 3 key, hanya superadmin)

### Fase 5 — Form Publik (Task-13 s/d Task-16)
Selesai bila: pengunjung bisa isi dan submit form dari mobile; anti-spam berfungsi.

- [ ] Task-13: Layout publik + view form buku tamu (semua 10 field, honeypot tersembunyi)
- [ ] Task-14: Controller Tamu (index, submit dengan validasi lengkap + upload foto, sukses)
- [ ] Task-15: Halaman sukses
- [ ] Task-16: JavaScript form-publik.js (geolocation API + conditional field nama_instansi)

### Fase 6 — CSS + Hardening (Task-17 s/d Task-18)
Selesai bila: CSS ter-compile dengan token warna Kemenag; semua QA checklist dicentang.

- [ ] Task-17: Tailwind CSS v4 setup + design tokens (@theme dengan brand-green, brand-gold, dll)
- [ ] Task-18: Final hardening + .htaccess + QA checklist (semua item SPEC.md §7)

---

## 11. Definition of Ready (DoR) per Task

Task siap dikerjakan agen koding bila:
- Task sebelumnya (dependency langsung) sudah dicentang selesai
- File yang menjadi dependensinya sudah ada di repo (mis. Task-07 butuh TamuModel dari Task-03)
- Tidak ada PERTANYAAN UNTUK LEAD yang belum dijawab dan mempengaruhi task tersebut

---

## 12. Definition of Done (DoD) per Task

Task dinyatakan selesai bila:
- Semua file yang disebutkan di "File dibuat / diubah" sudah ada di repo
- Semua "Kriteria selesai" di TASKS.md untuk task tersebut terpenuhi dan dilaporkan agen koding
- Tidak ada PHP error/warning di log untuk flow yang dicakup task tersebut
- Tidak ada console error JS untuk halaman yang dicakup task tersebut

DoD global v1.0:
- [ ] Tamu dapat mengisi dan mengirim form dari mobile tanpa error
- [ ] Admin dapat login, lihat dashboard, telusuri dan ekspor data tamu
- [ ] Badge notif tampil dan berkurang saat buka detail tamu
- [ ] Semua item QA checklist Task-18 dicentang oleh agen koding
- [ ] Tidak ada query tanpa prepared statement di seluruh codebase
- [ ] Upload foto: validasi mime + ekstensi + ukuran + rename acak + .htaccess terkunci
- [ ] CI_ENVIRONMENT = 'production', DBDebug = false
- [ ] Tidak ada file .env di root proyek
- [ ] app.compiled.css ter-build tanpa error, token brand-green dan brand-gold berfungsi
- [ ] Aplikasi bisa di-deploy ke InfinityFree tanpa modifikasi

---

**Versi:** 1.0.0
**Tanggal:** 2026-10-18
**Dikelola oleh:** Agen arsitek ([REDACTED])
**Sumber kebenaran:** docs/PRD.md, docs/SPEC.md, docs/TASKS.md
