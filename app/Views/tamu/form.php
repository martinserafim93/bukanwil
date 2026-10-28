<?php
/**
 * Public guestbook form - BUKANWIL
 */
$this->extend('layouts/publik');
$this->section('title');
echo 'Form Buku Tamu';
$this->endSection();
$this->section('content');
?>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-stretch">

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

    <!-- Kolom Kanan: Lembar Kerja Formulir -->
    <section class="lg:col-span-7 card-surface p-6 sm:p-8 lg:p-10" aria-labelledby="form-heading">
        <div class="mb-6">
            <h2 id="form-heading" class="text-2xl font-serif font-bold text-ink mb-1">Formulir Kunjungan</h2>
            <p class="text-sm text-ink-muted">Seluruh kolom bertanda bintang (*) wajib diisi sebelum mengirim data.</p>
        </div>

        <?php $errs = session('errors'); ?>
        <?php if (! empty($errs) && is_array($errs)): ?>
            <div class="mb-6 p-4 bg-danger-bg border border-danger text-danger text-sm" role="alert" aria-live="assertive">
                <p class="font-semibold mb-1">Mohon periksa kembali isian Anda:</p>
                <ul class="list-disc list-inside space-y-1">
                    <?php foreach ($errs as $field => $message): ?>
                        <li><?= esc(is_array($message) ? implode(' ', $message) : $message) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('tamu/submit') ?>" method="post" enctype="multipart/form-data" class="space-y-6" novalidate>
            <?= csrf_field() ?>

            <input type="text" name="_hp" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

            <div class="space-y-5">
                <div>
                    <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="nama"
                        name="nama"
                        value="<?= esc(old('nama')) ?>"
                        class="form-field <?= session('errors.nama') ? 'form-field-error' : '' ?>"
                        placeholder="Masukkan nama lengkap sesuai identitas"
                        autocomplete="name"
                        required
                    >
                </div>

                <fieldset>
                    <legend class="form-label">Jenis Kelamin <span class="text-danger">*</span></legend>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-1">
                        <label class="choice-card">
                            <input
                                type="radio"
                                name="jenis_kelamin"
                                value="Laki-laki"
                                <?= old('jenis_kelamin') === 'Laki-laki' ? 'checked' : '' ?>
                                class="h-4 w-4 accent-brand-500"
                                required
                            >
                            <span>Laki-laki</span>
                        </label>
                        <label class="choice-card">
                            <input
                                type="radio"
                                name="jenis_kelamin"
                                value="Perempuan"
                                <?= old('jenis_kelamin') === 'Perempuan' ? 'checked' : '' ?>
                                class="h-4 w-4 accent-brand-500"
                                required
                            >
                            <span>Perempuan</span>
                        </label>
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="form-label">Asal Pengunjung <span class="text-danger">*</span></legend>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-1">
                        <label class="choice-card">
                            <input
                                type="radio"
                                name="asal_pengunjung"
                                value="perorangan"
                                <?= old('asal_pengunjung') === 'perorangan' ? 'checked' : '' ?>
                                class="h-4 w-4 accent-brand-500"
                                required
                            >
                            <span>Perorangan</span>
                        </label>
                        <label class="choice-card">
                            <input
                                type="radio"
                                name="asal_pengunjung"
                                value="instansi"
                                <?= old('asal_pengunjung') === 'instansi' ? 'checked' : '' ?>
                                class="h-4 w-4 accent-brand-500"
                                required
                            >
                            <span>Instansi</span>
                        </label>
                        <label class="choice-card">
                            <input
                                type="radio"
                                name="asal_pengunjung"
                                value="lembaga"
                                <?= old('asal_pengunjung') === 'lembaga' ? 'checked' : '' ?>
                                class="h-4 w-4 accent-brand-500"
                                required
                            >
                            <span>Lembaga</span>
                        </label>
                    </div>
                </fieldset>

                <div id="wrap-nama-instansi" style="display: none;">
                    <label for="nama_instansi" class="form-label">Nama Instansi / Lembaga <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="nama_instansi"
                        name="nama_instansi"
                        value="<?= esc(old('nama_instansi')) ?>"
                        class="form-field <?= session('errors.nama_instansi') ? 'form-field-error' : '' ?>"
                        placeholder="Masukkan nama instansi atau lembaga asal"
                    >
                </div>

                <div>
                    <label for="no_hp" class="form-label">No. HP / WhatsApp <span class="text-danger">*</span></label>
                    <input
                        type="tel"
                        id="no_hp"
                        name="no_hp"
                        value="<?= esc(old('no_hp')) ?>"
                        class="form-field <?= session('errors.no_hp') ? 'form-field-error' : '' ?>"
                        placeholder="08xxxxxxxxxx"
                        autocomplete="tel"
                        required
                    >
                </div>

                <div>
                    <label for="alamat" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                    <textarea
                        id="alamat"
                        name="alamat"
                        rows="3"
                        class="form-field <?= session('errors.alamat') ? 'form-field-error' : '' ?>"
                        placeholder="Alamat tempat tinggal atau instansi"
                        required
                    ><?= esc(old('alamat')) ?></textarea>
                </div>

                <div>
                    <label for="keperluan" class="form-label">Keperluan Kunjungan <span class="text-danger">*</span></label>
                    <textarea
                        id="keperluan"
                        name="keperluan"
                        rows="3"
                        class="form-field <?= session('errors.keperluan') ? 'form-field-error' : '' ?>"
                        placeholder="Tuliskan maksud atau keperluan kedatangan Anda"
                        required
                    ><?= esc(old('keperluan')) ?></textarea>
                </div>

                <div>
                    <label for="foto" class="form-label">Foto Kunjungan <span class="text-ink-muted font-normal">(Opsional)</span></label>
                    <div class="upload-dropzone">
                        <input
                            type="file"
                            id="foto"
                            name="foto"
                            accept="image/jpeg,image/png,image/webp"
                            aria-describedby="foto-hint foto-error"
                            class="block w-full text-sm text-ink file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer"
                        >
                        <p id="foto-hint" class="form-hint mt-2">Format JPG, PNG, atau WebP. Maksimal 10 MB (otomatis dikompres di bawah 1 MB saat dikirim).</p>
                        <p id="foto-filename" class="text-sm font-semibold text-brand-700 mt-2 hidden"></p>
                        <p id="foto-error" class="text-sm text-danger mt-1 hidden" role="alert"></p>
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn-primary w-full sm:w-auto min-w-52">Kirim Data Kunjungan</button>
            </div>
        </form>
    </section>

</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script src="<?= base_url('js/form-publik.js') ?>?v=<?= filemtime(FCPATH . 'js/form-publik.js') ?>"></script>
<?php $this->endSection(); ?>
