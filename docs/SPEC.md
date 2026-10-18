# SPEC.md — Spesifikasi Teknis SANTRI
## Sistem Aplikasi Notifikasi dan Tamu Religius Integritas

**Versi:** 1.0.0
**Tanggal:** 2026-10-18
**Stack:** CodeIgniter 4.x + PHP 8.3, MySQL 8.0, Tailwind CSS v4, JavaScript native

---

## 1. Konfigurasi CI4 — File yang Wajib Diedit

Karena hosting InfinityFree tidak mendukung dotenv, semua konfigurasi ditulis
langsung di `app/Config/*.php`. TIDAK ADA `.env`.

### app/Config/App.php

```php
public string $baseURL     = 'https://tamu.namadomain.com/';  // sesuaikan
public string $indexPage   = '';                               // wajib kosong
public string $appTimezone = 'Asia/Makassar';                  // WITA UTC+8
public string $charset     = 'UTF-8';
public string $defaultLocale    = 'id';
public bool   $negotiateLocale  = false;
public bool   $CSRFProtection   = true;
public string $CSRFTokenName    = 'csrf_token';
public string $CSRFCookieName   = 'csrf_cookie';
public int    $CSRFExpire       = 7200;
public bool   $CSRFRegenerate   = true;
public bool   $CSRFExcludeURIs  = [];   // kosong = semua POST dilindungi
// Mode produksi: matikan display errors
public string $CI_ENVIRONMENT = 'production';
```

### app/Config/Database.php

```php
public array $default = [
    'DSN'          => '',
    'hostname'     => 'localhost',
    'username'     => 'db_user',      // ganti
    'password'     => 'db_password',  // ganti
    'database'     => 'santri_db',    // ganti
    'DBDriver'     => 'MySQLi',
    'DBPrefix'     => '',
    'pConnect'     => false,
    'DBDebug'      => false,          // false di produksi
    'charset'      => 'utf8mb4',
    'DBCollat'     => 'utf8mb4_unicode_ci',
    'swapPre'      => '',
    'encrypt'      => false,
    'compress'     => false,
    'strictOn'     => false,
    'failover'     => [],
    'port'         => 3306,
];
```

### app/Config/Session.php

```php
public string $driver            = 'CodeIgniter\Session\Handlers\FileHandler';
public string $cookieName        = 'santri_session';
public int    $expiration        = 7200;       // 2 jam
public string $savePath          = WRITEPATH . 'session';
public bool   $matchIP           = false;
public int    $timeToUpdate      = 300;
public bool   $regenerateDestroy = true;
```

### app/Config/Cookie.php

```php
public string $prefix   = '';
public bool   $secure   = true;    // PERTANYAAN UNTUK LEAD: set false bila domain tidak HTTPS
public bool   $httponly  = true;
public string $samesite  = 'Lax';
```

### app/Config/Encryption.php

```php
// Generate key dengan: php spark key:generate --show
// Salin hasilnya dan paste langsung di sini
public string|array $key = 'hex2bin:XXXXXXXX...';   // ganti dengan hasil spark
public string $driver = 'OpenSSL';
```

### app/Config/Filters.php

```php
// Tambahkan alias filter
public array $aliases = [
    'csrf'  => \CodeIgniter\Filters\CSRF::class,
    'auth'  => \App\Filters\AuthFilter::class,
];

// Terapkan CSRF global untuk POST, auth untuk semua route /admin/* kecuali login
public array $filters = [
    'auth' => [
        'before' => ['admin/*'],
        'except' => ['admin/login'],
    ],
];
```

---

## 2. Skema Database

Lihat DDL lengkap di `database/schema.sql`. Ringkasan di bawah.

### Tabel: tamu

| Kolom           | Tipe                           | Null | Default           | Keterangan                        |
|-----------------|--------------------------------|------|-------------------|-----------------------------------|
| id              | BIGINT UNSIGNED AUTO_INCREMENT | NO   | —                 | PK                                |
| nama            | VARCHAR(255)                   | NO   | —                 |                                   |
| jenis_kelamin   | ENUM('Laki-laki','Perempuan')  | NO   | —                 |                                   |
| asal_pengunjung | ENUM('perorangan','instansi')  | NO   | —                 |                                   |
| nama_instansi   | VARCHAR(255)                   | YES  | NULL              | Wajib diisi bila asal = instansi  |
| lat             | DECIMAL(10,8)                  | YES  | NULL              | Koordinat lintang                 |
| lng             | DECIMAL(11,8)                  | YES  | NULL              | Koordinat bujur                   |
| alamat          | TEXT                           | NO   | —                 |                                   |
| no_hp           | VARCHAR(20)                    | NO   | —                 | Format HP/WA Indonesia            |
| keperluan       | TEXT                           | NO   | —                 |                                   |
| foto            | VARCHAR(500)                   | YES  | NULL              | Path relatif dari public/         |
| is_read         | TINYINT(1) UNSIGNED            | NO   | 0                 | 0=belum dibaca, 1=sudah           |
| created_at      | DATETIME                       | NO   | CURRENT_TIMESTAMP |                                   |

