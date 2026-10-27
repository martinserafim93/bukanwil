<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TamuModel;
use CodeIgniter\HTTP\ResponseInterface;

class TamuAdmin extends BaseController
{
    /**
     * Render query string into a filter array used by TamuModel::applyFilter().
     *
     * @return array<string, mixed>
     */
    private function buildFilter(): array
    {
        $get = $this->request->getGet();

        return [
            'tab'             => $get['tab'] ?? 'aktif',
            'tahun'           => $get['tahun'] ?? '',
            'q'               => $get['q'] ?? '',
            'tanggal_dari'    => $get['tanggal_dari'] ?? '',
            'tanggal_sampai'  => $get['tanggal_sampai'] ?? '',
            'jenis_kelamin'   => $get['jenis_kelamin'] ?? '',
            'asal'            => $get['asal'] ?? '',
            'keperluan'       => $get['keperluan'] ?? '',
            'sort'            => $get['sort'] ?? '',
            'order'           => $get['order'] ?? '',
        ];
    }

    /**
     * Guard access to superadmin-only actions.
     */
    private function requireSuperadmin(): ?ResponseInterface
    {
        if (session('role') !== 'superadmin') {
            return redirect()->to('/admin/tamu')->with('error', 'Akses ditolak: Hanya Super Administrator yang diizinkan melakukan tindakan ini.');
        }

        return null;
    }

    /**
     * Display the guest list with filtering and pagination.
     */
    public function index(): string
    {
        $filter = $this->buildFilter();
        $model  = new TamuModel();

        // Non-superadmin cannot view trash tab
        if ($filter['tab'] === 'sampah' && session('role') !== 'superadmin') {
            $filter['tab'] = 'aktif';
        }

        $result = $model->getPaginasi($filter, 20);

        return view('admin/tamu/index', [
            'pengaturan'   => $this->pengaturan,
            'unreadCount'  => $this->unreadCount,
            'tamu'         => $result['data'],
            'pager'        => $result['pager'],
            'total'        => $result['pager']->getTotal(),
            'filter_aktif' => $filter,
            'tab_aktif'    => $filter['tab'],
            'count_aktif'  => $model->countAktif(),
            'count_arsip'  => $model->countArsip(),
            'count_sampah' => $model->countSampah(),
        ]);
    }

    /**
     * Export all filtered tamu records as a UTF-8 CSV.
     */
    public function exportCsv(): ResponseInterface
    {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filter = $this->buildFilter();
        $rows   = (new TamuModel())->getSemuaDenganFilter($filter);

        $handle = fopen('php://temp', 'r+');

        fwrite($handle, "\xEF\xBB\xBF");

        $headers = [
            'id',
            'nama',
            'jenis_kelamin',
            'asal_pengunjung',
            'nama_instansi',
            'alamat',
            'no_hp',
            'keperluan',
            'foto',
            'is_archived',
            'created_at',
            'deleted_at',
        ];
        fputcsv($handle, $headers);

        foreach ($rows as $row) {
            $csvRow = [];
            foreach ($headers as $col) {
                $csvRow[] = $row[$col] ?? '';
            }
            fputcsv($handle, $csvRow);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $tabSuffix = $filter['tab'] !== 'aktif' ? '_' . $filter['tab'] : '';
        $filename  = 'tamu_export' . $tabSuffix . '_' . date('Ymd_His') . '.csv';

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csv);
    }

