# SPEC-RENAME-BUKANWIL.md — Spesifikasi Teknis Full Rebrand

**Target:** Rename aplikasi SANTRI → BUKANWIL  
**Tanggal:** 2026-10-21  
**Status:** Dokumen Spesifikasi (review Lead sebelum eksekusi)  
**Scope:** Frontend, Backend, Dokumentasi, dan OPS (deployment)

---

## 1. Ringkasan Perubahan

| Aspek                     | Lama                                                      | Baru                               |
|---------------------------|-----------------------------------------------------------|-------------------------------------|
| Nama aplikasi             | SANTRI                                                    | BUKANWIL                            |
| Kepanjangan / Deskripsi   | Sistem Aplikasi Notifikasi dan Tamu Religius Integritas  | Buku Tamu Kanwil Kemenag Kaltara    |
| Seed `judul_aplikasi`     | `'SANTRI — Buku Tamu PTSP'`                               | `'BUKANWIL'`                        |
| Key DB baru               | —                                                         | `pengaturan.deskripsi_aplikasi`     |
| Fallback hardcoded        | `'SANTRI'` (4 titik layout)                               | `'BUKANWIL'`                        |
| Cookie session            | `'santri_session'`                                        | `'bukanwil_session'`                |
| Database lokal            | `'santri_db'`                                             | `'bukanwil_db'`                     |
| Repo GitHub               | `martinserafim93/santri`                                  | `martinserafim93/bukanwil`          |
| Domain deploy             | `santri.is-best.net`                                      | `bukanwil.is-best.net`              |
| package.json name         | `"santri"`                                                | `"bukanwil"`                        |

**Prinsip:**
- Deskripsi aplikasi (kepanjangan) menjadi **konfigurabel** lewat menu Pengaturan superadmin, bukan hardcoded.
- String hardcoded fallback (`'SANTRI'` → `'BUKANWIL'`) hanya sebagai default bila DB key kosong.
- Semua docblock komentar `— SANTRI` diganti `— BUKANWIL`.

---

## 2. Pemetaan File — Detail Perubahan

Setiap baris berikut menunjukkan:
- **Path file** → **String lama** → **String baru**
- **TAG**: `[FRONTEND]`, `[BACKEND]`, atau `[OPS]` (dokumentasi tag `[DOC]`)

---

### 2.1 DATABASE — schema.sql

**File:** `database/schema.sql`

| Baris | String Lama                                                    | String Baru                                    | Tag        |
|-------|----------------------------------------------------------------|------------------------------------------------|------------|
| 2     | `-- SANTRI — Skema Database MySQL 8.0 / MariaDB`               | `-- BUKANWIL — Skema Database MySQL 8.0 / MariaDB` | [BACKEND]  |
| 3     | `-- Sistem Aplikasi Notifikasi dan Tamu Religius Integritas`  | `-- Buku Tamu Kanwil Kemenag Kaltara`           | [BACKEND]  |
| 109   | `('judul_aplikasi', 'SANTRI — Buku Tamu PTSP'),`              | `('judul_aplikasi', 'BUKANWIL'),`               | [BACKEND]  |

**Tambahan baris baru (setelah baris 109, sebelum baris 110):**
```sql
    ('deskripsi_aplikasi', 'Buku Tamu Kanwil Kemenag Kaltara'),
```

**Hasil akhir seed `pengaturan` (baris 108-112):**
```sql
INSERT INTO `pengaturan` (`key`, `value`) VALUES
    ('judul_aplikasi',       'BUKANWIL'),
    ('deskripsi_aplikasi',   'Buku Tamu Kanwil Kemenag Kaltara'),
    ('nama_instansi_ptsp',   'Kantor Wilayah Kementerian Agama Provinsi Kalimantan Utara'),
    ('teks_header',          'Selamat datang. Silakan isi buku tamu sebelum melanjutkan pelayanan.')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
```

---

### 2.2 KONFIGURASI — app/Config/*.php

#### 2.2.1 app/Config/Database.php

**Baris 33:** `'database' => 'santri_db',` → `'database' => 'bukanwil_db',`  
**Tag:** `[BACKEND]`

#### 2.2.2 app/Config/Session.php

**Baris 34:** `public string $cookieName = 'santri_session';` → `public string $cookieName = 'bukanwil_session';`  
**Tag:** `[BACKEND]`

---