Index: PRIMARY(id), idx_tamu_created_at, idx_tamu_is_read, idx_tamu_jenis_kelamin,
       idx_tamu_asal

### Tabel: admin

| Kolom      | Tipe                             | Null | Default           | Keterangan               |
|------------|----------------------------------|------|-------------------|--------------------------|
| id         | INT UNSIGNED AUTO_INCREMENT      | NO   | —                 | PK                       |
| nama       | VARCHAR(255)                     | NO   | —                 |                          |
| username   | VARCHAR(100)                     | NO   | —                 | UNIQUE                   |
| password   | VARCHAR(255)                     | NO   | —                 | bcrypt hash              |
| role       | ENUM('superadmin','admin')       | NO   | 'admin'           |                          |
| created_at | DATETIME                         | NO   | CURRENT_TIMESTAMP |                          |
| updated_at | DATETIME                         | NO   | CURRENT_TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP |

Index: PRIMARY(id), uq_admin_username

### Tabel: pengaturan

| Kolom | Tipe                        | Null | Keterangan  |
|-------|-----------------------------|------|-------------|
| id    | INT UNSIGNED AUTO_INCREMENT | NO   | PK          |
| key   | VARCHAR(100)                | NO   | UNIQUE      |
| value | TEXT                        | NO   |             |

Key seed: judul_aplikasi, nama_instansi_ptsp, teks_header

---

## 3. Struktur Direktori MVC

```
santri/
├── app/
│   ├── Config/
│   │   ├── App.php
│   │   ├── Cookie.php
│   │   ├── Database.php
│   │   ├── Encryption.php
│   │   ├── Filters.php
│   │   ├── Routes.php
│   │   └── Session.php
│   ├── Controllers/
│   │   ├── BaseController.php      (extend CI4 default; inject $pengaturan + $unreadCount)
│   │   ├── Tamu.php                (form publik: index, submit, sukses)
│   │   └── Admin/
│   │       ├── Auth.php            (login, doLogin, logout)
│   │       ├── Dashboard.php       (index)
│   │       ├── TamuAdmin.php       (index, detail, tandaiBaca, exportCsv)
│   │       ├── AdminUser.php       (index, buat, simpan, edit, update, konfirmasiHapus, hapus)
│   │       └── Pengaturan.php      (index, simpan)
│   ├── Filters/
│   │   └── AuthFilter.php          (cek session admin; redirect ke /admin/login)
│   ├── Models/
│   │   ├── TamuModel.php
│   │   ├── AdminModel.php
│   │   └── PengaturanModel.php
│   └── Views/
│       ├── layouts/
│       │   ├── public.php          (layout form tamu: header instansi, slot konten, footer)
│       │   └── admin.php           (layout CMS: sidebar nav, topbar + badge notif, slot)
│       ├── tamu/
│       │   ├── form.php
│       │   └── sukses.php
│       └── admin/
│           ├── login.php
│           ├── dashboard.php
│           ├── tamu/
│           │   ├── index.php
│           │   └── detail.php
│           ├── admin_user/
│           │   ├── index.php
│           │   └── form.php        (dipakai untuk buat + edit; beda title/action)
│           └── pengaturan/
│               └── index.php
├── database/
│   └── schema.sql
├── docs/
│   ├── PRD.md
│   ├── SPEC.md
│   └── TASKS.md
└── public/
    ├── index.php                   (entry point CI4; jangan diubah)
    ├── .htaccess                   (rewrite semua ke index.php)
    ├── uploads/
    │   └── foto/
    │       └── .htaccess           (deny eksekusi PHP + deny akses listing)
    └── assets/
        ├── css/
        │   └── app.css             (output Tailwind v4 compiled)
        └── js/
            ├── form-publik.js      (geolocation + conditional field)
            └── admin.js            (misc: konfirmasi hapus, dll)
```

