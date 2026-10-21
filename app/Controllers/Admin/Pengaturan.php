<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PengaturanModel;
use CodeIgniter\HTTP\ResponseInterface;

class Pengaturan extends BaseController
{
    /**
     * Require the current admin to be a superadmin.
     */
    private function requireSuperadmin(): ?ResponseInterface
    {
        if (session('role') !== 'superadmin') {
            return redirect()->to('/admin')->with('error', 'Akses ditolak.');
        }

        return null;
    }

    /**
     * Display the settings form.
     */
    public function index(): ResponseInterface|string
    {
        $redirect = $this->requireSuperadmin();
        if ($redirect !== null) {
            return $redirect;
        }

        return view('admin/pengaturan/index', [
            'pengaturan'  => $this->pengaturan,
            'unreadCount' => $this->unreadCount,
        ]);
    }

    /**
     * Save settings.
     */
    public function simpan(): ResponseInterface
    {
        $redirect = $this->requireSuperadmin();
        if ($redirect !== null) {
            return $redirect;
        }

        $rules = [
            'judul_aplikasi'     => 'required|max_length[255]',
            'deskripsi_aplikasi' => 'required|max_length[255]',
            'nama_instansi_ptsp' => 'required|max_length[255]',
            'teks_header'        => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'judul_aplikasi'     => (string) $this->request->getPost('judul_aplikasi'),
            'deskripsi_aplikasi' => (string) $this->request->getPost('deskripsi_aplikasi'),
            'nama_instansi_ptsp' => (string) $this->request->getPost('nama_instansi_ptsp'),
            'teks_header'        => (string) $this->request->getPost('teks_header'),
        ];

        (new PengaturanModel())->setMany($data);

        return redirect()->to('admin/pengaturan')->with('sukses', 'Pengaturan berhasil disimpan.');
    }
}
