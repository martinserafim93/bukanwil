# Build Plan — SANTRI v1.0

**Rencana Build Teknis per Fitur/Task**

Setiap entri di sini adalah unit kerja agen `koding`.
Urutan wajib diikuti — setiap task bergantung pada task sebelumnya.
Referensi lengkap: `docs/TASKS.md` (checklist detail) dan `docs/SPEC.md` (kontrak teknis).

---

## Task-01 — Inisialisasi CI4 + Konfigurasi

Referensi SPEC: §1 (semua sub-bagian config)
Referensi TASKS: Task-01

Dependensi: tidak ada (task pertama)

File yang dibuat / diubah:
- app/Config/App.php — baseURL, indexPage='', appTimezone='Asia/Makassar', charset, defaultLocale='id', CSRFProtection=true, CSRFTokenName, CSRFCookieName, CSRFExpire=7200, CSRFRegenerate=true, CI_ENVIRONMENT='production'
- app/Config/Database.php — hostname, username, password, database='santri_db', DBDriver='MySQLi', DBDebug=false, charset='utf8mb4', DBCollat='utf8mb4_unicode_ci'
- app/Config/Session.php — driver FileHandler, cookieName='santri_session', expiration=7200, savePath=WRITEPATH.'session', regenerateDestroy=true
- app/Config/Cookie.php — httponly=true, samesite='Lax', secure=true (sesuaikan bila tidak HTTPS)
- app/Config/Encryption.php — key dari hasil 'php spark key:generate --show'
- public/.htaccess — rewrite semua request ke index.php (CI4 default)
- public/uploads/foto/.htaccess — blokir eksekusi script, izinkan serving gambar
- public/assets/css/ dan public/assets/js/ — direktori kosong (placeholder)

Definisi selesai:
- php spark serve (atau akses domain) menampilkan halaman CI4 welcome tanpa error
- php spark db:connect berhasil tanpa exception
- Folder public/uploads/foto/ ada dan .htaccess-nya menolak eksekusi PHP

---

## Task-02 — Import Skema Database + Seed

Referensi SPEC: §2 (skema DB)
Referensi TASKS: Task-02

Dependensi: Task-01 (Database.php sudah dikonfigurasi)

File yang dibuat / diubah:
- Tidak ada file baru di repo — hanya operasi database

Langkah eksekusi:
1. mysql -u db_user -p santri_db < database/schema.sql
2. php -r "echo password_hash('Admin@2026', PASSWORD_BCRYPT, ['cost'=>12]);"
3. UPDATE admin SET password='<hash>' WHERE username='superadmin'

Definisi selesai:
- Tabel tamu, admin, pengaturan ada dengan kolom sesuai SPEC §2
- Tabel pengaturan memiliki 3 baris seed (judul_aplikasi, nama_instansi_ptsp, teks_header)
- Tabel admin memiliki 1 baris superadmin dengan hash bcrypt valid (bukan placeholder)
- password_verify('Admin@2026', $stored_hash) mengembalikan true

---

## Task-03 — Model Layer

Referensi SPEC: §2 (skema), §5 (kontrak output dari model)
Referensi TASKS: Task-03

Dependensi: Task-02 (tabel sudah ada di DB)

File yang dibuat:
- app/Models/TamuModel.php
  extends Model, $table='tamu', $primaryKey='id', $useTimestamps=false, $returnType='array'
  Methods: getPaginasi(array $filter, int $perPage=20), getSemuaDenganFilter(array $filter),
           getStatistik(), countUnread(), tandaiBaca(int $id), tandaiBacaSemua()
- app/Models/AdminModel.php
  extends Model, $table='admin', $allowedFields=[nama,username,password,role]
  Methods: findByUsername(string $username), updatePassword(int $id, string $hashedPassword)
- app/Models/PengaturanModel.php
  extends Model, $table='pengaturan', $allowedFields=[key,value]
  Methods: getAll(), set(string $key, string $value), setMany(array $data)

Acceptance criteria:
- Setiap model bisa di-instantiate tanpa error
- TamuModel::getStatistik() mengembalikan array dengan semua key: total_tamu, tamu_hari_ini, per_jenis, per_asal, top_keperluan, tren_7hari
- PengaturanModel::getAll() mengembalikan assoc array key => value (bukan array of rows)
- TamuModel::getPaginasi() mengembalikan array hasil + CI4 pager
- TamuModel::getSemuaDenganFilter() mengembalikan semua baris tanpa limit (untuk export CSV)

