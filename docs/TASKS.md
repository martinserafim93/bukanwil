# TASKS.md — Dekomposisi Task untuk Agen Koding
## Aplikasi BUKANWIL v1.0

**Urutan eksekusi wajib diikuti** — setiap task bergantung pada task sebelumnya.
Baca SPEC.md secara penuh sebelum memulai task apapun.
Tidak ada `.env` — semua config di `app/Config/*.php`.

---

## Task-01 — Inisialisasi Proyek CI4 + Konfigurasi

**Tujuan:** Pasang CodeIgniter 4.x via Composer, edit semua file config inti,
           siapkan struktur folder upload.

**File dibuat / diubah:**
- `composer.json` + `vendor/` (via `composer create-project` atau require)
- `app/Config/App.php` — isi baseURL, indexPage='', appTimezone, CSRF config
- `app/Config/Database.php` — isi hostname, username, password, database, charset
- `app/Config/Session.php` — driver FileHandler, cookieName, expiration, savePath
- `app/Config/Cookie.php` — httponly=true, samesite=Lax, secure=true/false
- `app/Config/Encryption.php` — generate key via `php spark key:generate --show`
- `public/.htaccess` — rewrite semua request ke index.php (CI4 default)
- `public/uploads/foto/.htaccess` — blokir eksekusi script, izinkan penyajian gambar
- `public/assets/css/` dan `public/assets/js/` — buat direktori kosong

**Referensi SPEC:** §1 (semua sub-bagian config)

**Kriteria selesai:**
- `php spark serve` (atau akses via domain) menampilkan halaman CI4 welcome tanpa error.
- `php spark db:connect` berhasil tanpa exception.
- Folder `public/uploads/foto/` ada dan `.htaccess`-nya menolak eksekusi PHP.

---

## Task-02 — Import Skema Database + Seed

**Tujuan:** Buat database, import DDL, generate hash password admin, verifikasi isi tabel.

**File dibuat / diubah:**
- Database: import `database/schema.sql`
- Buat snippet PHP kecil (bisa dibuang setelah dipakai) untuk generate hash:
  `echo password_hash('Admin@2026', PASSWORD_BCRYPT, ['cost' => 12]);`
- Jalankan UPDATE: `UPDATE admin SET password = '<hash>' WHERE username = 'superadmin';`

**Cara eksekusi:**
```bash
mysql -u db_user -p bukanwil_db < database/schema.sql
php -r "echo password_hash('Admin@2026', PASSWORD_BCRYPT, ['cost'=>12]);"
# Salin hash, lalu:
mysql -u db_user -p bukanwil_db -e "UPDATE admin SET password='<hash>' WHERE username='superadmin';"
```

**Kriteria selesai:**
- Ketiga tabel (tamu, admin, pengaturan) ada dengan kolom sesuai SPEC §2.
- Tabel pengaturan terisi 3 baris seed.
- Tabel admin memiliki 1 baris superadmin dengan hash bcrypt valid (bukan placeholder).
- `password_verify('Admin@2026', $stored_hash)` mengembalikan true.

---

## Task-03 — Model Layer

**Tujuan:** Buat 3 Model CI4 dengan method yang dibutuhkan controller.

**File dibuat:**

### app/Models/TamuModel.php
```
extends Model
$table = 'tamu'
$primaryKey = 'id'
$allowedFields = [semua kolom kecuali id dan created_at]
$useTimestamps = false  (created_at dihandle CURRENT_TIMESTAMP DB)
$returnType = 'array'

Methods:
- getPaginasi(array $filter, int $perPage = 20): array hasil + pager
  (builder: where, orLike, orderBy, paginate)
- getSemuaDenganFilter(array $filter): array (untuk export CSV — tanpa paginate)
- getStatistik(): array [total, hari_ini, per_jenis, per_asal, top_keperluan, tren_7hari]
- countUnread(): int
- tandaiBaca(int $id): void  -- set is_read=1
- tandaiBacaSemua(): void    -- UPDATE tamu SET is_read=1 WHERE is_read=0
```

### app/Models/AdminModel.php
```
extends Model
$table = 'admin'
$allowedFields = [nama, username, password, role]
$returnType = 'array'

Methods:
- findByUsername(string $username): array|null
- updatePassword(int $id, string $hashedPassword): void
```

### app/Models/PengaturanModel.php
```
extends Model
$table = 'pengaturan'
$allowedFields = [key, value]

Methods:
- getAll(): array  -- return ['judul_aplikasi' => '...', ...]  (key => value)
- set(string $key, string $value): void  -- upsert
- setMany(array $data): void  -- loop set()
```

**Referensi SPEC:** §2 (skema), §5 (kontrak output dari model)

**Kriteria selesai:**
- Setiap model bisa di-instantiate tanpa error.
- `TamuModel::getStatistik()` mengembalikan array dengan semua key yang dibutuhkan dashboard.
- `PengaturanModel::getAll()` mengembalikan assoc array key => value.

---

## Task-04 — AuthFilter + BaseController

**Tujuan:** Filter sesi admin, inject data global ke semua controller admin.

**File dibuat / diubah:**

### app/Filters/AuthFilter.php
```php
implements FilterInterface

public function before(RequestInterface $request, $arguments = null)
{
    if (! session()->get('logged_in')) {
        return redirect()->to('/admin/login');
    }
}
```

### app/Config/Filters.php
- Daftarkan alias 'auth' => AuthFilter::class
- Terapkan ke route 'admin/*' dengan pengecualian 'admin/login'
  (Lihat SPEC §1 Filters.php)

### app/Controllers/BaseController.php
- Di `initController()`, load PengaturanModel dan TamuModel
- Set property `$this->pengaturan` (hasil PengaturanModel::getAll())
- Set property `$this->unreadCount` (hasil TamuModel::countUnread())
- Kedua property ini tersedia di semua controller turunan

**Referensi SPEC:** §4 (route + filter), §1 (Filters.php config)

**Kriteria selesai:**
- Akses `/admin` tanpa login mengalihkan ke `/admin/login`.
- Akses `/admin/login` tidak terkena loop redirect.
- `$this->pengaturan` dan `$this->unreadCount` tidak null di controller admin mana pun.

---

## Task-05 — Auth Admin (Login / Logout)

