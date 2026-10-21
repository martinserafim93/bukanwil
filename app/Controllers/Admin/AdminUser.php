<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdminModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Exceptions\PageNotFoundException;

class AdminUser extends BaseController
{
    private const HASH_COST = 12;

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
     * Helper: count how many superadmin accounts exist.
     */
    private function countSuperadmin(): int
    {
        return (new AdminModel())
            ->where('role', 'superadmin')
            ->countAllResults();
    }

    /**
     * Display the admin user list.
     */
    public function index(): string|ResponseInterface
    {
        $redirect = $this->requireSuperadmin();
        if ($redirect !== null) {
            return $redirect;
        }

        $adminModel = new AdminModel();
        $admins     = $adminModel->orderBy('id', 'ASC')->findAll();
        $jumlahSuperadmin = $this->countSuperadmin();

        return view('admin/admin-user/index', [
            'pengaturan'       => $this->pengaturan,
            'unreadCount'      => $this->unreadCount,
            'admins'           => $admins,
            'jumlahSuperadmin' => $jumlahSuperadmin,
        ]);
    }

    /**
     * Show the create form.
     */
    public function buat(): string|ResponseInterface
    {
        $redirect = $this->requireSuperadmin();
        if ($redirect !== null) {
            return $redirect;
        }

        return view('admin/admin-user/form', [
            'pengaturan' => $this->pengaturan,
            'unreadCount' => $this->unreadCount,
            'admin'      => null,
            'actionUrl'  => site_url('admin/admin-user/buat'),
            'judul'      => 'Tambah Admin',
        ]);
    }

    /**
     * Store a new admin user.
     */
    public function simpan(): ResponseInterface
    {
        $redirect = $this->requireSuperadmin();
        if ($redirect !== null) {
            return $redirect;
        }

        $role = (string) $this->request->getPost('role');

        // Escalation guard: non-superadmin cannot create superadmin accounts.
        if (session('role') !== 'superadmin' && $role === 'superadmin') {
            return redirect()->to('/admin')
                ->with('error', 'Anda tidak memiliki izin untuk membuat superadmin.');
        }

        $rules = [
            'nama'             => 'required|max_length[255]',
            'username'         => 'required|max_length[100]|is_unique[admin.username]',
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'matches[password]',
            'role'             => 'required|in_list[superadmin,admin]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nama'     => (string) $this->request->getPost('nama'),
            'username' => (string) $this->request->getPost('username'),
            'password' => password_hash((string) $this->request->getPost('password'), PASSWORD_BCRYPT, ['cost' => self::HASH_COST]),
            'role'     => $role,
        ];

        (new AdminModel())->insert($data);

        return redirect()->to('admin/admin-user')->with('sukses', 'Admin berhasil ditambahkan.');
    }

    /**
     * Show the edit form for the given admin.
     */
    public function edit(int $id): string|ResponseInterface
    {
        $redirect = $this->requireSuperadmin();
        if ($redirect !== null) {
            return $redirect;
        }

        $admin = (new AdminModel())->find($id);

        if ($admin === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('admin/admin-user/form', [
            'pengaturan' => $this->pengaturan,
            'unreadCount' => $this->unreadCount,
            'admin'      => $admin,
            'actionUrl'  => site_url('admin/admin-user/' . $id . '/edit'),
            'judul'      => 'Edit Admin',
        ]);
    }

    /**
     * Update the given admin.
     */
    public function update(int $id): ResponseInterface
    {
        $redirect = $this->requireSuperadmin();
        if ($redirect !== null) {
            return $redirect;
        }

        $adminModel = new AdminModel();
        $admin      = $adminModel->find($id);

        if ($admin === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $role = (string) $this->request->getPost('role');

        // Escalation guard: non-superadmin cannot edit superadmin accounts
        // and cannot assign the superadmin role.
        if (session('role') !== 'superadmin') {
            if ($admin['role'] === 'superadmin' || $role === 'superadmin') {
                return redirect()->to('/admin')
                    ->with('error', 'Anda tidak memiliki izin untuk mengubah akun superadmin.');
            }
        }

        $rules = [
            'nama'             => 'required|max_length[255]',
            'username'         => "required|max_length[100]|is_unique[admin.username,id,{$id}]",
            'password'         => 'permit_empty|min_length[8]',
            'password_confirm' => 'matches[password]',
            'role'             => 'required|in_list[superadmin,admin]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nama'     => (string) $this->request->getPost('nama'),
            'username' => (string) $this->request->getPost('username'),
            'role'     => $role,
        ];

        $password = (string) $this->request->getPost('password');
        if ($password !== '') {
            $adminModel->updatePassword($id, password_hash($password, PASSWORD_BCRYPT, ['cost' => self::HASH_COST]));
        }

        $adminModel->update($id, $data);

        return redirect()->to('admin/admin-user')->with('sukses', 'Admin berhasil diperbarui.');
    }

    /**
     * Show the delete confirmation page.
     */
    public function konfirmasi(int $id): ResponseInterface|string
    {
        $redirect = $this->requireSuperadmin();
        if ($redirect !== null) {
            return $redirect;
        }

        $admin = (new AdminModel())->find($id);

        if ($admin === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        // Self-delete guard.
        if ((int) session('admin_id') === (int) $id) {
            return redirect()->to('admin/admin-user')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Last superadmin guard.
        if ($admin['role'] === 'superadmin' && $this->countSuperadmin() <= 1) {
            return redirect()->to('admin/admin-user')
                ->with('error', 'Tidak dapat menghapus superadmin terakhir.');
        }

        return view('admin/admin-user/hapus', [
            'pengaturan' => $this->pengaturan,
            'unreadCount' => $this->unreadCount,
            'admin'      => $admin,
        ]);
    }

    /**
     * Delete the given admin.
     */
    public function hapus(int $id): ResponseInterface
    {
        $redirect = $this->requireSuperadmin();
        if ($redirect !== null) {
            return $redirect;
        }

        $adminModel = new AdminModel();
        $admin      = $adminModel->find($id);

        if ($admin === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        // Self-delete guard.
        if ((int) session('admin_id') === (int) $id) {
            return redirect()->to('admin/admin-user')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Last superadmin guard.
        if ($admin['role'] === 'superadmin' && $this->countSuperadmin() <= 1) {
            return redirect()->to('admin/admin-user')
                ->with('error', 'Tidak dapat menghapus superadmin terakhir.');
        }

        $adminModel->delete($id);

        return redirect()->to('admin/admin-user')->with('sukses', 'Admin berhasil dihapus.');
    }
}
