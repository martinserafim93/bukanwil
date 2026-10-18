<?php

namespace App\Models;

use CodeIgniter\Model;

class PengaturanModel extends Model
{
    protected $table            = 'pengaturan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;
    protected $allowedFields      = ['key', 'value'];

    /**
     * Get all settings as associative array key => value.
     *
     * @return array<string, string>
     */
    public function getAll(): array
    {
        $rows = $this->db->table($this->table)
            ->select('key, value')
            ->get()
            ->getResultArray();

        $result = [];
        foreach ($rows as $row) {
            $result[(string) $row['key']] = (string) $row['value'];
        }

        return $result;
    }

    /**
     * Set single setting value (upsert).
     */
    public function setValue(string $key, string $value): void
    {
        $db = $this->db->table($this->table);
        $existing = $db->where('key', $key)->get()->getRowArray();

        if ($existing === null) {
            $this->db->table($this->table)->insert(['key' => $key, 'value' => $value]);
        } else {
            $this->db->table($this->table)
                ->where('key', $key)
                ->update(['value' => $value]);
        }
    }

    /**
     * Set many settings at once.
     *
     * @param array<string, string> $data
     */
    public function setMany(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->setValue((string) $key, (string) $value);
        }
    }
}
