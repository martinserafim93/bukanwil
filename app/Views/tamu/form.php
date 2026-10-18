<?php
/**
 * Public guestbook form — SANTRI
 */
$this->extend('layouts/publik');
$this->section('title');
echo 'Form Buku Tamu';
$this->endSection();
$this->section('content');
?>

<section class="container-readable">
    <div class="card-surface p-6 md:p-8">
        <div class="mb-8 text-center">
            <h2 class="text-2xl md:text-3xl font-bold text-ink mb-2 font-serif">Buku Tamu Digital PTSP</h2>
            <p class="text-ink-muted">Silakan isi data kunjungan Anda dengan lengkap.</p>
        </div>

        <?php if (! empty($errors) && is_array($errors)): ?>
            <div class="mb-6 p-4 bg-danger-bg border border-danger text-danger text-sm" role="alert" aria-live="assertive">
                <p class="font-semibold mb-1">Mohon periksa kembali isian Anda:</p>
                <ul class="list-disc list-inside space-y-1">
                    <?php foreach ($errors as $field => $message): ?>
                        <li><?= esc(is_array($message) ? implode(' ', $message) : $message) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('tamu/submit') ?>" method="post" enctype="multipart/form-data" class="space-y-6" novalidate>
            <?= csrf_field() ?>

            <input type="text" name="_hp" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

            <div class="space-y-4">

                <div>
                    <label for="nama" class="form-label">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" value="<?= esc(old('nama')) ?>" class="form-field <?= session('errors.nama') ? 'form-field-error' : '' ?>" placeholder="Masukkan nama lengkap" autocomplete="name" required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="form-label block">Jenis Kelamin</span>
                        <div class="mt-2 flex flex-col gap-2">
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="jenis_kelamin" value="Laki-laki" <?= old('jenis_kelamin') === 'Laki-laki' ? 'checked' : '' ?> class="h-4 w-4 text-brand-500 border-border-strong" required>
                                <span class="text-sm text-ink">Laki-laki</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="jenis_kelamin" value="Perempuan" <?= old('jenis_kelamin') === 'Perempuan' ? 'checked' : '' ?> class="h-4 w-4 text-brand-500 border-border-strong" required>
                                <span class="text-sm text-ink">Perempuan</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <span class="form-label block">Asal Pengunjung</span>
                        <div class="mt-2 flex flex-col gap-2">
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="asal_pengunjung" value="perorangan" <?= old('asal_pengunjung') === 'perorangan' ? 'checked' : '' ?> class="h-4 w-4 text-brand-500 border-border-strong" required>
                                <span class="text-sm text-ink">Perorangan</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="asal_pengunjung" value="instansi" <?= old('asal_pengunjung') === 'instansi' ? 'checked' : '' ?> class="h-4 w-4 text-brand-500 border-border-strong" required>
                                <span class="text-sm text-ink">Instansi</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="asal_pengunjung" value="lembaga" <?= old('asal_pengunjung') === 'lembaga' ? 'checked' : '' ?> class="h-4 w-4 text-brand-500 border-border-strong" required>
                                <span class="text-sm text-ink">Lembaga</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div id="wrap-nama-instansi" style="display: none;">
                    <label for="nama_instansi" class="form-label">Nama Instansi</label>
                    <input type="text" id="nama_instansi" name="nama_instansi" value="<?= esc(old('nama_instansi')) ?>" class="form-field <?= session('errors.nama_instansi') ? 'form-field-error' : '' ?>" placeholder="Diisi jika asal instansi/lembaga">
                </div>

                <div>
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea id="alamat" name="alamat" rows="3" class="form-field" placeholder="Alamat lengkap" required><?= esc(old('alamat')) ?></textarea>
                </div>

                <div>
                    <label for="no_hp" class="form-label">No. HP / WhatsApp</label>
                    <input type="tel" id="no_hp" name="no_hp" value="<?= esc(old('no_hp')) ?>" class="form-field" placeholder="08xxxxxxxxxx" autocomplete="tel" required>
                </div>

                <div>
                    <label for="keperluan" class="form-label">Keperluan</label>
                    <textarea id="keperluan" name="keperluan" rows="3" class="form-field" placeholder="Tuliskan keperluan kunjungan" required><?= esc(old('keperluan')) ?></textarea>
                </div>

                <div>
                    <span class="form-label block">Lokasi</span>
                    <button type="button" id="btn-deteksi-lokasi" class="btn-secondary text-sm">Deteksi Lokasi Otomatis</button>
                    <p id="status-lokasi" class="form-hint mt-2 text-sm text-ink-muted" aria-live="polite"></p>
                    <input type="hidden" id="lat" name="lat" value="<?= esc(old('lat')) ?>">
                    <input type="hidden" id="lng" name="lng" value="<?= esc(old('lng')) ?>">
                    <div id="input-manual-lokasi" style="display: none;" class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="lat_manual" class="form-label">Latitude</label>
                            <input type="text" id="lat_manual" name="lat_manual" class="form-field" placeholder="-2.xxxxxx">
                        </div>
                        <div>
                            <label for="lng_manual" class="form-label">Longitude</label>
                            <input type="text" id="lng_manual" name="lng_manual" class="form-field" placeholder="117.xxxxxx">
                        </div>
                    </div>
                </div>

                <div>
                    <label for="foto" class="form-label">Foto Kunjungan</label>
                    <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" class="form-field file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                    <p class="form-hint">Maks 2 MB JPG/PNG/WebP (opsional)</p>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn-primary w-full md:w-auto">Kirim Data</button>
            </div>
        </form>
    </div>
</section>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script src="<?= base_url('js/form-publik.js') ?>"></script>
<?php $this->endSection(); ?>
