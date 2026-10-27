<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdminModel;
use App\Models\TamuModel;

class Dashboard extends BaseController
{
    public function index(): string
    {
        $statistik = (new TamuModel())->getStatistik();

        // Cek apakah akun superadmin masih memakai kata sandi awal (Admin@2026)
        $defaultPasswordWarning = false;
        $superadmin = (new AdminModel())->findByUsername('superadmin');
        if ($superadmin !== null && password_verify('Admin@2026', (string) ($superadmin['password'] ?? ''))) {
            $defaultPasswordWarning = true;
        }

        return view('admin/dashboard', [
            'pengaturan'             => $this->pengaturan,
            'unreadCount'            => $this->unreadCount,
            'statistik'              => $statistik,
            'defaultPasswordWarning' => $defaultPasswordWarning,
            'superadminId'           => $superadmin['id'] ?? null,
        ]);
    }
}
