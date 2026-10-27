# Feature: Redesign UI/UX Halaman Publik, Sukses, dan Login Admin dengan Tasteskill v2 (Fase 1)

**From build-plan:** feature 11
**Build attempt:** 1
**Branch:** feature/redesign-ui-ux-halaman-publik-sukses-dan-login-admin-dengan-tasteskill-v2-fase-1
**Status:** verified

## Goal

Memperbaiki token Tailwind CSS v4 yang belum terdefinisi (`--color-brand`, `--color-brand-gold`, `.btn-danger`, `.choice-card`, `.upload-dropzone`) dan merancang ulang tampilan Halaman Publik (`/`), Halaman Konfirmasi Sukses (`/tamu/sukses`), serta Halaman Login Admin (`/admin/login`) mengikuti kaidah Tasteskill v2 tanpa mengubah kontrak rute, validasi backend, sesi, maupun skrip kompresi foto yang sudah ada.

## Design reference

Mengacu pada aturan desain lokal di `.agents/skills/design-taste-frontend/SKILL.md` dan sistem desain di `themes/app.css`:

- **Brand & Palet Warna (`--keep palette,type`)**:
  - Ground utama: `--color-ground` (`#F8F7F4`), Ground hangat/sekunder: `--color-ground-warm` (`#F2F0EA`), Permukaan kartu: `--color-surface` (`#FFFFFF`), Permukaan sekunder: `--color-surface-raised` (`#FAFAF8`).
  - Ink utama: `--color-ink` (`#1C1C1A`), Ink sekunder: `--color-ink-muted` (`#6B6A65`).
  - Brand hijau Kementerian Agama: `--color-brand` / `--color-brand-500` (`#006B2E`), panel identitas gelap: `--color-brand-700` (`#004A1F`) dan `--color-brand-800` (`#003A17`).
  - Aksen emas: `--color-brand-gold` / `--color-accent-300` (`#E5A400`).
- **Tipografi**:
  - Heading utama menggunakan `--font-serif` (`Playfair Display`), teks isi dan kontrol menggunakan `--font-sans` (`Plus Jakarta Sans`), angka waktu menggunakan `tabular-nums`.
- **House Rules Tasteskill v2 yang Wajib Dipatuhi**:
  - Tepat **satu elemen `<h1>`** pada setiap halaman yang dirender (navbar di `layouts/publik.php` tidak boleh memakai `<h1>` agar tidak bentrok dengan `<h1>` di masing-masing view anak).
  - Tidak boleh memakai gradasi (`bg-gradient-*`, `linear-gradient`), tidak boleh memakai shadow pada elemen statis, tidak boleh memakai penomoran dekoratif (`01 / 02 / 03`), tidak boleh memakai huruf kapital kecil renggang (`uppercase tracking-wide text-xs`), dan tidak boleh memakai karakter em dash (`\u2014`).
  - Pemisahan antar-area utama dilakukan dengan perbedaan warna permukaan (*tone step*: `bg-ground`, `bg-ground-warm`, `bg-surface`, `bg-brand-700`), sedangkan kontainer komponen interaktif/form diberi atribut `data-ui` agar garis batas input dan kartu tetap diizinkan oleh linter Tasteskill.

## In scope

- Menambahkan token alias `--color-brand` dan `--color-brand-gold` serta class utilitas `.btn-danger`, `.choice-card`, dan `.upload-dropzone` pada `themes/app.css`, lalu mengompilasinya ke `public/css/app.css`.
- Merancang ulang `app/Views/layouts/publik.php` menjadi shell responsif lebar (`max-w-6xl`) dengan bar identitas atas berlogo transparan (`images/logo-kemenag-transparent.png`), jam layanan real-time WITA (`#live-wita-clock`, `Asia/Makassar`), dan footer dengan tautan akses cepat ke `/admin/login`.
- Merancang ulang `app/Views/tamu/form.php` menjadi komposisi 2 kolom pada layar desktop/tablet lanskap (`lg:grid-cols-12`): panel identitas PTSP di kiri (`lg:col-span-5`) dan lembar formulir buku tamu di kanan (`lg:col-span-7`) dengan pilihan radio berbentuk `.choice-card` serta area unggah foto `.upload-dropzone`.
- Menambahkan indikator nama dan ukuran berkas foto yang dipilih (`#foto-filename`) pada `public/js/form-publik.js` tanpa mengubah logika toggle `#wrap-nama-instansi` maupun kompresi foto `< 1 MB`.
- Merancang ulang `app/Views/tamu/sukses.php` menjadi kartu tanda terima pencatatan kunjungan PTSP yang proporsional.
- Merancang ulang `app/Views/admin/login.php` menjadi layout autentikasi 2 kolom yang fokus (panel identitas sistem di kiri, form login di kanan) tanpa menduplikasi banner sambutan tamu publik.

