# Feature: Redesign UI/UX Shell dan Halaman Kerja Panel Admin dengan Tasteskill v2 (Fase 2)

**From build-plan:** feature 12
**Build attempt:** 1
**Branch:** feature/redesign-ui-ux-shell-dan-halaman-kerja-panel-admin-dengan-tasteskill-v2-fase-2
**Status:** verified

## Goal

Merancang ulang shell navigasi Panel Admin (`layouts/admin.php`) beserta seluruh halaman kerja petugas (`dashboard.php`, `tamu/index.php`, `tamu/detail.php`, `admin-user/index.php`, `admin-user/form.php`, `admin-user/hapus.php`, `pengaturan/index.php`, dan pagination `pager/admin_pagination.php`) agar mematuhi kaidah **App Screens Tasteskill v2** (`--app`), responsif di perangkat mobile maupun desktop, bebas dari pola slop visual (`uppercase tracking-wide`, em dash, duplikasi `<h1>`, atau halaman tanpa `<h1>`), tanpa mengubah logika bisnis controller, model, rute, maupun kontrak keamanan yang sudah ada.

## Design reference

Mengacu pada aturan **App Screens** di `.agents/skills/design-taste-frontend/SKILL.md` (`references/app-screens.md`) dan token sistem desain di `themes/app.css`:

- **Palet Warna & Hirarki Permukaan (`--keep palette,type`)**:
  - Latar dasar ruang kerja: `--color-ground` (`#F8F7F4`) dan `--color-ground-warm` (`#F2F0EA`).
  - Panel & kartu kerja: `--color-surface` (`#FFFFFF`) dan `--color-surface-raised` (`#FAFAF8`) dengan batas halus `--color-border` (`#E4E2DC`).
  - Aksen utama: Hijau Kementerian Agama `--color-brand-500` (`#006B2E`), teks aktif `--color-brand-700` (`#004A1F`), latar status aktif `--color-brand-50` (`#EBF5EF`), dan aksen emas `--color-brand-gold` (`#E5A400`).
- **Tipografi & Keterbacaan Data**:
  - Judul halaman (`<h1>`): `--font-serif` (`Playfair Display`), ukuran `24px-30px` (`text-2xl font-serif font-bold text-ink`), diikuti subjudul penjelas minimal `16px` (`text-base text-ink-muted`) agar jelas fungsi halamannya.
  - Teks antarmuka, tabel, dan kontrol: `--font-sans` (`Plus Jakarta Sans`).
  - Seluruh angka metrik KPI, nomor telepon, tanggal/waktu, nomor urut, dan lencana jumlah wajib menggunakan class `tabular-nums`.
- **Aturan Wajib App Screens Tasteskill v2**:
  - **Tepat satu `<h1>` per halaman**: Topbar di `layouts/admin.php` tidak boleh menggunakan `<h1>` atau `<h2>` judul utama, melainkan teks indikator konteks/breadcrumb. Setiap view anak wajib memiliki tepat satu `<h1>` di bagian atas area kontennya.
  - **Tanpa `uppercase tracking-wide` (Eyebrow Caps)**: Seluruh label kartu KPI, subjudul panel grafik, dan label atribut detail tamu wajib menggunakan *sentence case* (`text-sm font-medium text-ink-muted` atau `text-xs font-medium text-ink-muted`), bukan huruf kapital renggang.
  - **Tanpa Karakter Em Dash (`\u2014` / `&mdash;`)**: Ganti seluruh em dash pada komentar PHP, tag `<title>`, dan tampilan dengan tanda hubung biasa (`-`).
  - **Tinggi Kartu Seimbang (`items-stretch`)**: Kartu yang berada pada satu baris grid wajib memiliki tinggi yang rata di bagian bawah (`flex flex-col justify-between`).

## In scope

- Memperbarui `app/Views/layouts/admin.php`:
  - Mengganti logo berlatar putih `logo-kemenag.jpg` menjadi `images/logo-kemenag-transparent.png`.
  - Menambahkan tombol menu mobile (`#mobile-menu-btn`) dan panel navigasi responsif (`#mobile-nav-panel`) untuk layar `< 768px`.
  - Memindahkan indikator jumlah tamu belum dibaca (`$unreadCount`) menjadi lencana (*badge*) langsung pada menu **Daftar Tamu** di sidebar & menu mobile, serta menampilkan nama dan role petugas di topbar.
  - Mempertahankan form POST `#admin-logout-form` dan dialog konfirmasi `#admin-logout-btn`.