---

## Task-04 — AuthFilter + BaseController

Referensi SPEC: §4 (route + filter), §1 (Filters.php config)
Referensi TASKS: Task-04

Dependensi: Task-03 (PengaturanModel dan TamuModel sudah ada)

File yang dibuat / diubah:
- app/Filters/AuthFilter.php — implements FilterInterface; before(): cek session logged_in, redirect ke /admin/login bila tidak ada
- app/Config/Filters.php — daftarkan alias 'auth' => AuthFilter::class; terapkan ke 'admin/*' dengan pengecualian 'admin/login'
- app/Controllers/BaseController.php — di initController(): load PengaturanModel + TamuModel, set $this->pengaturan = PengaturanModel::getAll(), set $this->unreadCount = TamuModel::countUnread()

Acceptance criteria:
- Akses /admin tanpa login mengalihkan ke /admin/login (kode 302)
- Akses /admin/login tidak terkena loop redirect
- $this->pengaturan dan $this->unreadCount tersedia (tidak null) di controller admin mana pun
- Filter tidak memblokir route publik (GET /, POST /tamu/submit, GET /tamu/sukses)

---

## Task-05 — Auth Admin (Login / Logout)

Referensi SPEC: §5.8, §6.2, §7 (rate limit login, session hardening)
Referensi TASKS: Task-05

Dependensi: Task-04 (AuthFilter + BaseController)

File yang dibuat:
- app/Controllers/Admin/Auth.php
  login(): tampilkan form
  doLogin(): cek rate limit (session login_attempts + login_attempt_time, max 5 per 15 menit) → validasi input → AdminModel::findByUsername → password_verify → session set + regenerate → redirect /admin; gagal: increment attempts + redirect back dengan flash error
  logout(): session()->destroy() + redirect /admin/login
- app/Views/admin/login.php — form standalone (tanpa layout admin), field username + password, flash message error, warna brand Kemenag, mobile-friendly

Acceptance criteria:
- Login dengan kredensial benar mengarahkan ke /admin
- Login salah 5 kali berturut: percobaan ke-6 tidak diproses, pesan tunggu + sisa waktu tampil
- Session setelah login memiliki key: logged_in, admin_id, admin_nama, role
- Logout menghapus session (session()->destroy()) dan redirect ke /admin/login
- Halaman login tidak bisa diakses bila sudah login (redirect ke /admin)

---

## Task-06 — Layout Admin + Dashboard

Referensi SPEC: §5.3, §8 (design tokens)
Referensi TASKS: Task-06

Dependensi: Task-05 (admin bisa login)

File yang dibuat:
- app/Views/layouts/admin.php
  head: meta, title dari $pengaturan['judul_aplikasi'], link app.compiled.css, Chart.js CDN (pin 4.4.x), Leaflet CSS CDN
  sidebar: navigasi (Dashboard, Daftar Tamu, Kelola Admin, Pengaturan), link aktif via current URL
  topbar: nama admin dari session, badge unread count (tersembunyi bila 0), link logout
  slot konten: $this->renderSection('content')
  script block: Leaflet JS CDN + $this->renderSection('scripts')
- app/Controllers/Admin/Dashboard.php — index(): TamuModel::getStatistik() → view
- app/Views/admin/dashboard.php
  extends layouts/admin
  4 kartu: total tamu, tamu hari ini, belum dibaca, per jenis kelamin (rasio)
  Chart.js Line: tren_7hari (labels = tanggal, data = count)
  Chart.js Doughnut: per_jenis (Laki-laki/Perempuan)
  List top 5 keperluan
  Semua data dari PHP $data — tidak ada AJAX

Acceptance criteria:
- Dashboard tampil tanpa error JS di console
- Grafik Chart.js render dengan data aktual dari DB (angka 0 bila tabel kosong, tanpa error)
- Badge notif menampilkan jumlah tamu is_read=0 yang akurat; tersembunyi bila 0
- Title browser berisi nilai judul_aplikasi dari tabel pengaturan
- Sidebar link "Dashboard" aktif (visual berbeda) saat di halaman /admin

---

## Task-07 — Daftar Tamu (List, Search, Filter, Pagination)

Referensi SPEC: §5.4, §4 (route)
Referensi TASKS: Task-07

Dependensi: Task-06 (layout admin berfungsi)

