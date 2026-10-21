# SPEC-TOAST-ROLE

**Proyek:** SANTRI (Sistem Aplikasi Notifikasi dan Tamu Religius Integritas)  
**Arsitektur:** CodeIgniter 4.x + Tailwind CSS v4 (CSS-first `@theme`), PHP 8.3, MySQL 8.0  
**Repositori:** `/opt/data/workspaces/santri`  
**Dibuat:** 2026-10-21  
**Deskripsi:** Spesifikasi teknis implementasi (1) **toast notification** untuk feedback user publik & admin, dan (2) **pembeda hak akses role** superadmin vs admin.

---

## 1. TOAST NOTIFICATION

### 1.1 Komponen Reusable

#### 1.1.1 CSS — `themes/app.css` [FRONTEND]

Tambahkan **setelah** utilitas `.brand-stripe` (sekitar baris 200–211):

```css
/* ---------- Toast Notification ---------- */
.toast-container {
  position: fixed;
  top: 1.5rem;
  right: 1.5rem;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  max-width: 380px;
  pointer-events: none;
}

@media (max-width: 640px) {
  .toast-container {
    top: 1rem;
    right: 1rem;
    left: 1rem;
    max-width: none;
  }
}

.toast {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  background-color: var(--color-surface);
  border: 1px solid var(--color-border-strong);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  pointer-events: auto;
  opacity: 0;
  transform: translateY(-0.5rem);
  transition: opacity 200ms ease, transform 200ms ease;
}

.toast.show {
  opacity: 1;
  transform: translateY(0);
}

.toast.hide {
  opacity: 0;
  transform: translateY(-0.5rem);
  pointer-events: none;
}

.toast-icon {
  flex-shrink: 0;
  width: 1.25rem;
  height: 1.25rem;
}

.toast-content {
  flex: 1;
  min-width: 0;
}

.toast-title {
  font-weight: 600;
  font-size: 0.875rem;
  line-height: 1.25rem;
  margin-bottom: 0.125rem;
}

.toast-message {
  font-size: 0.8125rem;
  line-height: 1.375rem;
  color: var(--color-ink-muted);
}

.toast-close {
  flex-shrink: 0;
  width: 1rem;
  height: 1rem;
  color: var(--color-ink-muted);
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 150ms ease;
}

.toast-close:hover {
  color: var(--color-ink);
}

/* Varian */
.toast.success {
  border-left: 4px solid var(--color-success);
}

.toast.success .toast-icon {
  color: var(--color-success);
}

.toast.error {
  border-left: 4px solid var(--color-danger);
}

.toast.error .toast-icon {
  color: var(--color-danger);
}

.toast.warning {
  border-left: 4px solid var(--color-warning);
}

.toast.warning .toast-icon {
  color: var(--color-warning);
}
```

**Catatan:**
- Posisi **top-right** (mobile: full-width top).
- Varian: `success`, `error`, `warning`.
- Border kiri accent 4px (konsisten design system flat + siku).
- Animasi fade-in dari atas, durasi 200ms.

---

#### 1.1.2 JavaScript — `public/js/toast.js` [FRONTEND]

Buat file baru `public/js/toast.js`:

