# Build Plan

List the features that make up your project, high level and in rough build order.
Keep each item to one line; the details come later in `/feature`.

Plain bullets are fine. When both planning docs are ready, run `/overview`.
It adds tracking numbers and checkboxes to your feature list before generating
the project overview.

Run `/feature` to spec the next unchecked item, or `/feature 9` to pick one.
Keep completed items checked and append new features as the project grows.
Do not renumber completed features; their archived specs refer to those IDs.

## Shipped features

- [x] 1. **Formulir Tamu Publik Mandiri** - Form pengisian data pengunjung (nama, gender, asal, instansi, alamat, no HP, keperluan, upload foto opsional) dengan validasi finfo MIME, proteksi CSRF, honeypot anti-bot, rate limit, dan halaman sukses.
- [x] 2. **Autentikasi & Sesi Petugas CMS** - Sistem login admin dengan bcrypt hashing, proteksi brute-force (rate limit 5 gagal / 15 menit), kontrol hak akses superadmin & admin, dan modal konfirmasi logout kustom.
- [x] 3. **Dashboard Statistik & Analitik Tren** - Halaman utama admin dengan metrik total tamu, tamu hari ini, tamu belum dibaca, grafik tren kunjungan 7 hari Chart.js, serta distribusi asal & jenis kelamin.
- [x] 4. **Manajemen & Penelusuran Riwayat Tamu** - Tabel data tamu terintegrasi dengan pencarian (nama, no HP, instansi), filter tanggal/kategori, penomoran halaman (pagination), detail riwayat lengkap, dan status baca / belum dibaca.
- [x] 5. **Ekspor Laporan Tamu CSV** - Ekspor data kunjungan tamu ke berkas CSV dengan UTF-8 BOM untuk pembacaan rapi di Microsoft Excel.
- [x] 6. **Manajemen Akun Petugas Admin (CRUD)** - Antarmuka superadmin untuk membuat, memperbarui, mengatur ulang kata sandi, dan menghapus akun petugas CMS.
- [x] 7. **Konfigurasi Identitas Aplikasi & PTSP** - Pengaturan nama aplikasi, deskripsi, nama instansi PTSP, dan pesan salam sambutan di database.
- [x] 8. **Pengerasan Keamanan & Kompatibilitas Produksi** - Cache-busting aset CSS/JS, penyesuaian zona waktu WITA (UTC+8), validasi input ketat, dan penanganan error terjemahan bahasa Indonesia.
- [x] 9. **Manajemen Arsip dan Pembersihan Data Tamu** - Pengelolaan siklus hidup data tamu (soft delete/tong sampah, pemulihan, hapus permanen berkas fisik, dan arsip riwayat tahunan).

## Upcoming roadmap

- [ ] 10. **Penyempurnaan & Pemeliharaan Berkala** - Optimasi performa kueri data tamu, pemantauan log aplikasi, serta penyesuaian kebutuhan operasional PTSP Kanwil Kemenag Kaltara.
- [x] 11. **Redesign UI/UX Halaman Publik, Sukses, dan Login Admin dengan Tasteskill v2 (Fase 1)** - Perbaikan token Tailwind CSS v4 (`brand`, `brand-gold`, `.btn-danger`, `.choice-card`), komposisi editorial 2 kolom pada halaman buku tamu publik dengan jam WITA real-time dan kartu pilihan ramah sentuh, kartu bukti kunjungan pada halaman sukses, serta layout mandiri untuk login admin.
- [ ] 12. **Redesign UI/UX Shell dan Halaman Kerja Panel Admin dengan Tasteskill v2 (Fase 2)** - Penerapan kaidah App Screens Tasteskill v2 pada sidebar/shell admin (navigasi mobile, logo transparan, badge antrean tamu belum dibaca), penyegaran hierarki KPI dan grafik dashboard, toolbar filter ringkas dan pagination Tailwind pada daftar tamu, serta penyeragaman halaman detail, CRUD admin, dan pengaturan.
