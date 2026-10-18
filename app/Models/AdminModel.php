<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminModel extends Model
{
    protected $table            = 'admin';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;
    protected $allowedFields      = ['nama', 'username', 'password', 'role'];

    /**
     * Find admin by username.
     *
     * @return array<string, mixed>|null
     */
    public function findByUsername(string $username): ?array
    {
        $row = $this->db->table($this->table)
            ->where('username', $username)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    /**
     * Update password hash for a given admin id.
     */
    public function updatePassword(int $id, string $hashedPassword): void
    {
        $this->db->table($this->table)
            ->where('id', $id)
            ->update(['password' => $hashedPassword]);
    }
}