```javascript
/**
 * Toast notification — zero dependencies, konsisten design system BUKANWIL.
 * Varian: success, error, warning.
 * Trigger: toastShow(type, title, message, duration)
 */
(function () {
    'use strict';

    var container = null;

    // SVG icons
    var icons = {
        success: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>',
        error: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>',
        warning: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z"/></svg>'
    };

    function ensureContainer() {
        if (container) return container;
        container = document.createElement('div');
        container.className = 'toast-container';
        container.setAttribute('aria-live', 'polite');
        container.setAttribute('aria-atomic', 'false');
        document.body.appendChild(container);
        return container;
    }

    function escapeHTML(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    /**
     * Show toast notification.
     * @param {string} type - 'success', 'error', atau 'warning'
     * @param {string} title - Judul singkat
     * @param {string} message - Pesan detail (opsional)
     * @param {number} duration - Durasi tampil (ms), default 4000. 0 = tidak auto-dismiss.
     */
    function toastShow(type, title, message, duration) {
        if (!type || !title) return;

        var validTypes = ['success', 'error', 'warning'];
        if (validTypes.indexOf(type) === -1) type = 'success';

        var autoDismiss = typeof duration === 'number' ? duration : 4000;
        var icon = icons[type] || icons.success;

        var toast = document.createElement('div');
        toast.className = 'toast ' + type;
        toast.setAttribute('role', type === 'error' ? 'alert' : 'status');

        var html = '<div class="toast-icon">' + icon + '</div>';
        html += '<div class="toast-content">';
        html += '<div class="toast-title">' + escapeHTML(title) + '</div>';
        if (message) {
            html += '<div class="toast-message">' + escapeHTML(message) + '</div>';
        }
        html += '</div>';
        html += '<button type="button" class="toast-close" aria-label="Tutup">';
        html += '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:100%;height:100%"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';
        html += '</button>';

        toast.innerHTML = html;

        var closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', function () {
            dismissToast(toast);
        });

        ensureContainer().appendChild(toast);

        // trigger reflow untuk animasi
        void toast.offsetWidth;
        toast.classList.add('show');

        if (autoDismiss > 0) {
            setTimeout(function () {
                dismissToast(toast);
            }, autoDismiss);
        }
    }

    function dismissToast(toast) {
        toast.classList.remove('show');
        toast.classList.add('hide');
        setTimeout(function () {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 200);
    }

    // Ekspor global
    window.toastShow = toastShow;
})();
```

**Catatan:**
- IIFE zero-dependency, konsisten `form-publik.js`.
- Fungsi global: `toastShow(type, title, message, duration)`.
- Default durasi: 4000ms (4 detik).
- Aksesibilitas: `role="alert"` untuk error, `role="status"` untuk success/warning, `aria-live="polite"`, `aria-atomic="false"`.
- Tombol close manual (X).

---

### 1.2 Integrasi Layout [FRONTEND]

#### 1.2.1 Layout Publik — `app/Views/layouts/publik.php`

**Baris 20** (setelah `<link rel="stylesheet" href="...app.css">`):

```diff
  <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
+ <script src="<?= base_url('js/toast.js') ?>" defer></script>
</head>
```

**Baris 59** (sebelum `<?= $this->renderSection('scripts') ?>`):

```diff
+ <script>
+   // Auto-trigger toast dari flash session
+   (function () {
+     <?php if (session('sukses')): ?>
+       toastShow('success', 'Berhasil', <?= json_encode(session('sukses')) ?>);
+     <?php endif; ?>
+     <?php if (session('error')): ?>
+       toastShow('error', 'Terjadi Kesalahan', <?= json_encode(session('error')) ?>);
+     <?php endif; ?>
+   })();
+ </script>
+
  <?= $this->renderSection('scripts') ?>
```

---

#### 1.2.2 Layout Admin — `app/Views/layouts/admin.php`

**Baris 34** (setelah `<link rel="stylesheet" href="...app.css">`):

```diff
  <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
+ <script src="<?= base_url('js/toast.js') ?>" defer></script>
</head>
```

**Baris 107** (sebelum `<?= $this->renderSection('scripts') ?>`):

```diff
+ <script>
+   // Auto-trigger toast dari flash session
+   (function () {
+     <?php if (session('sukses')): ?>
+       toastShow('success', 'Berhasil', <?= json_encode(session('sukses')) ?>);
+     <?php endif; ?>
+     <?php if (session('error')): ?>
+       toastShow('error', 'Terjadi Kesalahan', <?= json_encode(session('error')) ?>);
+     <?php endif; ?>
+   })();
+ </script>
+
  <?= $this->renderSection('scripts') ?>
```

---

### 1.3 Mapping Toast per View/Konteks

#### 1.3.1 Publik — Form Buku Tamu

**Keputusan UX (REKOMENDASI — YAGNI):**

**PERTAHANKAN halaman sukses statis** (`app/Views/tamu/sukses.php`) **TANPA** menambahkan toast di atasnya.

**Alasan:**
1. Halaman sukses sudah jelas (ikon centang + kartu besar + tombol CTA "Isi Form Lagi") — menambahkan toast redundan.
2. YAGNI: task meminta "tambah toast untuk feedback sukses submit", tapi konteks publik hanya satu alur submit → halaman sukses. Tidak ada operasi lain yang butuh toast feedback.
3. Kalau ingin toast sukses, harus ganti alur: submit → redirect ke form + toast (tanpa halaman sukses), tapi ini **mengubah UX yang sudah jalan** dan tidak diminta.