**Tujuan:** Halaman login, proses autentikasi, rate limit login, logout.

**File dibuat:**

### app/Controllers/Admin/Auth.php
```
login(): tampilkan form
doLogin():
  1. Cek rate limit (session: login_attempts, login_attempt_time)
     Jika attempts >= 5 dan time < 15 menit: tampilkan sisa waktu, stop
  2. Validasi input (required username + password)
  3. AdminModel::findByUsername($username)
  4. password_verify($input_password, $stored_hash)
  5. Jika berhasil: session()->set([...]), session()->regenerate(true), redirect /admin
  6. Jika gagal: increment login_attempts, redirect back dengan pesan error
logout():
  session()->destroy()
  redirect('/admin/login')
```

### app/Views/admin/login.php
- Form standalone (tanpa layout admin — tidak ada sidebar saat belum login)
- Field: username, password
- Tampilkan flash message error bila ada
- Warna brand Kemenag, solid flat, mobile-friendly

**Referensi SPEC:** §5.8, §6.2, §7 (rate limit login, session hardening)

**Kriteria selesai:**
- Login dengan kredensial benar mengarahkan ke `/admin`.
- Login salah 5 kali berturut: muncul pesan tunggu, percobaan ke-6 tidak diproses.
- Session setelah login memiliki key `logged_in`, `admin_id`, `role`.
- Logout menghapus session dan redirect ke login.

---

## Task-06 — Layout Admin + Dashboard

**Tujuan:** Template layout CMS admin dan halaman dashboard dengan statistik + Chart.js.

**File dibuat:**

### app/Views/layouts/admin.php
Struktur HTML:
- `<head>`: meta, title dari $pengaturan['judul_aplikasi'], link app.css, Chart.js CDN (pin versi: 4.4.x), Leaflet CSS CDN
- Sidebar: navigasi (Dashboard, Daftar Tamu, Kelola Admin, Pengaturan), link aktif
- Topbar: nama admin (`session()->get('admin_nama')`), badge notif unread count, link logout
- Slot konten: `<?= $this->renderSection('content') ?>`
- Script block di bawah body: Leaflet JS CDN, `<?= $this->renderSection('scripts') ?>`

### app/Controllers/Admin/Dashboard.php
- index(): ambil data dari TamuModel::getStatistik(), pass ke view

### app/Views/admin/dashboard.php
- Extends layout admin
- 4 kartu statistik: total tamu, tamu hari ini, tamu belum dibaca, rasio jenis kelamin
- Chart.js Line: tren 7 hari (labels = tanggal, data = count)
- Chart.js Doughnut atau Bar: per jenis_kelamin + per asal_pengunjung
- List top 5 keperluan
- Semua data dari PHP `$data` array — tidak ada fetch AJAX

**Referensi SPEC:** §5.3, §8 (design tokens)

**Kriteria selesai:**
- Dashboard tampil tanpa error JS di console.
- Grafik Chart.js render dengan data aktual dari DB.
- Badge notif menampilkan jumlah tamu belum dibaca.
- Saat 0 tamu, statistik menampilkan angka 0 tanpa error.

---

## Task-07 — Daftar Tamu (List, Search, Filter, Pagination)

**Tujuan:** Halaman daftar tamu dengan pencarian, filter multi-kriteria, pagination, dan kolom sortable.

**File dibuat / diubah:**

### app/Controllers/Admin/TamuAdmin.php (method index)
- Baca query params: q, tanggal_dari, tanggal_sampai, jenis_kelamin, asal, keperluan, sort, order, page
- Validasi sort: hanya izinkan kolom yang aman (whitelist: created_at, nama)
- Validasi order: hanya 'asc' atau 'desc'
- Panggil TamuModel::getPaginasi($filter, 20)
- Pass hasil + pager + filter_aktif ke view

### app/Views/admin/tamu/index.php
- Extends layout admin
- Form filter (collapse-able atau selalu tampil): tanggal range, jenis_kelamin, asal, keperluan
- Input pencarian di atas tabel
- Tabel: kolom id, nama, asal, no_hp, keperluan (truncate), created_at, aksi (detail)
- Header kolom created_at dan nama bisa diklik untuk sort (toggle asc/desc)
- Pagination CI4 di bawah tabel
- Tombol Export CSV (link ke /admin/tamu/export dengan filter aktif sebagai query string)
- Baris kosong: tampilkan pesan "Belum ada data tamu."

**Referensi SPEC:** §5.4, §4 (route)

**Kriteria selesai:**
- Pagination berfungsi (20 per halaman).
- Pencarian nama/no_hp/instansi berfungsi.
- Filter tanggal dan enum berfungsi dan bisa dikombinasikan.
- Sort asc/desc per kolom berfungsi.
- Filter aktif tetap di-persist di URL (query string) saat ganti halaman.

---

## Task-08 — Detail Tamu + Peta Leaflet

**Tujuan:** Halaman detail tamu lengkap dengan foto dan peta lokasi Leaflet + OpenStreetMap.

**File dibuat / diubah:**

### app/Controllers/Admin/TamuAdmin.php (method detail)
- Ambil tamu by id; jika tidak ada: throw 404
- Set is_read = 1 via TamuModel::tandaiBaca($id) (otomatis saat buka halaman)
- Pass data ke view

### app/Views/admin/tamu/detail.php
- Tampilkan semua field dalam layout dua kolom (info tamu | foto + peta)
- Foto: tag `<img>` dengan path dari `public/` + fallback teks "Tidak ada foto"
- Peta: `<div id="map">` berukuran min 300px; inisialisasi Leaflet di section scripts
  - Bila lat/lng tidak null: L.map, L.tileLayer OSM, L.marker di koordinat
  - Bila lat/lng null: tampilkan teks "Lokasi tidak tersedia"
- Tombol Kembali ke daftar tamu (pertahankan filter di referer bila memungkinkan)

**Referensi SPEC:** §5.6, §7 (XSS: semua output di-esc())

**Kriteria selesai:**
- Buka detail: is_read tamu berubah jadi 1 di DB.
- Foto tampil bila ada; placeholder bila tidak ada.
- Leaflet map render dengan marker di koordinat tamu bila lat/lng tersedia.
- Tidak ada error JS di console.

---

