# Feature: Manajemen Arsip dan Pembersihan Data Tamu

**From build-plan:** feature 9
**Build attempt:** 1
**Status:** verified
**Branch:** `feature/manajemen-arsip-dan-pembersihan-data-tamu`

## Goal

Menyediakan mekanisme pengelolaan siklus hidup (*lifecycle*) data tamu di panel administrasi PTSP BUKANWIL:
1. Pembersihan data uji coba/spam menggunakan **Soft Delete** (`deleted_at`) agar data tidak valid dapat disembunyikan dari daftar aktif dan dikeluarkan dari perhitungan statistik dashboard tanpa menghilangkan jejak audit, dilengkapi fitur pemulihan (*restore*) dan penghapusan permanen (*hard purge*) khusus untuk Superadmin.
2. Pengarsipan data riwayat tamu lama (`is_archived`) untuk memisahkan buku tamu periode aktif harian dari arsip tahun-tahun lampau, menjaga kerapian tabel kerja petugas dan kinerja penelusuran data.

## In scope

- Penambahan kolom `is_archived` (TINYINT(1) DEFAULT 0) dan `deleted_at` (DATETIME NULL DEFAULT NULL) beserta indeks performa pada tabel `tamu` di `database/schema.sql`.
- Konfigurasi CodeIgniter 4 Model di `app/Models/TamuModel.php` (`$useSoftDeletes = true;`, `$deletedField = 'deleted_at';`, `$allowedFields`).
- Pembaruan kueri statistik `TamuModel::getStatistik()` agar data soft-deleted tidak mengotori total tamu, tamu hari ini, dan grafik tren kunjungan mingguan.
- Penambahan 3 tab navigasi di halaman daftar tamu admin (`app/Views/admin/tamu/index.php`):
  - **Aktif** (`tab=aktif`, default): `is_archived = 0` dan `deleted_at IS NULL`.
  - **Arsip** (`tab=arsip`): `is_archived = 1` dan `deleted_at IS NULL` (didukung filter tahun).
  - **Tong Sampah** (`tab=sampah`, khusus Superadmin): `deleted_at IS NOT NULL`.
- Endpoint aksi mutasi dengan proteksi role `superadmin` dan CSRF token:
  - `POST /admin/tamu/(:num)/hapus` (Soft Delete ke Tong Sampah)
  - `POST /admin/tamu/(:num)/pulihkan` (Restore dari Tong Sampah)
  - `POST /admin/tamu/(:num)/hapus-permanen` (Hard Delete rekaman database + pembersihan berkas fisik foto di `public/uploads/foto/`)
  - `POST /admin/tamu/(:num)/arsipkan` (Pindahkan ke tab Arsip)
  - `POST /admin/tamu/(:num)/batal-arsip` (Kembalikan ke tab Aktif)
  - `POST /admin/tamu/arsipkan-massal` (Opsi arsipkan data sebelum tahun tertentu)
- Penyesuaian tombol aksi status di halaman rincian tamu (`app/Views/admin/tamu/detail.php`) dengan modal konfirmasi dialog kustom `confirmDialog()`.
- Penyesuaian fungsi ekspor CSV (`exportCsv`) agar menghormati tab filter yang aktif.
- Unit test PHPUnit untuk menguji model soft delete, pengarsipan, dan isolasi statistik.

## Out of scope

- Pengubahan alur formulir publik buku tamu (`GET /` dan `POST /tamu/submit`) — form publik tetap murni menerima entri kunjungan baru.
- Otomasi cronjob latar belakang tanpa konfirmasi — pengarsipan data lama dipicu secara terarah oleh Superadmin (misalnya saat penutupan buku tahun anggaran).
- Pengeditan isi teks formulir tamu oleh admin biasa (menjaga integritas deklarasi identitas pengunjung).

## Build loop

Build one small step at a time. Follow `workflow.stepReview: "feature"` in `blueprint/config.json`: implementation runs through all small steps, verified with `composer test`, and produces one review packet before completion. Checkpoint commits are disabled. `/complete` creates the final feature commit.

## Build steps

