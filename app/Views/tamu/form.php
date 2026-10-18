<?php
/**
 * Shell halaman form publik — SANTRI
 * Belum ada logika bisnis / controller.
 */
$this->extend('layouts/publik');
$this->section('title', 'Form Buku Tamu');
$this->section('content');
?>

<section class="container-readable">
    <div class="card-surface p-6 md:p-8">
        <div class="mb-8 text-center">
            <h2 class="text-2xl md:text-3xl font-bold text-ink mb-2 font-serif">Buku Tamu Digital PTSP</h2>
            <p class="text-ink-muted">Silakan isi data kunjungan Anda dengan lengkap.</p>
        </div>

        <form action="#" method="post" enctype="multipart/form-data" class="space-y-6">
            <div class="space-y-4">
                <div>
                    <label for="nama" class="form-label">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" class="form-field" placeholder="Masukkan nama lengkap" autocomplete="name">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                        <select id="jenis_kelamin" name="jenis_kelamin" class="form-field">
                            <option value="">Pilih</option>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label for="asal_pengunjung" class="form-label">Asal Pengunjung</label>
                        <select id="asal_pengunjung" name="asal_pengunjung" class="form-field">
                            <option value="">Pilih</option>
                            <option value="perorangan">Perorangan</option>
                            <option value="instansi">Instansi</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="nama_instansi" class="form-label">Nama Instansi</label>
                    <input type="text" id="nama_instansi" name="nama_instansi" class="form-field" placeholder="Diisi jika asal instansi">
                </div>

                <div>
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea id="alamat" name="alamat" rows="3" class="form-field" placeholder="Alamat lengkap"></textarea>
                </div>

                <div>
                    <label for="no_hp" class="form-label">No. HP / WhatsApp</label>
                    <input type="tel" id="no_hp" name="no_hp" class="form-field" placeholder="08xxxxxxxxxx">
                </div>

                <div>
                    <label for="keperluan" class="form-label">Keperluan</label>
                    <textarea id="keperluan" name="keperluan" rows="3" class="form-field" placeholder="Tuliskan keperluan kunjungan"></textarea>
                </div>

                <div>
                    <label for="foto" class="form-label">Foto Kunjungan</label>
                    <input type="file" id="foto" name="foto" accept="image/*" class="form-field file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                    <p class="form-hint">Maksimal 2 MB, format JPG/PNG/WebP.</p>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn-primary w-full md:w-auto">Kirim Data</button>
            </div>
        </form>
    </div>
</section>

<?php $this->endSection(); ?>
