# Fix: Hapus Header Redundan di Layout Publik, Hapus Panduan di Aside Form Tamu, dan Seragamkan Aside Login Admin

**Type:** Fix
**Status:** verified
**Branch:** fix/hapus-header-redundan-di-layout-publik-hapus-panduan-di-aside-form-tamu-dan-seragamkan-aside-login-admin

## The problem

1. **Redundansi `<header>` di `app/Views/layouts/publik.php` (baris 28-51):**
   - Menampilkan logo Kemenag, judul aplikasi (`BUKANWIL`), dan nama instansi PTSP di bagian atas halaman, padahal elemen `<aside>` di halaman utama sudah memuat informasi yang sama persis. Hal ini menyebabkan duplikasi visual (dua logo dan dua judul dalam satu layar).
2. **Blok "Panduan Pengisian Mandiri" yang tidak diperlukan di `app/Views/tamu/form.php` (baris 37-44):**
   - Kotak `<div class="bg-brand-800 p-5 mb-8 text-left">` di dalam `<aside>` mengulang instruksi yang sudah jelas dari label dan placeholder input formulir di kolom kanan.
3. **Ketidaksinkronan layout `<aside>` di `app/Views/admin/login.php` (baris 16-33):**
   - Kolom kiri halaman login admin masih memakai `<div>` biasa dengan logo rata kiri (tanpa `mx-auto` dan `text-center`) serta belum menampilkan nama aplikasi (`$pengaturan['judul_aplikasi'] ?? 'BUKANWIL'`).
4. **Konteks identitas di `app/Views/tamu/sukses.php` (baris 12-36):**
   - Setelah `<header>` dihapus dari `layouts/publik.php`, kartu konfirmasi sukses memerlukan baris identitas ringkas (logo Kemenag transparan + nama aplikasi `BUKANWIL`) di bagian atas kartu agar tetap memiliki identitas instansi yang jelas.

## The fix

Perbaiki keempat file view secara terarah tanpa mengubah controller, rute, validasi, maupun logika bisnis. Ikuti panduan kode eksak di bawah ini langkah demi langkah.

### Panduan Khusus untuk Junior Programmer / AI:
- **Jangan mengubah file di luar 4 file view ini** (`app/Views/layouts/publik.php`, `app/Views/tamu/form.php`, `app/Views/admin/login.php`, `app/Views/tamu/sukses.php`) ditambah hasil kompilasi `public/css/app.css`.
- **Aturan Tasteskill v2:**
  - Dilarang menggunakan karakter em dash (`\u2014` atau `&mdash;`) atau class `uppercase tracking-wide`. Gunakan tanda hubung biasa (`-`).
  - Setiap halaman (`/`, `/tamu/sukses`, `/admin/login`) wajib memiliki **tepat satu tag `<h1>`**.
- **Kompilasi CSS di Windows PowerShell:** Gunakan `cmd /c npm run build:css` jika `npm run build:css` terblokir ExecutionPolicy PowerShell.

## Build steps

- [x] 1. **Hapus `<header>` dari `app/Views/layouts/publik.php`**
  - Buka `app/Views/layouts/publik.php`.
  - Hapus seluruh blok `<header class="bg-ground-warm">...</header>` (baris 28 sampai 51) sehingga setelah `<div class="brand-stripe" aria-hidden="true"></div>` langsung masuk ke `<main class="flex-1 py-8 md:py-12">`.
  - Kode `<body>` di `app/Views/layouts/publik.php` (baris 24-69) harus menjadi seperti ini:
    ```php
    <body class="bg-ground text-ink antialiased min-h-screen flex flex-col">

        <div class="brand-stripe" aria-hidden="true"></div>

        <main class="flex-1 py-8 md:py-12 flex flex-col justify-center">
            <div class="max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8">
                <?= $this->renderSection('content') ?>
            </div>
        </main>

        <footer class="bg-ground-warm mt-auto">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-ink-muted">
                <p>
                    Tim Humas dan Komunikasi Publik - Kanwil Kementerian Agama Provinsi Kalimantan Utara
                </p>
                <a href="<?= site_url('admin/login') ?>" class="text-xs font-semibold text-brand-700 hover:underline">
                    Portal Petugas PTSP
                </a>
            </div>
        </footer>
    ```
  - Pertahankan blok `<script>` di bagian bawah `app/Views/layouts/publik.php` (termasuk script `#live-wita-clock` dan `toastShow`).
  - **Done when:** `php -l app/Views/layouts/publik.php` bebas syntax error dan tidak ada lagi tag `<header>` di dalam file tersebut.

