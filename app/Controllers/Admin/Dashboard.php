<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TamuModel;

class Dashboard extends BaseController
{
    public function index(): string
    {
        $statistik = (new TamuModel())->getStatistik();

        return view('admin/dashboard', [
            'pengaturan' => $this->pengaturan,
            'unreadCount' => $this->unreadCount,
            'statistik' => $statistik,
        ]);
    }
}
