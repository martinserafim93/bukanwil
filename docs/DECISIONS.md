# Keputusan Lead — SANTRI

Jawaban atas "PERTANYAAN UNTUK LEAD" dari agen `arsitek` (docs/SPEC.md §9 dan blueprint/context/project-overview.md §Open Questions). Berlaku untuk seluruh fase pembangunan.

## Keputusan final (disetujui Martinus, 2026-10-18)

1. **Koordinat lokasi (lat/lng): OPSIONAL** (nullable). Geolokasi bisa ditolak browser — form tetap harus bisa disubmit tanpa koordinat. Bila tersedia, simpan; bila tidak, kosongkan.
2. **Foto: OPSIONAL** (nullable), sesuai skema DB (`foto` NULL). Boleh diisi, tidak wajib.
3. **HTTPS: AKTIF** → set `$secure = true` di `app/Config/Cookie.php`. InfinityFree menyediakan SSL gratis. (Untuk lokal dev, `koding` boleh membuat `$secure` menyesuaikan `ENVIRONMENT`, tapi produksi wajib `true`.)
4. **Rate limit form publik: berbasis SESSION** (FileHandler). Terima keterbatasannya; tanpa Redis (batasan hosting).
5. **Tandai dibaca: OTOMATIS** saat admin buka detail (`is_read = 1`), plus tombol "tandai semua dibaca" opsional di daftar.

## Keputusan pelengkap

- **Akun admin awal:** username `superadmin`, password `Admin@2026` (hash bcrypt cost-12 sudah ada di `database/schema.sql`). Wajib diganti saat login pertama; password final diset Martinus setelah aplikasi live & dipakai.
- **Prinsip spesifikasi:** semua detail teknis wajib bisa dieksekusi model murah (`kimi-k2-7-code`, `gemini-3-8-flash`) tanpa menebak — tulis path/nama presisi + contoh nyata (sudah masuk Persona `arsitek`).
- **Konvensi file Tailwind:** sumber `themes/app.css` → output build `public/css/app.css` (diterapkan agen `koding` Fase 1). Ini menggantikan asumsi `public/assets/css/` yang tertulis di SPEC deployment-notes.