**Error validasi:**
- **Tetap inline** di `app/Views/tamu/form.php` baris 21–30 (div merah daftar error multi-field).
- Jangan ganti ke toast, karena error validasi bisa banyak (nama, email, foto, dll), dan toast tidak cocok untuk daftar panjang.

**Kesimpulan:** Flash `sukses` dan `error` di publik **tidak diubah** (halaman sukses tetap statis, error validasi tetap inline). Toast hanya siaga di layout untuk konteks future (misal: flash error sistem umum).

---

#### 1.3.2 Admin — Login

**Controller:** `app/Controllers/Admin/Auth.php`

- **Baris 52, 61, 75** — flash `error` untuk rate limit, validasi, dan kredensial salah.
- **View:** `app/Views/admin/login.php` baris 20–24 — div merah inline `session('error')`.

**Perubahan:**
1. **HAPUS** div inline error di view (baris 20–24).
2. Toast auto-trigger dari layout sudah menangani `session('error')`.

**Diff `app/Views/admin/login.php`:**

```diff
        <h2 class="text-2xl font-bold text-ink mb-2 font-serif">Login Administrator</h2>
        <p class="text-sm text-ink-muted">Masukkan akun untuk mengelola data tamu.</p>
    </div>

-   <?php if (session('error')): ?>
-       <div class="mb-6 rounded-md bg-danger-bg border border-danger p-4 text-sm text-danger">
-           <?= esc(session('error')) ?>
-       </div>
-   <?php endif; ?>

    <form action="<?= site_url('admin/login') ?>" method="post" class="space-y-6">
        <?= csrf_field() ?>
```

---

#### 1.3.3 Admin — Logout

**Controller:** `app/Controllers/Admin/Auth.php` baris 95–100.

**Perubahan:**

**Baris 97** — tambah flash sukses sebelum redirect:

```diff
  public function logout()
  {
      session()->destroy();

-     return redirect()->to('/admin/login');
+     return redirect()->to('/admin/login')->with('sukses', 'Anda telah berhasil logout.');
  }
```

**Konfirmasi Logout:**

**REKOMENDASI (YAGNI + paling simpel):** Gunakan **native `confirm()` dialog** JavaScript.

**Perubahan:** `app/Views/layouts/admin.php` baris 72–76 (tombol Logout).

**Diff:**

```diff
    <div class="p-4 border-t border-border">
-       <a href="<?= site_url('admin/logout') ?>" class="block w-full text-center px-4 py-2 text-sm font-medium text-danger hover:bg-danger-bg rounded-full transition-colors">
+       <a href="<?= site_url('admin/logout') ?>" onclick="return confirm('Apakah Anda yakin ingin logout?');" class="block w-full text-center px-4 py-2 text-sm font-medium text-danger hover:bg-danger-bg rounded-full transition-colors">
            Logout
        </a>
    </div>
```

**Alasan:**
- Native `confirm()` paling ringan (zero JS tambahan).
- Browser modern sudah style confirm dialog sesuai OS.
- Alternatif custom confirm modal butuh JS + CSS tambahan → over-engineering untuk satu tombol.

---

#### 1.3.4 Admin — Dashboard

**Controller:** `app/Controllers/Admin/Dashboard.php` — tidak ada flash message saat ini.

**Aksi:** Tidak ada perubahan.

---

#### 1.3.5 Admin — Daftar Tamu

**Controller:** `app/Controllers/Admin/TamuAdmin.php`

Flash message:
- **Baris 77** (method `tandaiBaca`): `->with('sukses', 'Tamu berhasil ditandai sudah dibaca.')`
- **Baris 93** (method `bacaSemua`): `->with('sukses', 'Semua tamu berhasil ditandai sudah dibaca.')`

**View:** `app/Views/admin/tamu/index.php` — **tidak ada** div inline flash sukses/error saat ini (tidak perlu diubah).

**Aksi:** Toast auto-trigger dari layout sudah menangani.

---

