<?php

namespace App\Controllers;

use App\Models\TamuModel;

/**
 * Public guestbook form controller — SANTRI
 */
class Tamu extends BaseController
{
    /**
     * Display the public guestbook form.
     */
    public function index(): string
    {
        return view('tamu/form', ['pengaturan' => $this->pengaturan]);
    }

    /**
     * Process public guestbook form submission.
     */
    public function submit()
    {
        // 1. Honeypot: silently drop suspected bots.
        if ($this->request->getPost('_hp') !== '') {
            return redirect()->to('/tamu/sukses');
        }

        // 2. Rate limit: max 3 submissions per 10 minutes per session.
        $session = session();
        $maxAttempts = 3;
        $windowSeconds = 600;
        $count = (int) $session->get('form_submit_count');
        $time  = (int) $session->get('form_submit_time');

        // Reset once the window has expired — persist immediately.
        if ($time > 0 && (time() - $time) >= $windowSeconds) {
            $session->set('form_submit_count', 0);
            $session->set('form_submit_time', 0);
            $count = 0;
            $time  = 0;
        }

        // Block if still within the window and limit reached.
        if ($count >= $maxAttempts && $time > 0) {
            return redirect()->to('/tamu/sukses'); // silent drop
        }

        // 3. Validation rules
        $rules = [
            'nama'            => 'required|max_length[255]',
            'jenis_kelamin'   => 'required|in_list[Laki-laki,Perempuan]',
            'asal_pengunjung' => 'required|in_list[perorangan,instansi,lembaga]',
            'nama_instansi'   => 'permit_empty|max_length[255]',
            'alamat'          => 'required',
            'no_hp'           => 'required|regex_match[/^(0|62|\+62)[0-9]{8,13}$/]',
            'keperluan'       => 'required',
            'lat'             => 'permit_empty|decimal',
            'lng'             => 'permit_empty|decimal',
            'foto'            => [
                'rules' => 'permit_empty|max_size[foto,2048]|mime_in[foto,image/jpeg,image/png,image/webp]|ext_in[foto,jpg,jpeg,png,webp]',
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // 4. Manual check: nama_instansi required when asal is instansi or lembaga.
        $asal = $this->request->getPost('asal_pengunjung');
        $namaInstansi = (string) $this->request->getPost('nama_instansi');

        if (in_array($asal, ['instansi', 'lembaga'], true) && trim($namaInstansi) === '') {
            $errors = $this->validator?->getErrors() ?? [];
            $errors['nama_instansi'] = 'Nama instansi wajib diisi bila asal pengunjung adalah Instansi atau Lembaga.';

            return redirect()->back()->withInput()->with('errors', $errors);
        }

        // 5. Process optional photo upload.
        $fotoPath = $this->prosesUploadFoto();

        // 6. Prepare data and insert.
        $lat = $this->request->getPost('lat');
        $lng = $this->request->getPost('lng');

        $data = [
            'nama'            => $this->request->getPost('nama'),
            'jenis_kelamin'   => $this->request->getPost('jenis_kelamin'),
            'asal_pengunjung' => $asal,
            'nama_instansi'   => in_array($asal, ['instansi', 'lembaga'], true) ? $namaInstansi : null,
            'alamat'          => $this->request->getPost('alamat'),
            'no_hp'           => $this->request->getPost('no_hp'),
            'keperluan'       => $this->request->getPost('keperluan'),
            'lat'             => is_numeric($lat) ? (float) $lat : null,
            'lng'             => is_numeric($lng) ? (float) $lng : null,
            'foto'            => $fotoPath,
        ];

        $tamuModel = new TamuModel();
        $tamuModel->insert($data);

        // 7. Increment rate limit counter; start window on first submit of a window.
        if ($time === 0) {
            $session->set('form_submit_time', time());
        }
        $session->set('form_submit_count', $count + 1);

        // 8. Redirect to success page.
        return redirect()->to('/tamu/sukses');
    }

    /**
     * Display success confirmation page.
     */
    public function sukses(): string
    {
        return view('tamu/sukses', ['pengaturan' => $this->pengaturan]);
    }

    /**
     * Handle optional photo upload with MIME whitelist and random filename.
     *
     * @return string|null Relative path (uploads/foto/<name>) or null.
     */
    private function prosesUploadFoto(): ?string
    {
        $file = $this->request->getFile('foto');

        if (! $file || ! $file->isValid() || ! $file->isUploaded()) {
            return null;
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getTempName());

        if (! in_array($mime, $allowedMimeTypes, true)) {
            return null;
        }

        $extMap = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];
        $ext = $extMap[$mime];
        $newName = bin2hex(random_bytes(16)) . '.' . $ext;

        $uploadDir = FCPATH . 'uploads/foto/';
        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $file->move($uploadDir, $newName);

        return 'uploads/foto/' . $newName;
    }
}