File yang dibuat / diubah:
- app/Controllers/Admin/TamuAdmin.php (method index)
  Baca query params: q, tanggal_dari, tanggal_sampai, jenis_kelamin, asal, keperluan, sort, order, page
  Whitelist sort: hanya ['created_at', 'nama']
  Whitelist order: hanya ['asc', 'desc']
  TamuModel::getPaginasi($filter, 20) → view
- app/Views/admin/tamu/index.php
  extends layouts/admin
  Form filter: tanggal range, jenis_kelamin, asal, keperluan
  Input pencarian di atas tabel
  Tabel: id, nama, asal, no_hp, keperluan (truncate 50 char), created_at, aksi (detail)
  Header created_at + nama: klik untuk toggle sort asc/desc
  Pagination CI4 di bawah tabel
  Tombol Export CSV: link ke /admin/tamu/export dengan filter aktif sebagai query string
  Baris kosong: pesan "Belum ada data tamu."

Acceptance criteria:
- Pagination berfungsi (20 baris per halaman)
- Pencarian nama/no_hp/nama_instansi berfungsi (LIKE)
- Filter tanggal range berfungsi dan bisa dikombinasikan dengan filter lain
- Filter enum (jenis_kelamin, asal) berfungsi
- Sort asc/desc per kolom berfungsi
- Filter aktif ter-persist di URL (query string) saat pindah halaman
- Link Export CSV membawa semua filter aktif sebagai query string

---

## Task-08 — Detail Tamu + Peta Leaflet

Referensi SPEC: §5.6, §7 (XSS: semua output di-esc())
Referensi TASKS: Task-08

Dependensi: Task-07 (TamuAdmin controller sudah ada)

File yang dibuat / diubah:
- app/Controllers/Admin/TamuAdmin.php (method detail)
  Ambil tamu by id; jika tidak ada: throw PageNotFoundException
  TamuModel::tandaiBaca($id) — otomatis saat halaman dibuka
  Pass data ke view
- app/Views/admin/tamu/detail.php
  Layout dua kolom: info tamu (kiri) | foto + peta (kanan)
  Foto: tag img dengan path dari public/ + fallback teks "Tidak ada foto"
  Peta: div id="map" min 300px; di section scripts: bila lat/lng tidak null → L.map + L.tileLayer OSM + L.marker; bila null → teks "Lokasi tidak tersedia"
  Semua output variabel dibungkus esc()
  Tombol Kembali ke /admin/tamu

Acceptance criteria:
- Buka detail: is_read tamu berubah jadi 1 di DB, badge di topbar berkurang
- Foto tampil bila path ada di DB; teks fallback bila null
- Leaflet map render dengan marker di koordinat bila lat/lng tidak null
- Tidak ada error JS di console
- Tidak ada output variabel tanpa esc() di view ini

---

## Task-09 — Export CSV

Referensi SPEC: §5.5
Referensi TASKS: Task-09

Dependensi: Task-07 (filter logic sudah ada di TamuAdmin)

File yang dibuat / diubah:
- app/Controllers/Admin/TamuAdmin.php (method exportCsv)
  Baca filter dari query string (sama dengan index)
  TamuModel::getSemuaDenganFilter($filter) — tanpa paginate
  Header: Content-Type: text/csv; charset=UTF-8
  Header: Content-Disposition: attachment; filename="tamu_export_YYYYMMDD_HHiiss.csv"
  BOM: echo "\xEF\xBB\xBF"
  fopen('php://output', 'w') → fputcsv header row → loop fputcsv data → fclose → exit
  Kolom: ID, Nama, Jenis Kelamin, Asal, Instansi, Alamat, No HP, Keperluan, Lat, Lng, Foto, Waktu Kunjungan

Acceptance criteria:
- Klik Export mengunduh file .csv (bukan tampil di browser)
- Filter aktif dari halaman daftar tamu ter-apply di CSV (bukan export semua data)
- File CSV bisa dibuka di Excel/LibreOffice tanpa karakter rusak (BOM UTF-8 ada)
- Tidak ada output HTML sebelum CSV header (tidak ada whitespace atau echo sebelum header())

---

## Task-10 — Notifikasi In-App

Referensi SPEC: §5.7, §4 (route)
Referensi TASKS: Task-10

Dependensi: Task-08 (detail tamu sudah otomatis tandai dibaca)