- [x] **Step 1 - Skema Database & Pembaruan TamuModel** - Perbarui `database/schema.sql` dengan menambahkan kolom `is_archived` dan `deleted_at` serta indeksnya, aktifkan `$useSoftDeletes` di `app/Models/TamuModel.php`, perbarui `$allowedFields`, dan sesuaikan `applyFilter()` serta `getStatistik()` agar query data aktif mengecualikan rekaman berstatus soft-delete. *Done when:* Kueri `getStatistik()` hanya menghitung data non-deleted, filter tab didukung pada builder, dan pengujian `composer test` tetap 100% lulus.
- [x] **Step 2 - Controller Endpoint Otorisasi: Hapus, Pulihkan, dan Hapus Permanen** - Tambahkan rute POST dan controller method di `app/Controllers/Admin/TamuAdmin.php` untuk soft-delete, restore, dan hard-purge (lengkap dengan `unlink()` foto fisik) yang diproteksi guard `requireSuperadmin()` dan validasi CSRF. *Done when:* Aksi POST berhasil mengubah `deleted_at` atau menghapus permanen berkas foto di disk, serta menolak akses selain role superadmin.
- [x] **Step 3 - Controller Endpoint Otorisasi: Arsip & Batal Arsip** - Tambahkan rute POST dan controller method di `app/Controllers/Admin/TamuAdmin.php` untuk mengarsipkan tamu per ID, membatalkan arsip, dan arsip massal berdasarkan cut-off tahun. *Done when:* Status `is_archived` berhasil diperbarui dan dialihkan dengan notifikasi toast sukses.
- [x] **Step 4 - Tampilan Antarmuka Daftar Tamu (Tab Aktif, Arsip, dan Tong Sampah)** - Perbarui `app/Views/admin/tamu/index.php` untuk menyajikan bilah tab navigasi (Aktif, Arsip, dan Tong Sampah khusus Superadmin), badge status data, dan tombol aksi interaktif dengan modal dialog konfirmasi kustom. *Done when:* Tabel menampilkan data terisolasi sesuai tab yang dipilih dan modal konfirmasi logout/aksi berjalan lancar tanpa error konsol.
- [x] **Step 5 - Pembaruan Tampilan Detail Tamu & Penyesuaian Ekspor CSV** - Tambahkan tombol aksi status (Arsipkan / Hapus) di `app/Views/admin/tamu/detail.php` dan pastikan method `exportCsv()` mengunduh data sesuai tab yang sedang difilter. *Done when:* Halaman detail menampilkan status arsip/terhapus dengan tombol aksi yang tepat, dan unduhan CSV menghasilkan daftar tamu sesuai tab aktif.
- [x] **Step 6 - Unit Testing & Verifikasi Regresi** - Buat berkas pengujian `tests/unit/TamuLifecycleTest.php` untuk menguji fungsionalitas soft delete, restore, arsip, dan penghitungan statistik. Jalankan `composer test`. *Done when:* Pengujian siklus hidup data tamu baru lulus 100% dan seluruh 5 pengujian sebelumnya tetap lulus.

## Files / areas

- `database/schema.sql` - Penambahan DDL kolom `is_archived`, `deleted_at`, dan indeks.
- `app/Models/TamuModel.php` - Fitur bawaan CI4 soft-delete, penyesuaian filter tab dan statistik.
- `app/Controllers/Admin/TamuAdmin.php` - Endpoint aksi hapus, pulihkan, purge foto, arsip, dan ekspor tab.
- `app/Config/Routes.php` - Pendaftaran rute POST untuk lifecycle tamu.
- `app/Views/admin/tamu/index.php` - Tampilan antarmuka bilah tab, badge, dan tombol aksi.
- `app/Views/admin/tamu/detail.php` - Penambahan tombol aksi status di halaman rincian tamu.
- `tests/unit/TamuLifecycleTest.php` - Pengujian unit PHPUnit siklus hidup data tamu.

## Data / contracts