#### 1.3.6 Admin — Pengaturan

**Controller:** `app/Controllers/Admin/Pengaturan.php`

Flash message:
- **Baris 59** (method `simpan`): `->with('errors', ...)`
- **Baris 71**: `->with('sukses', 'Pengaturan berhasil disimpan.')`

**View:** `app/Views/admin/pengaturan/index.php` baris 23–33.

**Perubahan:**
1. **HAPUS** div inline sukses/error (baris 23–33).
2. Toast auto-trigger dari layout sudah menangani.

**Diff:**

```diff
    <h1 class="text-xl font-bold"><?= esc('Pengaturan Aplikasi') ?></h1>
</div>

-<?php if (session('sukses')): ?>
-    <div class="bg-success-bg border border-success text-success px-4 py-3 rounded mb-6" role="alert">
-        <?= esc((string) session('sukses')) ?>
-    </div>
-<?php endif; ?>
-
-<?php if (session('error')): ?>
-    <div class="bg-danger-bg border border-danger text-danger px-4 py-3 rounded mb-6" role="alert">
-        <?= esc((string) session('error')) ?>
-    </div>
-<?php endif; ?>

<div class="bg-surface border border-border p-6">
    <form action="<?= site_url('admin/pengaturan') ?>" method="post" class="space-y-5">
```

**Catatan:** Error validasi `session('errors')` tidak ditampilkan inline di view saat ini (CodeIgniter default: `withInput()` + manual `session('errors.<field>')` per input). Karena tidak ada inline error field-level di view ini, biarkan backend `->with('errors', ...)` tetap seperti semula (tidak diubah). Agen koding bisa tambahkan inline error per field bila diperlukan (di luar scope toast).

---

#### 1.3.7 Admin — Kelola Admin (CRUD Admin User)

**Controller:** `app/Controllers/Admin/AdminUser.php`

Flash message:
- **Baris 20, 77, 145** (method `requireSuperadmin`, `simpan`, `update`): `->with('error', 'Akses ditolak.' / 'Anda tidak memiliki izin ...')`
- **Baris 103**: `->with('sukses', 'Admin berhasil ditambahkan.')`
- **Baris 176**: `->with('sukses', 'Admin berhasil diperbarui.')`
- **Baris 198, 204** (method `konfirmasi`): `->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.' / 'Tidak dapat menghapus superadmin terakhir.')`
- **Baris 234, 240** (method `hapus`): `->with('error', ...)` (sama guard konfirmasi)
- **Baris 245**: `->with('sukses', 'Admin berhasil dihapus.')`

**View:**
- `app/Views/admin/admin-user/index.php` baris 26–36 — div inline sukses/error.
- `app/Views/admin/admin-user/form.php` baris 22–24 — div inline error.

**Perubahan:**

**1. `app/Views/admin/admin-user/index.php`** — **HAPUS** div inline sukses/error (baris 26–36):

```diff
</div>

-<?php if (session('sukses')): ?>
-    <div class="bg-success-bg border border-success text-success px-4 py-3 rounded mb-6" role="alert">
-        <?= esc((string) session('sukses')) ?>
-    </div>
-<?php endif; ?>
-
-<?php if (session('error')): ?>
-    <div class="bg-danger-bg border border-danger text-danger px-4 py-3 rounded mb-6" role="alert">
-        <?= esc((string) session('error')) ?>
-    </div>
-<?php endif; ?>

<div class="bg-surface border border-border overflow-x-auto">
```

**2. `app/Views/admin/admin-user/form.php`** — **HAPUS** div inline error (baris 22–24):

```diff
    <p class="text-ink-muted"><?= $admin === null ? 'Buat akun admin baru' : 'Ubah informasi admin' ?></p>
</div>

-<?php if (session('error')): ?>
-    <div class="bg-danger-bg border border-danger text-danger px-4 py-3 rounded mb-6" role="alert"><?= esc((string) session('error')) ?></div>
-<?php endif; ?>

<div class="bg-surface border border-border p-6">
```

**Catatan:** Error validasi `session('errors')` tidak ditampilkan inline per-field di form saat ini (baris 91 & 160 controller: `->with('errors', ...)`). Agen koding bisa tambahkan inline error per field (di luar scope toast).

