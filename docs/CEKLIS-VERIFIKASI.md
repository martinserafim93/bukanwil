# Ceklis Verifikasi per Fase — BUKANWIL

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
- [x] 1. File nyata — Admin\AdminUser (CRUD), Admin\Pengaturan, views admin-user (index/form/hapus) + pengaturan/index; notifikasi in-app (badge + tandai-baca) tuntas di Fase 3
- [x] 2. Build/test — `php -l` lolos + runtime E2E (buat/edit/hapus admin, guard self-delete int-cast, guard superadmin terakhir, anti-escalation, pengaturan simpan → DB berubah, non-superadmin 302)
- [x] 3. Review loop tertutup — reviewer APPROVE (4a: 1 iterasi + 1 fix guard; 4b: 1 iterasi + 1 fix return type)
- [x] 4. Tidak menggantung — temuan eskalasi/last-superadmin/return-type tertutup; saran audit-log dicatat sebagai enhancement
- [x] 5. Secret aman — password bcrypt cost 12, tak pernah tampil di view
- [x] 6. Push terverifikasi — `9723fa6` (4a), `804d0f8` (4b) — author Martinus Lukas

## Fase 5 — Uji lokal (smoke test + keamanan)
- [x] 1. File nyata — perbaikan Filters.php (auth /admin), detail.php (SRI Leaflet + CSS head), layouts/admin.php (slot styles), public/css/app.css (rebuild)
- [x] 2. Build/test — smoke test semua route (publik 200, admin 302/200, 404 benar); build Tailwind ulang (h-80/h-72/h-64 masuk); upload finfo_file + .htaccess blokir eksekusi; CSRF aktif
- [x] 3. Review loop tertutup — temuan runtime (3 bug: filter /admin, SRI Leaflet, CSS head) difix koding, DIVERIFIKASI ulang render peta (320px + marker + tile)
- [x] 4. Tidak menggantung — pra-deploy TODO: cookieSecure=true saat HTTPS (Fase 6); audit-log sebagai enhancement
- [x] 5. Secret aman — seed admin tetap superadmin/Admin@2026 (hash bcrypt), tak ada secret di commit
- [x] 6. Push terverifikasi — `90abfca` (Martinus Lukas)

## Fase 6 — Paket & deploy FTP (InfinityFree)
- [x] 1. File nyata — 3.217 file ter-upload ke `santri.is-best.net/htdocs/` (subdomain LAMA, sebelum rename; verifikasi koneksi baru)
- [x] 2. Build/test — live: form buku tamu + login admin render 200, DB terbaca (tabel `pengaturan`)
- [x] 3. Review loop tertutup — bug config produksi (`$proxyIPs` format CI4 4.7) ditemukan+fiksasi sampai app jalan
- [x] 4. Tidak menggantung — upload tuntas, tak ada proses background tersisa
- [x] 5. Secret aman — kredensial DB produksi hanya di staging deploy, BUKAN di repo git (repo pakai localhost)
- [x] 6. Push terverifikasi — kode Fase 0–5 sudah di remote; deploy add bukan commit baru (config produksi tak di-commit)

## Fase 7 — Rename SANTRI → BUKANWIL
- [x] 1. File nyata — 32 file ter-rename (views/layouts, config, controller, schema.sql, package.json+lock, docs/blueprint) + spec `SPEC-RENAME-BUKANWIL.md`
- [x] 2. Build/test — `php -l` semua file lolos; `npm run build:css` OK; grep sisa `santri`/`Religius` kosong di app/database/public/themes
- [x] 3. Review loop tertutup — reviewer APPROVE (temuan: TIDAK ADA P0/P1/P2)
- [x] 4. Tidak menggantung — tidak ada proses tersisa; OPS lanjutan dialihkan ke Fase 8
- [x] 5. Secret aman — tidak ada secret di commit
- [x] 6. Push terverifikasi — `6cd5b29` (Martinus Lukas); repo di-rename `martinserafim93/bukanwil`

## Fase 8 — Deploy produksi BUKANWIL (subdomain + migrasi data)
- [ ] 1. File nyata — subdomain `bukanwil.is-best.net` dibuat di panel InfinityFree (manual Martinus)
- [ ] 2. Build/test — upload + smoke test live di subdomain baru
- [ ] 3. Review loop tertutup
- [ ] 4. Tidak menggantung
- [ ] 5. Secret aman — kredensial DB produksi tak di-commit
- [ ] 6. Push terverifikasi