Catatan: root-level `.htaccess` (bila domain tidak diarahkan langsung ke `public/`)
harus meredirect semua request ke `public/`. Jika hosting sudah mengarahkan document
root ke `public/`, root `.htaccess` tidak diperlukan.

---

## 4. Daftar Route

### Route Publik

| Method | URI           | Controller#Method | Akses   | Keterangan                  |
|--------|---------------|-------------------|---------|-----------------------------|
| GET    | /             | Tamu#index        | Publik  | Tampilkan form buku tamu    |
| POST   | /tamu/submit  | Tamu#submit       | Publik  | Proses submit form          |
| GET    | /tamu/sukses  | Tamu#sukses       | Publik  | Halaman konfirmasi sukses   |

### Route Admin (semua dilindungi AuthFilter, kecuali login)

| Method | URI                            | Controller#Method          | Role      | Keterangan               |
|--------|--------------------------------|----------------------------|-----------|--------------------------|
| GET    | /admin/login                   | Admin\Auth#login           | Publik    | Form login               |
| POST   | /admin/login                   | Admin\Auth#doLogin         | Publik    | Proses login             |
| GET    | /admin/logout                  | Admin\Auth#logout          | Auth      | Logout + destroy session |
| GET    | /admin                         | Admin\Dashboard#index      | Auth      | Dashboard + statistik    |
| GET    | /admin/tamu                    | Admin\TamuAdmin#index      | Auth      | Daftar tamu              |
| GET    | /admin/tamu/export             | Admin\TamuAdmin#exportCsv  | Auth      | Download CSV             |
| GET    | /admin/tamu/(:num)             | Admin\TamuAdmin#detail     | Auth      | Detail tamu              |
| POST   | /admin/tamu/(:num)/baca        | Admin\TamuAdmin#tandaiBaca | Auth      | Tandai dibaca (opsional) |
| POST   | /admin/tamu/baca-semua         | Admin\TamuAdmin#bacaSemua  | Auth      | Tandai semua dibaca      |
| GET    | /admin/admin-user              | Admin\AdminUser#index      | Auth      | Daftar admin             |
| GET    | /admin/admin-user/buat         | Admin\AdminUser#buat       | Auth      | Form tambah admin        |
| POST   | /admin/admin-user/buat         | Admin\AdminUser#simpan     | Auth      | Proses tambah admin      |
| GET    | /admin/admin-user/(:num)/edit  | Admin\AdminUser#edit       | Auth      | Form edit admin          |
| POST   | /admin/admin-user/(:num)/edit  | Admin\AdminUser#update     | Auth      | Proses update admin      |
| GET    | /admin/admin-user/(:num)/hapus | Admin\AdminUser#konfirmasi | superadmin| Halaman konfirmasi hapus |
| POST   | /admin/admin-user/(:num)/hapus | Admin\AdminUser#hapus      | superadmin| Proses hapus admin       |
| GET    | /admin/pengaturan              | Admin\Pengaturan#index     | superadmin| Form pengaturan          |
| POST   | /admin/pengaturan              | Admin\Pengaturan#simpan    | superadmin| Proses simpan pengaturan |

Catatan role: pemeriksaan 'superadmin' dilakukan di dalam controller, bukan di filter
terpisah (YAGNI — cukup satu cek `session()->get('role') !== 'superadmin'`).

---

## 5. Kontrak Input / Output

### 5.1 GET / — Form Buku Tamu

Output ke view:
```
$data = [
    'pengaturan' => [
        'judul_aplikasi'    => string,
        'nama_instansi_ptsp'=> string,
        'teks_header'       => string,
    ],
    'csrf_field' => string,  // CI4 csrf_field() helper
]
```

### 5.2 POST /tamu/submit — Proses Form Publik

Input (multipart/form-data):

| Field           | Tipe    | Required | Keterangan                       |
|-----------------|---------|----------|----------------------------------|
| nama            | string  | Ya       |                                  |
| jenis_kelamin   | string  | Ya       | 'Laki-laki' atau 'Perempuan'     |
| asal_pengunjung | string  | Ya       | 'perorangan' atau 'instansi'     |
| nama_instansi   | string  | Kondisional | Wajib bila asal = instansi    |
| alamat          | string  | Ya       |                                  |
| no_hp           | string  | Ya       | Regex format Indonesia           |
| keperluan       | string  | Ya       |                                  |
| lat             | string  | Tidak    | Desimal; kosong bila tidak ada   |
| lng             | string  | Tidak    | Desimal; kosong bila tidak ada   |
| foto            | file    | Ya       | jpg/jpeg/png/webp, max 2 MB      |
| _hp             | string  | Tidak    | Honeypot — HARUS kosong          |
| csrf_token      | string  | Ya       | CI4 CSRF                         |