- [x] 2. **Hapus kotak panduan dan rapikan `<aside>` di `app/Views/tamu/form.php`**
  - Buka `app/Views/tamu/form.php`.
  - Ganti seluruh blok `<aside>` (baris 14 sampai 50) dengan kode berikut (kotak *Panduan Pengisian Mandiri* dihapus, dan badge `#live-wita-clock` dipindahkan ke dalam `<aside>` secara rata tengah):
    ```php
        <!-- Kolom Kiri: Panel Identitas Editorial -->
        <aside class="lg:col-span-5 bg-brand-700 text-white p-6 sm:p-8 lg:p-10 flex flex-col justify-between text-center">
            <div class="my-auto py-4">
                <img
                    src="<?= base_url('images/logo-kemenag-transparent.png') ?>"
                    alt="Logo Kementerian Agama"
                    class="h-20 w-20 object-contain mx-auto mb-6"
                >

                <h1 class="text-2xl sm:text-3xl font-serif font-bold text-white leading-tight mb-2">
                    <?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?>
                </h1>

                <p class="text-lg text-brand-100 font-medium mb-4">
                    <?= esc($pengaturan['deskripsi_aplikasi'] ?? 'Buku Tamu Kanwil Kemenag Kaltara') ?>
                </p>

                <p class="text-base text-brand-50 leading-relaxed max-w-md mx-auto">
                    <?= esc((string) ($pengaturan['teks_header'] ?? 'Selamat datang di Pelayanan Terpadu Satu Pintu (PTSP). Silakan lengkapi data kunjungan Anda.')) ?>
                </p>
            </div>

            <div class="pt-6 border-t border-brand-600 space-y-3">
                <div class="inline-flex items-center gap-2 bg-brand-800 px-3.5 py-1.5 text-xs font-medium text-brand-100" data-ui>
                    <span class="inline-block h-2 w-2 rounded-full bg-accent-500" aria-hidden="true"></span>
                    <span id="live-wita-clock" class="tabular-nums text-white font-semibold">Zona Waktu: WITA (UTC+8)</span>
                </div>
                <p class="text-sm text-brand-100">
                    <?= esc($pengaturan['nama_instansi_ptsp'] ?? 'Kantor Wilayah Kementerian Agama Provinsi Kalimantan Utara') ?>
                </p>
            </div>
        </aside>
    ```
  - Jangan mengubah bagian `<section class="lg:col-span-7 ...">` (formulir input kanan tetap utuh).
  - **Done when:** `php -l app/Views/tamu/form.php` bebas syntax error, tidak ada teks `"Panduan Pengisian Mandiri"`, dan elemen `#live-wita-clock` berada di dalam `<aside>`.

- [x] 3. **Seragamkan `<aside>` rata tengah beserta nama aplikasi di `app/Views/admin/login.php` dan tambahkan identitas di `app/Views/tamu/sukses.php`**
  - **Buka `app/Views/admin/login.php`**:
    - Ganti blok kolom kiri (baris 15 sampai 33, `<div class="md:col-span-5 ...">...</div>`) menjadi elemen `<aside>` rata tengah berikut:
      ```php
              <!-- Sisi Kiri: Panel Identitas Petugas -->
              <aside class="md:col-span-5 bg-brand-700 text-white p-6 sm:p-8 lg:p-10 flex flex-col justify-between text-center">
                  <div class="my-auto py-2">
                      <img
                          src="<?= base_url('images/logo-kemenag-transparent.png') ?>"
                          alt="Logo Kementerian Agama"
                          class="h-16 w-16 object-contain mx-auto mb-5"
                      >
                      <p class="text-2xl font-serif font-bold text-white leading-tight mb-1">
                          <?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?>
                      </p>
                      <h2 class="text-base font-medium text-brand-100 mb-4">Panel Administrasi PTSP</h2>
                      <p class="text-sm text-brand-50 leading-relaxed">
                          Akses khusus petugas penerima tamu dan administrator untuk memantau kunjungan, meninjau keperluan, serta mengunduh laporan rekapitulasi.
                      </p>
                  </div>
                  <div class="pt-6 border-t border-brand-600">
                      <a href="<?= site_url('/') ?>" class="inline-flex items-center justify-center gap-2 text-xs font-semibold text-accent-200 hover:underline">
                          &larr; Kembali ke Buku Tamu Publik
                      </a>
                  </div>
              </aside>
      ```
  - **Buka `app/Views/tamu/sukses.php`**:
    - Tambahkan blok identitas instansi (logo + nama aplikasi) di bagian atas dalam kartu `.card-surface` (sebelum badge `Kunjungan Tercatat`) agar halaman sukses tetap memiliki identitas yang jelas tanpa `<header>`:
      ```php
      <section class="max-w-2xl mx-auto">
          <div class="brand-stripe" aria-hidden="true"></div>
          <div class="card-surface p-8 sm:p-12">
              <div class="flex items-center gap-3 pb-6 mb-6 border-b border-border">
                  <img
                      src="<?= base_url('images/logo-kemenag-transparent.png') ?>"
                      alt="Logo Kementerian Agama"
                      class="h-10 w-10 object-contain"
                  >
                  <div>
                      <p class="text-sm font-bold text-brand-700 leading-tight">
                          <?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?>
                      </p>
                      <p class="text-xs text-ink-muted">
                          <?= esc($pengaturan['nama_instansi_ptsp'] ?? 'Kantor Wilayah Kementerian Agama Provinsi Kalimantan Utara') ?>
                      </p>
                  </div>
              </div>

              <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                  <span class="inline-flex items-center gap-2 px-3 py-1 bg-success-bg text-success text-xs font-semibold">
                      Kunjungan Tercatat
                  </span>
                  <span class="text-xs text-ink-muted tabular-nums">
                      <?= esc(date('d/m/Y H:i')) ?> WITA
                  </span>
              </div>

              <h1 class="text-2xl sm:text-3xl font-bold text-ink font-serif mt-2 mb-3">
                  Kunjungan Berhasil Dicatat
              </h1>

              <p class="text-base text-ink-muted leading-relaxed mb-8">
                  Terima kasih, data kehadiran Anda telah masuk ke dalam sistem Pelayanan Terpadu Satu Pintu (PTSP). Silakan menuju meja petugas pelayanan atau isi formulir baru untuk pengunjung berikutnya.
              </p>

              <div class="flex flex-wrap items-center gap-4">
                  <a href="<?= site_url('/') ?>" class="btn-primary">Isi Form Lagi</a>
              </div>
          </div>
      </section>
      ```
  - **Kompilasi CSS & Jalankan Test**:
    - Jalankan `cmd /c npm run build:css`.
    - Jalankan `php -l` pada keempat file (`layouts/publik.php`, `tamu/form.php`, `admin/login.php`, `tamu/sukses.php`).
    - Jalankan `composer test`.
  - **Done when:** Keempat file lolos `php -l`, `public/css/app.css` terkompilasi tanpa error, `<aside>` di `admin/login.php` memiliki class `text-center`, logo `mx-auto`, dan menampilkan `$pengaturan['judul_aplikasi']`, serta `composer test` lulus 100% (8 tests, 22 assertions).