## Task-09 — Export CSV

**Tujuan:** Download CSV dengan filter yang sama persis seperti daftar tamu.

**File dibuat / diubah:**

### app/Controllers/Admin/TamuAdmin.php (method exportCsv)
```php
// Ambil filter dari query string (sama dengan index)
// TamuModel::getSemuaDenganFilter($filter) — tanpa paginate, bisa ratusan baris
// Set headers
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="tamu_export_' . date('Ymd_His') . '.csv"');
// BOM untuk Excel
echo "\xEF\xBB\xBF";
$output = fopen('php://output', 'w');
fputcsv($output, ['ID','Nama','Jenis Kelamin','Asal','Instansi','Alamat','No HP','Keperluan','Lat','Lng','Foto','Waktu Kunjungan']);
foreach ($data as $row) {
    fputcsv($output, [...]);
}
fclose($output);
exit;
```

**Referensi SPEC:** §5.5

**Kriteria selesai:**
- Klik Export mengunduh file CSV.
- Filter aktif dari halaman daftar tamu ter-apply di CSV (bukan export semua data).
- File CSV bisa dibuka di Excel/LibreOffice tanpa karakter rusak (BOM UTF-8 hadir).
- Tidak ada output HTML sebelum CSV header.

---

## Task-10 — Notifikasi In-App (Badge + Tandai Dibaca)

**Tujuan:** Badge jumlah tamu baru di topbar, aksi tandai dibaca, dan tandai semua dibaca.

**File dibuat / diubah:**

### app/Views/layouts/admin.php (diupdate)
- Badge di topbar: `<span class="badge">{{ unread_count }}</span>` — sembunyikan bila 0
- Tombol "Tandai semua dibaca" di halaman daftar tamu (POST ke /admin/tamu/baca-semua)

### app/Controllers/Admin/TamuAdmin.php (method tandaiBaca dan bacaSemua)
```
tandaiBaca(int $id):
  TamuModel::tandaiBaca($id)
  Respons: JSON {success: true} bila AJAX, redirect back bila form POST biasa

bacaSemua():
  TamuModel::tandaiBacaSemua()
  redirect('/admin/tamu')->with('sukses', 'Semua tamu ditandai sudah dibaca.')
```

### app/Config/Routes.php (tambah route)
- POST /admin/tamu/baca-semua -> TamuAdmin::bacaSemua

### public/assets/js/admin.js (opsional)
- Fetch POST ke /admin/tamu/{id}/baca, update badge angka tanpa reload halaman
- Hanya bila JS enhancement diinginkan; fallback form POST sudah cukup untuk P1

**Referensi SPEC:** §5.7, §4 (route)

**Kriteria selesai:**
- Badge menampilkan jumlah tamu is_read=0 yang akurat.
- Membuka detail tamu mengubah is_read menjadi 1 dan badge berkurang.
- "Tandai semua dibaca" mengatur semua is_read=1 sekaligus.
- Badge tidak tampil (atau tampil 0) bila tidak ada tamu baru.

---

## Task-11 — Kelola Admin (CRUD)

**Tujuan:** Daftar, tambah, edit, dan hapus akun admin. Hapus hanya superadmin.

**File dibuat:**

### app/Controllers/Admin/AdminUser.php
```
index():    daftar semua admin (AdminModel::findAll())
buat():     tampilkan form kosong
simpan():   validasi + hash password + AdminModel::insert()
edit($id):  ambil admin by id, tampilkan form terisi
update($id):validasi + (hash password bila diisi) + AdminModel::update()
konfirmasiHapus($id): cek role superadmin; tampilkan halaman konfirmasi
hapus($id): cek role superadmin; cek tidak hapus diri sendiri; AdminModel::delete($id)
```

Cek role superadmin di awal method yang membutuhkan:
```php
if (session()->get('role') !== 'superadmin') {
    return redirect('/admin')->with('error', 'Akses ditolak.');
}
```

### app/Views/admin/admin_user/index.php
- Tabel: nama, username, role, created_at, aksi (edit | hapus bila superadmin)

### app/Views/admin/admin_user/form.php
- Dipakai untuk buat dan edit (beda `action` dan nilai default)
- Field: nama, username, password, password_confirm, role (select)
- Saat edit: password kosong = tidak ubah; isi = ubah password

**Referensi SPEC:** §5.9, §6.3, §7

**Kriteria selesai:**
- Admin baru bisa dibuat, login berhasil dengan password yang baru.
- Edit admin: username diubah, username baru unik (tidak bentrok).
- Edit admin: password kosong = password lama tidak berubah.
- Hapus: hanya superadmin bisa; superadmin tidak bisa hapus akunnya sendiri.
- Role 'admin' biasa tidak bisa akses route hapus maupun pengaturan.

---

## Task-12 — Halaman Pengaturan

**Tujuan:** Form edit pengaturan aplikasi (judul, nama instansi, teks header).
           Hanya superadmin yang bisa akses.

**File dibuat:**

### app/Controllers/Admin/Pengaturan.php
```
index():   PengaturanModel::getAll() -> tampilkan form terisi
simpan():  validasi + PengaturanModel::setMany($data) + redirect dengan flash sukses
```
Cek role superadmin (sama dengan Task-11).

### app/Views/admin/pengaturan/index.php
- Form 3 field: judul_aplikasi, nama_instansi_ptsp, teks_header (textarea)
- Flash message sukses/error

**Referensi SPEC:** §5.10

**Kriteria selesai:**
- Ubah judul_aplikasi: judul di `<title>` dan sidebar berubah setelah refresh.
- Simpan dengan salah satu field kosong: gagal validasi + pesan error tampil.
- Akses oleh role 'admin' biasa: redirect ke dashboard dengan pesan "Akses ditolak."

---

## Task-13 — Layout Publik + View Form Buku Tamu

**Tujuan:** Halaman form buku tamu yang bisa diisi pengunjung dari perangkat apa pun.

**File dibuat:**

### app/Views/layouts/public.php
- Head: meta viewport, title, link app.css
- Header: logo Kemenag + nama_instansi_ptsp + teks_header (dari $pengaturan)
- Slot konten: `<?= $this->renderSection('content') ?>`
- Script block: `<?= $this->renderSection('scripts') ?>`