Output sukses: `redirect('/tamu/sukses')`

Output gagal validasi: `redirect()->back()->withInput()->with('errors', $validationErrors)`

Output honeypot/rate-limit terdeteksi: `redirect('/tamu/sukses')` (silent drop —
jangan tampilkan pesan error yang memberi tahu bot bahwa ia terdeteksi)

### 5.3 GET /admin — Dashboard

Output ke view:
```
$data = [
    'total_tamu'      => int,
    'tamu_hari_ini'   => int,
    'per_jenis'       => [['label'=>'Laki-laki','count'=>int], ['label'=>'Perempuan','count'=>int]],
    'per_asal'        => [['label'=>'perorangan','count'=>int], ['label'=>'instansi','count'=>int]],
    'top_keperluan'   => [['keperluan'=>string,'count'=>int], ...],  // top 5
    'tren_7hari'      => [['tanggal'=>'Y-m-d','count'=>int], ...],   // 7 hari terakhir
    'unread_count'    => int,
]
```

### 5.4 GET /admin/tamu — Daftar Tamu

Query parameters (semua optional):

| Param        | Tipe   | Keterangan                          |
|--------------|--------|-------------------------------------|
| q            | string | Pencarian: nama, no_hp, nama_instansi|
| tanggal_dari | date   | Filter created_at >= (Y-m-d)        |
| tanggal_sampai | date | Filter created_at <= (Y-m-d 23:59)  |
| jenis_kelamin| string | Filter enum                         |
| asal         | string | Filter asal_pengunjung              |
| keperluan    | string | Filter keperluan (LIKE)             |
| sort         | string | Kolom urut; default: created_at     |
| order        | string | 'asc'/'desc'; default: desc         |
| page         | int    | Halaman pagination; default: 1      |

Output ke view:
```
$data = [
    'tamu'         => array of tamu objects,
    'pager'        => CI4 Pager object,
    'total'        => int,
    'filter_aktif' => array of active filters (untuk ditampilkan + diteruskan ke export),
    'unread_count' => int,
]
```

### 5.5 GET /admin/tamu/export — Export CSV

Query parameters: sama persis dengan GET /admin/tamu (filter diteruskan).

Output:
- Header: `Content-Type: text/csv; charset=UTF-8`
- Header: `Content-Disposition: attachment; filename="tamu_export_YYYYMMDD_HHiiss.csv"`
- Body: BOM UTF-8 (\xEF\xBB\xBF) + baris header kolom + data via fputcsv
- Kolom CSV: id, nama, jenis_kelamin, asal_pengunjung, nama_instansi, alamat, no_hp,
             keperluan, lat, lng, foto, created_at

### 5.6 GET /admin/tamu/(:num) — Detail Tamu

Output ke view:
```
$data = [
    'tamu'         => tamu object (semua kolom),
    'unread_count' => int,
]
```
Efek samping: otomatis set is_read = 1 saat halaman ini dibuka (via TamuModel).

### 5.7 POST /admin/tamu/(:num)/baca — Tandai Dibaca

Input: csrf_token

Output: JSON `{"success": true}` atau `{"success": false, "message": "..."}`
(digunakan oleh fetch() dari JS bila badge diupdate secara AJAX — opsional; fallback
redirect ke referer bila Accept bukan application/json)

### 5.8 Admin Login

POST /admin/login input:
```
username: string (required)
password: string (required)
csrf_token: string
```

Output sukses: `redirect('/admin')`
Output gagal: `redirect()->back()->with('login_error', 'Username atau password salah.')`

Session yang di-set saat login berhasil:
```
session()->set([
    'admin_id'       => int,
    'admin_username' => string,
    'admin_nama'     => string,
    'role'           => string,
    'logged_in'      => true,
]);
session_regenerate_id(true);   // atau CI4 equivalent: session()->regenerate(true)
```

### 5.9 Kelola Admin — CRUD