- Memperbarui `app/Views/admin/dashboard.php`:
  - Menambahkan header halaman dengan satu `<h1>Dashboard Statistik PTSP</h1>` dan subjudul ringkas.
  - Memperbarui 4 kartu KPI dengan angka `tabular-nums` tanpa `uppercase tracking-wide`.
  - Memperbarui kartu grafik Chart.js dan menambahkan visualisasi bar proporsional pada panel **Top 5 Keperluan** serta **Distribusi Asal Pengunjung**.
  - Mempertahankan banner peringatan keamanan kata sandi bawaan (`$defaultPasswordWarning`).
- Membuat template pagination Tailwind CSS di `app/Views/pager/admin_pagination.php` dan mendaftarkan alias `admin_tailwind` di `app/Config/Pager.php`.
- Memperbarui `app/Views/admin/tamu/index.php`:
  - Menambahkan header halaman dengan satu `<h1>Daftar Kunjungan Tamu</h1>`, subjudul, dan tombol aksi (`Export CSV`, `Tandai Semua Dibaca`).
  - Merapikan bilah tab siklus hidup (`Tamu Aktif`, `Arsip Riwayat`, `Tong Sampah`), panel pengarsipan massal, dan panel filter pencarian menjadi ringkas serta bebas `uppercase tracking-wide`.
  - Merapikan tabel data tamu dengan `tabular-nums` dan menghubungkan pagination ke template `admin_tailwind`.
- Memperbarui `app/Views/admin/tamu/detail.php`, `app/Views/admin/admin-user/index.php`, `app/Views/admin/admin-user/form.php`, `app/Views/admin/admin-user/hapus.php`, dan `app/Views/admin/pengaturan/index.php`:
  - Menyeragamkan header satu `<h1>` beserta subjudul penjelas (`text-base text-ink-muted`).
  - Menghapus seluruh penggunaan `uppercase tracking-wide` dan karakter em dash (`\u2014`).

## Out of scope

- Perubahan pada logika bisnis di `app/Controllers/*`, `app/Models/*`, `app/Filters/*`, `app/Config/Routes.php`, atau skema database `database/schema.sql`.
- Perubahan pada halaman publik (`app/Views/layouts/publik.php`, `app/Views/tamu/*`, dan `app/Views/admin/login.php`) yang sudah selesai pada Feature 11 (Fase 1).

## Build loop

Follow `workflow.stepReview: "feature"` and `workflow.checkpointCommits: "disabled"` from `blueprint/config.json`:

- Buat dan berpindah ke cabang `feature/redesign-ui-ux-shell-dan-halaman-kerja-panel-admin-dengan-tasteskill-v2-fase-2`.
- Kerjakan seluruh 5 langkah di bawah secara berurutan tanpa membuat checkpoint commit per langkah.
- Jalankan verifikasi di setiap akhir langkah sesuai kriteria `Done when`, lalu sajikan satu paket tinjauan utuh di akhir implementasi.

## Build steps