- Kolom baru `tamu`:
  - `is_archived`: `TINYINT(1) UNSIGNED NOT NULL DEFAULT 0` (0 = aktif, 1 = diarsipkan)
  - `deleted_at`: `DATETIME NULL DEFAULT NULL` (NULL = aktif/arsip sah, NOT NULL = di tong sampah)
- Indeks baru: `idx_tamu_archive` (`is_archived`), `idx_tamu_deleted` (`deleted_at`)
- Model kontrak:
  - Default query `find()`, `findAll()`, `paginate()` otomatis menambahkan `WHERE deleted_at IS NULL`.
  - Tab Aktif: `is_archived = 0 AND deleted_at IS NULL`
  - Tab Arsip: `is_archived = 1 AND deleted_at IS NULL`
  - Tab Sampah: `deleted_at IS NOT NULL`
- Batasan Keamanan & Hak Akses:
  - Role `admin` biasa: hanya dapat membaca tab Aktif dan Arsip.
  - Role `superadmin`: dapat membaca semua tab, memindahkan ke Tong Sampah, melakukan Restore, melakukan Hard Purge berkas fisik, dan mengarsipkan data.
  - Semua aksi mutasi wajib menggunakan HTTP method `POST` dan memvalidasi `csrf_field()`.

## Testing

- Menjalankan `composer test` untuk memverifikasi logika unit test siklus hidup data tamu.
- Verifikasi alur browser:
  1. Login sebagai superadmin, buka `/admin/tamu`.
  2. Buka tab **Aktif**, pilih entri uji coba &rarr; klik tombol **Hapus** &rarr; konfirmasi modal &rarr; entri berpindah ke tab **Tong Sampah**.
  3. Buka halaman Dashboard (`/admin`) &rarr; verifikasi total tamu berkurang (data sampah tidak terhitung).
  4. Buka tab **Tong Sampah** &rarr; klik **Pulihkan** &rarr; entri kembali ke tab **Aktif**.
  5. Buka tab **Aktif** &rarr; klik **Arsipkan** &rarr; entri berpindah ke tab **Arsip**.
  6. Login sebagai akun petugas non-superadmin &rarr; pastikan tab Tong Sampah dan tombol Hapus/Arsip tidak tampil.

## Notes for the AI

- Gunakan helper dialog modal `confirmDialog()` dari `public/js/dialog.js` (jangan gunakan `confirm()` bawaan browser).
- Pada saat *hard purge* permanen rekaman tamu, periksa keberadaan berkas foto dengan `is_file(FCPATH . $path)` sebelum memanggil `unlink()` untuk mencegah *unhandled filesystem error*.
- Pastikan kueri agregasi di `TamuModel::getStatistik()` secara eksplisit memuat `where('deleted_at IS NULL')` karena kueri builder langsung `$db->table('tamu')` membypass scoping soft delete bawaan Model.

<!-- blueprint:completion {"schemaVersion":2,"specBytes":8670,"specSha256":"52dc29c962fb86a70099277b8cd0ec2bd49c843f1c48fb441821a2c1e9be28ba","branch":"refs/heads/feature/manajemen-arsip-dan-pembersihan-data-tamu","head":"2359861850adfa003e6de852e1e7039db90a247a","baseRef":"refs/heads/main","baseCommit":"2359861850adfa003e6de852e1e7039db90a247a","sourceTree":"cb5cbf0d4f018c3a363a33d62869b7d384672d76","landing":"local-merge","absentOptional":[]} -->

## Try it now

Run or do:

Buka `http://bukanwil.test/admin/tamu` sebagai superadmin, pada tab **Aktif** klik tombol **Hapus** pada salah satu baris tamu lalu konfirmasi modal. Buka tab **Tong Sampah** dan klik tombol **Pulihkan**.

Expected:

Dialog konfirmasi kustom muncul, entri tamu berpindah ke tab Tong Sampah (data sampah tidak dihitung di statistik total tamu dashboard), dan kembali ke tab Aktif setelah dipulihkan.

This proves:

Mekanisme lifecycle data tamu (soft delete, isolasi dashboard, dan pemulihan data) pada TamuModel dan antarmuka admin berfungsi sesuai spesifikasi.
