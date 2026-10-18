# Ceklis Verifikasi per Fase — SANTRI

Ledger verifikasi Lead sebelum menyatakan fase selesai & push. Enam poin WAJIB (lihat "Aturan Kerja Lead" di AGENTS.md). Tiap fase dicentang bila semua poinnya hijau.

Poin: (1) File nyata · (2) Build/test lolos · (3) Review loop tertutup · (4) Tidak menggantung · (5) Secret aman · (6) Push terverifikasi.

---

## Fase 0 — Setup & Spesifikasi
- [x] 1. File nyata — repo bootstrap CI4 4.7.5 + PRD/SPEC/TASKS/schema.sql (arsitek)
- [x] 2. Build/test — composer scaffold + Tailwind build awal OK
- [x] 3. Review loop — spec diperiksa Lead & konsisten
- [x] 4. Tidak menggantung — keputusan stack final (CI4 tanpa .env)
- [x] 5. Secret aman — token GitHub di /opt/data/.gh_token (chmod 600), tak masuk repo
- [x] 6. Push terverifikasi — `2ed145d` (Martinus Lukas)

## Fase 1 — Design System (token & layout)
- [x] 1. File nyata — themes/app.css, layouts/publik+admin, form/login, DESIGN.md, preview.html; preview_generator.php dihapus
- [x] 2. Build/test — `npm run build:css` OK (179ms)
- [x] 3. Review loop — reviewer APPROVE (iterasi 3)
- [x] 4. Tidak menggantung — 5 keputusan Lead terjawab di DECISIONS.md
- [x] 5. Secret aman — tidak ada secret di commit
- [x] 6. Push terverifikasi — `69cba69` (Martinus Lukas)

## Fase 2 — Form buku tamu publik
- [x] 1. File nyata — Routes.php, Tamu.php (index/submit/sukses), layouts/publik.php, tamu/form.php, tamu/sukses.php, public/js/form-publik.js
- [x] 2. Build/test — `php -l` lolos + runtime E2E TERVERIFIKASI (GET form 200, POST submit → insert MySQL → 303 → sukses 200, CSRF+upload aman)
- [x] 3. Review loop tertutup — reviewer APPROVE (3 iterasi + review final perbaikan runtime)
- [x] 4. Tidak menggantung — keputusan foto/koordinat opsional sudah dijalankan
- [x] 5. Secret aman — tidak ada secret di commit (Encryption key dev + TODO regenerate)
- [x] 6. Push terverifikasi — `8a17190` (Martinus Lukas)

## Fase 3 — CMS admin (auth + dashboard + daftar + detail + export)
- [x] 1. File nyata — AuthFilter, Admin\Auth, Admin\Dashboard, Admin\TamuAdmin, views admin (login/dashboard/tamu/index+detail), pager, layouts/admin wiring
- [x] 2. Build/test — `php -l` semua lolos + runtime E2E TERVERIFIKASI (login sesi persisten, dashboard 200 + title ganda fix + Chart.js, daftar + filter + paginasi, export CSV BOM UTF-8, detail + Leaflet peta + tandai-baca 0→1, baca-semua)
- [x] 3. Review loop tertutup — reviewer APPROVE tiap sub-fase (3a: 2 iterasi; 3b: 2 iterasi; 3c-1: 2 iterasi; 3c-2: 1 iterasi)
- [x] 4. Tidak menggantung — bug CI4 4.7 (paginate Builder, filter except, regenerasi sesi) tertutup
- [x] 5. Secret aman — tidak ada secret di commit
- [x] 6. Push terverifikasi — `f6c0629` (3a), `4605681` (3b), `1cca251` (3c-1), `3a7e1c0` (3c-2) — author Martinus Lukas

## Fase 4 — Kelola admin + pengaturan + notifikasi in-app
- [ ] 1. File nyata
- [ ] 2. Build/test
- [ ] 3. Review loop tertutup
- [ ] 4. Tidak menggantung
- [ ] 5. Secret aman
- [ ] 6. Push terverifikasi

## Fase 5 — Uji lokal (php -S + smoke test + keamanan)
- [ ] 1. File nyata
- [ ] 2. Build/test
- [ ] 3. Review loop tertutup
- [ ] 4. Tidak menggantung
- [ ] 5. Secret aman
- [ ] 6. Push terverifikasi

## Fase 6 — Paket & deploy FTP (InfinityFree)
- [ ] 1. File nyata
- [ ] 2. Build/test
- [ ] 3. Review loop tertutup
- [ ] 4. Tidak menggantung
- [ ] 5. Secret aman
- [ ] 6. Push terverifikasi

## Fase 7 — Dokumentasi & serah terima
- [ ] 1. File nyata
- [ ] 2. Build/test
- [ ] 3. Review loop tertutup
- [ ] 4. Tidak menggantung
- [ ] 5. Secret aman
- [ ] 6. Push terverifikasi