File yang dibuat / diubah:
- app/Views/layouts/admin.php (update) — badge unread count di topbar; tombol "Tandai semua dibaca" di halaman daftar tamu (POST ke /admin/tamu/baca-semua); badge tersembunyi bila 0
- app/Controllers/Admin/TamuAdmin.php (method bacaSemua) — TamuModel::tandaiBacaSemua() + redirect /admin/tamu dengan flash sukses
- app/Config/Routes.php (tambah route) — POST /admin/tamu/baca-semua → TamuAdmin::bacaSemua
- public/assets/js/admin.js (opsional enhancement) — fetch POST ke /admin/tamu/{id}/baca, update badge tanpa reload; fallback POST biasa sudah cukup untuk P1

Acceptance criteria:
- Badge menampilkan jumlah tamu is_read=0 yang akurat di semua halaman admin
- Membuka detail tamu mengubah is_read jadi 1 dan badge berkurang (tanpa reload bila JS enhancement aktif)
- "Tandai semua dibaca" mengatur semua is_read=1 sekaligus
- Badge tidak tampil (atau tampil 0) bila tidak ada tamu baru

---

## Task-11 — Kelola Admin (CRUD)

Referensi SPEC: §5.9, §6.3, §7
Referensi TASKS: Task-11

Dependensi: Task-06 (layout admin berfungsi), Task-04 (AuthFilter aktif)

File yang dibuat:
- app/Controllers/Admin/AdminUser.php
  index(): AdminModel::findAll() → view
  buat(): tampilkan form kosong
  simpan(): validasi + hash password (cost 12) + AdminModel::insert() + redirect
  edit($id): ambil admin by id → view form terisi
  update($id): validasi + hash password bila diisi + AdminModel::update() + redirect
  konfirmasiHapus($id): cek role superadmin, tampilkan halaman konfirmasi
  hapus($id): cek role superadmin, cek tidak hapus diri sendiri, AdminModel::delete($id) + redirect
  Cek role: if (session()->get('role') !== 'superadmin') redirect /admin with error
- app/Views/admin/admin_user/index.php — tabel: nama, username, role, created_at, aksi (edit | hapus bila superadmin)
- app/Views/admin/admin_user/form.php — dipakai untuk buat dan edit (beda action + nilai default); field: nama, username, password, password_confirm, role (select); hint saat edit: kosong = tidak ubah password

Acceptance criteria:
- Admin baru bisa dibuat, login berhasil dengan password yang baru (bcrypt cost 12)
- Edit admin: username bisa diubah, unique constraint dicek (tidak bentrok dengan admin lain)
- Edit admin: password kosong = password lama tidak berubah di DB
- Hapus: hanya superadmin bisa akses; superadmin tidak bisa hapus akunnya sendiri
- Role 'admin' biasa: akses /admin/admin-user/(:num)/hapus → redirect /admin dengan pesan "Akses ditolak."

---

## Task-12 — Halaman Pengaturan

Referensi SPEC: §5.10
Referensi TASKS: Task-12

Dependensi: Task-04 (PengaturanModel sudah ada di BaseController)

File yang dibuat:
- app/Controllers/Admin/Pengaturan.php
  index(): PengaturanModel::getAll() → view form terisi
  simpan(): validasi (required, max_length untuk setiap key) + PengaturanModel::setMany($data) + redirect dengan flash sukses
  Cek role superadmin di kedua method
- app/Views/admin/pengaturan/index.php — form 3 field: judul_aplikasi, nama_instansi_ptsp, teks_header (textarea); flash message sukses/error

Acceptance criteria:
- Ubah judul_aplikasi: title browser dan nama di sidebar berubah setelah refresh
- Simpan dengan field kosong: gagal validasi, pesan error tampil, data lama tidak berubah
- Akses oleh role 'admin' biasa: redirect ke /admin dengan pesan "Akses ditolak."

---

## Task-13 — Layout Publik + View Form Buku Tamu

Referensi SPEC: §5.1, §5.2, §6.1
Referensi TASKS: Task-13

Dependensi: Task-12 (pengaturan tersedia via BaseController)