POST /admin/admin-user/buat input:
```
nama:              string (required, max 255)
username:          string (required, max 100, unik)
password:          string (required, min 8)
password_confirm:  string (required, matches password)
role:              string (required, in: superadmin,admin)
csrf_token:        string
```

POST /admin/admin-user/(:num)/edit input: sama, password bersifat opsional
(bila kosong = tidak ubah password; bila diisi = wajib memenuhi aturan min_length/matches).

### 5.10 Pengaturan

POST /admin/pengaturan input:
```
judul_aplikasi:    string (required, max 255)
nama_instansi_ptsp:string (required, max 255)
teks_header:       string (required)
csrf_token:        string
```

Output sukses: `redirect('/admin/pengaturan')->with('sukses', 'Pengaturan berhasil disimpan.')`

---

## 6. Aturan Validasi

### 6.1 Form Tamu (Publik)

| Field           | Aturan CI4 Validation                                              |
|-----------------|--------------------------------------------------------------------|
| nama            | `required\|max_length[255]`                                        |
| jenis_kelamin   | `required\|in_list[Laki-laki,Perempuan]`                           |
| asal_pengunjung | `required\|in_list[perorangan,instansi]`                           |
| nama_instansi   | `permit_empty\|max_length[255]` + cek manual: required bila asal=instansi |
| alamat          | `required`                                                         |
| no_hp           | `required\|regex_match[/^(0\|62\|\+62)[0-9]{8,13}$/]`             |
| keperluan       | `required`                                                         |
| lat             | `permit_empty\|decimal`                                            |
| lng             | `permit_empty\|decimal`                                            |
| foto            | `uploaded[foto]\|max_size[foto,2048]\|mime_in[foto,image/jpeg,image/png,image/webp]\|ext_in[foto,jpg,jpeg,png,webp]` |

Tambahan non-CI4-Validation (cek manual di controller sebelum validate()):
- Honeypot: `if ($request->getPost('_hp') !== '') { // silent drop }`
- nama_instansi wajib bila asal_pengunjung = 'instansi'

### 6.2 Login Admin

| Field    | Aturan                            |
|----------|-----------------------------------|
| username | `required\|max_length[100]`        |
| password | `required`                         |

Rate limit: counter di session. Setelah 5 kali gagal dalam 15 menit, tampilkan
pesan "Terlalu banyak percobaan. Coba lagi setelah X menit." dan tidak proses login.

### 6.3 CRUD Admin User

| Field            | Aturan                                                   |
|------------------|----------------------------------------------------------|
| nama             | `required\|max_length[255]`                               |
| username         | `required\|max_length[100]\|is_unique[admin.username,id,{id}]` |
| password (buat)  | `required\|min_length[8]`                                 |
| password (edit)  | `permit_empty\|min_length[8]`                             |
| password_confirm | `matches[password]`                                      |
| role             | `required\|in_list[superadmin,admin]`                     |

---

## 7. Checklist Keamanan

Setiap item wajib diverifikasi sebelum aplikasi dinyatakan selesai.

### Database
- [ ] Semua query melalui CI4 Model (QueryBuilder) — tidak ada query string mentah yang mengandung input user tanpa binding.
- [ ] Tidak ada `$db->query("SELECT ... WHERE id = " . $id)` — wajib pakai parameter binding.

### Autentikasi
- [ ] Password disimpan dengan `password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12])`.
- [ ] Verifikasi dengan `password_verify($input, $hash)`.
- [ ] Session di-regenerate saat login berhasil: `session()->regenerate(true)`.
- [ ] Session di-destroy saat logout: `session()->destroy()`.
- [ ] Cookie session: httponly=true, samesite=Lax, secure=true (bila HTTPS).

### CSRF
- [ ] CI4 CSRF protection aktif di App.php.
- [ ] Semua form POST menyertakan `<?= csrf_field() ?>`.
- [ ] CSRF token diverifikasi sebelum memproses data POST.

### Upload File
- [ ] Mime type dicek dengan `finfo_file()` (bukan hanya ekstensi).
- [ ] Ekstensi whitelist: jpg, jpeg, png, webp.
- [ ] Ukuran maksimum: 2 MB (2048 KB).
- [ ] Nama file di-rename ke acak: `bin2hex(random_bytes(16)) . '.' . strtolower($ext)`.
- [ ] File disimpan di `public/uploads/foto/`.
- [ ] `.htaccess` di folder upload memblokir eksekusi script (FilesMatch + `php_flag engine off` + `Options -Indexes -ExecCGI`) namun TETAP mengizinkan foto disajikan ke browser (jangan pakai `Deny from all` global — itu akan mematikan tampilan foto).
- [ ] Tidak menyimpan path absolut server — hanya path relatif dari `public/`.