    /**
     * Show detail for a single tamu record.
     */
    public function detail(int $id): string
    {
        $tamuModel = new TamuModel();
        // Allow loading detail even if soft-deleted (so Superadmin in trash can inspect)
        $tamu = $tamuModel->withDeleted()->find($id);

        if ($tamu === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (empty($tamu['deleted_at'])) {
            $tamuModel->tandaiBaca($id);
            $tamu['is_read'] = 1;
        }

        return view('admin/tamu/detail', [
            'pengaturan'  => $this->pengaturan,
            'unreadCount' => $this->unreadCount,
            'tamu'        => $tamu,
        ]);
    }

    /**
     * Mark a single tamu record as read.
     */
    public function tandaiBaca(int $id): ResponseInterface
    {
        (new TamuModel())->tandaiBaca($id);

        if ($this->request->isAJAX() || strpos($this->request->getHeaderLine('Accept'), 'application/json') !== false) {
            return $this->response->setJSON(['success' => true]);
        }

        return redirect()->back();
    }

    /**
     * Mark all unread tamu records as read.
     */
    public function bacaSemua(): ResponseInterface
    {
        (new TamuModel())->tandaiBacaSemua();

        if ($this->request->isAJAX() || strpos($this->request->getHeaderLine('Accept'), 'application/json') !== false) {
            return $this->response->setJSON(['success' => true]);
        }

        return redirect()->to('/admin/tamu');
    }

    /**
     * Soft delete a tamu record (move to trash). Superadmin only.
     */
    public function hapus(int $id): ResponseInterface
    {
        if ($redirect = $this->requireSuperadmin()) {
            return $redirect;
        }

        $model = new TamuModel();
        $tamu  = $model->find($id);
        if ($tamu === null) {
            return redirect()->to('/admin/tamu')->with('error', 'Data tamu tidak ditemukan.');
        }

        $model->delete($id);

        return redirect()->back()->with('success', 'Data tamu berhasil dipindahkan ke Tong Sampah.');
    }

    /**
     * Restore a soft-deleted tamu record back to active. Superadmin only.
     */
    public function pulihkan(int $id): ResponseInterface
    {
        if ($redirect = $this->requireSuperadmin()) {
            return $redirect;
        }

        $model = new TamuModel();
        $model->pulihkan($id);

        return redirect()->back()->with('success', 'Data tamu berhasil dipulihkan ke daftar aktif.');
    }

    /**
     * Permanently delete a tamu record and its physical photo. Superadmin only.
     */
    public function hapusPermanen(int $id): ResponseInterface
    {
        if ($redirect = $this->requireSuperadmin()) {
            return $redirect;
        }

        $model   = new TamuModel();
        $success = $model->hapusPermanen($id);

        if ($success) {
            return redirect()->back()->with('success', 'Data tamu dan berkas foto berhasil dihapus permanen.');
        }

        return redirect()->back()->with('error', 'Gagal menghapus data tamu.');
    }

    /**
     * Move a tamu record to archive. Superadmin only.
     */
    public function arsipkan(int $id): ResponseInterface
    {
        if ($redirect = $this->requireSuperadmin()) {
            return $redirect;
        }

        $model = new TamuModel();
        $model->arsipkan($id);

        return redirect()->back()->with('success', 'Data tamu berhasil diarsipkan.');
    }

    /**
     * Restore an archived tamu record back to active. Superadmin only.
     */
    public function batalArsip(int $id): ResponseInterface
    {
        if ($redirect = $this->requireSuperadmin()) {
            return $redirect;
        }

        $model = new TamuModel();
        $model->batalArsip($id);

        return redirect()->back()->with('success', 'Data tamu berhasil dikembalikan ke daftar aktif.');
    }

    /**
     * Bulk archive tamu records created before specified year. Superadmin only.
     */
    public function arsipkanMassal(): ResponseInterface
    {
        if ($redirect = $this->requireSuperadmin()) {
            return $redirect;
        }

        $tahun = (int) $this->request->getPost('tahun');
        if ($tahun < 2000 || $tahun > (int) date('Y')) {
            return redirect()->back()->with('error', 'Tahun cut-off tidak valid.');
        }

        $model = new TamuModel();
        $total = $model->arsipkanSebelumTahun($tahun);

        return redirect()->back()->with('success', "Sebanyak {$total} data tamu sebelum tahun {$tahun} berhasil diarsipkan.");
    }
}