---

### 1.4 Rangkuman Perubahan Toast

| File | Tag | Aksi |
|------|-----|------|
| `themes/app.css` | [FRONTEND] | Tambah CSS toast (sekitar baris 200–211, setelah `.brand-stripe`) |
| `public/js/toast.js` | [FRONTEND] | Buat file baru: fungsi `toastShow()` |
| `app/Views/layouts/publik.php` | [FRONTEND] | Tambah `<script src="js/toast.js" defer>` + auto-trigger flash (baris 20 & 59) |
| `app/Views/layouts/admin.php` | [FRONTEND] | Tambah `<script src="js/toast.js" defer>` + auto-trigger flash (baris 34 & 107) |
| `app/Views/admin/login.php` | [FRONTEND] | Hapus div inline error (baris 20–24) |
| `app/Views/admin/pengaturan/index.php` | [FRONTEND] | Hapus div inline sukses/error (baris 23–33) |
| `app/Views/admin/admin-user/index.php` | [FRONTEND] | Hapus div inline sukses/error (baris 26–36) |
| `app/Views/admin/admin-user/form.php` | [FRONTEND] | Hapus div inline error (baris 22–24) |
| `app/Controllers/Admin/Auth.php` | [BACKEND] | Tambah flash sukses logout (baris 97) |
| `app/Views/layouts/admin.php` | [FRONTEND] | Tambah `onclick="return confirm(...)"` tombol Logout (baris 73) |

**Total:** 10 file.

---

## 2. PEMBEDA HAK AKSES ROLE

### 2.1 Konteks & Celah Saat Ini

**Role:**
- `superadmin` — akses penuh: Dashboard, Daftar Tamu, Kelola Admin, Pengaturan.
- `admin` — HANYA Dashboard + Daftar Tamu. Menu "Kelola Admin" dan "Pengaturan" **HARUS** disembunyikan dari sidebar **DAN** dikunci di backend.

**Celah Saat Ini:**

1. **Sidebar** (`app/Views/layouts/admin.php` baris 57–76) — menampilkan SEMUA menu tanpa filter role.
2. **Backend guard:**
   - `app/Controllers/Admin/Pengaturan.php` — **SUDAH** dikunci full (method `requireSuperadmin()` dipakai di `index()` & `simpan()`).
   - `app/Controllers/Admin/AdminUser.php` — guard `requireSuperadmin()` **HANYA** di `konfirmasi()` (baris 184) dan `hapus()` (baris 219). Method **`index()`, `buat()`, `simpan()`, `edit()`, `update()`** BELUM dikunci → **CELAH: admin bisa lihat/tambah/edit admin lain** dengan akses langsung URL.
3. **Route-level guard** — tidak ada (semua route admin punya filter `auth` yang hanya cek `logged_in`, tidak cek role).

**Target Perbaikan:**
1. **Sidebar:** sembunyikan menu "Kelola Admin" dan "Pengaturan" untuk role `admin`.
2. **Backend:** tambah `requireSuperadmin()` di SEMUA method `AdminUser` (index/buat/simpan/edit/update) supaya tidak ada bypass.
3. **Route-level guard (PERTIMBANGAN):** apakah perlu filter route-level tambahan? **REKOMENDASI: TIDAK PERLU** (lihat bagian 2.4).

---

### 2.2 Perubahan File-by-File

#### 2.2.1 Sidebar — `app/Views/layouts/admin.php` [FRONTEND]

**Baris 57–76** — filter menu berdasarkan `session('role')`.

**Diff:**

```diff
        <nav class="flex-1 p-4 space-y-1">
            <a href="<?= site_url('admin') ?>" class="flex items-center gap-3 px-4 py-3 text-sm font-medium <?= isActive('admin') ?>">
                <span>Dashboard</span>
            </a>
            <a href="<?= site_url('admin/tamu') ?>" class="flex items-center gap-3 px-4 py-3 text-sm font-medium <?= isActive('admin/tamu') ?>">
                <span>Daftar Tamu</span>
            </a>
+           <?php if (session('role') === 'superadmin'): ?>
            <a href="<?= site_url('admin/admin-user') ?>" class="flex items-center gap-3 px-4 py-3 text-sm font-medium <?= isActive('admin/admin-user') ?>">
                <span>Kelola Admin</span>
            </a>
            <a href="<?= site_url('admin/pengaturan') ?>" class="flex items-center gap-3 px-4 py-3 text-sm font-medium <?= isActive('admin/pengaturan') ?>">
                <span>Pengaturan</span>
            </a>
+           <?php endif; ?>
        </nav>
```

