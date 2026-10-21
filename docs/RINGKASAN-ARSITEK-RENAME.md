# Ringkasan Arsitek — Spesifikasi Rename BUKANWIL

**Tanggal:** 2026-10-21  
**Arsitek:** Kiro (profile `arsitek`)  
**Status:** ✅ SELESAI — Menunggu review Lead  
**Repo:** `/opt/data/workspaces/santri`

---

## Dokumen yang Dihasilkan

### 1. **docs/SPEC-RENAME-BUKANWIL.md** (437 baris, 25 KB)
   Spesifikasi teknis lengkap rebrand SANTRI → BUKANWIL dengan pemetaan **file-by-file, baris-per-baris** yang presisi. Berisi:
   - Ringkasan perubahan (tabel mapping lama → baru)
   - Pemetaan detail per file (path → baris → string lama → string baru) dengan TAG `[FRONTEND]`, `[BACKEND]`, `[OPS]`, `[DOC]`
   - Langkah deployment produksi (OPS): rename repo GitHub, buat subdomain baru, migrasi database
   - Checklist verifikasi pasca-rename (grep, build CSS, smoke test)
   - Estimasi waktu kerja: **6.1 jam total**

### 2. **docs/TASKS.md** (update: +326 baris, total 1128 baris)
   Tambah task baru **Task-RENAME** dengan 6 sub-task terurut:
   - **RENAME-01** (1 jam): BACKEND — Database Schema + Config (agen `koding`)
   - **RENAME-02** (2 jam): FRONTEND — Layout + Views (agen `frontend`)
   - **RENAME-03** (1 jam): DOKUMENTASI — docs/ dan blueprint/ (agen `koding`)
   - **RENAME-04** (0.5 jam): VERIFIKASI + BUILD (agen `koding`/`frontend`)
   - **RENAME-05** (2 jam): OPS — Deployment Produksi (Lead, TIDAK DIKERJAKAN AGEN)
   - **RENAME-06** (0.25 jam): DOKUMENTASI AKHIR — Catat di DECISIONS.md (agen `koding`)

   **Urutan eksekusi wajib:** BACKEND → FRONTEND → DOC → VERIFIKASI → OPS → DOC AKHIR (tidak paralel).

---

## Ringkasan Perubahan Utama

### Branding
- **Nama:** SANTRI → **BUKANWIL**
- **Deskripsi:** "Sistem Aplikasi Notifikasi dan Tamu Religius Integritas" → **"Buku Tamu Kanwil Kemenag Kaltara"**
- **Seed DB:** `judul_aplikasi` = `'SANTRI — Buku Tamu PTSP'` → `'BUKANWIL'`
- **Key DB baru:** `deskripsi_aplikasi` = `'Buku Tamu Kanwil Kemenag Kaltara'` (konfigurabel via menu Pengaturan)

### Identifier Internal
- **Database:** `santri_db` → `bukanwil_db`
- **Cookie session:** `santri_session` → `bukanwil_session`
- **package.json name:** `"santri"` → `"bukanwil"`

### GitHub & Deployment (OPS — Lead)
- **Repo GitHub:** `martinserafim93/santri` → `martinserafim93/bukanwil` (rename via `gh repo rename`)
- **Domain produksi:** `santri.is-best.net` → `bukanwil.is-best.net` (buat subdomain baru di InfinityFree)
- **Migrasi data:** Dump tabel `tamu` dari DB lama, import ke DB baru (JANGAN buang data)

### Fitur Baru
- **Deskripsi aplikasi konfigurabel:** String hardcoded `'Sistem Aplikasi Notifikasi dan Tamu Religius Integritas'` di `app/Views/layouts/publik.php` baris 37 kini diganti dengan **dinamis dari DB** (`$pengaturan['deskripsi_aplikasi']`).
- **Field baru di menu Pengaturan (superadmin):** Input `Deskripsi Aplikasi` — validasi + simpan ke tabel `pengaturan` key `deskripsi_aplikasi`.

---

## Inventori File Terdampak

**Total file:** 28 file (14 BACKEND, 14 FRONTEND, 10 DOC)

### BACKEND (14 file)
1. `database/schema.sql` — komentar header + seed `judul_aplikasi` + tambah key `deskripsi_aplikasi`
2. `app/Config/Database.php` — `'database' => 'bukanwil_db'`
3. `app/Config/Session.php` — `cookieName = 'bukanwil_session'`
4. `app/Controllers/Admin/Pengaturan.php` — tambah validasi + simpan `deskripsi_aplikasi`
5. `app/Controllers/Tamu.php` — docblock `— BUKANWIL`
6. `package.json` — name, description, repository URL