- [x] 1. **Perbarui shell navigasi admin di `app/Views/layouts/admin.php` dengan dukungan menu mobile dan logo transparan**
  - Buka `app/Views/layouts/admin.php`.
  - Pastikan komentar pembuka dan tag `<title>` menggunakan tanda hubung biasa (`-`), bukan em dash (`\u2014`):
    ```php
    <title><?= $this->renderSection('title', true) ?> - <?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?></title>
    ```
  - Tambahkan variabel `$adminRole = session('role') ?? 'admin';` di blok PHP atas.
  - Perbarui fungsi `isActive(string $path)` agar mendukung sub-rute (misalnya ketika membuka `/admin/tamu/12`, menu `Daftar Tamu` tetap aktif):
    ```php
    function isActive(string $path): string
    {
        $current = trim(service('request')->getPath(), '/');
        $target  = trim($path, '/');
        $active  = ($target === 'admin')
            ? ($current === 'admin')
            : ($current === $target || str_starts_with($current, $target . '/'));

        return $active
            ? 'bg-brand-50 text-brand-700 font-semibold'
            : 'text-ink-muted hover:bg-surface-raised hover:text-ink font-medium';
    }
    ```
  - Letakkan satu `<div class="brand-stripe" aria-hidden="true"></div>` di bagian paling atas `<body>` (ubah `<body>` menjadi `class="bg-ground text-ink antialiased min-h-screen flex flex-col"`), lalu di bawahnya buat `<div class="flex-1 flex min-h-0">`.
  - **Sidebar Desktop (`<aside class="hidden md:flex md:w-64 shrink-0 flex-col bg-surface border-r border-border">`)**:
    - Header sidebar: Gunakan `<img src="<?= base_url('images/logo-kemenag-transparent.png') ?>" alt="Logo Kementerian Agama" class="h-10 w-10 object-contain">`, nama aplikasi `<?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?>`, dan subteks `Panel Petugas PTSP`.
    - Navigasi (`<nav class="flex-1 p-4 space-y-1" aria-label="Navigasi Utama Admin">`):
      - Tautan `Dashboard` (`site_url('admin')`).
      - Tautan `Daftar Tamu` (`site_url('admin/tamu')`) dengan badge `$unreadCount` di sisi kanan item menu bila `($unreadCount ?? 0) > 0`:
        ```php
        <a href="<?= site_url('admin/tamu') ?>" class="flex items-center justify-between gap-3 px-4 py-3 text-sm <?= isActive('admin/tamu') ?>">
            <span>Daftar Tamu</span>
            <?php if (($unreadCount ?? 0) > 0): ?>
                <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold text-white bg-danger rounded-full tabular-nums" aria-label="<?= esc((string) ((int) $unreadCount)) ?> tamu belum dibaca">
                    <?= esc((string) ((int) $unreadCount)) ?>
                </span>
            <?php endif; ?>
        </a>
        ```
      - Jika `session('role') === 'superadmin'`, tampilkan tautan `Kelola Admin` (`site_url('admin/admin-user')`) dan `Pengaturan` (`site_url('admin/pengaturan')`).
    - Bagian bawah sidebar (`<div class="p-4 border-t border-border space-y-2">`):
      - Tautan pintas `<a href="<?= site_url('/') ?>" target="_blank" rel="noopener" class="block w-full text-center px-4 py-2 text-xs font-semibold text-brand-700 hover:bg-brand-50 rounded-full transition-colors">Lihat Buku Tamu Publik &nearr;</a>`.
      - Form `#admin-logout-form` (`action="<?= site_url('admin/logout') ?>" method="post"`) beserta `<?= csrf_field() ?>` dan tombol `#admin-logout-btn`.
  - **Topbar & Navigasi Mobile**:
    - Di dalam `<header class="bg-surface border-b border-border">`:
      - Sisi kiri: Tombol hamburger `#mobile-menu-btn` (`type="button" class="md:hidden inline-flex items-center justify-center p-2 text-ink hover:bg-surface-raised" aria-expanded="false" aria-controls="mobile-nav-panel" aria-label="Buka menu navigasi"`) dan teks konteks `<span class="text-sm font-semibold text-ink-muted"><?= $this->renderSection('title') ?></span>` *(Penting: Jangan gunakan `<h1>` atau `<h2>` di topbar agar tidak bentrok dengan `<h1>` halaman anak)*.
      - Sisi kanan: Informasi petugas (`$adminNama`), badge role (`superadmin` / `admin`), dan inisial `$adminInisial`.
    - Tambahkan `#mobile-nav-panel` (`class="hidden md:hidden bg-surface border-b border-border px-4 pt-2 pb-4 space-y-1"`) tepat di bawah topbar yang memuat daftar menu yang sama persis beserta tombol `.mobile-logout-trigger` yang memicu klik pada `#admin-logout-btn`.
    - Di dalam `<script>`, tambahkan event listener toggle untuk `#mobile-menu-btn` dan `.mobile-logout-trigger`, sambil mempertahankan script `toastShow` dan `confirmDialog` pada `#admin-logout-btn`.
  - **Done when:** `php -l app/Views/layouts/admin.php` bebas syntax error, logo menggunakan `logo-kemenag-transparent.png`, tidak ada karakter em dash, tidak ada tag `<h1>`/`<h2>` di topbar, dan tombol menu mobile serta form logout `#admin-logout-form` berfungsi.