File yang dibuat:
- app/Views/layouts/public.php — head: meta viewport, title, link app.compiled.css; header: logo Kemenag + nama_instansi_ptsp + teks_header dari $pengaturan; slot konten + script
- app/Views/tamu/form.php — extends layouts/public; 10 field dalam urutan benar:
  1. nama (text)
  2. jenis_kelamin (radio Laki-laki/Perempuan)
  3. asal_pengunjung (radio Perorangan/Instansi)
  4. nama_instansi (text, hidden secara default via CSS, muncul bila asal=instansi)
  5. alamat (textarea)
  6. no_hp (text)
  7. keperluan (textarea)
  8. lat (hidden input)
  9. lng (hidden input)
  10. foto (file input, accept image/*)
  Honeypot: input _hp tersembunyi (display:none, tabindex=-1, autocomplete=off)
  Tombol "Deteksi Lokasi Otomatis" + status lokasi + fallback input manual lat/lng (hidden awalnya)
  csrf_field() di form
  Flash message errors di atas form
  Submit button

Acceptance criteria:
- Form tampil di mobile (375px) tanpa overflow horizontal
- Field nama_instansi tersembunyi saat asal=perorangan (CSS/JS)
- Honeypot field tidak terlihat user (display:none) tapi ada di DOM
- csrf_field() ada di form
- Flash error tampil bila ada redirect back with errors

---

## Task-14 — Controller Tamu (Submit Form Publik)

Referensi SPEC: §5.2, §6.1, §7 (upload, honeypot, rate limit, CSRF)
Referensi TASKS: Task-14

Dependensi: Task-13 (view form sudah ada), Task-03 (TamuModel)

File yang dibuat / diubah:
- app/Controllers/Tamu.php
  index(): pass $this->pengaturan → view tamu/form
  submit():
    1. Cek honeypot (_hp): bila terisi → redirect sukses (silent)
    2. Cek rate limit (session: submit_count, submit_time; max 3 per 10 menit): bila limit → redirect sukses (silent)
    3. Validasi input (semua aturan dari SPEC §6.1)
    4. Upload foto: cek mime via finfo_file (image/jpeg, image/png, image/webp), whitelist ekstensi (jpg/jpeg/png/webp), max 2MB, rename ke bin2hex(random_bytes(16)).'.'.$ext, simpan ke FCPATH.'uploads/foto/'
    5. TamuModel::insert($data) — $data['foto'] adalah path relatif atau null
    6. Increment session submit counter
    7. redirect('/tamu/sukses')
  sukses(): view halaman sukses

Aturan validasi (SPEC §6.1 — kutip langsung, jangan diubah tanpa persetujuan Lead):
- nama: required|max_length[255]
- jenis_kelamin: required|in_list[Laki-laki,Perempuan]
- asal_pengunjung: required|in_list[perorangan,instansi]
- nama_instansi: permit_empty|max_length[255] + cek manual: wajib bila asal_pengunjung = 'instansi'
- alamat: required
- no_hp: required|regex_match[/^(0|62|\+62)[0-9]{8,13}$/]
- keperluan: required
- lat: permit_empty|decimal
- lng: permit_empty|decimal
- foto: uploaded[foto]|max_size[foto,2048]|mime_in[foto,image/jpeg,image/png,image/webp]|ext_in[foto,jpg,jpeg,png,webp]

Catatan: status wajib/opsional `foto` dan `lat`/`lng` masih menunggu keputusan Lead
(SPEC.md — PERTANYAAN UNTUK LEAD no. 1 dan 3). Implementasi mengikuti aturan di atas
(foto wajib, koordinat opsional) sampai ada keputusan berbeda.

Acceptance criteria:
- Submit valid: data tersimpan di DB, redirect ke /tamu/sukses
- Honeypot terisi: redirect ke /tamu/sukses (silent, tidak disimpan)
- Rate limit terlampaui: redirect ke /tamu/sukses (silent)
- Validasi gagal: redirect back with input + errors, data tidak tersimpan
- Upload foto PHP.jpg (mime bukan image/*): ditolak, pesan validasi tampil
- Foto disimpan dengan nama acak (bukan nama asli), path relatif tersimpan di kolom foto

---

## Task-15 — Halaman Sukses

Referensi TASKS: Task-15
Dependensi: Task-13 (layout publik)

File yang dibuat:
- app/Views/tamu/sukses.php — extends layouts/public; pesan terima kasih; link kembali ke form (untuk tamu berikutnya)

Acceptance criteria:
- Halaman tampil setelah submit valid maupun silent drop (bot/rate limit)
- Ada link/tombol kembali ke halaman form (GET /)
- Tampil dengan layout publik (header instansi, css ter-apply)

---

## Task-16 — JavaScript Form Publik (Geolocation + Conditional Field)

Referensi SPEC: §5.2, §6.1
Referensi TASKS: Task-16

Dependensi: Task-13 (elemen HTML yang dibutuhkan sudah ada di form.php)

File yang dibuat:
- public/assets/js/form-publik.js
  IIFE, tidak ada dependensi eksternal
  Fungsi 1 — Geolocation: klik #btn-deteksi-lokasi → navigator.geolocation.getCurrentPosition; sukses: isi #lat dan #lng (hidden), tampilkan koordinat di #status-lokasi; gagal: sembunyikan tombol, tampilkan #input-manual-lokasi (div dengan dua input text lat+lng manual)
  Fungsi 2 — Conditional field: change listener pada radio asal_pengunjung; bila 'instansi': tampilkan #wrap-nama-instansi, required=true; bila 'perorangan': sembunyikan, required=false, clear value

Elemen HTML yang dibutuhkan di form.php (Task-13):
- button#btn-deteksi-lokasi
- p#status-lokasi
- input#lat[type=hidden][name=lat]
- input#lng[type=hidden][name=lng]
- div#input-manual-lokasi[style=display:none] (berisi dua input text lat + lng manual)
- div#wrap-nama-instansi[style=display:none] (berisi input nama_instansi)

Acceptance criteria:
- Klik "Deteksi Lokasi": bila diizinkan, lat/lng terisi di input hidden, status tampil
- Bila ditolak: input manual muncul; user bisa ketik koordinat
- Bila lat/lng kosong, form tetap bisa disubmit (koordinat NULL di DB)
- nama_instansi muncul/tersembunyi sesuai pilihan asal_pengunjung
- Tidak ada error JS di console pada halaman form

---

## Task-17 — Tailwind CSS v4 Setup + Design Tokens

Referensi SPEC: §8
Referensi TASKS: Task-17

Dependensi: Task-13 (view form sudah ada — butuh CSS untuk tampil benar)

File yang dibuat / diubah:
- public/assets/css/app.css (source Tailwind v4)
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
    --radius-sm:  0px;
    --radius-md:  0px;
    --radius-pill: 9999px;
    --font-size-xs:  0.75rem;
    --font-size-sm:  0.875rem;
    --font-size-base: 1rem;
    --font-size-lg:  1.25rem;
    --font-size-xl:  1.625rem;
    --font-size-2xl: 2rem;
    --line-height-tight:  1.2;
    --line-height-normal: 1.6;
  }
- public/assets/css/app.compiled.css — OUTPUT dari build command (di-commit ke repo)
- package.json — sudah ada; verifikasi tailwindcss@next dan @tailwindcss/cli@next terpasang

Build command (jalankan di environment develop, bukan di server):
npx @tailwindcss/cli -i public/assets/css/app.css -o public/assets/css/app.compiled.css --minify

Aturan anti-slop yang WAJIB dipatuhi saat menulis class Tailwind di view:
- Tidak ada shadow-lg, shadow-xl pada elemen diam
- Tidak ada bg-gradient-*
- Border-radius tidak dicampur (pilih satu dari --radius-sm atau --radius-pill)
- Button primary: bg-brand-green text-white tanpa gradient
- Surface card: bg-surface-raised border border-border

Link di layout: link rel="stylesheet" href="<?= base_url('assets/css/app.compiled.css') ?>"

Acceptance criteria:
- File app.compiled.css ter-build tanpa error
- Token brand-green (#006B2E) dan brand-gold (#E5A400) bisa dipakai via class Tailwind di view
- Tidak ada bg-gradient-* di view mana pun di seluruh repo
- Build dijalankan ulang bila ada perubahan class baru di view

---

## Task-18 — Final Hardening + .htaccess + QA Checklist

Referensi SPEC: §7 (checklist keamanan lengkap)
Referensi TASKS: Task-18

Dependensi: Task-01 s/d Task-17 semua selesai

File yang dibuat / diubah:
- .htaccess (root level) — redirect semua request ke public/ bila domain tidak langsung ke public/
- public/.htaccess — verifikasi isi CI4 default (rewrite ke index.php)
- public/uploads/foto/.htaccess — FilesMatch blokir ekstensi PHP, php_flag engine off, Options -Indexes -ExecCGI (PENTING: jangan Deny from all global — foto harus tetap bisa disajikan)
- app/Controllers/BaseController.php (tambah security headers di initController):
  X-Content-Type-Options: nosniff
  X-Frame-Options: SAMEORIGIN
  Referrer-Policy: strict-origin-when-cross-origin

QA Checklist — semua item wajib dicentang oleh agen koding sebelum lapor selesai:

Database:
- [ ] Semua query via CI4 Model QueryBuilder — tidak ada query string mentah mengandung input user tanpa binding
- [ ] Tidak ada $db->query("... " . $id) — wajib pakai parameter binding

Autentikasi:
- [ ] Password disimpan dengan password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12])
- [ ] Verifikasi dengan password_verify($input, $hash)
- [ ] Session di-regenerate saat login: session()->regenerate(true)
- [ ] Session di-destroy saat logout: session()->destroy()
- [ ] Cookie session: httponly=true, samesite=Lax, secure=true (bila HTTPS)

CSRF:
- [ ] CI4 CSRF protection aktif di App.php (CSRFProtection=true)
- [ ] Semua form POST menyertakan csrf_field()
- [ ] CSRF token diverifikasi sebelum memproses data POST (CI4 filter csrf)

Upload File:
- [ ] Mime type dicek dengan finfo_file() (bukan hanya ekstensi)
- [ ] Ekstensi whitelist: jpg, jpeg, png, webp
- [ ] Ukuran maksimum: 2 MB (2048 KB)
- [ ] Nama file di-rename ke acak: bin2hex(random_bytes(16)).'.'.$ext
- [ ] File disimpan di public/uploads/foto/
- [ ] .htaccess di folder upload: blokir eksekusi PHP TAPI tetap izinkan foto disajikan ke browser

XSS:
- [ ] Semua output variabel di view dibungkus esc($var)
- [ ] Tidak ada echo $var tanpa escaping di view mana pun

Anti-Spam Form Publik:
- [ ] Honeypot field ada di form: input _hp, display:none, tabindex=-1, autocomplete=off
- [ ] Cek honeypot di controller: bila terisi → silent drop (redirect sukses)
- [ ] Rate limit: max 3 submit form publik per 10 menit (session counter)

Rate Limit Login:
- [ ] Max 5 percobaan gagal per 15 menit (session: login_attempts, login_attempt_time)
- [ ] Setelah limit: tampilkan sisa waktu tunggu, tidak proses password_verify

Path Traversal:
- [ ] Gunakan CI4 $file->move(FCPATH.'uploads/foto/', $newName) — tidak ada manipulasi path manual
- [ ] ID pada route (:num) dijamin integer oleh CI4 route constraint

Mode Produksi:
- [ ] CI_ENVIRONMENT = 'production' di App.php
- [ ] DBDebug = false di Database.php
- [ ] Log threshold diset 4 (warning ke atas)

Header HTTP:
- [ ] X-Content-Type-Options: nosniff
- [ ] X-Frame-Options: SAMEORIGIN
- [ ] Referrer-Policy: strict-origin-when-cross-origin

Deploy checklist:
- [ ] Tidak ada file .env di root proyek
- [ ] app.compiled.css ter-commit dan ter-upload ke server
- [ ] Tidak ada PHP error/warning di writable/logs/ saat semua flow utama dijalankan
- [ ] Aplikasi bisa di-deploy ke InfinityFree (tanpa dotenv, tanpa Redis, tanpa CLI PHP production)

Acceptance criteria:
- Semua item QA checklist di atas dicentang dan dilaporkan agen koding
- Tidak ada PHP error/warning di log saat menjalankan semua flow utama
- Semua test manual (form submit, login, ekspor, upload foto) berjalan tanpa error
- Aplikasi siap deploy ke InfinityFree

---

## Urutan Eksekusi Ringkas

Task-01 → Task-02 → Task-03 → Task-04 → Task-05 → Task-06 → Task-07 → Task-08 → Task-09 → Task-10 → Task-11 → Task-12 → Task-13 → Task-14 → Task-15 → Task-16 → Task-17 → Task-18

Setiap task bergantung pada task sebelumnya. Jangan lewati urutan.
Selesaikan dan verifikasi satu task sebelum pindah ke task berikutnya.

---

**Versi:** 1.0.0
**Tanggal:** 2026-10-18
**Dikelola oleh:** Agen arsitek ([REDACTED])
**Sumber kebenaran:** docs/TASKS.md (checklist detail), docs/SPEC.md (kontrak teknis)