### app/Views/tamu/form.php
Extends layouts/public.php

Field yang dirender (urutan sesuai brief):
1. nama — input text
2. jenis_kelamin — radio button Laki-laki / Perempuan
3. asal_pengunjung — radio button Perorangan / Instansi / Lembaga
4. nama_instansi — input text; tersembunyi secara default (display:none via CSS/JS),
   muncul HANYA bila asal_pengunjung ∈ {instansi, lembaga}
5. alamat — textarea
6. no_hp — input tel
7. keperluan — textarea
8. Lokasi — tombol "Deteksi Lokasi Otomatis"; progress/status text;
   dua input manual lat + lng tersembunyi yang muncul bila geolocation gagal/ditolak
9. foto — input file (accept="image/jpeg,image/png,image/webp")
10. _hp — honeypot: `<input type="text" name="_hp" tabindex="-1" autocomplete="off">`
    disembunyikan via CSS (bukan display:none inline — gunakan class CSS helper)
- Tombol Submit
- Tampilkan error validasi dari flash data (loop `$errors`)
- CSRF token: `<?= csrf_field() ?>`

### app/Views/tamu/sukses.php
- Pesan konfirmasi sukses
- Tombol "Isi Form Lagi" (link ke /)

**Referensi SPEC:** §5.1, §6.1, §8 (design tokens Kemenag)

**Kriteria selesai:**
- Form tampil di mobile (375px) tanpa overflow horizontal.
- Field nama_instansi tersembunyi saat asal = perorangan, muncul saat asal ∈ {instansi, lembaga}.
- Honeypot tidak tampak secara visual di browser.
- Validasi error dari server tampil di bawah field yang relevan.

---

## Task-14 — Controller Form Publik (Submit + Validasi Server-Side)

**Tujuan:** Proses POST form buku tamu dengan semua validasi, honeypot, rate limit,
           dan penyimpanan data (koordinat + path foto).

**File dibuat / diubah:**

### app/Controllers/Tamu.php
```php
index():
  $pengaturan = $this->pengaturan;
  return view('tamu/form', ['pengaturan' => $pengaturan]);

sukses():
  return view('tamu/sukses', ['pengaturan' => $this->pengaturan]);

submit():
  // 1. Cek honeypot
  if ($request->getPost('_hp') !== '') {
      return redirect('/tamu/sukses');   // silent drop
  }

  // 2. Rate limit (session)
  // Key: 'form_submit_count', 'form_submit_time'
  // Max 3 submit per 10 menit
  // Jika limit terlampaui: redirect('/tamu/sukses') — silent drop

  // 3. Validasi CI4 (rules sesuai SPEC §6.1)

  // 4. Validasi manual: nama_instansi wajib bila asal ∈ {instansi, lembaga}

  // 5. Upload foto (dipanggil setelah validasi dasar lolos)
  //    — lihat Task-15

  // 6. Simpan ke DB
  $this->tamuModel->insert([
      'nama'            => $this->request->getPost('nama'),
      'jenis_kelamin'   => ...,
      'asal_pengunjung' => ...,
      'nama_instansi'   => ...,
      'alamat'          => ...,
      'no_hp'           => ...,
      'keperluan'       => ...,
      'lat'             => $lat ?: null,
      'lng'             => $lng ?: null,
      'foto'            => $fotoPath,
  ]);

  // 7. Increment rate limit counter

  // 8. Redirect sukses
  return redirect('/tamu/sukses');
```

**Referensi SPEC:** §5.2, §6.1, §7 (honeypot, rate limit)

**Kriteria selesai:**
- Submit valid: data tersimpan di tabel tamu, redirect ke /tamu/sukses.
- Submit honeypot terisi: silent drop ke /tamu/sukses, tidak ada insert ke DB.
- Submit lebih dari 3 kali dalam 10 menit: silent drop, tidak ada insert.
- Validasi gagal: redirect back, field terisi ulang (withInput), pesan error tampil.
- `no_hp = "08123456789"` lolos; `no_hp = "1234"` ditolak.

---

## Task-15 — Upload Foto

**Tujuan:** Validasi, rename, dan simpan file foto dengan aman.

**Diimplementasikan di dalam Tamu::submit() (Task-14), dipecah menjadi method privat:**

### app/Controllers/Tamu.php (method private prosesUploadFoto())
```php
private function prosesUploadFoto(): string|null
{
    $file = $this->request->getFile('foto');

    if (! $file || ! $file->isValid()) {
        return null;
    }

    // Cek mime via finfo (server-side, bukan hanya ekstensi)
    $finfo    = new \finfo(FILEINFO_MIME_TYPE);
    $mime     = $finfo->file($file->getTempName());
    $allowed  = ['image/jpeg', 'image/png', 'image/webp'];

    if (! in_array($mime, $allowed, true)) {
        return null;   // akan ditangkap validasi sebelumnya; ini lapisan kedua
    }

    // Ekstensi dari mime (bukan dari nama file user)
    $extMap  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $ext     = $extMap[$mime];

    // Nama acak
    $newName = bin2hex(random_bytes(16)) . '.' . $ext;

    // Pindahkan ke folder upload
    $destDir = FCPATH . 'uploads/foto/';
    $file->move($destDir, $newName);

    return 'uploads/foto/' . $newName;
}
```

**Referensi SPEC:** §7 (upload checklist)

**Kriteria selesai:**
- Upload JPG/PNG/WebP <= 2 MB: berhasil disimpan dengan nama acak.
- Upload file PHP atau ekstensi terlarang: ditolak di validasi CI4 + lapisan finfo.
- Nama file di DB adalah path relatif (contoh: `uploads/foto/a3f8b2c1...d9.jpg`).
- File PHP yang di-rename jadi .jpg tidak bisa dieksekusi karena .htaccess upload folder.

---

## Task-16 — Geolocation JavaScript

**Tujuan:** Deteksi koordinat via browser API; fallback input manual bila ditolak/gagal.

**File dibuat / diubah:**