- [x] 2. **Redesign halaman Dashboard Admin di `app/Views/admin/dashboard.php` sesuai kaidah App Screens**
  - Buka `app/Views/admin/dashboard.php`.
  - Tambahkan blok header halaman di bagian paling atas section `content`:
    ```php
    <div class="mb-8 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-serif font-bold text-ink">Dashboard Statistik PTSP</h1>
            <p class="text-base text-ink-muted mt-1">Ringkasan kehadiran pengunjung, tren harian, dan distribusi layanan buku tamu.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= site_url('admin/tamu') ?>" class="btn-secondary text-sm">Kelola Daftar Tamu</a>
        </div>
    </div>
    ```
  - Pertahankan blok alert peringatan keamanan `$defaultPasswordWarning` (`role="alert"`).
  - **Baris 4 Kartu KPI (`<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8 items-stretch">`)**:
    - Setiap kartu menggunakan `class="bg-surface border border-border p-5 flex flex-col justify-between"`:
      1. **Total Tamu**: Label `<p class="text-sm font-medium text-ink-muted mb-2">Total Kunjungan</p>`, Nilai `<p class="text-3xl font-bold text-ink tabular-nums"><?= esc((string) ($statistik['total_tamu'] ?? 0)) ?></p>`, Keterangan bawah `<p class="text-xs text-ink-muted mt-2">Akumulasi tamu aktif tercatat</p>`.
      2. **Tamu Hari Ini**: Label `<p class="text-sm font-medium text-ink-muted mb-2">Tamu Hari Ini</p>`, Nilai `<p class="text-3xl font-bold text-brand-700 tabular-nums"><?= esc((string) ($statistik['tamu_hari_ini'] ?? 0)) ?></p>`, Keterangan bawah `<p class="text-xs text-ink-muted mt-2">Kunjungan masuk hari ini (WITA)</p>`.
      3. **Belum Dibaca**: Label `<p class="text-sm font-medium text-ink-muted mb-2">Belum Ditinjau</p>`, Nilai `<p class="text-3xl font-bold <?= (($statistik['unread_count'] ?? 0) > 0) ? 'text-danger' : 'text-ink' ?> tabular-nums"><?= esc((string) ($statistik['unread_count'] ?? 0)) ?></p>`, Keterangan bawah `<p class="text-xs text-ink-muted mt-2">Menunggu peninjauan petugas</p>`.
      4. **Rasio Jenis Kelamin**: Label `<p class="text-sm font-medium text-ink-muted mb-2">Rasio Jenis Kelamin</p>`, Nilai baris `tabular-nums` untuk Laki-laki (`$lakiPercent%`) dan Perempuan (`$perempuanPercent%`).
  - **Baris Grafik (`<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 items-stretch">`)**:
    - Ganti heading `uppercase tracking-wide` menjadi `<h2 class="text-base font-bold text-ink mb-1">Tren Kunjungan 7 Hari Terakhir</h2>` dan `<h2 class="text-base font-bold text-ink mb-1">Proporsi Jenis Kelamin</h2>` beserta subteks singkat `text-xs text-ink-muted mb-4`.
    - Pertahankan `<canvas id="trenChart"></canvas>` dan `<canvas id="jenisChart"></canvas>` beserta konfigurasi Chart.js di section `scripts`.
  - **Baris Top 5 Keperluan & Distribusi Asal Pengunjung (`<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">`)**:
    - Ganti heading `uppercase tracking-wide` menjadi `<h2 class="text-base font-bold text-ink mb-4">5 Keperluan Terbanyak</h2>` dan `<h2 class="text-base font-bold text-ink mb-4">Distribusi Asal Pengunjung</h2>`.
    - Tampilkan setiap item dengan angka `tabular-nums` dan bar proporsional sederhana (`<div class="w-full bg-ground-warm h-2 mt-1.5"><div class="bg-brand-500 h-2" style="width: ...%"></div></div>`) sehingga perbandingan jumlah terlihat jelas secara visual.
  - **Done when:** `php -l app/Views/admin/dashboard.php` bebas syntax error, halaman memiliki tepat satu `<h1>`, tidak ada class `uppercase tracking-wide`, dan seluruh angka metrik memiliki class `tabular-nums`.