### 2.3 LAYOUT VIEWS — Hardcoded fallback + deskripsi

#### 2.3.1 app/Views/layouts/publik.php

| Baris | String Lama                                           | String Baru                                       | Tag         |
|-------|-------------------------------------------------------|---------------------------------------------------|-------------|
| 3     | `* Layout Publik — SANTRI`                            | `* Layout Publik — BUKANWIL`                      | [FRONTEND]  |
| 12    | `— <?= esc($pengaturan['judul_aplikasi'] ?? 'SANTRI') ?>` | `— <?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?>` | [FRONTEND]  |
| 34    | `<?= esc($pengaturan['judul_aplikasi'] ?? 'SANTRI') ?>`   | `<?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?>`   | [FRONTEND]  |
| 37    | `<?= esc('Sistem Aplikasi Notifikasi dan Tamu Religius Integritas') ?>` | `<?= esc($pengaturan['deskripsi_aplikasi'] ?? 'Buku Tamu Kanwil Kemenag Kaltara') ?>` | [FRONTEND]  |

**CATATAN baris 37:**  
String hardcoded `'Sistem Aplikasi Notifikasi dan Tamu Religius Integritas'` **diganti** menjadi **dinamis**:  
`<?= esc($pengaturan['deskripsi_aplikasi'] ?? 'Buku Tamu Kanwil Kemenag Kaltara') ?>`

#### 2.3.2 app/Views/layouts/admin.php

| Baris | String Lama                                                | String Baru                                               | Tag         |
|-------|------------------------------------------------------------|-----------------------------------------------------------|-------------|
| 3     | `* Layout Admin — SANTRI`                                  | `* Layout Admin — BUKANWIL`                               | [FRONTEND]  |
| 24    | `— <?= esc($pengaturan['judul_aplikasi'] ?? 'SANTRI') ?>`  | `— <?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?>` | [FRONTEND]  |
| 50    | `<?= esc($pengaturan['judul_aplikasi'] ?? 'SANTRI') ?>`    | `<?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?>`   | [FRONTEND]  |

---

### 2.4 VIEWS (docblock komentar `— SANTRI` → `— BUKANWIL`)

Semua file view berikut memiliki docblock header di baris 3: `* <Deskripsi> — SANTRI`  
Ganti `— SANTRI` menjadi `— BUKANWIL`.

| Path                                       | Baris | String Lama                              | String Baru                                | Tag        |
|--------------------------------------------|-------|------------------------------------------|--------------------------------------------|------------|
| `app/Views/tamu/form.php`                  | 3     | `* Public guestbook form — SANTRI`       | `* Public guestbook form — BUKANWIL`       | [FRONTEND] |
| `app/Views/tamu/sukses.php`                | 3     | `* Public guestbook success page — SANTRI` | `* Public guestbook success page — BUKANWIL` | [FRONTEND] |
| `app/Views/admin/tamu/index.php`           | 3     | `* Admin guest list — SANTRI`            | `* Admin guest list — BUKANWIL`            | [FRONTEND] |
| `app/Views/admin/tamu/detail.php`          | 3     | `* Admin detail tamu — SANTRI`           | `* Admin detail tamu — BUKANWIL`           | [FRONTEND] |
| `app/Views/admin/pengaturan/index.php`     | 3     | `* Admin settings form — SANTRI`         | `* Admin settings form — BUKANWIL`         | [FRONTEND] |
| `app/Views/admin/login.php`                | 3     | `* Standalone admin login page — SANTRI` | `* Standalone admin login page — BUKANWIL` | [FRONTEND] |
| `app/Views/admin/admin-user/index.php`     | 3     | `* Admin user list — SANTRI`             | `* Admin user list — BUKANWIL`             | [FRONTEND] |
| `app/Views/admin/admin-user/form.php`      | 3     | `* Admin user form — SANTRI`             | `* Admin user form — BUKANWIL`             | [FRONTEND] |
| `app/Views/admin/admin-user/hapus.php`     | 3     | `* Delete admin confirmation — SANTRI`   | `* Delete admin confirmation — BUKANWIL`   | [FRONTEND] |

---

### 2.5 CONTROLLER (docblock komentar)

| Path                                | Baris | String Lama                                      | String Baru                                        | Tag        |
|-------------------------------------|-------|--------------------------------------------------|----------------------------------------------------|------------|
| `app/Controllers/Tamu.php`          | 8     | `* Public guestbook form controller — SANTRI`    | `* Public guestbook form controller — BUKANWIL`    | [BACKEND]  |