### FRONTEND (14 file)
7. `app/Views/layouts/publik.php` — fallback `'BUKANWIL'` + deskripsi dinamis dari DB
8. `app/Views/layouts/admin.php` — fallback `'BUKANWIL'`
9. `app/Views/tamu/form.php` — docblock `— BUKANWIL`
10. `app/Views/tamu/sukses.php` — docblock `— BUKANWIL`
11. `app/Views/admin/tamu/index.php` — docblock `— BUKANWIL`
12. `app/Views/admin/tamu/detail.php` — docblock `— BUKANWIL`
13. `app/Views/admin/pengaturan/index.php` — docblock + tambah field `deskripsi_aplikasi`
14. `app/Views/admin/login.php` — docblock `— BUKANWIL`
15. `app/Views/admin/admin-user/index.php` — docblock `— BUKANWIL`
16. `app/Views/admin/admin-user/form.php` — docblock `— BUKANWIL`
17. `app/Views/admin/admin-user/hapus.php` — docblock `— BUKANWIL`
18. `themes/app.css` — komentar header `BUKANWIL Design System`

### DOKUMENTASI (10 file)
19. `docs/PRD.md` — judul + subtitle + penyebutan SANTRI
20. `docs/SPEC.md` — judul + subtitle + contoh config DB
21. `docs/TASKS.md` — judul + contoh command DB (SUDAH DIUPDATE + tambah Task-RENAME)
22. `docs/DECISIONS.md` — judul (+ nanti tambah entry rebrand di RENAME-06)
23. `docs/DESIGN.md` — judul + penyebutan SANTRI
24. `docs/CEKLIS-VERIFIKASI.md` — judul
25. `blueprint/project-plan.md` — judul + subtitle + penyebutan SANTRI
26. `blueprint/build-plan.md` — judul
27. `blueprint/context/project-overview.md` — judul + subtitle + penyebutan SANTRI
28. `docs/preview.html` — title tag

---

## Estimasi Waktu Kerja

| Task                          | Estimasi | Eksekutor  |
|-------------------------------|----------|------------|
| RENAME-01 (BACKEND)           | 1 jam    | `koding`   |
| RENAME-02 (FRONTEND)          | 2 jam    | `frontend` |
| RENAME-03 (DOKUMENTASI)       | 1 jam    | `koding`   |
| RENAME-04 (VERIFIKASI + BUILD)| 0.5 jam  | `koding` / `frontend` |
| RENAME-05 (OPS — Lead)        | 2 jam    | **Lead**   |
| RENAME-06 (DOC AKHIR)         | 0.25 jam | `koding`   |
| **TOTAL**                     | **6.75 jam** | —      |

**Catatan:**
- Tidak ada pekerjaan paralel — urutan ketat BACKEND → FRONTEND → DOC → VERIFIKASI → OPS → DOC AKHIR.
- OPS (RENAME-05) dilakukan oleh **Lead** setelah verifikasi lokal selesai, **BUKAN** oleh agen.

---

## Batasan & Prinsip YAGNI

✅ **Yang DIKERJAKAN:**
- Rename lengkap tampilan, identifier, dokumen.
- Deskripsi aplikasi menjadi konfigurabel via menu Pengaturan.
- Migrasi data tamu lama (OPS — Lead).

❌ **Yang TIDAK DIKERJAKAN (di luar scope):**
- Fitur tambahan (QR code, multi-bahasa, notif email).
- Perubahan desain/UI/UX (tetap sama, hanya branding).
- Refactoring kode yang tidak terkait rename.

---

## Checklist Verifikasi (untuk Lead sebelum approve)

- [ ] **SPEC-RENAME-BUKANWIL.md** dibaca lengkap — pemetaan file-by-file presisi?
- [ ] **TASKS.md** Task-RENAME jelas, urutan eksekusi ketat, estimasi realistis?
- [ ] Inventori 28 file lengkap — tidak ada file yang terlewat?
- [ ] Fitur baru (deskripsi konfigurabel) jelas — controller, view, DB seed?
- [ ] Langkah OPS deployment produksi lengkap — rename repo, subdomain, migrasi DB?
- [ ] Batasan YAGNI jelas — tidak ada over-engineering?

---

## Langkah Berikutnya (untuk Lead)

1. **Review dokumen ini + SPEC-RENAME-BUKANWIL.md.**
2. **Bila approved:** Delegasikan ke agen `koding` dan `frontend` sesuai Task-RENAME di TASKS.md.
3. **Setelah RENAME-04 selesai (verifikasi lokal):** Eksekusi RENAME-05 (OPS deployment produksi) oleh Lead.
4. **Setelah OPS selesai:** Agen `koding` eksekusi RENAME-06 (catat di DECISIONS.md).

---

**END OF RINGKASAN**