- [x] 3. **Buat view pagination Tailwind di `app/Views/pager/admin_pagination.php` dan daftarkan di `app/Config/Pager.php`**
  - Buka `app/Config/Pager.php`.
  - Di dalam properti `public array $templates`, tambahkan baris `'admin_tailwind' => 'App\Views\pager\admin_pagination',` tanpa menghapus template bawaan (`default_full`, `default_simple`, `default_head`).
  - Buat file baru `app/Views/pager/admin_pagination.php`:
    - Panggil `$pager->setSurroundCount(2);`.
    - Render `<nav aria-label="Navigasi halaman" class="flex items-center gap-1" data-ui>` yang menampilkan tombol *Sebelumnya* (`$pager->hasPrevious()`), daftar nomor halaman (`$pager->links()`) dengan class `tabular-nums` (halaman aktif diberi `bg-brand-500 text-white font-semibold`, halaman tidak aktif diberi `bg-surface border border-border text-ink hover:bg-surface-raised`), serta tombol *Berikutnya* (`$pager->hasNext()`).
  - **Done when:** `php -l app/Config/Pager.php` dan `php -l app/Views/pager/admin_pagination.php` bebas syntax error, serta template `admin_tailwind` terdaftar di `Config\Pager`.

- [x] 4. **Redesign halaman Daftar Tamu (`app/Views/admin/tamu/index.php`) dengan filter ringkas dan tabel data terstruktur**
  - Buka `app/Views/admin/tamu/index.php`.
  - Hapus karakter em dash (`\u2014`) pada komentar pembuka file.
  - Tambahkan header halaman utama di bagian paling atas section `content`:
    ```php
    <div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-serif font-bold text-ink">Daftar Kunjungan Tamu</h1>
            <p class="text-base text-ink-muted mt-1">Kelola buku tamu aktif, arsip riwayat kunjungan, dan pembersihan data.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="<?= $exportUrl ?>" class="btn-secondary text-sm">Export CSV</a>
            <?php if ($tab_aktif === 'aktif'): ?>
                <form action="<?= site_url('admin/tamu/baca-semua') ?>" method="post" class="inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-primary text-sm">Tandai Semua Dibaca</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    ```
  - Pada bilah tab (`Tamu Aktif`, `Arsip Riwayat`, `Tong Sampah`), tambahkan class `tabular-nums` pada badge penghitung (`$count_aktif`, `$count_arsip`, `$count_sampah`).
  - Pada panel **Alat Pengarsipan Massal** (`$isSuperadmin && $tab_aktif === 'arsip'`), ganti `uppercase tracking-wide` pada `<h4>` menjadi `<h2 class="text-sm font-bold text-ink mb-2">Alat Pengarsipan Massal (Tutup Buku Tahunan)</h2>`.
  - Pada **Filter Panel**:
    - Tambahkan atribut `data-ui` pada kontainer filter (`<div class="bg-surface border border-border p-5 mb-6" data-ui>`).
    - Ganti heading `uppercase tracking-wide` menjadi `<div class="flex items-center justify-between mb-4"><h2 class="text-sm font-bold text-ink">Filter & Pencarian Data (<?= esc(ucfirst($tab_aktif)) ?>)</h2><span class="text-xs text-ink-muted tabular-nums">Total hasil: <strong class="text-ink"><?= esc((string) $total) ?></strong> data</span></div>`.
    - Pertahankan seluruh input filter (`tab`, `q`, `tahun`, `tanggal_dari`, `tanggal_sampai`, `jenis_kelamin`, `asal`, `keperluan`) beserta tombol `Filter` dan `Reset`.
  - Pada **Tabel Data Tamu**:
    - Tambahkan atribut `data-ui` pada kontainer tabel (`<div class="overflow-x-auto bg-surface border border-border mb-6" data-ui>`).
    - Tambahkan class `tabular-nums` pada sel ID (`$row['id']`), No. HP (`$row['no_hp']`), dan Tanggal (`$row['created_at']`).
    - Pertahankan seluruh tombol aksi (`Detail`, `Arsip`, `Hapus`, `Aktifkan`, `Pulihkan`, `Hapus Permanen`) beserta class `.action-confirm-btn` dan script `confirmDialog`.
  - Pada blok pagination (sekitar baris 316), ubah pemanggilan `$pager->links('default')` menjadi:
    ```php
    <?= $pager->links('default', 'admin_tailwind') ?>
    ```
  - **Done when:** `php -l app/Views/admin/tamu/index.php` bebas syntax error, halaman memiliki tepat satu `<h1>`, tidak ada `uppercase tracking-wide` atau em dash, dan pagination memanggil template `admin_tailwind`.