### XSS
- [ ] Semua output variabel di view dibungkus `esc($var)` atau `<?= esc($data['field']) ?>`.
- [ ] Tidak ada `echo $var` tanpa escaping di view.

### Anti-Spam Form Publik
- [ ] Honeypot field: `<input type="text" name="_hp" style="display:none" tabindex="-1" autocomplete="off">` — cek di controller: bila terisi = bot, silent drop.
- [ ] Rate limit: counter di session; max 3 submit form publik per 10 menit.

### Rate Limit Login
- [ ] Max 5 percobaan gagal per 15 menit (key session: `login_attempts`, `login_attempt_time`).
- [ ] Setelah limit: tampilkan sisa waktu tunggu, tidak proses `password_verify`.

### Path Traversal
- [ ] Gunakan CI4 `$file->move(FCPATH . 'uploads/foto/', $newName)` — tidak ada manipulasi path manual.
- [ ] ID pada route `(:num)` dijamin integer oleh CI4 route constraint.

### Mode Produksi
- [ ] `CI_ENVIRONMENT = 'production'` di App.php.
- [ ] `DBDebug = false` di Database.php.
- [ ] Log threshold diset wajar (level 4 = warning ke atas).

### Header HTTP (opsional tapi direkomendasikan)
- [ ] Tambahkan di BaseController atau via `.htaccess`:
  - `X-Content-Type-Options: nosniff`
  - `X-Frame-Options: SAMEORIGIN`
  - `Referrer-Policy: strict-origin-when-cross-origin`

---

## 8. Desain Token Warna (Tailwind CSS v4)

File: `public/assets/css/app.css` — definisi token via `@theme`.

Warna brand Kemenag:
- Hijau primer: `#006B2E`
- Emas aksen: `#E5A400`

Prinsip anti-slop (dari ui-design-standards):
- Warna solid, tanpa gradient pada elemen diam.
- Border-radius: pilih 0 (siku) atau nilai besar (pil), konsisten satu pilihan.
- Tombol utama: satu warna solid flat, tanpa gradient.
- Surface pemisah: beda tone, bukan shadow berat.
- Tidak ada eyebrow label, tiny-uppercase, atau deretan ikon-card identik.

Contoh token `@theme` (diimplementasikan agen koding):

```css
@theme {
  --color-brand-green:  #006B2E;
  --color-brand-gold:   #E5A400;
  --color-surface:      #F7F7F5;
  --color-surface-raised: #FFFFFF;
  --color-border:       #D9D9D6;
  --color-ink:          #1A1A18;
  --color-ink-muted:    #6B6B67;
  --color-danger:       #C0392B;
  --color-success:      #1A7A3F;
}
```

Mobile-first: form publik didesain untuk layar 375px ke atas.

---

## PERTANYAAN UNTUK LEAD

1. **Koordinat wajib atau opsional?**
   Saat ini `lat` dan `lng` di skema DB adalah `NULL`-able. Jika koordinat dianggap
   WAJIB (field required), form tidak bisa disubmit bila geolocation ditolak dan user
   tidak isi manual. Konfirmasi: boleh kosong (NULL) atau harus ada?

2. **HTTPS di InfinityFree?**
   InfinityFree free tier menyediakan sertifikat SSL otomatis. Bila domain sudah
   dikonfigurasi dengan HTTPS, set `$secure = true` di Cookie.php. Konfirmasi status HTTPS.

3. **Foto wajib atau opsional?**
   Spesifikasi menyebutkan upload foto tanpa keterangan "optional". Saat ini
   diperlakukan sebagai WAJIB di aturan validasi. Konfirmasi: apakah foto boleh
   dilewati?

4. **Rate limit berbasis session vs. IP?**
   Saat ini rate limit form publik dan login menggunakan session PHP (tidak perlu
   Redis). Ini berarti pengguna yang membersihkan cookie bisa melewatinya. Untuk
   hosting shared tanpa Redis, ini batasan yang disadari. Terima?

5. **Tandai dibaca: otomatis saat buka detail atau tombol manual?**
   Saat ini spec: otomatis tandai is_read=1 saat admin membuka halaman detail.
   Alternatif: tombol eksplisit "Tandai Dibaca". Pilih salah satu.