## Out of scope

- Perubahan pada controller (`app/Controllers/*`), model (`app/Models/*`), rute (`app/Config/Routes.php`), atau skema database (`database/schema.sql`).
- Redesign shell dan halaman internal Panel Admin (`app/Views/layouts/admin.php`, `app/Views/admin/dashboard.php`, `app/Views/admin/tamu/*`, `app/Views/admin/admin-user/*`, `app/Views/admin/pengaturan/*`, dan pagination) yang akan dikerjakan terpisah pada Fase 2.

## Build loop

Follow `workflow.stepReview: "feature"` and `workflow.checkpointCommits: "disabled"` from `blueprint/config.json`:

- Kerjakan seluruh langkah di bawah secara berurutan di cabang fitur `feature/redesign-ui-ux-halaman-publik-sukses-dan-login-admin-dengan-tasteskill-v2-fase-1`.
- Jangan membuat checkpoint commit di tengah langkah.
- Verifikasi setiap langkah dengan perintah yang tercantum pada `Done when`, lalu sajikan satu paket tinjauan lengkap di akhir implementasi sebelum `/complete`.

## Build steps

- [x] 1. **Lengkapi token warna dan komponen utilitas di `themes/app.css` lalu kompilasi ke `public/css/app.css`**
  - Buka `themes/app.css`.
  - Di dalam blok `@theme` (sekitar baris 12-31), tambahkan dua variabel alias berikut:
    ```css
    --color-brand: #006B2E;
    --color-brand-gold: #E5A400;
    ```
    Penambahan ini memperbaiki seluruh pemanggilan `text-brand`, `border-brand`, `bg-brand/10`, dan `text-brand-gold` di proyek.
  - Di dalam blok `@layer utilities` (setelah definisi `.btn-secondary:active` sekitar baris 147), tambahkan definisi `.btn-danger`:
    ```css
    .btn-danger {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      background-color: var(--color-danger);
      color: #FFFFFF;
      font-weight: 600;
      padding: 0.75rem 1.5rem;
      border-radius: 999px;
      border: none;
      cursor: pointer;
      transition: background-color 150ms ease, transform 150ms ease;
    }

    .btn-danger:hover {
      background-color: #9B1C12;
    }

    .btn-danger:active {
      transform: scale(0.98);
    }
    ```
  - Masih di dalam `@layer utilities` (setelah `.card-surface` sekitar baris 204), tambahkan komponen `.choice-card` dan `.upload-dropzone` untuk formulir ramah sentuh:
    ```css
    .choice-card {
      display: flex;
      align-items: center;
      gap: 0.625rem;
      padding: 0.75rem 1rem;
      background-color: var(--color-surface);
      border: 1px solid var(--color-border-strong);
      border-radius: 0;
      cursor: pointer;
      font-size: 0.9375rem;
      font-weight: 500;
      color: var(--color-ink);
      transition: background-color 150ms ease, border-color 150ms ease;
    }

    .choice-card:hover {
      background-color: var(--color-surface-raised);
      border-color: var(--color-brand-500);
    }

    .choice-card:has(input:checked) {
      background-color: var(--color-brand-50);
      border-color: var(--color-brand-500);
      color: var(--color-brand-800);
      font-weight: 600;
    }

    .upload-dropzone {
      background-color: var(--color-surface-raised);
      border: 1px dashed var(--color-border-strong);
      border-radius: 0;
      padding: 1.25rem;
      transition: border-color 150ms ease, background-color 150ms ease;
    }

    .upload-dropzone:hover,
    .upload-dropzone:focus-within {
      border-color: var(--color-brand-500);
      background-color: var(--color-surface);
    }
    ```
  - Pastikan tidak ada karakter em dash (`\u2014`) pada komentar di `themes/app.css` (ganti dengan tanda hubung `-` biasa bila ada) dan tambahkan komentar `/* taste-skill-ignore */` pada baris di `themes/app.css` yang memang membutuhkan `border-top` / `border-left` / `box-shadow` (seperti `.toast` dan `.dialog-panel`) agar `lint.mjs` lulus 0 FAIL.
  - Jalankan `npm run build:css` untuk menghasilkan `public/css/app.css`.
  - **Done when:** Perintah `npm run build:css` selesai dengan exit code 0, berkas `public/css/app.css` memuat `.btn-danger`, `.choice-card`, `.upload-dropzone`, `--color-brand`, serta `--color-brand-gold`, dan `node .agents/skills/design-taste-frontend/scripts/lint.mjs themes/app.css` menghasilkan `0 FAIL`.