- [x] 5. **Seragamkan halaman Detail Tamu, CRUD Kelola Admin, dan Pengaturan Aplikasi, lalu jalankan verifikasi penuh**
  - **Perbarui `app/Views/admin/tamu/detail.php`**:
    - Ganti em dash (`\u2014`) di komentar atas dengan tanda hubung `-`.
    - Pada bagian header atas, pastikan `<h1>` menggunakan `class="text-2xl sm:text-3xl font-serif font-bold text-ink"` dan tambahkan subjudul `<p class="text-base text-ink-muted mt-1">Rincian lengkap data kehadiran dan keperluan pengunjung PTSP.</p>`.
    - Ganti seluruh 9 class `text-xs font-semibold uppercase tracking-wide text-ink-muted` menjadi `text-xs font-medium text-ink-muted` (*sentence case*).
    - Tambahkan `tabular-nums` pada tampilan No. HP dan Tanggal Kunjungan.
    - Pertahankan seluruh form aksi siklus hidup Superadmin dan script `.action-confirm-btn`.
  - **Perbarui `app/Views/admin/admin-user/index.php`**:
    - Ganti em dash (`\u2014`) di komentar atas dengan `-`.
    - Perbarui header menjadi `<h1 class="text-2xl sm:text-3xl font-serif font-bold text-ink">Kelola Akun Petugas Admin</h1>` disertai subjudul `<p class="text-base text-ink-muted mt-1">Daftar akun administrator dan petugas penerima tamu PTSP.</p>`.
    - Tambahkan `data-ui` pada kontainer tabel dan `tabular-nums` pada kolom nomor urut `#`.
  - **Perbarui `app/Views/admin/admin-user/form.php`**:
    - Ganti em dash (`\u2014`) di komentar atas dengan `-`.
    - Buat header di atas kartu form dengan `<h1 class="text-2xl sm:text-3xl font-serif font-bold text-ink"><?= esc($judul) ?></h1>` dan subjudul `<p class="text-base text-ink-muted mt-1">Lengkapi data kredensial dan tingkat hak akses petugas panel administrasi.</p>`.
    - Tambahkan `data-ui` pada kontainer kartu form (`<div class="bg-surface border border-border p-6 sm:p-8 max-w-2xl" data-ui>`).
  - **Perbarui `app/Views/admin/admin-user/hapus.php`**:
    - Ganti em dash (`\u2014`) di komentar atas dengan `-`.
    - Perbarui `<h1 class="text-2xl sm:text-3xl font-serif font-bold text-ink mb-2">Konfirmasi Hapus Admin</h1>` dan ubah paragraf di bawahnya menjadi `text-base text-ink-muted mb-6`.
  - **Perbarui `app/Views/admin/pengaturan/index.php`**:
    - Ganti em dash (`\u2014`) di komentar atas dengan `-`.
    - Perbarui header menjadi `<h1 class="text-2xl sm:text-3xl font-serif font-bold text-ink">Pengaturan Identitas Aplikasi</h1>` disertai subjudul `<p class="text-base text-ink-muted mt-1">Konfigurasi nama aplikasi, instansi PTSP, dan teks sambutan pada halaman buku tamu publik.</p>`.
    - Tambahkan `data-ui` pada kontainer kartu form (`<div class="bg-surface border border-border p-6 sm:p-8 max-w-3xl" data-ui>`).
  - **Kompilasi & Verifikasi Akhir**:
    - Jalankan `npm run build:css`.
    - Jalankan pemeriksaan `php -l` pada seluruh 8 file view/config yang dimodifikasi.
    - Jalankan `composer test`.
  - **Done when:** `npm run build:css` sukses, seluruh file lulus `php -l` tanpa error, tidak ada em dash atau `uppercase tracking-wide` yang tersisa di `app/Views/admin/` maupun `app/Views/layouts/admin.php`, dan `composer test` lulus 100% (8 tests, 22 assertions).

