# SANTRI Design System

Dokumen ini merangkum arah desain, token, dan aturan visual yang digunakan pada Fase 1 pengembangan SANTRI.

---

## 1. Arah desain

SANTRI adalah aplikasi internal sekaligus publik yang mewakili instansi pemerintah (Kementerian Agama). Oleh karena itu pendekatan visualnya:

- **Tegas & bersih**: tampilan dipercaya, tidak norak, mudah diakses masyarakat umum.
- **Institusional namun hangat**: brand Kemenag hijau + emas dipadukan dengan netral hangat supaya tidak terlalu kaku.
- **Mobile-first**: form publik diprioritaskan untuk layar kecil.
- **Anti-slop**: diterapkan sejak awal agar UI tidak terlihat generik/template-ly.

---

## 2. Token warna

| Nama                 | Hex       | Peran                                    |
| -------------------- | --------- | ---------------------------------------- |
| `--color-brand-500`  | `#006B2E` | Primary / brand Kemenag                  |
| `--color-brand-600`  | `#005A27` | Hover primary                              |
| `--color-brand-50`   | `#E6F5EC` | Background active/soft nav               |
| `--color-accent-300` | `#E5A400` | Accent emas Kemenag (strip, highlight) |
| `--color-ground`     | `#F8F7F4` | Background halaman                       |
| `--color-surface`    | `#FFFFFF` | Kartu, header, sidebar                   |
| `--color-surface-raised` | `#FAFAF8` | Hover/raised surface                     |
| `--color-border`     | `#E5E3DD` | Border halus                             |
| `--color-border-strong` | `#D9D6CE` | Border form/input                        |
| `--color-ink`        | `#1C1C1A` | Teks utama                               |
| `--color-ink-muted`  | `#6B6A65` | Teks sekunder                            |
| `--color-ink-subtle` | `#9B9A95` | Placeholder/hint                         |
| `--color-danger`     | `#B42318` | Error / logout                           |
| `--color-danger-bg`  | `#FEF2F1` | Background error hover                   |
| `--color-success`    | `#006B2E` | Sukses (sama dengan brand)               |

---

## 3. Tipografi

| Elemen          | Font                     | Catatan                        |
| --------------- | ------------------------ | ------------------------------ |
| Body / UI       | Plus Jakarta Sans        | Semua teks body, label, tombol, dan elemen UI |
| Judul halaman utama / h1 | Playfair Display | Hanya untuk judul halaman utama (misal `Buku Tamu Digital PTSP`, `Login Administrator`) |
| Fallback        | system-ui, sans-serif   | Untuk performa & offline       |

- **Plus Jakarta Sans**: digunakan untuk semua body text, label form, tombol, navigasi, dan komponen UI.
- **Playfair Display**: hanya untuk judul halaman utama / `h1`, bukan untuk subjudul atau elemen UI.
- Ukuran judul halaman publik: `text-2xl md:text-3xl`.
- Body berjalan di 16 px (`text-base`).
- Label form: `text-sm font-semibold`.

---

## 4. Spacing & bentuk

| Elemen                | Aturan                              |
| --------------------- | ----------------------------------- |
| Border radius         | `0` (siku) atau `999px` (pil penuh) |
| Tombol primary        | `rounded-full`, flat solid          |
| Kartu                 | `0` (siku)                          |
| Field form            | `0` (siku), border tipis            |
| Container readable    | `max-width: 680px`                  |
| Padding halaman publik | `py-8 md:py-12`                     |
| Padding card          | `p-6 md:p-8`                        |

---

## 5. Aturan anti-slop yang berlaku

Diadaptasi dari skill `design-taste-frontend`, `minimalist-ui`, dan `high-end-visual-design`, disesuaikan dengan konteks instansi:

- **Tanpa gradient** pada elemen diam, termasuk tombol, header, dan kartu.
- **Tanpa box-shadow** pada elemen diam. Surface dipisahkan dengan border atau perubahan tone.
- **Bidang warna SOLID** saja.
- **Satu accent utama**: emas `#E5A400` digunakan hemat (strip brand saja).
- **Tombol flat solid**: background `#006B2E`, hover `#005A27`, tanpa shadow/gradient.
- **Border radius**: pil penuh untuk tombol; siku (0) untuk kartu dan field form.
- **Pengecualian aksesibilitas**: field form diperbolehkan border tipis + focus ring (satu-satunya pengecualian) untuk membantu navigasi keyboard dan memenuhi WCAG focus indicator.
- **Judul besar maksimal 2 baris** (sudah dicek di copy).
- **No emoji**, **no generic icon cards**, **no decorative gradients/blobs**.

---

## 6. Aksesibilitas & kontras

### Logo / identitas institusi

- Logo/ikon "Km" pada layout publik dan admin bersifat dekoratif dan diberi `aria-hidden="true"`. Identitas institusi (publik: "Kementerian Agama RI / Kanwil Kemenag"; admin: "SANTRI / Admin Panel") dibacakan oleh screen reader melalui teks heading di sampingnya, sehingga tidak perlu `aria-label` pada lingkaran logo.

### Kontras warna utama

- Teks utama (body) menggunakan `--color-ink: #1C1C1A` pada latar `--color-surface: #FFFFFF`. Rasio kontras: ~15.8:1 (WCAG AAA).
- Warna brand `--color-brand-500: #006B2E` sebagai teks/ikon pada latar putih `#FFFFFF`: rasio kontrast dihitung sekitar **6.7:1**, memenuhi **WCAG AA** (threshold 4.5:1) untuk teks normal.
- Oleh karena itu brand-500 cukup aman untuk tombol primary (teks putih pada bg brand-500: 6.7:1) dan ikon/logo.

---

## 7. File terkait

- `themes/app.css` — sumber Tailwind v4 (CSS-first `@theme`).
- `public/css/app.css` — hasil build (output).
- `app/Views/layouts/publik.php`
- `app/Views/layouts/admin.php`
- `app/Views/tamu/form.php`
- `app/Views/admin/login.php`
- `docs/preview.html` — self-contained preview (CSS inline) untuk screenshot.

---

## 8. Build

```bash
cd /opt/data/workspaces/santri && npm run build:css
```

Saat ini script build menggunakan binary `tailwindcss` langsung ( `./node_modules/.bin/tailwindcss ...`) karena `@tailwindcss/cli` tidak tereksekusi di environment ini. Perintah `npm run build:css` sudah disesuaikan.
