<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLifecycleColumnsToTamu extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('is_archived', 'tamu')) {
            $this->forge->addColumn('tamu', [
                'is_archived' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'unsigned'   => true,
                    'default'    => 0,
                    'after'      => 'is_read',
                ],
            ]);
        }

        if (! $this->db->fieldExists('deleted_at', 'tamu')) {
            $this->forge->addColumn('tamu', [
                'deleted_at' => [
                    'type'    => 'DATETIME',
                    'null'    => true,
                    'default' => null,
                    'after'   => 'created_at',
                ],
            ]);
        }

        $indices    = $this->db->getIndexData('tamu');
        $indexNames = array_column($indices, 'name');

        if (! in_array('idx_tamu_is_archived', $indexNames, true)) {
            $this->db->query('ALTER TABLE `tamu` ADD INDEX `idx_tamu_is_archived` (`is_archived`)');
        }

        if (! in_array('idx_tamu_deleted_at', $indexNames, true)) {
            $this->db->query('ALTER TABLE `tamu` ADD INDEX `idx_tamu_deleted_at` (`deleted_at`)');
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('is_archived', 'tamu')) {
            $this->forge->dropColumn('tamu', 'is_archived');
        }

        if ($this->db->fieldExists('deleted_at', 'tamu')) {
            $this->forge->dropColumn('tamu', 'deleted_at');
        }
    }
}