### public/assets/js/form-publik.js
```javascript
(function () {
  const btnDeteksi  = document.getElementById('btn-deteksi-lokasi');
  const statusEl    = document.getElementById('status-lokasi');
  const latInput    = document.getElementById('lat');
  const lngInput    = document.getElementById('lng');
  const manualWrap  = document.getElementById('input-manual-lokasi');

  // Conditional show/hide nama_instansi
  document.querySelectorAll('input[name="asal_pengunjung"]').forEach(function(radio) {
    radio.addEventListener('change', function () {
      const instansiWrap = document.getElementById('wrap-nama-instansi');
      const showInstansi = this.value === 'instansi' || this.value === 'lembaga';
      instansiWrap.style.display = showInstansi ? 'block' : 'none';
      document.getElementById('nama_instansi').required = showInstansi;
    });
  });

  // Geolocation
  if (btnDeteksi) {
    btnDeteksi.addEventListener('click', function () {
      if (! navigator.geolocation) {
        tampilkanManual('Browser tidak mendukung geolocation.');
        return;
      }
      statusEl.textContent = 'Mendeteksi lokasi...';
      navigator.geolocation.getCurrentPosition(
        function (pos) {
          latInput.value = pos.coords.latitude.toFixed(8);
          lngInput.value = pos.coords.longitude.toFixed(8);
          statusEl.textContent = 'Lokasi berhasil dideteksi.';
          manualWrap.style.display = 'none';
        },
        function () {
          tampilkanManual('Akses lokasi ditolak. Silakan isi koordinat secara manual.');
        },
        { timeout: 10000, maximumAge: 0 }
      );
    });
  }

  function tampilkanManual(pesan) {
    statusEl.textContent = pesan;
    manualWrap.style.display = 'block';
  }
})();
```

Elemen HTML yang dibutuhkan di form.php (Task-13):
- `<button type="button" id="btn-deteksi-lokasi">Deteksi Lokasi Otomatis</button>`
- `<p id="status-lokasi"></p>`
- `<input type="hidden" name="lat" id="lat">`
- `<input type="hidden" name="lng" id="lng">`
- `<div id="input-manual-lokasi" style="display:none">` berisi dua input text lat + lng
- `<div id="wrap-nama-instansi" style="display:none">` berisi input nama_instansi

**Referensi SPEC:** §5.2 (field lat/lng), §6.1

**Kriteria selesai:**
- Klik "Deteksi Lokasi": bila diizinkan, lat/lng terisi di input hidden.
- Bila ditolak: input manual muncul; user bisa ketik koordinat.
- Bila lat/lng kosong, form tetap bisa disubmit (koordinat NULL di DB).
- Nama instansi muncul/tersembunyi sesuai pilihan asal_pengunjung.

---

## Task-17 — Tailwind CSS v4 Setup + Design Tokens

**Tujuan:** Kompilasi CSS dengan token warna Kemenag, anti-slop, mobile-first.

**File dibuat / diubah:**

### Setup (jalankan sekali di environment develop)
```bash
npm init -y
npm install tailwindcss@next @tailwindcss/cli@next
```

### public/assets/css/app.css (source)
```css
@import "tailwindcss";

@theme {
  --color-brand-green:    #006B2E;
  --color-brand-gold:     #E5A400;
  --color-surface:        #F7F7F5;
  --color-surface-raised: #FFFFFF;
  --color-border:         #D9D9D6;
  --color-ink:            #1A1A18;
  --color-ink-muted:      #6B6B67;
  --color-danger:         #C0392B;
  --color-success:        #1A7A3F;

  /* Radius — pilih siku (0) atau pil (9999px); konsisten */
  --radius-sm: 0px;
  --radius-md: 0px;
  --radius-pill: 9999px;

  /* Type scale */
  --font-size-xs:  0.75rem;
  --font-size-sm:  0.875rem;
  --font-size-base:1rem;
  --font-size-lg:  1.25rem;
  --font-size-xl:  1.625rem;
  --font-size-2xl: 2rem;

  --line-height-tight:  1.2;
  --line-height-normal: 1.6;
}
```

### Build command
```bash
npx @tailwindcss/cli -i public/assets/css/app.css -o public/assets/css/app.compiled.css --minify
```

Catatan untuk hosting InfinityFree: commit file `app.compiled.css` yang sudah ter-build
ke repo / upload langsung. Tidak perlu Node.js di server.

Link di layout: `<link rel="stylesheet" href="<?= base_url('assets/css/app.compiled.css') ?>">`.

**Aturan anti-slop yang WAJIB dipatuhi saat menulis class Tailwind di view:**
- Tidak ada `shadow-lg`, `shadow-xl` pada elemen diam.
- Tidak ada `bg-gradient-*`.
- Tidak ada border-radius campuran (pilih satu dari --radius-sm atau --radius-pill, terapkan konsisten).
- Button primary: `bg-brand-green text-white` — tanpa gradient, tanpa hover gradient.
- Surface card: `bg-surface-raised border border-border` — bukan `shadow-md rounded-lg`.

**Referensi SPEC:** §8

**Kriteria selesai:**
- File `app.compiled.css` ter-build tanpa error.
- Token warna `brand-green` dan `brand-gold` bisa dipakai via class Tailwind.
- Tidak ada `bg-gradient-*` di view mana pun.

---

## Task-18 — Final Hardening + .htaccess + QA Checklist

**Tujuan:** Pastikan semua item checklist keamanan terpenuhi sebelum aplikasi dinyatakan selesai.

**File dibuat / diubah:**