- [x] 2. **Perbarui shell publik di `app/Views/layouts/publik.php` dengan bar identitas minimalis dan jam layanan WITA real-time**
  - Buka `app/Views/layouts/publik.php`.
  - Pada tag `<title>`, ganti pemisah em dash menjadi tanda hubung biasa:
    ```php
    <title><?= esc($this->renderSection('title')) ?: 'Form Buku Tamu' ?> - <?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?></title>
    ```
  - Ganti `<header>` lama yang menumpuk teks di tengah (`680px`) menjadi bar identitas atas yang bersih di atas permukaan `bg-ground-warm` (pemisahan menggunakan perbedaan warna permukaan *tone step*, bukan garis tipis):
    - Tetap letakkan `<div class="brand-stripe" aria-hidden="true"></div>` di bagian paling atas `<body>`.
    - Di dalam `<header class="bg-ground-warm">`, buat kontainer `<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-wrap items-center justify-between gap-4">`:
      - **Sisi kiri**: Tautan `<a href="<?= site_url('/') ?>" class="inline-flex items-center gap-3 text-ink hover:opacity-90">` berisi `<img src="<?= base_url('images/logo-kemenag-transparent.png') ?>" alt="Logo Kementerian Agama" class="h-10 w-10 object-contain">` dan div teks:
        - `<span class="block text-sm font-bold text-brand-700 leading-tight"><?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?></span>`
        - `<span class="block text-xs text-ink-muted"><?= esc($pengaturan['nama_instansi_ptsp'] ?? 'Kantor Wilayah Kementerian Agama Provinsi Kalimantan Utara') ?></span>`
        - *(Penting: Jangan gunakan tag `<h1>` di dalam `<header>` layout ini agar setiap halaman anak memiliki tepat satu `<h1>`)*.
      - **Sisi kanan**: Indikator waktu layanan WITA dengan atribut `data-ui`:
        ```html
        <div class="inline-flex items-center gap-2 bg-surface px-3.5 py-1.5 text-xs font-medium text-ink-muted" data-ui>
            <span class="inline-block h-2 w-2 rounded-full bg-brand-500" aria-hidden="true"></span>
            <span id="live-wita-clock" class="tabular-nums text-ink font-semibold">Zona Waktu: WITA (UTC+8)</span>
        </div>
        ```
  - Ubah `<main>` menjadi:
    ```php
    <main class="flex-1 py-8 md:py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <?= $this->renderSection('content') ?>
        </div>
    </main>
    ```
  - Ubah `<footer>` menjadi latar `bg-ground-warm mt-auto` dengan kontainer `<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-ink-muted">`:
    - Sisi kiri: `<p>Tim Humas dan Komunikasi Publik - Kanwil Kementerian Agama Provinsi Kalimantan Utara</p>` (tanpa karakter `&mdash;`).
    - Sisi kanan: `<a href="<?= site_url('admin/login') ?>" class="text-xs font-semibold text-brand-700 hover:underline">Portal Petugas PTSP</a>`.
  - Di dalam blok `<script>` sebelum `</body>`, pertahankan pemanggilan `toastShow('success', ...)` dan `toastShow('error', ...)`, lalu tambahkan fungsi pembaru jam WITA:
    ```javascript
    var clockEl = document.getElementById('live-wita-clock');
    if (clockEl && typeof Intl !== 'undefined' && Intl.DateTimeFormat) {
        var fmt = new Intl.DateTimeFormat('id-ID', {
            timeZone: 'Asia/Makassar',
            weekday: 'short',
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        });
        var updateClock = function () {
            clockEl.textContent = fmt.format(new Date()) + ' WITA';
        };
        updateClock();
        setInterval(updateClock, 1000);
    }
    ```
  - **Done when:** `php -l app/Views/layouts/publik.php` tidak menghasilkan syntax error, `images/logo-kemenag-transparent.png` dan `#live-wita-clock` terpasang, tidak ada tag `<h1>` atau karakter em dash di `layouts/publik.php`, serta script `toastShow` tetap utuh.

