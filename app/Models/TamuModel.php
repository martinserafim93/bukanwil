<?php

namespace App\Models;

use CodeIgniter\Model;

class TamuModel extends Model
{
    protected $table            = 'tamu';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;
    protected $allowedFields      = [
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
        'is_read',
    ];

    /**
     * Apply common filters to a query builder for tamu table.
     */
    protected function applyFilter(\CodeIgniter\Database\BaseBuilder $builder, array $filter): \CodeIgniter\Database\BaseBuilder
    {

        if (! empty($filter['q'])) {
            $q = (string) $filter['q'];
            $builder->groupStart()
                ->like('nama', $q, 'both', true)
                ->orLike('no_hp', $q, 'both', true)
                ->orLike('nama_instansi', $q, 'both', true)
                ->groupEnd();
        }

        if (! empty($filter['tanggal_dari'])) {
            $builder->where('created_at >=', (string) $filter['tanggal_dari'] . ' 00:00:00');
        }

        if (! empty($filter['tanggal_sampai'])) {
            $builder->where('created_at <=', (string) $filter['tanggal_sampai'] . ' 23:59:59');
        }

        if (! empty($filter['jenis_kelamin'])) {
            $builder->where('jenis_kelamin', (string) $filter['jenis_kelamin']);
        }

        if (! empty($filter['asal'])) {
            $builder->where('asal_pengunjung', (string) $filter['asal']);
        }

        if (! empty($filter['keperluan'])) {
            $builder->like('keperluan', (string) $filter['keperluan'], 'both', true);
        }

        $allowedSort = ['created_at', 'nama'];
        $sort = in_array(($filter['sort'] ?? ''), $allowedSort, true) ? $filter['sort'] : 'created_at';
        $order = in_array(strtolower($filter['order'] ?? ''), ['asc', 'desc'], true)
            ? strtolower($filter['order'])
            : 'desc';
        $builder->orderBy($sort, $order);

        return $builder;
    }

    /**
     * Get paginated list of tamu with filters.
     *
     * @return array{data: array, pager: \CodeIgniter\Pager\Pager}
     */
    public function getPaginasi(array $filter, int $perPage = 20): array
    {
        $this->applyFilter($this->builder(), $filter);
        $items = $this->paginate($perPage, 'default');
        $pager = $this->pager;

        return ['data' => $items, 'pager' => $pager];
    }

    /**
     * Get all tamu matching filters (no pagination) for export.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getSemuaDenganFilter(array $filter): array
    {
        $builder = $this->applyFilter($this->db->table($this->table), $filter);

        return $builder->get()->getResultArray();
    }

    /**
     * Aggregate statistics for the dashboard.
     *
     * @return array<string, mixed>
     */
    public function getStatistik(): array
    {
        $db = $this->db;

        $total = $db->table($this->table)->countAllResults();

        $tamuHariIni = $db->table($this->table)
            ->where('DATE(created_at)', 'CURDATE()', false)
            ->countAllResults();

        $belumDibaca = $this->countUnread();

        $perJenisResult = $db->table($this->table)
            ->select('jenis_kelamin as label, COUNT(*) as count')
            ->groupBy('jenis_kelamin')
            ->get()
            ->getResultArray();
        $perJenis = [];
        foreach ($perJenisResult as $row) {
            $perJenis[] = ['label' => $row['label'], 'count' => (int) $row['count']];
        }
        // Ensure both labels always present (count 0 if missing)
        $perJenis = $this->normalizeCounts(['Laki-laki', 'Perempuan'], $perJenis);

        $perAsalResult = $db->table($this->table)
            ->select('asal_pengunjung as label, COUNT(*) as count')
            ->groupBy('asal_pengunjung')
            ->get()
            ->getResultArray();
        $perAsal = [];
        foreach ($perAsalResult as $row) {
            $perAsal[] = ['label' => $row['label'], 'count' => (int) $row['count']];
        }
        $perAsal = $this->normalizeCounts(['perorangan', 'instansi'], $perAsal);

        $topKeperluanRaw = $db->table($this->table)
            ->select('keperluan, COUNT(*) as count')
            ->groupBy('keperluan')
            ->orderBy('count', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();
        $topKeperluan = [];
        foreach ($topKeperluanRaw as $row) {
            $topKeperluan[] = ['keperluan' => $row['keperluan'], 'count' => (int) $row['count']];
        }

        $trenRaw = $db->table($this->table)
            ->select('DATE(created_at) as tanggal, COUNT(*) as count')
            ->where('created_at >=', date('Y-m-d', strtotime('-6 days')) . ' 00:00:00')
            ->groupBy('DATE(created_at)')
            ->orderBy('tanggal', 'ASC')
            ->get()
            ->getResultArray();
        $trenMap = [];
        foreach ($trenRaw as $row) {
            $trenMap[$row['tanggal']] = (int) $row['count'];
        }
        $tren = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $tren[] = ['tanggal' => $d, 'count' => $trenMap[$d] ?? 0];
        }

        return [
            'total_tamu'    => $total,
            'tamu_hari_ini' => $tamuHariIni,
            'unread_count'  => $belumDibaca,
            'per_jenis'     => $perJenis,
            'per_asal'      => $perAsal,
            'top_keperluan' => $topKeperluan,
            'tren_7hari'    => $tren,
        ];
    }

    /**
     * Normalize result so every expected label has a count.
     *
     * @param list<string> $labels
     * @param list<array<string, mixed>> $existing
     * @return list<array<string, mixed>>
     */
    protected function normalizeCounts(array $labels, array $existing): array
    {
        $map = [];
        foreach ($existing as $row) {
            $map[$row['label']] = $row['count'];
        }

        $out = [];
        foreach ($labels as $label) {
            $out[] = ['label' => $label, 'count' => $map[$label] ?? 0];
        }

        return $out;
    }

    /**
     * Count tamu where is_read is 0.
     */
    public function countUnread(): int
    {
        return $this->db->table($this->table)
            ->where('is_read', 0)
            ->countAllResults();
    }

    /**
     * Mark single tamu as read.
     */
    public function tandaiBaca(int $id): void
    {
        $this->db->table($this->table)
            ->where('id', $id)
            ->update(['is_read' => 1]);
    }

    /**
     * Mark all unread tamu as read.
     */
    public function tandaiBacaSemua(): void
    {
        $this->db->table($this->table)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);
    }
}
