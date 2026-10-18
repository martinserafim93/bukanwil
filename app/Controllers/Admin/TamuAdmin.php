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
     * Display the guest list with filtering and pagination.
     */
    public function index(): string
    {
        $filter = $this->buildFilter();
        $result = (new TamuModel())->getPaginasi($filter, 20);

        return view('admin/tamu/index', [
            'pengaturan'   => $this->pengaturan,
            'unreadCount'  => $this->unreadCount,
            'tamu'         => $result['data'],
            'pager'        => $result['pager'],
            'total'        => $result['pager']->getTotal(),
            'filter_aktif' => $filter,
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
            'lat',
            'lng',
            'foto',
            'created_at',
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

        $filename = 'tamu_export_' . date('Ymd_His') . '.csv';

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
        // Implemented in Fase 3c-2.
        return 'Detail tamu akan diimplementasikan di Fase 3c-2.';
    }

    /**
     * Mark a single tamu record as read.
     */
    public function tandaiBaca(int $id): ResponseInterface
    {
        // Implemented in Fase 3c-2.
        return $this->response->setStatusCode(501);
    }

    /**
     * Mark all unread tamu records as read.
     */
    public function bacaSemua(): ResponseInterface
    {
        // Implemented in Fase 3c-2.
        return $this->response->setStatusCode(501);
    }
}