- [x] 3. **Redesign halaman formulir buku tamu di `app/Views/tamu/form.php` dan tambahkan indikator berkas foto di `public/js/form-publik.js`**
  - Buka `app/Views/tamu/form.php`.
  - Buat struktur 2 kolom asimetris (`<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">`):
    - **Kolom Kiri - Panel Identitas Editorial (`<aside class="lg:col-span-5 bg-brand-700 text-white p-6 sm:p-8 lg:p-10">`)**:
      - Tampilkan `<img src="<?= base_url('images/logo-kemenag-transparent.png') ?>" alt="Logo Kementerian Agama" class="h-16 w-16 object-contain mb-6">`.
      - Tampilkan satu-satunya `<h1>` halaman ini:
        ```php
        <h1 class="text-3xl sm:text-4xl font-serif font-bold text-white leading-tight mb-3">
            <?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?>
        </h1>
        ```
      - Tampilkan subjudul deskripsi aplikasi:
        ```php
        <p class="text-base text-brand-100 font-medium mb-4">
            <?= esc($pengaturan['deskripsi_aplikasi'] ?? 'Buku Tamu Kanwil Kemenag Kaltara') ?>
        </p>
        ```
      - Tampilkan pesan sambutan dari pengaturan database:
        ```php
        <p class="text-sm text-brand-50 leading-relaxed mb-8">
            <?= esc((string) ($pengaturan['teks_header'] ?? 'Selamat datang di Pelayanan Terpadu Satu Pintu (PTSP). Silakan lengkapi data kunjungan Anda.')) ?>
        </p>
        ```
      - Tambahkan blok panduan ringkas di bagian bawah panel kiri dengan latar `bg-brand-800 p-5`:
        - `<h2 class="text-base font-bold text-accent-200 mb-3">Panduan Pengisian Mandiri</h2>`
        - `<ul class="space-y-2 text-sm text-brand-50">` berisi 3 poin:
          1. `Isi identitas diri dan pilih kategori asal pengunjung sesuai kedatangan Anda.`
          2. `Tuliskan nomor WhatsApp aktif serta maksud keperluan layanan secara jelas.`
          3. `Lampirkan foto kunjungan bila diperlukan (otomatis dikompres oleh sistem).`
      - Di bagian paling bawah panel kiri, tampilkan nama instansi:
        ```php
        <p class="text-xs text-brand-200 mt-6">
            <?= esc($pengaturan['nama_instansi_ptsp'] ?? 'Kantor Wilayah Kementerian Agama Provinsi Kalimantan Utara') ?>
        </p>
        ```
    - **Kolom Kanan - Lembar Kerja Formulir (`<section class="lg:col-span-7 card-surface p-6 sm:p-8 lg:p-10" data-ui aria-labelledby="form-heading">`)**:
      - Header bagian form:
        ```html
        <div class="mb-6">
            <h2 id="form-heading" class="text-2xl font-serif font-bold text-ink mb-1">Formulir Kunjungan</h2>
            <p class="text-sm text-ink-muted">Seluruh kolom bertanda bintang (*) wajib diisi sebelum mengirim data.</p>
        </div>
        ```
      - Pertahankan blok alert error validasi `<?php $errs = session('errors'); ?>` lengkap dengan `role="alert" aria-live="assertive"`.
      - Pertahankan tag `<form action="<?= site_url('tamu/submit') ?>" method="post" enctype="multipart/form-data" class="space-y-6" novalidate>`, `<?= csrf_field() ?>`, dan honeypot `<input type="text" name="_hp" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">`.
      - **Field Nama Lengkap**:
        - `<label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>`
        - `<input type="text" id="nama" name="nama" value="<?= esc(old('nama')) ?>" class="form-field <?= session('errors.nama') ? 'form-field-error' : '' ?>" placeholder="Masukkan nama lengkap sesuai identitas" autocomplete="name" required>`
      - **Field Jenis Kelamin (Choice Cards 2 Kolom)**:
        ```php
        <fieldset>
            <legend class="form-label">Jenis Kelamin <span class="text-danger">*</span></legend>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-1">
                <label class="choice-card">
                    <input type="radio" name="jenis_kelamin" value="Laki-laki" <?= old('jenis_kelamin') === 'Laki-laki' ? 'checked' : '' ?> class="h-4 w-4 accent-brand-500" required>
                    <span>Laki-laki</span>
                </label>
                <label class="choice-card">
                    <input type="radio" name="jenis_kelamin" value="Perempuan" <?= old('jenis_kelamin') === 'Perempuan' ? 'checked' : '' ?> class="h-4 w-4 accent-brand-500" required>
                    <span>Perempuan</span>
                </label>
            </div>
        </fieldset>
        ```
      - **Field Asal Pengunjung (Choice Cards 3 Kolom)**:
        ```php
        <fieldset>
            <legend class="form-label">Asal Pengunjung <span class="text-danger">*</span></legend>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-1">
                <label class="choice-card">
                    <input type="radio" name="asal_pengunjung" value="perorangan" <?= old('asal_pengunjung') === 'perorangan' ? 'checked' : '' ?> class="h-4 w-4 accent-brand-500" required>
                    <span>Perorangan</span>
                </label>
                <label class="choice-card">
                    <input type="radio" name="asal_pengunjung" value="instansi" <?= old('asal_pengunjung') === 'instansi' ? 'checked' : '' ?> class="h-4 w-4 accent-brand-500" required>
                    <span>Instansi</span>
                </label>
                <label class="choice-card">
                    <input type="radio" name="asal_pengunjung" value="lembaga" <?= old('asal_pengunjung') === 'lembaga' ? 'checked' : '' ?> class="h-4 w-4 accent-brand-500" required>
                    <span>Lembaga</span>
                </label>
            </div>
        </fieldset>
        ```
      - **Field Kondisional Nama Instansi (`#wrap-nama-instansi` & `#nama_instansi`)**:
        ```php
        <div id="wrap-nama-instansi" style="display: none;">
            <label for="nama_instansi" class="form-label">Nama Instansi / Lembaga <span class="text-danger">*</span></label>
            <input type="text" id="nama_instansi" name="nama_instansi" value="<?= esc(old('nama_instansi')) ?>" class="form-field <?= session('errors.nama_instansi') ? 'form-field-error' : '' ?>" placeholder="Masukkan nama instansi atau lembaga asal">
        </div>
        ```
      - **Field No. HP / WhatsApp (`#no_hp`) & Alamat (`#alamat`) & Keperluan (`#keperluan`)**:
        - Pertahankan `id="no_hp" name="no_hp"`, `id="alamat" name="alamat"`, dan `id="keperluan" name="keperluan"` beserta `old(...)` dan class error kondisional `session('errors.<field>') ? 'form-field-error' : ''`.
      - **Field Foto Kunjungan (`#foto`) dalam `.upload-dropzone`**:
        ```php
        <div>
            <label for="foto" class="form-label">Foto Kunjungan <span class="text-ink-muted font-normal">(Opsional)</span></label>
            <div class="upload-dropzone">
                <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" aria-describedby="foto-hint foto-error" class="block w-full text-sm text-ink file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer">
                <p id="foto-hint" class="form-hint mt-2">Format JPG, PNG, atau WebP. Maksimal 10 MB (otomatis dikompres di bawah 1 MB saat dikirim).</p>
                <p id="foto-filename" class="text-sm font-semibold text-brand-700 mt-2 hidden"></p>
                <p id="foto-error" class="text-sm text-danger mt-1 hidden" role="alert"></p>
            </div>
        </div>
        ```
      - **Tombol Submit**:
        ```html
        <div class="pt-2">
            <button type="submit" class="btn-primary w-full sm:w-auto min-w-52">Kirim Data Kunjungan</button>
        </div>
        ```
  - Buka `public/js/form-publik.js`:
    - Di dalam `initFotoCompression()`, tambahkan listener `input.addEventListener('change', ...)` yang mereset `#foto-error` dan menampilkan nama serta ukuran berkas pada `#foto-filename` (misalnya `Berkas dipilih: foto.jpg (1.2 MB)`), atau menyembunyikan `#foto-filename` jika input dikosongkan.
    - Ganti semua em dash (`\u2014`) di komentar `public/js/form-publik.js` dengan tanda hubung `-` agar bersih dari peringatan linter.
  - **Done when:** `php -l app/Views/tamu/form.php` bebas syntax error, seluruh atribut `id` dan `name` form publik (`nama`, `jenis_kelamin`, `asal_pengunjung`, `wrap-nama-instansi`, `nama_instansi`, `alamat`, `no_hp`, `keperluan`, `foto`, `foto-error`, `_hp`) tetap ada, dan `node .agents/skills/design-taste-frontend/scripts/lint.mjs public/js/form-publik.js` menghasilkan `0 FAIL`.