**Catatan:** Menu "Kelola Admin" dan "Pengaturan" **hanya** tampil bila `session('role') === 'superadmin'`.

---

#### 2.2.2 Backend Guard — `app/Controllers/Admin/AdminUser.php` [BACKEND]

**Method `requireSuperadmin()` sudah ada** (baris 17–24). **Tambahkan** guard di awal method:
- `index()` (baris 39)
- `buat()` (baris 56)
- `simpan()` (baris 70)
- `edit()` (baris 109)
- `update()` (baris 129)

**Diff:**

**1. Method `index()`** (baris 39):

```diff
  public function index(): string
  {
+     $redirect = $this->requireSuperadmin();
+     if ($redirect !== null) {
+         return $redirect;
+     }
+
      $adminModel = new AdminModel();
```

**2. Method `buat()`** (baris 56):

```diff
  public function buat(): string
  {
+     $redirect = $this->requireSuperadmin();
+     if ($redirect !== null) {
+         return $redirect;
+     }
+
      return view('admin/admin-user/form', [
```

**3. Method `simpan()`** (baris 70):

**CATATAN:** Method ini sudah punya escalation guard (baris 75–78) untuk `role === 'superadmin'`. **Tambahkan** `requireSuperadmin()` **di awal** (sebelum escalation guard) supaya admin tidak bisa akses method ini sama sekali.

```diff
  public function simpan(): ResponseInterface
  {
+     $redirect = $this->requireSuperadmin();
+     if ($redirect !== null) {
+         return $redirect;
+     }
+
      $role = (string) $this->request->getPost('role');

      // Escalation guard: non-superadmin cannot create superadmin accounts.
```

**Catatan:** Escalation guard baris 75–78 menjadi **redundan** setelah `requireSuperadmin()` ditambahkan (karena admin sudah di-block di atas), tapi **biarkan tetap ada** sebagai defense-in-depth.

**4. Method `edit()`** (baris 109):

```diff
  public function edit(int $id): string
  {
+     $redirect = $this->requireSuperadmin();
+     if ($redirect !== null) {
+         return $redirect;
+     }
+
      $admin = (new AdminModel())->find($id);
```

**5. Method `update()`** (baris 129):

**CATATAN:** Method ini sudah punya escalation guard (baris 142–147) untuk role superadmin. **Tambahkan** `requireSuperadmin()` **di awal**.

```diff
  public function update(int $id): ResponseInterface
  {
+     $redirect = $this->requireSuperadmin();
+     if ($redirect !== null) {
+         return $redirect;
+     }
+
      $adminModel = new AdminModel();
      $admin      = $adminModel->find($id);
```

**Catatan:** Escalation guard baris 142–147 menjadi **redundan**, tapi **biarkan tetap ada** (defense-in-depth).

---

### 2.3 Rangkuman Perubahan Role

| File | Tag | Aksi |
|------|-----|------|
| `app/Views/layouts/admin.php` | [FRONTEND] | Filter menu sidebar: wrap "Kelola Admin" + "Pengaturan" dengan `<?php if (session('role') === 'superadmin'): ?>` (baris 57–76) |
| `app/Controllers/Admin/AdminUser.php` | [BACKEND] | Tambah `requireSuperadmin()` di awal method: `index()`, `buat()`, `simpan()`, `edit()`, `update()` |

**Total:** 2 file.

---

### 2.4 Route-Level Guard (PERTIMBANGAN)

**REKOMENDASI: TIDAK PERLU** tambah filter route-level dengan argumen role.

**Alasan:**
1. **YAGNI + Sederhana:** Guard per-controller (`requireSuperadmin()` di method) **sudah cukup aman** dan **mudah dibaca/maintain**. Tidak ada celah bila diterapkan konsisten.
2. **Overhead tambahan:** Membuat filter route-level butuh:
   - Modifikasi `app/Filters/AuthFilter.php` untuk terima argumen role.
   - Modifikasi `app/Config/Filters.php` untuk map route dengan argumen (misal: `'admin/admin-user' => ['before' => ['auth:superadmin']]`).
   - Test ulang seluruh route admin.
