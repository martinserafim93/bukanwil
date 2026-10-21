# PRD — BUKANWIL
## Buku Tamu Kanwil Kemenag Kaltara

**Versi:** 1.0.0
**Tanggal:** 2026-10-18
**Pemilik:** Kantor Wilayah Kementerian Agama Provinsi Kalimantan Utara (Kanwil Kemenag Kaltara)
**Status:** Draft — menunggu persetujuan Lead

---

## 1. Latar Belakang

PTSP (Pelayanan Terpadu Satu Pintu) Kanwil Kemenag Kaltara saat ini menggunakan Google Form sebagai buku tamu digital. Pendekatan ini memiliki keterbatasan: data tersimpan di platform pihak ketiga, tidak ada CMS terpusat, tidak ada notifikasi in-app, dan tampilan tidak mencerminkan identitas instansi.

BUKANWIL hadir sebagai pengganti Google Form sekaligus CMS internal — tetap ringan, mudah di-deploy ke hosting shared (InfinityFree), dan tidak bergantung pada layanan eksternal untuk fungsi inti.

---

## 2. Pengguna (User Persona)

### 2.1 Pengunjung PTSP (Tamu)
- Masyarakat umum, atau perwakilan instansi/lembaga yang datang ke kantor PTSP.
- Mengakses halaman publik via perangkat apa pun (mobile-first).
- Tidak perlu akun. Mengisi form satu kali per kunjungan.
- Tingkat literasi digital bervariasi — form harus sesederhana mungkin.

### 2.2 Operator / Admin
- Staff atau operator PTSP Kanwil Kemenag Kaltara.
- Mengelola data tamu, melihat statistik, mengekspor laporan.
- Mengakses CMS dari browser desktop/laptop di jaringan kantor.

### 2.3 Superadmin
- Pemegang akun admin dengan hak penuh: CRUD admin lain + ubah pengaturan aplikasi.
- Minimal 1 akun; strukturnya extensible untuk penambahan admin di kemudian hari.

---

## 3. Fitur & Prioritas

### P1 — Wajib ada di versi pertama

| Kode | Fitur |
|------|-------|
| F-01 | Form buku tamu publik dengan 8 field (termasuk foto) |
| F-02 | Anti-spam: honeypot field + rate limit form publik |
| F-03 | Login/logout admin dengan proteksi CSRF dan session hardening |
| F-04 | Dashboard admin: statistik ringkas dan grafik tren kunjungan |
| F-05 | Daftar tamu: pagination, pencarian, filter, urutkan |
| F-06 | Detail tamu: semua field + foto |
| F-07 | Export daftar tamu ke CSV sesuai filter aktif |
| F-08 | Notifikasi in-app: badge tamu baru belum dibaca + tandai dibaca |
| F-09 | Kelola admin: CRUD user admin, ubah password, role superadmin/admin |
| F-10 | Pengaturan: judul aplikasi, nama instansi PTSP, teks header (tabel pengaturan) |
| F-11 | Checklist keamanan lengkap (lihat SPEC.md §7) |

### P2 — Dapat ditambahkan di iterasi berikutnya (di luar scope v1)

- Notifikasi email/Telegram saat ada tamu baru.
- QR code untuk akses form publik.
- Multi-bahasa (Indonesia/Inggris).
- Fitur print kartu tamu.

---

## 4. Batasan & Asumsi

- Hosting: InfinityFree free tier — tidak mendukung dotenv; semua config di `app/Config/*.php`.
- Tidak ada Redis/Memcached — rate limiting menggunakan session PHP.
- Tidak ada queue/worker — semua proses sinkron.
- Foto disimpan di filesystem (`public/uploads/foto/`), bukan blob DB.
- Chart.js dimuat dari CDN (versi di-pin di view untuk stabilitas).
- Aplikasi dalam Bahasa Indonesia.

---

## 5. Kriteria Keberhasilan (Definition of Done v1)

1. Tamu dapat mengisi dan mengirim form dari perangkat mobile tanpa error.
2. Admin dapat login, melihat dashboard, menelusuri dan mengekspor data tamu.
3. Notifikasi badge tamu baru tampil dan bisa di-dismiss.
4. Semua item checklist keamanan di SPEC.md §7 terpenuhi.
5. Tidak ada query tanpa prepared statement.
6. Upload foto berjalan: validasi mime, ekstensi, ukuran, rename acak, folder terkunci.