- [x] 4. **Redesign halaman sukses (`app/Views/tamu/sukses.php`) dan halaman login admin (`app/Views/admin/login.php`), lalu jalankan verifikasi akhir**
  - Buka `app/Views/tamu/sukses.php` dan perbarui tampilannya menjadi Kartu Bukti Kunjungan PTSP:
    - Gunakan kontainer `<section class="max-w-2xl mx-auto" data-ui>`:
      - Di bagian atas kartu, beri aksen `<div class="brand-stripe" aria-hidden="true"></div>`.
      - Di dalam `<div class="card-surface p-8 sm:p-12">`:
        - Baris status atas: badge hijau `<span class="inline-flex items-center gap-2 px-3 py-1 bg-success-bg text-success text-xs font-semibold">Kunjungan Tercatat</span>` dan keterangan waktu pencatatan `<?= esc(date('d/m/Y H:i')) ?> WITA`.
        - Satu-satunya `<h1>` halaman ini:
          ```html
          <h1 class="text-2xl sm:text-3xl font-bold text-ink font-serif mt-4 mb-3">
              Terima kasih, data kunjungan Anda telah tercatat.
          </h1>
          ```
        - Paragraf penjelas:
          ```html
          <p class="text-base text-ink-muted leading-relaxed mb-8">
              Data kehadiran Anda telah masuk ke dalam sistem Pelayanan Terpadu Satu Pintu (PTSP). Silakan menuju meja petugas pelayanan atau isi formulir baru untuk pengunjung berikutnya.
          </p>
          ```
        - Tombol kembali:
          ```php
          <div class="flex flex-wrap items-center gap-4">
              <a href="<?= site_url('/') ?>" class="btn-primary">Isi Form Lagi</a>
          </div>
          ```
  - Buka `app/Views/admin/login.php` dan perbarui menjadi layout autentikasi 2 kolom yang fokus (`<section class="max-w-4xl mx-auto" data-ui>`):
    - Di dalam `<div class="card-surface grid grid-cols-1 md:grid-cols-12">`:
      - **Kolom Kiri (`<div class="md:col-span-5 bg-brand-700 text-white p-8 flex flex-col justify-between">`)**:
        - Bagian atas: `<img src="<?= base_url('images/logo-kemenag-transparent.png') ?>" alt="Logo Kementerian Agama" class="h-14 w-14 object-contain mb-6">`, judul samping `<h2 class="text-xl font-serif font-bold text-white mb-2">Panel Administrasi PTSP</h2>`, dan deskripsi `<p class="text-sm text-brand-100 leading-relaxed">Akses khusus petugas penerima tamu dan administrator untuk memantau kunjungan, meninjau keperluan, serta mengunduh laporan rekapitulasi.</p>`.
        - Bagian bawah: `<a href="<?= site_url('/') ?>" class="inline-flex items-center gap-2 text-xs font-semibold text-accent-200 hover:underline mt-8">&larr; Kembali ke Buku Tamu Publik</a>`.
      - **Kolom Kanan (`<div class="md:col-span-7 p-8 sm:p-10 bg-surface">`)**:
        - Satu-satunya `<h1>` halaman ini:
          ```html
          <div class="mb-8">
              <h1 class="text-2xl sm:text-3xl font-bold text-ink font-serif mb-2">Login Administrator</h1>
              <p class="text-sm text-ink-muted">Masukkan kredensial petugas untuk mengelola data buku tamu.</p>
          </div>
          ```
        - Form login tetap menjaga kontrak penuh:
          ```php
          <form action="<?= site_url('admin/login') ?>" method="post" class="space-y-5">
              <?= csrf_field() ?>
              <div>
                  <label for="username" class="form-label">Username</label>
                  <input type="text" id="username" name="username" value="<?= esc(old('username')) ?>" class="form-field" placeholder="Masukkan username petugas" autocomplete="username" required>
              </div>
              <div>
                  <label for="password" class="form-label">Password</label>
                  <input type="password" id="password" name="password" class="form-field" placeholder="Masukkan kata sandi" autocomplete="current-password" required>
              </div>
              <div class="pt-2">
                  <button type="submit" class="btn-primary w-full">Masuk</button>
              </div>
          </form>
          ```
  - Jalankan kompilasi CSS, pemeriksaan sintaks PHP, linter Tasteskill, dan test suite PHPUnit:
    - `npm run build:css`
    - `php -l app/Views/layouts/publik.php`
    - `php -l app/Views/tamu/form.php`
    - `php -l app/Views/tamu/sukses.php`
    - `php -l app/Views/admin/login.php`
    - `node .agents/skills/design-taste-frontend/scripts/lint.mjs themes/app.css public/js/form-publik.js`
    - `composer test`
  - **Done when:** Keempat file view lulus `php -l`, `npm run build:css` sukses, `lint.mjs` menghasilkan `0 FAIL`, dan `composer test` lulus 100% (8 tests, 22 assertions).