## Files / areas

- `app/Views/layouts/admin.php` - Shell layout panel admin dengan sidebar desktop, drawer navigasi mobile, logo transparan, badge tamu belum dibaca, dan form logout.
- `app/Views/admin/dashboard.php` - Halaman dashboard statistik KPI, grafik tren 7 hari & jenis kelamin, serta bar proporsional keperluan & asal pengunjung.
- `app/Config/Pager.php` - Pendaftaran alias template pagination `admin_tailwind`.
- `app/Views/pager/admin_pagination.php` - View baru untuk tombol navigasi halaman bergaya Tailwind CSS.
- `app/Views/admin/tamu/index.php` - Halaman tabel daftar tamu (Aktif, Arsip, Tong Sampah), filter pencarian, dan pengarsipan massal.
- `app/Views/admin/tamu/detail.php` - Halaman rincian data kunjungan tamu dan penampil foto.
- `app/Views/admin/admin-user/index.php` - Halaman daftar pengguna admin.
- `app/Views/admin/admin-user/form.php` - Halaman form tambah/edit pengguna admin.
- `app/Views/admin/admin-user/hapus.php` - Halaman konfirmasi penghapusan pengguna admin.
- `app/Views/admin/pengaturan/index.php` - Halaman pengaturan konfigurasi identitas aplikasi.
- `public/css/app.css` - Berkas CSS hasil kompilasi Tailwind CSS v4.

## Data / contracts

- **Kontrak Sesi & Otorisasi Role**:
  - Menu `Kelola Admin` (`/admin/admin-user`), `Pengaturan` (`/admin/pengaturan`), tab `Tong Sampah` (`/admin/tamu?tab=sampah`), panel `Alat Pengarsipan Massal`, dan tombol aksi destruktif/siklus hidup tamu wajib tetap dibatasi dengan pemeriksaan `session('role') === 'superadmin'`.
- **Kontrak Form & Aksi POST**:
  - Seluruh form POST (`#admin-logout-form`, `admin/tamu/baca-semua`, `admin/tamu/arsipkan-massal`, `admin/tamu/(:num)/arsipkan`, `batal-arsip`, `hapus`, `pulihkan`, `hapus-permanen`, `admin/admin-user/*`, `admin/pengaturan`) wajib menyertakan `<?= csrf_field() ?>` dan mempertahankan atribut `name` serta `action` yang sama persis.
  - Tombol aksi dengan class `.action-confirm-btn` dan atribut `data-title`, `data-message`, `data-confirm-text`, `data-danger` wajib tetap dipertahankan agar modal `confirmDialog` dari `public/js/dialog.js` tetap bekerja.
- **Kontrak Visualisasi Chart.js**:
  - Elemen `<canvas id="trenChart">` dan `<canvas id="jenisChart">` wajib mempertahankan ID dan struktur JSON dataset dari `$statistik['tren_7hari']` serta `$statistik['per_jenis']`.
- **Kontrak Keamanan Output**:
  - Seluruh output dinamis wajib dibungkus `esc(...)` (atau `json_encode(..., JSON_HEX_TAG | ...)` di dalam blok script).

## Testing

- **Automated Unit & Integration Tests**:
  - Jalankan `composer test` (8 tests, 22 assertions wajib tetap PASS).
- **Static & Build Checks**:
  - `npm run build:css` selesai dengan exit code 0.
  - `php -l` pada seluruh 9 file PHP yang diubah/dibuat selesai tanpa syntax error.
  - `node .agents/skills/design-taste-frontend/scripts/lint.mjs themes/app.css` menghasilkan `0 FAIL`.