### .htaccess (root level — bila diperlukan)
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### public/.htaccess (sudah ada dari CI4, verifikasi isinya)
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php/$1 [L]
</IfModule>
```

### public/uploads/foto/.htaccess
```apache
# Izinkan foto disajikan ke browser, tapi larang eksekusi script
<FilesMatch "\.(php|php3|php4|php5|phtml|pl|py|jsp|asp|cgi|sh)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>
php_flag engine off
Options -Indexes -ExecCGI
AddHandler cgi-script .php .php3 .php4 .php5 .phtml .pl .py .jsp .asp .cgi .sh
```

### Security header di BaseController::initController() (opsional, direkomendasikan)
```php
$this->response->setHeader('X-Content-Type-Options', 'nosniff');
$this->response->setHeader('X-Frame-Options', 'SAMEORIGIN');
$this->response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
```

### QA Checklist (verifikasi manual sebelum lapor selesai)
- [ ] Semua item di SPEC §7 dicentang.
- [ ] Form publik: submit valid berhasil, error tampil, honeypot silent drop.
- [ ] Upload foto PHP.jpg ditolak (mime bukan image/*).
- [ ] Login 6 kali salah: dikunci 15 menit.
- [ ] Akses /admin tanpa login: redirect ke /admin/login.
- [ ] Admin biasa akses /admin/pengaturan: redirect dengan pesan "Akses ditolak."
- [ ] Export CSV: filter aktif ter-apply, file bisa dibuka di Excel.
- [ ] Peta Leaflet tampil di detail tamu yang punya koordinat.
- [ ] Badge notif berkurang setelah buka detail tamu.
- [ ] Tidak ada `echo $var` tanpa esc() di view mana pun.
- [ ] `DBDebug = false` di Database.php.
- [ ] Tidak ada file `.env` di root proyek.

**Kriteria selesai:**
- Semua item QA checklist di atas dicentang dan dilaporkan oleh agen koding.
- Tidak ada PHP error/warning di log saat menjalankan semua flow utama.
- Aplikasi bisa di-deploy ke InfinityFree (tanpa dotenv, tanpa Redis, tanpa CLI PHP production).

---

## Task-RENAME — Full Rebrand BUKANWIL (2026-10-21)

**Tujuan:** Rename lengkap aplikasi BUKANWIL → BUKANWIL, ubah deskripsi dari hardcoded menjadi konfigurabel via menu Pengaturan.

**Referensi:** Baca `docs/SPEC-RENAME-BUKANWIL.md` secara LENGKAP sebelum mulai — dokumen tersebut berisi pemetaan file-by-file yang presisi.

**Urutan eksekusi wajib:**

### RENAME-01 — BACKEND: Database Schema + Config (agen `koding`)

**Estimasi:** 1 jam

**File diubah:**

1. **database/schema.sql**
   - Baris 2: `-- BUKANWIL —` → `-- BUKANWIL —`
   - Baris 3: `-- Buku Tamu Kanwil Kemenag Kaltara` → `-- Buku Tamu Kanwil Kemenag Kaltara`
   - Baris 109: `('judul_aplikasi', 'BUKANWIL — Buku Tamu PTSP'),` → `('judul_aplikasi', 'BUKANWIL'),`
   - **TAMBAH baris baru** setelah baris 109 (sebelum baris 110):
     ```sql
     ('deskripsi_aplikasi', 'Buku Tamu Kanwil Kemenag Kaltara'),
     ```
   - Hasil akhir seed (4 baris key-value, lihat SPEC-RENAME §2.1).

2. **app/Config/Database.php**
   - Baris 33: `'database' => 'bukanwil_db',` → `'database' => 'bukanwil_db',`

3. **app/Config/Session.php**
   - Baris 34: `public string $cookieName = 'bukanwil_session';` → `public string $cookieName = 'bukanwil_session';`

4. **app/Controllers/Admin/Pengaturan.php**
   - Method `simpan()`, array `$rules` (sekitar baris 49-53):
     ```php
     $rules = [
         'judul_aplikasi'       => 'required|max_length[255]',
         'deskripsi_aplikasi'   => 'required|max_length[255]',  // TAMBAH
         'nama_instansi_ptsp'   => 'required|max_length[255]',
         'teks_header'          => 'required',
     ];
     ```
   - Array `$data` (sekitar baris 61-65):
     ```php
     $data = [
         'judul_aplikasi'       => (string) $this->request->getPost('judul_aplikasi'),
         'deskripsi_aplikasi'   => (string) $this->request->getPost('deskripsi_aplikasi'),  // TAMBAH
         'nama_instansi_ptsp'   => (string) $this->request->getPost('nama_instansi_ptsp'),
         'teks_header'          => (string) $this->request->getPost('teks_header'),
     ];
     ```

5. **package.json**
   - Baris 2: `"name": "bukanwil",` → `"name": "bukanwil",`
   - Baris 4: `"description": "BUKANWIL — Buku Tamu Kanwil Kemenag Kaltara (CodeIgniter 4 + Tailwind CSS)",` → `"description": "BUKANWIL — Buku Tamu Kanwil Kemenag Kaltara (CodeIgniter 4 + Tailwind CSS)",`
   - Baris 12: `"url": "git+https://github.com/martinserafim93/bukanwil.git"` → `"url": "git+https://github.com/martinserafim93/bukanwil.git"`

6. **app/Controllers/Tamu.php**
   - Baris 8: `* Public guestbook form controller — BUKANWIL` → `* Public guestbook form controller — BUKANWIL`

**Kriteria selesai:**
- `grep -r "bukanwil_db" app/ database/` hanya di 2 file yang sudah diganti.
- `grep -r "bukanwil_session" app/` hanya di 1 file yang sudah diganti.
- Seed `pengaturan` memiliki 4 baris (judul, deskripsi, nama_instansi, teks_header).
- Controller Pengaturan memvalidasi dan menyimpan `deskripsi_aplikasi`.

---

### RENAME-02 — FRONTEND: Layout + Views (agen `frontend`)

**Estimasi:** 2 jam

**File diubah:**

1. **app/Views/layouts/publik.php**
   - Baris 3: `* Layout Publik — BUKANWIL` → `* Layout Publik — BUKANWIL`
   - Baris 12: `— <?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?>` → `— <?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?>`
   - Baris 34: `<?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?>` → `<?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?>`
   - Baris 37: `<?= esc('Buku Tamu Kanwil Kemenag Kaltara') ?>` → `<?= esc($pengaturan['deskripsi_aplikasi'] ?? 'Buku Tamu Kanwil Kemenag Kaltara') ?>`  
     (PERHATIAN: dari hardcoded string menjadi dinamis dari DB)

2. **app/Views/layouts/admin.php**
   - Baris 3: `* Layout Admin — BUKANWIL` → `* Layout Admin — BUKANWIL`
   - Baris 24: `— <?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?>` → `— <?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?>`
   - Baris 50: `<?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?>` → `<?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?>`

3. **app/Views/tamu/form.php**
   - Baris 3: `* Public guestbook form — BUKANWIL` → `* Public guestbook form — BUKANWIL`

4. **app/Views/tamu/sukses.php**
   - Baris 3: `* Public guestbook success page — BUKANWIL` → `* Public guestbook success page — BUKANWIL`

5. **app/Views/admin/tamu/index.php**
   - Baris 3: `* Admin guest list — BUKANWIL` → `* Admin guest list — BUKANWIL`

6. **app/Views/admin/tamu/detail.php**
   - Baris 3: `* Admin detail tamu — BUKANWIL` → `* Admin detail tamu — BUKANWIL`

7. **app/Views/admin/pengaturan/index.php**
   - Baris 3: `* Admin settings form — BUKANWIL` → `* Admin settings form — BUKANWIL`
   - **TAMBAH field baru** setelah field `judul_aplikasi` (setelah `</div>` baris 45, sebelum `<div>` baris 47):
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

8. **app/Views/admin/login.php**
   - Baris 3: `* Standalone admin login page — BUKANWIL` → `* Standalone admin login page — BUKANWIL`

9. **app/Views/admin/admin-user/index.php**
   - Baris 3: `* Admin user list — BUKANWIL` → `* Admin user list — BUKANWIL`

10. **app/Views/admin/admin-user/form.php**
    - Baris 3: `* Admin user form — BUKANWIL` → `* Admin user form — BUKANWIL`

11. **app/Views/admin/admin-user/hapus.php**
    - Baris 3: `* Delete admin confirmation — BUKANWIL` → `* Delete admin confirmation — BUKANWIL`

12. **themes/app.css**
    - Baris 4: `   BUKANWIL Design System — Tailwind CSS v4 (CSS-first)` → `   BUKANWIL Design System — Tailwind CSS v4 (CSS-first)`

**Kriteria selesai:**
- `grep -ri "— BUKANWIL" app/Views/` tidak mengembalikan hasil.
- `grep -ri "BUKANWIL" themes/` tidak mengembalikan hasil.
- Form Pengaturan memiliki field `Deskripsi Aplikasi` setelah field `Judul Aplikasi`.
- Layout publik menggunakan `$pengaturan['deskripsi_aplikasi']` (dinamis) bukan string hardcoded.

---

### RENAME-03 — DOKUMENTASI: docs/ dan blueprint/ (agen `koding`)

**Estimasi:** 1 jam

**File diubah:**

1. **docs/PRD.md**
   - Baris 1: `# PRD — BUKANWIL` → `# PRD — BUKANWIL`
   - Baris 2: `## Buku Tamu Kanwil Kemenag Kaltara` → `## Buku Tamu Kanwil Kemenag Kaltara`
   - Baris 15: `BUKANWIL hadir sebagai pengganti Google Form…` → `BUKANWIL hadir sebagai pengganti Google Form…`