## Files / areas

- `themes/app.css` - Penambahan token `--color-brand`, `--color-brand-gold`, serta komponen `.btn-danger`, `.choice-card`, dan `.upload-dropzone`.
- `public/css/app.css` - Hasil build terkompilasi dari `npm run build:css`.
- `app/Views/layouts/publik.php` - Shell halaman publik dengan bar identitas berlogo transparan, jam layanan WITA real-time (`#live-wita-clock`), dan footer dengan pintasan Portal Petugas.
- `app/Views/tamu/form.php` - Halaman formulir buku tamu dengan komposisi 2 kolom (panel identitas PTSP di kiri, lembar kerja form dengan `.choice-card` dan `.upload-dropzone` di kanan).
- `public/js/form-publik.js` - Penambahan indikator nama & ukuran berkas foto pada `#foto-filename` dengan tetap mempertahankan toggle `#wrap-nama-instansi` dan kompresi gambar `< 1 MB`.
- `app/Views/tamu/sukses.php` - Halaman konfirmasi selesai mengisi buku tamu berbentuk kartu tanda terima kunjungan PTSP.
- `app/Views/admin/login.php` - Halaman login administrator 2 kolom mandiri dengan tautan kembali ke buku tamu publik.

## Data / contracts

- **Kontrak Form Buku Tamu Publik (`POST /tamu/submit`)**:
  - Wajib menyertakan `<?= csrf_field() ?>` dan honeypot `<input type="text" name="_hp" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">`.
  - Seluruh nama field POST tidak boleh berubah: `nama`, `jenis_kelamin` (`Laki-laki` | `Perempuan`), `asal_pengunjung` (`perorangan` | `instansi` | `lembaga`), `nama_instansi`, `alamat`, `no_hp`, `keperluan`, dan berkas `foto`.
  - Elemen DOM yang diikat oleh `public/js/form-publik.js` wajib mempertahankan ID/selector yang sama persis: `input[name="asal_pengunjung"]`, `#wrap-nama-instansi`, `#nama_instansi`, `#foto`, dan `#foto-error`.