## Verify

1. Buka halaman utama buku tamu publik (`/`):
   - Pastikan tidak ada lagi bar `<header>` di bagian atas halaman.
   - Pastikan panel `<aside>` di sebelah kiri menampilkan Logo Kemenag, judul `BUKANWIL`, deskripsi, teks sambutan, jam WITA, dan nama instansi secara rata tengah tanpa kotak *Panduan Pengisian Mandiri*.
2. Buka halaman login admin (`/admin/login`):
   - Pastikan tidak ada bar `<header>` di atas kartu login.
   - Pastikan panel `<aside>` di sebelah kiri kartu login rata tengah (`text-center`), menampilkan Logo Kemenag di tengah (`mx-auto`), nama aplikasi `BUKANWIL`, subjudul `Panel Administrasi PTSP`, dan tautan kembali ke Buku Tamu Publik.
3. Buka halaman konfirmasi sukses (`/tamu/sukses`):
   - Pastikan kartu bukti kunjungan menampilkan logo dan nama aplikasi di bagian atas kartu serta tepat satu `<h1>`.


<!-- blueprint:completion {"schemaVersion":2,"specBytes":11994,"specSha256":"5ae51aabed6ffd2e7d1f3dbe27b505eb695fdbcb761b1ce34cc64ca13be2c0fc","branch":"refs/heads/fix/hapus-header-redundan-di-layout-publik-hapus-panduan-di-aside-form-tamu-dan-seragamkan-aside-login-admin","head":"ca79d5f589ecafa1d13a654819a32ba6e5e2da99","baseRef":"refs/heads/main","baseCommit":"ca79d5f589ecafa1d13a654819a32ba6e5e2da99","sourceTree":"02d9ef9f602f0270fea698c49e259fd2146defae","landing":"local-merge","absentOptional":[]} -->

## Try it now

Run or do:

Jalankan server aplikasi di terminal jika belum aktif:
```bash
php spark serve
```
Lalu buka peramban (*browser*) ke:
```text
http://localhost:8080/
```

Expected:

Bagian atas halaman bersih langsung dimulai dari garis aksen hijau (*brand stripe*) tanpa adanya bar `<header>` abu-abu ganda. Panel hijau di sebelah kiri (`<aside>`) tertata rata tengah (*centered*), memuat:
1. Logo Kementerian Agama transparan di tengah (`mx-auto`).
2. Judul aplikasi `BUKANWIL` (`<h1>`) dan deskripsi buku tamu.
3. Teks sambutan PTSP.
4. Kotak *"Panduan Pengisian Mandiri"* sudah **tidak ada lagi**.
5. Di bagian bawah `<aside>`, terdapat lencana jam WITA real-time (`#live-wita-clock`) dan teks nama instansi PTSP Kanwil Kemenag Kaltara.

This proves:

Header redundan di layout publik telah dihapus, kotak panduan pengisian mandiri telah dibersihkan, dan panel identitas `<aside>` publik kini tampil fokus dan proporsional.