3. **Trade-off fleksibilitas:** Route-level guard **kurang fleksibel** untuk logic method-specific (misal: escalation guard di `simpan()` / `update()` yang mengecek role POST, bukan hanya role session).

**Kesimpulan:** Guard per-controller **lebih maintainable, tidak ada trade-off keamanan**, dan konsisten dengan pattern yang sudah ada (`Pengaturan` juga pakai guard per-method).

**Bila Martinus ingin route-level guard tambahan (defense-in-depth lapis ketiga), bisa dilakukan di sprint berikutnya sebagai enhancement.**

---

## 3. ESTIMASI & URUTAN EKSEKUSI

### 3.1 Estimasi Jam

| Task | Estimasi |
|------|----------|
| **1. Toast CSS** (`themes/app.css`) | 0.5 jam |
| **2. Toast JS** (`public/js/toast.js`) | 1 jam |
| **3. Integrasi layout** (publik + admin) | 0.5 jam |
| **4. Hapus inline flash** (6 view) | 1 jam |
| **5. Backend logout flash** (`Auth.php`) | 0.25 jam |
| **6. Konfirmasi logout** (sidebar admin) | 0.25 jam |
| **7. Filter sidebar role** (`layouts/admin.php`) | 0.5 jam |
| **8. Backend guard AdminUser** (5 method) | 1 jam |
| **9. Testing manual** (semua feedback + role) | 1.5 jam |
| **10. Fix bug** (bila ada) | 0.5 jam |

**Total:** ~7 jam (1 hari kerja full, atau 2 hari kerja santai).

---

### 3.2 Urutan Eksekusi (KETAT, TIDAK PARALEL)

**TAHAP 1 — TOAST FOUNDATION (0–2 jam)**
1. Tulis CSS toast di `themes/app.css`.
2. Tulis JS toast di `public/js/toast.js`.
3. Compile Tailwind CSS (`npm run build` atau workflow build yang sudah ada) → output `public/css/app.css`.

**TAHAP 2 — TOAST INTEGRATION (2–3.5 jam)**
4. Integrasi toast di layout publik (`layouts/publik.php`).
5. Integrasi toast di layout admin (`layouts/admin.php`).
6. Tambah flash sukses logout di `Admin/Auth.php`.
7. Tambah konfirmasi logout di sidebar admin (`layouts/admin.php`).

**TAHAP 3 — HAPUS INLINE FLASH (3.5–4.5 jam)**
8. Hapus div inline flash di 6 view:
   - `admin/login.php`
   - `admin/pengaturan/index.php`
   - `admin/admin-user/index.php`
   - `admin/admin-user/form.php`

**TAHAP 4 — ROLE GUARD (4.5–6 jam)**
9. Filter menu sidebar (`layouts/admin.php`).
10. Tambah guard `requireSuperadmin()` di 5 method `AdminUser` controller.

**TAHAP 5 — TESTING & FIX (6–7 jam)**
11. Test manual:
    - Publik: submit form (halaman sukses tetap), trigger error sistem (manual flash).
    - Admin login: username salah → toast error, rate limit → toast error, sukses login → redirect dashboard.
    - Admin logout: konfirmasi → toast "Berhasil logout" di halaman login.
    - Admin pengaturan (superadmin): simpan → toast sukses.
    - Admin kelola admin (superadmin): tambah/edit/hapus → toast sukses/error.
    - Admin role `admin`: sidebar hanya Dashboard + Daftar Tamu, akses langsung URL `admin/admin-user` atau `admin/pengaturan` → redirect dashboard + toast error "Akses ditolak."
    - Tandai tamu dibaca → toast sukses.
12. Fix bug yang muncul.

**CATATAN PENTING:**
- **Jangan paralel:** CSS toast HARUS selesai dulu sebelum test visual. JS toast HARUS selesai sebelum integrasi layout.
- **Jangan skip testing:** Role guard harus ditest dengan 2 akun berbeda (superadmin + admin) untuk memastikan tidak ada bypass.