- **Browser & Tasteskill App Screen Verification (saat `/check`)**:
  - Verifikasi visual dan struktural menggunakan `inspect.mjs --app` pada rute admin di ukuran desktop (`1440px`), tablet (`768px`), dan mobile (`390px`).
  - Verifikasi menu navigasi mobile dapat dibuka/ditutup pada layar `< 768px`.

## Notes for the AI

- **Panduan Khusus untuk Junior Programmer / AI Implementer**:
  - Jangan mengubah file apapun di `app/Controllers/` atau `app/Models/`. Seluruh variabel view (`$statistik`, `$tamu`, `$pager`, `$filter_aktif`, `$tab_aktif`, `$count_aktif`, `$count_arsip`, `$count_sampah`, `$unreadCount`, `$pengaturan`, `$defaultPasswordWarning`, `$admins`, `$admin`, `$judul`, `$actionUrl`) sudah dikirim dengan lengkap oleh controller yang ada.
  - Jangan pernah menuliskan karakter em dash (`\u2014` atau `&mdash;`), en dash (`\u2013`), atau ellipsis (`\u2026`) di file manapun. Gunakan tanda hubung biasa (`-`).
  - Pastikan di setiap halaman admin hanya terdapat **satu** tag `<h1>` (di dalam blok konten view masing-masing, **bukan** di `layouts/admin.php`), dan tepat di bawah `<h1>` selalu ada `<p class="text-base text-ink-muted mt-1">` yang menjelaskan fungsi halaman tersebut agar lolos pemeriksaan `[name-only-title]` dan `[multiple-h1]` pada Tasteskill Inspector.
  - Setelah seluruh edit view selesai, wajib jalankan `npm run build:css` agar seluruh class utilitas Tailwind CSS v4 yang baru dipakai terkompilasi ke dalam `public/css/app.css`.


<!-- blueprint:completion {"schemaVersion":2,"specBytes":25987,"specSha256":"ba976e796eeb96ca010c9ce3b2d3fc5d1779dafc0ef49a5a1f34e658f8f9eed8","branch":"refs/heads/feature/redesign-ui-ux-shell-dan-halaman-kerja-panel-admin-dengan-tasteskill-v2-fase-2","head":"f1077bf8aa27b473f41ec75964962c1c91ce67e0","baseRef":"refs/heads/main","baseCommit":"f1077bf8aa27b473f41ec75964962c1c91ce67e0","sourceTree":"5957476e5ea3e09f2e6000982c118fba805bcf8f","landing":"local-merge","absentOptional":[]} -->

## Try it now

Jalankan server aplikasi jika belum berjalan:
```bash
php spark serve
```

1. **Dashboard Admin (`http://localhost:8080/admin`):**
   - Masuk sebagai superadmin atau admin.
   - Perhatikan logo Kemenag transparan di sidebar dan ketiadaan kotak putih.
   - Amati 4 kartu KPI berimbang di bagian atas dengan font angka `tabular-nums` dan label *sentence case* yang bersih.
   - Periksa grafik tren 7 hari Chart.js dan bar visual proporsional di panel *5 Keperluan Terbanyak* serta *Distribusi Asal Pengunjung*.
2. **Daftar Tamu & Pagination (`http://localhost:8080/admin/tamu`):**
   - Amati header satu `<h1>` beserta tombol `Export CSV` dan `Tandai Semua Dibaca`.
   - Periksa tab *Tamu Aktif*, *Arsip Riwayat*, dan *Tong Sampah*.
   - Periksa tabel tamu; kolom ID, No. HP, dan Tanggal tersusun rapi dengan angka tabular.
   - Periksa komponen pagination Tailwind di bawah tabel (`admin_tailwind`).
3. **Navigasi Responsif (Mobile View):**
   - Buka DevTools (tekan `F12`) dan ubah ukuran layar ke mode ponsel (`< 768px`).
   - Klik tombol menu hamburger di sisi kiri topbar; menu navigasi drawer akan terbuka dan tertutup secara mulus.
4. **Halaman Detail & Manajemen Lainnya:**
   - Buka salah satu tamu untuk melihat halaman rincian (`admin/tamu/{id}`).
   - Buka menu [Kelola Admin](`admin/admin-user`) dan [Pengaturan](`admin/pengaturan`) untuk memastikan konsistensi tipografi editorial dan kartu permukaan.
