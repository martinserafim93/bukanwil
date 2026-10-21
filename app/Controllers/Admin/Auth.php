<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdminModel;

class Auth extends BaseController
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    /**
     * Show the admin login form.
     */
    public function login()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/admin');
        }

        return view('admin/login', [
            'pengaturan' => $this->pengaturan,
        ]);
    }

    /**
     * Process admin login attempt with rate limiting.
     */
    public function doLogin()
    {
        $session = session();
        $now     = time();

        // Rate limit check
        $attempts  = (int) $session->get('login_attempts');
        $firstFail = (int) $session->get('login_attempt_time');

        // Reset window bila sudah lewat 15 menit — persist segera.
        if ($firstFail > 0 && ($now - $firstFail) >= (self::LOCKOUT_MINUTES * 60)) {
            $session->set('login_attempts', 0);
            $session->set('login_attempt_time', 0);
            $attempts  = 0;
            $firstFail = 0;
        }

        $elapsed = $now - $firstFail;

        if ($attempts >= self::MAX_ATTEMPTS && $elapsed < (self::LOCKOUT_MINUTES * 60)) {
            $remaining = ceil((self::LOCKOUT_MINUTES * 60 - $elapsed) / 60);

            return redirect()->back()->with('error', "Terlalu banyak percobaan. Coba lagi dalam {$remaining} menit.")->withInput();
        }

        $rules = [
            'username' => 'required|max_length[100]',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Username dan password wajib diisi.');
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $admin = (new AdminModel())->findByUsername($username);

        if ($admin === null || ! password_verify($password, $admin['password'])) {
            if ($attempts === 0) {
                $session->set('login_attempt_time', $now);
            }
            $session->set('login_attempts', $attempts + 1);

            return redirect()->back()->withInput()->with('error', 'Username atau password salah.');
        }

        // Successful login
        $session->regenerate(true);
        $session->set([
            'logged_in'  => true,
            'admin_id'   => $admin['id'],
            'admin_nama' => $admin['nama'],
            'role'       => $admin['role'],
        ]);
        $session->remove('login_attempts');
        $session->remove('login_attempt_time');

        return redirect()->to('/admin');
    }

    /**
     * Destroy the admin session and redirect to login.
     */
    public function logout()
    {
        $session = session();
        $session->remove([
            'logged_in',
            'admin_id',
            'admin_nama',
            'role',
            'login_attempts',
            'login_attempt_time',
        ]);

        return redirect()->to('/admin/login')->with('sukses', 'Anda telah berhasil logout.');
    }
}