- **Kontrak Form Login Admin (`POST /admin/login`)**:
  - Wajib menyertakan `<?= csrf_field() ?>`, `input[name="username"]` (`#username`), dan `input[name="password"]` (`#password`).
- **Kontrak Keamanan & Escaping**:
  - Seluruh variabel dinamis dari database (`$pengaturan['judul_aplikasi']`, `$pengaturan['deskripsi_aplikasi']`, `$pengaturan['nama_instansi_ptsp']`, `$pengaturan['teks_header']`), flashdata, dan `old(...)` wajib dibungkus dengan `esc(...)`.
- **Kontrak Zona Waktu**:
  - Jam interaktif di `layouts/publik.php` wajib menggunakan zona waktu `Asia/Makassar` (WITA / UTC+8) sesuai aturan proyek.

## Testing

- **Automated Unit & Integration Tests**:
  - Jalankan `composer test` (saat ini: 8 tests, 22 assertions PASS). Karena langkah-langkah pada fitur ini murni mencakup lapisan UI/View dan styling CSS tanpa menambah logika bisnis PHP baru, tidak diperlukan penambahan file test PHPUnit baru, tetapi seluruh test yang ada wajib tetap hijau.
- **Static & Build Checks**:
  - `npm run build:css` wajib selesai tanpa error.
  - `php -l` pada seluruh file `.php` yang diubah wajib bebas dari syntax error.
  - `node .agents/skills/design-taste-frontend/scripts/lint.mjs themes/app.css public/js/form-publik.js` wajib menghasilkan `0 FAIL`.