2. **docs/SPEC.md**
   - Baris 1: `# SPEC.md — Spesifikasi Teknis BUKANWIL` → `# SPEC.md — Spesifikasi Teknis BUKANWIL`
   - Baris 2: `## Buku Tamu Kanwil Kemenag Kaltara` → `## Buku Tamu Kanwil Kemenag Kaltara`
   - Baris 42 (dalam contoh config): `'database' => 'bukanwil_db',` → `'database' => 'bukanwil_db',`

3. **docs/DECISIONS.md**
   - Baris 1: `# Keputusan Lead — BUKANWIL` → `# Keputusan Lead — BUKANWIL`

4. **docs/DESIGN.md**
   - Baris 1: `# BUKANWIL Design System` → `# BUKANWIL Design System`
   - Baris 3: `…pengembangan BUKANWIL.` → `…pengembangan BUKANWIL.`
   - Baris 9: `BUKANWIL adalah aplikasi internal…` → `BUKANWIL adalah aplikasi internal…`
   - Baris 90: `…(publik: "Kementerian Agama RI / Kanwil Kemenag"; admin: "BUKANWIL / Admin Panel")…` → `…(admin: "BUKANWIL / Admin Panel")…`

5. **docs/CEKLIS-VERIFIKASI.md**
   - Baris 1: `# Ceklis Verifikasi per Fase — BUKANWIL` → `# Ceklis Verifikasi per Fase — BUKANWIL`

6. **blueprint/project-plan.md**
   - Baris 1: `# Project Plan — BUKANWIL v1.0` → `# Project Plan — BUKANWIL v1.0`
   - Baris 3: `**Buku Tamu Kanwil Kemenag Kaltara**` → `**Buku Tamu Kanwil Kemenag Kaltara**`
   - Baris 17: `BUKANWIL menggantikan Google Form…` → `BUKANWIL menggantikan Google Form…`

7. **blueprint/build-plan.md**
   - Baris 1: `# Build Plan — BUKANWIL v1.0` → `# Build Plan — BUKANWIL v1.0`

8. **blueprint/context/project-overview.md**
   - Baris 1: `# Project Overview — BUKANWIL` → `# Project Overview — BUKANWIL`
   - Baris 3: `**Buku Tamu Kanwil Kemenag Kaltara**` → `**Buku Tamu Kanwil Kemenag Kaltara**`
   - Baris 17: `BUKANWIL hadir sebagai solusi pengganti…` → `BUKANWIL hadir sebagai solusi pengganti…`

9. **docs/preview.html**
   - Baris 6: `<title>Form Buku Tamu — BUKANWIL</title>` → `<title>Form Buku Tamu — BUKANWIL</title>`

**Kriteria selesai:**
- `grep -ri "BUKANWIL" docs/ blueprint/` tidak mengembalikan hasil (kecuali file `SPEC-RENAME-BUKANWIL.md` yang sengaja berisi dokumentasi proses rename).
- `grep -ri "Religius" docs/ blueprint/` tidak mengembalikan hasil.
- Semua dokumen konsisten dengan nama baru.

---

### RENAME-04 — VERIFIKASI + BUILD (agen `koding` atau `frontend`)

**Estimasi:** 0.5 jam

**Checklist:**

1. **Grep final (pastikan tidak ada sisa string lama):**
   ```bash
   cd /opt/data/workspaces/bukanwil
   grep -ri "BUKANWIL" app/ public/ database/ docs/ blueprint/ themes/ package.json --exclude-dir=vendor --exclude-dir=node_modules --exclude-dir=.claude
   grep -ri "bukanwil_session" app/
   grep -ri "bukanwil_db" app/ database/
   grep -ri "Religius" app/ public/ database/ docs/ blueprint/
   ```
   **Target:** Semua perintah di atas tidak mengembalikan hasil (atau hanya mengembalikan file `docs/SPEC-RENAME-BUKANWIL.md` dan AGENTS.md/CLAUDE.md yang memang dokumentasi proses).

