# Keputusan Lead — BUKANWIL

Jawaban atas "PERTANYAAN UNTUK LEAD" dari agen `arsitek` (docs/SPEC.md §9 dan blueprint/context/project-overview.md §Open Questions). Berlaku untuk seluruh fase pembangunan.

## Keputusan final (disetujui Martinus, 2026-10-18)

1. **Koordinat lokasi: DIHAPUS** (2026-10-18). Setelah review awal, fitur geolokasi dinilai tidak esensial untuk buku tamu PTSP — data kunjungan cukup dengan identitas tamu dan keperluan. Menghapus lat/lng menyederhanakan form publik (prinsip YAGNI) dan menghilangkan ketergantungan pada geolocation API browser yang bisa ditolak pengguna. Peta Leaflet juga dihapus dari halaman detail.
2. **Foto: OPSIONAL** (nullable), sesuai skema DB (`foto` NULL). Boleh diisi, tidak wajib.
3. **HTTPS: AKTIF** → set `$secure = true` di `app/Config/Cookie.php`. InfinityFree menyediakan SSL gratis. (Untuk lokal dev, `koding` boleh membuat `$secure` menyesuaikan `ENVIRONMENT`, tapi produksi wajib `true`.)
4. **Rate limit form publik: berbasis SESSION** (FileHandler). Terima keterbatasannya; tanpa Redis (batasan hosting).
5. **Tandai dibaca: OTOMATIS** saat admin buka detail (`is_read = 1`), plus tombol "tandai semua dibaca" opsional di daftar.

## Keputusan pelengkap

- **Akun admin awal:** username `superadmin`, password `Admin@2026` (hash bcrypt cost-12 sudah ada di `database/schema.sql`). Wajib diganti saat login pertama; password final diset Martinus setelah aplikasi live & dipakai.
- **Prinsip spesifikasi:** semua detail teknis wajib bisa dieksekusi model murah (`kimi-k2-7-code`, `gemini-3-8-flash`) tanpa menebak — tulis path/nama presisi + contoh nyata (sudah masuk Persona `arsitek`).
- **Konvensi file Tailwind:** sumber `themes/app.css` → output build `public/css/app.css` (diterapkan agen `koding` Fase 1). Ini menggantikan asumsi `public/assets/css/` yang tertulis di SPEC deployment-notes.
- **Nilai `asal_pengunjung`: 3 nilai — `perorangan`, `instansi`, `lembaga`** (disetujui Lead + user, 2026-10-18). Sebelumnya hanya 2 nilai. Konsekuensi: `nama_instansi` wajib di level aplikasi bila `asal_pengunjung` ∈ {`instansi`, `lembaga`} (tamu asal lembaga juga mencatat nama lembaganya). Validasi: `in_list[perorangan,instansi,lembaga]`. Migrasi DB berjalan: `ALTER TABLE tamu MODIFY asal_pengunjung ENUM('perorangan','instansi','lembaga') NOT NULL;`