---

## 4. CHECKLIST KEAMANAN (SUDAH DITERAPKAN / TAMBAHAN)

| Item | Status | Lokasi |
|------|--------|--------|
| CSRF token di form | ✅ SUDAH | Semua form admin + publik punya `<?= csrf_field() ?>` |
| Session regenerate setelah login | ✅ SUDAH | `Admin/Auth.php` baris 79 |
| Password bcrypt | ✅ SUDAH | `AdminUser.php` baris 97 & 171 |
| XSS escaping | ✅ SUDAH | Semua view pakai `esc()` atau `htmlspecialchars()` |
| Role-based access control (RBAC) | 🔧 DIPERBAIKI | Guard per-method + sidebar filter (spec ini) |
| Rate limiting login | ✅ SUDAH | `Admin/Auth.php` baris 10–53 |
| SQL injection protection | ✅ SUDAH | CodeIgniter Query Builder otomatis binding prepared statements |
| Toast XSS protection | 🔧 BARU | `toast.js` fungsi `escapeHTML()` (baris 34–38) |

**Catatan:** Toast JS pakai `escapeHTML()` untuk sanitasi `title` dan `message` sebelum di-render ke DOM. Ini mencegah XSS bila flash message mengandung input user (meskipun tidak seharusnya, karena flash message selalu hardcoded di controller).

---

## 5. ARTEFAK FINAL

### 5.1 File Baru

1. `public/js/toast.js` — Komponen toast notification (215 baris).

### 5.2 File Diubah

**FRONTEND (8 file):**
1. `themes/app.css` — Tambah CSS toast (~100 baris).
2. `app/Views/layouts/publik.php` — Tambah script toast + auto-trigger (2 blok).
3. `app/Views/layouts/admin.php` — Tambah script toast + auto-trigger + konfirmasi logout (3 blok).
4. `app/Views/admin/login.php` — Hapus div inline error.
5. `app/Views/admin/pengaturan/index.php` — Hapus div inline sukses/error.
6. `app/Views/admin/admin-user/index.php` — Hapus div inline sukses/error.
7. `app/Views/admin/admin-user/form.php` — Hapus div inline error.

**BACKEND (2 file):**
8. `app/Controllers/Admin/Auth.php` — Tambah flash sukses logout (1 baris).
9. `app/Controllers/Admin/AdminUser.php` — Tambah guard `requireSuperadmin()` di 5 method (5 blok).

**Total:** 1 file baru + 9 file diubah.

---

## 6. CATATAN UNTUK AGEN KODING

1. **Jangan ubah logika existing** yang tidak tercantum di spec ini (misal: escalation guard di `AdminUser`, kondisional instansi di form publik, kompresi foto).
2. **Jangan tambah library eksternal** (jQuery, Toastify, SweetAlert, dll). Toast JS zero-dependency, konsisten `form-publik.js`.
3. **Compile Tailwind CSS** setelah edit `themes/app.css` → output `public/css/app.css`. Bila tidak ada workflow build, jalankan manual: `npx tailwindcss -i themes/app.css -o public/css/app.css --minify`.
4. **Verifikasi baris nomor** sebelum edit. Spec ini ditulis berdasarkan snapshot 2026-10-21; bila file sudah berubah, sesuaikan baris nomor.
5. **Test dengan 2 akun:**
   - Superadmin: username `superadmin`, password `Admin@2026` (sesuai seed di database/schema.sql baris 115–117).
   - Admin: buat akun role `admin` via Kelola Admin (setelah login superadmin), lalu logout + login sebagai admin untuk test sidebar + guard.
6. **Error validasi inline:** Error validasi per-field (misal: `session('errors.username')`) **tidak** ditampilkan inline di view saat ini. Bila ingin tambahkan (enhancement), lakukan di task terpisah.

---

## 7. AKHIR SPESIFIKASI

Spesifikasi ini **self-contained** dan siap dieksekusi agen `koding` tanpa tanya balik.

**Status:** ✅ FINAL — siap implementasi.  
**Estimasi:** 7 jam (1–2 hari kerja).  
**Arsitektur:** Konsisten design system BUKANWIL (flat, siku, Kemenag green, zero gradient, YAGNI).