2. **Build CSS:**
   ```bash
   npm run build:css
   ```
   **Target:** Selesai tanpa error, file `public/css/app.css` ter-generate.

3. **Smoke test lokal (bila DB lokal sudah ada):**
   - Buat database `bukanwil_db`, import `database/schema.sql`.
   - Update `app/Config/Database.php` lokal: `'database' => 'bukanwil_db'`.
   - Akses halaman publik: verifikasi judul `BUKANWIL` dan deskripsi `Buku Tamu Kanwil Kemenag Kaltara` tampil.
   - Login admin (username: `superadmin`, password: `Admin@2026`).
   - Buka menu Pengaturan: verifikasi field `Deskripsi Aplikasi` ada dan terisi default `Buku Tamu Kanwil Kemenag Kaltara`.
   - Ubah deskripsi menjadi string lain (misal: `Buku Tamu Kanwil Test`), simpan, reload halaman publik: verifikasi deskripsi berubah.
   - Cek cookie session via DevTools → Application → Cookies: nama cookie `bukanwil_session`.

4. **Commit lokal (JANGAN push — tunggu Lead review):**
   ```bash
   git add -A
   git commit -m "feat: Full rebrand BUKANWIL → BUKANWIL + deskripsi konfigurabel"
   ```

**Kriteria selesai:**
- Semua checklist di atas terpenuhi.
- Tidak ada error build.
- Halaman publik dan admin menampilkan branding baru.
- Deskripsi aplikasi konfigurabel via menu Pengaturan.

---

### RENAME-05 — OPS: Deployment Produksi (LEAD — TIDAK DIKERJAKAN AGEN)

**Estimasi:** 2 jam (Lead)

**Langkah:**

1. **Rename repo GitHub:**
   ```bash
   gh repo rename bukanwil --repo martinserafim93/bukanwil
   ```
   - URL lama redirect otomatis.
   - Update remote lokal: `git remote set-url origin https://github.com/martinserafim93/bukanwil.git`

2. **Buat subdomain baru `bukanwil.is-best.net`:**
   - Login panel InfinityFree.
   - Buat subdomain, arahkan root ke folder `htdocs/` (atau folder baru).
   - Upload repo dari GitHub atau sync lokal.

3. **Update config produksi:**
   - `app/Config/App.php`: `public string $baseURL = 'https://bukanwil.is-best.net/';`
   - `app/Config/Database.php`: hostname, username, password, database produksi (misal `epiz_xxxxx_bukanwil`).

4. **Migrasi database:**
   - Dump tabel `tamu` dari `bukanwil_db` lama:
     ```bash
     mysqldump -u user -p bukanwil_db tamu > tamu_backup.sql
     ```
   - Buat database baru `bukanwil_db` (atau `epiz_xxxxx_bukanwil`).
   - Import skema baru:
     ```bash
     mysql -u user -p bukanwil_db < database/schema.sql
     ```
   - Import data tamu lama:
     ```bash
     mysql -u user -p bukanwil_db < tamu_backup.sql
     ```
   - Verifikasi:
     ```sql
     SELECT COUNT(*) FROM bukanwil_db.tamu;
     SELECT * FROM bukanwil_db.pengaturan;
     ```
   - Update seed `pengaturan` bila perlu:
     ```sql
     UPDATE pengaturan SET value = 'BUKANWIL' WHERE `key` = 'judul_aplikasi';
     INSERT INTO pengaturan (`key`, `value`) VALUES ('deskripsi_aplikasi', 'Buku Tamu Kanwil Kemenag Kaltara')
     ON DUPLICATE KEY UPDATE value = VALUES(value);
     ```

5. **Instruksi admin:**
   - Admin logout dari `bukanwil.is-best.net`.
   - Login ulang di `bukanwil.is-best.net` (cookie session berbeda, domain berbeda).

**Kriteria selesai:**
- Subdomain baru online, halaman publik dan admin berfungsi.
- Data tamu lama berhasil dimigrasikan tanpa kehilangan.
- Notifikasi badge tamu baru berfungsi.
- Admin bisa login dan mengubah deskripsi aplikasi via menu Pengaturan.

---

### RENAME-06 — DOKUMENTASI AKHIR: Catat Perubahan (agen `koding`)

**Estimasi:** 0.25 jam

**Tujuan:** Catat hasil rename di dokumen `docs/DECISIONS.md` atau buat changelog.

**File diubah:**

1. **docs/DECISIONS.md**
   - Tambah bagian baru di akhir:
     ```markdown
     ## 2026-10-21 — Rebrand BUKANWIL → BUKANWIL

     **Keputusan:**
     - Nama aplikasi diubah dari BUKANWIL (Buku Tamu Kanwil Kemenag Kaltara) menjadi BUKANWIL (Buku Tamu Kanwil Kemenag Kaltara).
     - Deskripsi aplikasi (kepanjangan) yang sebelumnya hardcoded di `app/Views/layouts/publik.php` kini menjadi konfigurabel via menu Pengaturan (superadmin), disimpan di tabel `pengaturan` key `deskripsi_aplikasi`.
     - Database lokal: `bukanwil_db` → `bukanwil_db`.
     - Cookie session: `bukanwil_session` → `bukanwil_session`.
     - Repo GitHub: `martinserafim93/bukanwil` → `martinserafim93/bukanwil`.
     - Domain produksi: `bukanwil.is-best.net` → `bukanwil.is-best.net`.

     **Referensi:**
     - Spesifikasi lengkap: `docs/SPEC-RENAME-BUKANWIL.md`.
     - Task rename: `docs/TASKS.md` → Task-RENAME.

     **Estimasi total:** 6.1 jam (BACKEND 1 jam + FRONTEND 2 jam + DOC 1 jam + OPS 2 jam + VERIFIKASI 0.5 jam).
     ```

**Kriteria selesai:**
- Dokumen `DECISIONS.md` mencatat perubahan ini dengan timestamp dan referensi.

---

## END OF TASK-RENAME