**CATATAN:** Controller lain (Admin/*) tidak memiliki docblock dengan string `— SANTRI` (sudah diverifikasi).

---

### 2.6 PENGATURAN FORM — Tambah field `deskripsi_aplikasi`

#### 2.6.1 app/Views/admin/pengaturan/index.php

**Tambah field input baru** (setelah field `judul_aplikasi`, sebelum field `nama_instansi_ptsp`):

```php
        <div>
            <label for="deskripsi_aplikasi" class="form-label"><?= esc('Deskripsi Aplikasi') ?></label>
            <input type="text" id="deskripsi_aplikasi" name="deskripsi_aplikasi" value="<?= esc((string) old('deskripsi_aplikasi', $pengaturan['deskripsi_aplikasi'] ?? '')) ?>" class="form-field" required>
            <?php if (session('errors.deskripsi_aplikasi')): ?>
                <p class="text-danger text-sm mt-1"><?= esc((string) session('errors.deskripsi_aplikasi')) ?></p>
            <?php endif; ?>
            <p class="text-xs text-ink-muted mt-1">Deskripsi singkat yang tampil di bawah judul pada halaman publik.</p>
        </div>
```

**Lokasi insert:** Setelah `</div>` penutup field `judul_aplikasi` (sekitar baris 45), sebelum `<div>` pembuka field `nama_instansi_ptsp` (baris 47).

**Tag:** `[FRONTEND]`

#### 2.6.2 app/Controllers/Admin/Pengaturan.php

**Tambah validasi** di method `simpan()`, dalam array `$rules` (baris 49-53):

```php
        $rules = [
            'judul_aplikasi'       => 'required|max_length[255]',
            'deskripsi_aplikasi'   => 'required|max_length[255]',
            'nama_instansi_ptsp'   => 'required|max_length[255]',
            'teks_header'          => 'required',
        ];
```

**Tambah key di array `$data`** (baris 61-65):

```php
        $data = [
            'judul_aplikasi'       => (string) $this->request->getPost('judul_aplikasi'),
            'deskripsi_aplikasi'   => (string) $this->request->getPost('deskripsi_aplikasi'),
            'nama_instansi_ptsp'   => (string) $this->request->getPost('nama_instansi_ptsp'),
            'teks_header'          => (string) $this->request->getPost('teks_header'),
        ];
```

**Tag:** `[BACKEND]`

---

### 2.7 CSS (komentar header)

**File:** `themes/app.css`

| Baris | String Lama                                      | String Baru                                        | Tag         |
|-------|--------------------------------------------------|----------------------------------------------------|-------------|
| 4     | `   SANTRI Design System — Tailwind CSS v4 (CSS-first)` | `   BUKANWIL Design System — Tailwind CSS v4 (CSS-first)` | [FRONTEND]  |

---

### 2.8 PACKAGE.JSON

**File:** `package.json`

| Baris | String Lama                                                                                  | String Baru                                                                                     | Tag      |
|-------|----------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------|----------|
| 2     | `"name": "santri",`                                                                          | `"name": "bukanwil",`                                                                           | [BACKEND]|
| 4     | `"description": "SANTRI — Sistem Aplikasi Notifikasi dan Tamu Religius Integritas (CodeIgniter 4 + Tailwind CSS)",` | `"description": "BUKANWIL — Buku Tamu Kanwil Kemenag Kaltara (CodeIgniter 4 + Tailwind CSS)",` | [BACKEND]|
| 12    | `"url": "git+https://github.com/martinserafim93/santri.git"`                                 | `"url": "git+https://github.com/martinserafim93/bukanwil.git"`                                 | [BACKEND]|

---

### 2.9 DOKUMENTASI (docs/*.md dan blueprint/*.md)

Ganti semua kemunculan:
- `SANTRI` → `BUKANWIL`
- `Sistem Aplikasi Notifikasi dan Tamu Religius Integritas` → `Buku Tamu Kanwil Kemenag Kaltara`

#### 2.9.1 docs/PRD.md

| Baris | String Lama                                                      | String Baru                                        | Tag   |
|-------|------------------------------------------------------------------|----------------------------------------------------|-------|
| 1     | `# PRD — SANTRI`                                                 | `# PRD — BUKANWIL`                                 | [DOC] |
| 2     | `## Sistem Aplikasi Notifikasi dan Tamu Religius Integritas`    | `## Buku Tamu Kanwil Kemenag Kaltara`              | [DOC] |
| 15    | `SANTRI hadir sebagai pengganti Google Form…`                    | `BUKANWIL hadir sebagai pengganti Google Form…`    | [DOC] |

#### 2.9.2 docs/SPEC.md

| Baris | String Lama                                                      | String Baru                                        | Tag   |
|-------|------------------------------------------------------------------|----------------------------------------------------|-------|
| 1     | `# SPEC.md — Spesifikasi Teknis SANTRI`                          | `# SPEC.md — Spesifikasi Teknis BUKANWIL`          | [DOC] |
| 2     | `## Sistem Aplikasi Notifikasi dan Tamu Religius Integritas`    | `## Buku Tamu Kanwil Kemenag Kaltara`              | [DOC] |
| 42    | `'database' => 'santri_db',`                                     | `'database' => 'bukanwil_db',`                     | [DOC] |

#### 2.9.3 docs/TASKS.md

| Baris | String Lama                     | String Baru                      | Tag   |
|-------|---------------------------------|----------------------------------|-------|
| 2     | `## Aplikasi SANTRI v1.0`       | `## Aplikasi BUKANWIL v1.0`      | [DOC] |
| 42    | `'database' => 'santri_db',`    | `'database' => 'bukanwil_db',`   | [DOC] |
| 47    | `mysql -u db_user -p santri_db < database/schema.sql` | `mysql -u db_user -p bukanwil_db < database/schema.sql` | [DOC] |
| 50    | `mysql -u db_user -p santri_db -e "UPDATE admin SET password='<hash>' WHERE username='superadmin';"` | `mysql -u db_user -p bukanwil_db -e "UPDATE admin SET password='<hash>' WHERE username='superadmin';"` | [DOC] |

#### 2.9.4 docs/DECISIONS.md

| Baris | String Lama                          | String Baru                           | Tag   |
|-------|--------------------------------------|---------------------------------------|-------|
| 1     | `# Keputusan Lead — SANTRI`          | `# Keputusan Lead — BUKANWIL`         | [DOC] |

#### 2.9.5 docs/DESIGN.md

| Baris | String Lama                                                      | String Baru                                           | Tag   |
|-------|------------------------------------------------------------------|-------------------------------------------------------|-------|
| 1     | `# SANTRI Design System`                                         | `# BUKANWIL Design System`                            | [DOC] |
| 3     | `…pengembangan SANTRI.`                                          | `…pengembangan BUKANWIL.`                             | [DOC] |
| 9     | `SANTRI adalah aplikasi internal…`                               | `BUKANWIL adalah aplikasi internal…`                  | [DOC] |
| 90    | `…(publik: "Kementerian Agama RI / Kanwil Kemenag"; admin: "SANTRI / Admin Panel")…` | `…(publik: "Kementerian Agama RI / Kanwil Kemenag"; admin: "BUKANWIL / Admin Panel")…` | [DOC] |

#### 2.9.6 docs/CEKLIS-VERIFIKASI.md

| Baris | String Lama                              | String Baru                               | Tag   |
|-------|------------------------------------------|-------------------------------------------|-------|
| 1     | `# Ceklis Verifikasi per Fase — SANTRI` | `# Ceklis Verifikasi per Fase — BUKANWIL`| [DOC] |

#### 2.9.7 blueprint/project-plan.md

| Baris | String Lama                                                      | String Baru                                        | Tag   |
|-------|------------------------------------------------------------------|----------------------------------------------------|-------|
| 1     | `# Project Plan — SANTRI v1.0`                                   | `# Project Plan — BUKANWIL v1.0`                   | [DOC] |
| 3     | `**Sistem Aplikasi Notifikasi dan Tamu Religius Integritas**`   | `**Buku Tamu Kanwil Kemenag Kaltara**`             | [DOC] |
| 17    | `SANTRI menggantikan Google Form…`                               | `BUKANWIL menggantikan Google Form…`               | [DOC] |

#### 2.9.8 blueprint/build-plan.md

| Baris | String Lama                     | String Baru                      | Tag   |
|-------|---------------------------------|----------------------------------|-------|
| 1     | `# Build Plan — SANTRI v1.0`    | `# Build Plan — BUKANWIL v1.0`   | [DOC] |

#### 2.9.9 blueprint/context/project-overview.md

| Baris | String Lama                                                      | String Baru                                        | Tag   |
|-------|------------------------------------------------------------------|----------------------------------------------------|-------|
| 1     | `# Project Overview — SANTRI`                                    | `# Project Overview — BUKANWIL`                    | [DOC] |
| 3     | `**Sistem Aplikasi Notifikasi dan Tamu Religius Integritas**`   | `**Buku Tamu Kanwil Kemenag Kaltara**`             | [DOC] |
| 17    | `SANTRI hadir sebagai solusi pengganti…`                         | `BUKANWIL hadir sebagai solusi pengganti…`         | [DOC] |

#### 2.9.10 docs/preview.html

| Baris | String Lama                     | String Baru                      | Tag   |
|-------|---------------------------------|----------------------------------|-------|
| 6     | `<title>Form Buku Tamu — SANTRI</title>` | `<title>Form Buku Tamu — BUKANWIL</title>` | [DOC] |

---

## 3. Operasi Deployment (DI LUAR REPO — eksekusi oleh Lead)

**TAG:** `[OPS]`

### 3.1 Rename Repo GitHub

```bash
gh repo rename bukanwil --repo martinserafim93/santri
```

- URL lama `github.com/martinserafim93/santri` akan redirect otomatis ke `…/bukanwil`.
- Clone/fetch yang sudah ada tetap berfungsi.

### 3.2 Buat Subdomain Baru `bukanwil.is-best.net`

1. Login ke panel InfinityFree.
2. Buat subdomain baru `bukanwil.is-best.net`, arahkan root ke folder `htdocs/` (atau folder baru).
3. Upload ulang repo atau sync dari GitHub.

### 3.3 Update Konfigurasi Produksi

**File:** `app/Config/App.php`

```php
public string $baseURL = 'https://bukanwil.is-best.net/';
```

**File:** `app/Config/Database.php`

```php
public array $default = [
    'hostname' => 'sql123.infinityfree.com',  // sesuaikan
    'username' => 'epiz_xxxxx',                // akun DB produksi
    'password' => 'xxxxxxxx',
    'database' => 'epiz_xxxxx_bukanwil',       // nama DB baru
    'DBDriver' => 'MySQLi',
    'charset'  => 'utf8mb4',
    'DBCollat' => 'utf8mb4_unicode_ci',
    // sisa konfigurasi sama
];
```

### 3.4 Migrasi Data Tamu dari `santri_db` ke `bukanwil_db`

**PENTING:** Jangan buang data tamu yang sudah ada.

**Langkah:**

1. **Dump tabel `tamu` dari database lama:**
   ```bash
   mysqldump -u user -p santri_db tamu > tamu_backup.sql
   ```

2. **Buat database baru `bukanwil_db`:**
   ```sql
   CREATE DATABASE bukanwil_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Import skema baru:**
   ```bash
   mysql -u user -p bukanwil_db < database/schema.sql
   ```

4. **Import data tamu lama:**
   ```bash
   mysql -u user -p bukanwil_db < tamu_backup.sql
   ```

5. **Verifikasi:**
   ```sql
   SELECT COUNT(*) FROM bukanwil_db.tamu;
   SELECT * FROM bukanwil_db.pengaturan;
   ```

6. **Update seed `pengaturan` bila perlu:**
   ```sql
   UPDATE pengaturan SET value = 'BUKANWIL' WHERE `key` = 'judul_aplikasi';
   INSERT INTO pengaturan (`key`, `value`) VALUES ('deskripsi_aplikasi', 'Buku Tamu Kanwil Kemenag Kaltara')
   ON DUPLICATE KEY UPDATE value = VALUES(value);
   ```

### 3.5 Update .htaccess (bila perlu)

Bila `.htaccess` di `public/` memiliki hardcoded reference ke `santri`, ganti dengan `bukanwil` atau gunakan variable dinamis. Biasanya CI4 default `.htaccess` tidak memerlukan perubahan.

### 3.6 Hapus Session Lama (opsional)

Cookie `santri_session` dari subdomain lama tidak akan terbaca di subdomain baru (domain berbeda).  
Instruksikan admin untuk **logout dari `santri.is-best.net`**, lalu **login ulang di `bukanwil.is-best.net`**.

---

## 4. Checklist Verifikasi Pasca-Rename

Setelah semua task frontend & backend selesai:

- [ ] `grep -ri "SANTRI" app/ public/ database/ docs/ blueprint/ themes/` tidak mengembalikan hasil (kecuali `AGENTS.md`, `CLAUDE.md`, `.claude/`, dan file yang memang dokumentasi proses rename ini).
- [ ] `grep -ri "Religius" app/ public/ database/ docs/ blueprint/` tidak mengembalikan hasil.
- [ ] `grep -ri "santri_session" app/` hanya di `app/Config/Session.php` (sudah diganti).
- [ ] `grep -ri "santri_db" app/ database/` hanya di `app/Config/Database.php` dan `database/schema.sql` (sudah diganti).
- [ ] Build CSS: `npm run build:css` tanpa error.
- [ ] Akses halaman publik: judul `BUKANWIL`, deskripsi `Buku Tamu Kanwil Kemenag Kaltara` tampil di header.
- [ ] Login admin: sidebar dan topbar menampilkan `BUKANWIL` (dari DB atau fallback).
- [ ] Menu Pengaturan (superadmin): field `Deskripsi Aplikasi` ada, bisa disimpan, dan muncul di halaman publik.
- [ ] Cookie session bernama `bukanwil_session` (cek via DevTools → Application → Cookies).
- [ ] Database lokal `bukanwil_db` terhubung tanpa error.

---

## 5. Estimasi Waktu Kerja

| Task                                                    | Estimasi Jam | Eksekutor  |
|---------------------------------------------------------|--------------|------------|
| **BACKEND — database/schema.sql (seed + komentar)**     | 0.25         | `koding`   |
| **BACKEND — app/Config/Database.php + Session.php**     | 0.25         | `koding`   |
| **BACKEND — app/Controllers/Admin/Pengaturan.php**      | 0.5          | `koding`   |
| **FRONTEND — app/Views/layouts/*.php (2 file)**         | 0.5          | `frontend` |
| **FRONTEND — app/Views/tamu/*.php (2 file)**            | 0.25         | `frontend` |
| **FRONTEND — app/Views/admin/**/*.php (6 file)**        | 0.5          | `frontend` |
| **FRONTEND — app/Views/admin/pengaturan/index.php (tambah field)** | 0.5 | `frontend` |
| **FRONTEND — themes/app.css (komentar header)**         | 0.1          | `frontend` |
| **FRONTEND — app/Controllers/Tamu.php (docblock)**      | 0.1          | `frontend` |
| **BACKEND — package.json**                              | 0.1          | `koding`   |
| **DOC — docs/*.md (5 file)**                            | 0.5          | `koding`   |
| **DOC — blueprint/*.md (3 file)**                       | 0.5          | `koding`   |
| **OPS — Rename repo GitHub**                            | 0.1          | Lead       |
| **OPS — Subdomain baru + upload + config produksi**     | 1.0          | Lead       |
| **OPS — Migrasi database (dump/import/verifikasi)**     | 0.5          | Lead       |
| **VERIFIKASI — grep, build CSS, smoke test**            | 0.5          | `koding`   |
| **TOTAL**                                               | **6.1 jam**  | —          |

**Catatan:**
- `koding` menangani BACKEND, package.json, DOC.
- `frontend` menangani FRONTEND (view + CSS).
- Lead menangani OPS (GitHub, deploy, DB produksi).
- Tidak ada pekerjaan paralel — urutan ketat: BACKEND → FRONTEND → DOC → OPS → VERIFIKASI.

---

## 6. Catatan Penting

1. **Jangan ubah file kode aplikasi** di luar yang tercantum di dokumen ini.
2. **Jangan push, rename repo, atau deploy** saat mengerjakan task ini — itu tugas Lead setelah verifikasi lokal selesai.
3. **Deskripsi aplikasi (`deskripsi_aplikasi`) wajib konfigurabel** — bukan hardcoded. Fallback hardcoded hanya sebagai default bila DB key kosong.
4. **Migrasi data tamu lama (OPS)** wajib dilakukan — jangan buang data yang sudah ada.
5. **Batasan YAGNI:** Tidak ada fitur tambahan (misal QR code, multi-bahasa, notif email) — fokus rename saja.

---

**END OF SPEC**