- **Browser / Visual Verification (saat `/check`)**:
  - Verifikasi tampilan pada lebar desktop (`1440px`), tablet (`768px`), dan mobile (`390px`) untuk rute `/`, `/tamu/sukses`, dan `/admin/login`.
  - Verifikasi klik kartu `Instansi` / `Lembaga` pada `Asal Pengunjung` memunculkan field `#wrap-nama-instansi`, dan klik `Perorangan` menyembunyikannya kembali.

## Notes for the AI

- **Panduan Khusus untuk AI / Junior Programmer**:
  - Jangan mengubah file apapun di luar daftar `Files / areas` di atas.
  - Jangan pernah menggunakan karakter em dash (`\u2014` atau `&mdash;`), en dash (`\u2013`), atau ellipsis (`\u2026`) di dalam komentar, HTML, maupun teks UI. Gunakan tanda hubung biasa (`-`).
  - Setiap kali selesai mengedit `themes/app.css` atau file di `app/Views/`, wajib jalankan `npm run build:css` agar class Tailwind baru langsung terkompilasi ke `public/css/app.css`.
  - Pastikan di setiap halaman (`/`, `/tamu/sukses`, `/admin/login`) hanya ada **satu** tag `<h1>` (di dalam section konten masing-masing view, bukan di `<header>` `layouts/publik.php`).


<!-- blueprint:completion {"schemaVersion":2,"specBytes":29026,"specSha256":"139e1f084fe644ed912dfcbfa97b06c26ad09d5c5bec5755a24ea418e4c4b3a6","branch":"refs/heads/feature/redesign-ui-ux-halaman-publik-sukses-dan-login-admin-dengan-tasteskill-v2-fase-1","head":"438c759b920b9fd086a9a5543e4e5d464924a6df","baseRef":"refs/heads/main","baseCommit":"438c759b920b9fd086a9a5543e4e5d464924a6df","sourceTree":"b7871b13ef452cbe7428f75614d101616b3227f7","landing":"local-merge","absentOptional":[]} -->

## Try it now

Jalankan server aplikasi jika belum berjalan:
```bash
php spark serve --port 8080
```

1. **Halaman Formulir Buku Tamu (`/`):**
   - Buka `http://localhost:8080/` di browser.
   - Periksa bar atas: logo Kementerian Agama transparan, identitas Kanwil Kemenag Kaltara, dan jam WITA real-time (`Asia/Makassar`).
   - Periksa panel identitas di sisi kiri: logo dan judul aplikasi (`BUKANWIL`) tertata rapi rata tengah dengan warna hijau Kemenag (`bg-brand-700`).
   - Pada kolom formulir, klik pilihan **Instansi** atau **Lembaga** pada Asal Pengunjung, pastikan field *Nama Instansi / Lembaga* muncul secara dinamis. Klik **Perorangan** untuk menyembunyikannya kembali.
   - Pilih berkas foto pada area dropzone, teks nama berkas terpilih akan muncul.

2. **Halaman Sukses (`/tamu/sukses`):**
   - Buka `http://localhost:8080/tamu/sukses`.
   - Amati kartu bukti pencatatan kunjungan bergaya struk (*receipt card*) dengan badge status hijau, waktu tercatat WITA, dan tombol *Isi Form Lagi*.

3. **Halaman Login Admin (`/admin/login`):**
   - Buka `http://localhost:8080/admin/login` (atau klik tautan *Portal Petugas PTSP* di footer layout publik).
   - Amati kartu autentikasi 2 kolom yang fokus dengan panel identitas petugas di sisi kiri dan formulir login di sisi kanan.
