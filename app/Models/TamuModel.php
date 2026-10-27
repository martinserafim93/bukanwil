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
    protected $useSoftDeletes   = true;
    protected $deletedField     = 'deleted_at';
    protected $allowedFields    = [
        'nama',
        'jenis_kelamin',
        'asal_pengunjung',
        'nama_instansi',
        'alamat',
        'no_hp',
        'keperluan',
        'foto',
        'is_read',
        'is_archived',
        'deleted_at',
    ];

    /**
     * Apply common filters to a query builder for tamu table.
     */
    protected function applyFilter(\CodeIgniter\Database\BaseBuilder $builder, array $filter): \CodeIgniter\Database\BaseBuilder
    {
        $tab = $filter['tab'] ?? 'aktif';
        if ($tab === 'arsip') {
            $builder->where('is_archived', 1);
        } elseif ($tab === 'sampah') {
            $builder->where('deleted_at IS NOT NULL');
        } else {
            // default: aktif
            $builder->where('is_archived', 0);
        }

        if (! empty($filter['tahun'])) {
            $builder->where('YEAR(created_at)', (int) $filter['tahun']);
        }

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
        $tab = $filter['tab'] ?? 'aktif';
        if ($tab === 'sampah') {
            $this->onlyDeleted();
        }

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
        $tab = $filter['tab'] ?? 'aktif';
        $builder = $this->db->table($this->table);
        if ($tab !== 'sampah') {
            $builder->where('deleted_at IS NULL');
        }
        $builder = $this->applyFilter($builder, $filter);

        return $builder->get()->getResultArray();
    }

    /**
     * Aggregate statistics for the dashboard.
     * Soft-deleted records (spam/testing) are strictly excluded.
     *
     * @return array<string, mixed>
     */
    public function getStatistik(): array
    {
        $db = $this->db;

        $total = $db->table($this->table)
            ->where('deleted_at IS NULL')
            ->countAllResults();

        $tamuHariIni = $db->table($this->table)
            ->where('deleted_at IS NULL')
            ->where('DATE(created_at)', 'CURDATE()', false)
            ->countAllResults();

        $belumDibaca = $this->countUnread();

        $perJenisResult = $db->table($this->table)
            ->select('jenis_kelamin as label, COUNT(*) as count')
            ->where('deleted_at IS NULL')
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
            ->where('deleted_at IS NULL')
            ->groupBy('asal_pengunjung')
            ->get()
            ->getResultArray();
        $perAsal = [];
        foreach ($perAsalResult as $row) {
            $perAsal[] = ['label' => $row['label'], 'count' => (int) $row['count']];
        }
        $perAsal = $this->normalizeCounts(['perorangan', 'instansi', 'lembaga'], $perAsal);

        $topKeperluanRaw = $db->table($this->table)
            ->select('keperluan, COUNT(*) as count')
            ->where('deleted_at IS NULL')
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
            ->where('deleted_at IS NULL')
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
     * Count tamu where is_read is 0 and not deleted.
     */
    public function countUnread(): int
    {
        return $this->db->table($this->table)
            ->where('deleted_at IS NULL')
            ->where('is_read', 0)
            ->countAllResults();
    }

    /**
     * Count tamu on active tab (is_archived = 0 and deleted_at IS NULL).
     */
    public function countAktif(): int
    {
        return $this->db->table($this->table)
            ->where('is_archived', 0)
            ->where('deleted_at IS NULL')
            ->countAllResults();
    }

    /**
     * Count tamu on archived tab (is_archived = 1 and deleted_at IS NULL).
     */
    public function countArsip(): int
    {
        return $this->db->table($this->table)
            ->where('is_archived', 1)
            ->where('deleted_at IS NULL')
            ->countAllResults();
    }

    /**
     * Count tamu on trash tab (deleted_at IS NOT NULL).
     */
    public function countSampah(): int
    {
        return $this->db->table($this->table)
            ->where('deleted_at IS NOT NULL')
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
            ->where('deleted_at IS NULL')
            ->where('is_read', 0)
            ->update(['is_read' => 1]);
    }

    /**
     * Archive a tamu record.
     */
    public function arsipkan(int $id): bool
    {
        return $this->update($id, ['is_archived' => 1]);
    }

    /**
     * Unarchive a tamu record.
     */
    public function batalArsip(int $id): bool
    {
        return $this->update($id, ['is_archived' => 0]);
    }

    /**
     * Bulk archive all non-deleted tamu records created before specified year.
     */
    public function arsipkanSebelumTahun(int $tahun): int
    {
        $this->db->table($this->table)
            ->where('is_archived', 0)
            ->where('deleted_at IS NULL')
            ->where('YEAR(created_at) <', $tahun)
            ->update(['is_archived' => 1]);

        return $this->db->affectedRows();
    }

    /**
     * Restore a soft-deleted tamu record back to active.
     */
    public function pulihkan(int $id): bool
    {
        $this->builder()->where('id', $id)->update(['deleted_at' => null]);

        return $this->db->affectedRows() > 0;
    }

    /**
     * Permanently delete a tamu record and remove its physical photo file if present.
     */
    public function hapusPermanen(int $id): bool
    {
        $tamu = $this->withDeleted()->find($id);
        if ($tamu === null) {
            return false;
        }

        if (! empty($tamu['foto'])) {
            $foto = (string) $tamu['foto'];
            $relPath = str_starts_with($foto, 'uploads/') ? $foto : 'uploads/foto/' . $foto;
            $fullPath = FCPATH . $relPath;
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
        }

        return (bool) $this->delete($id, true);
    }
}
