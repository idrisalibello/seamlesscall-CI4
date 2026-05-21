<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPopularServicesAndPromotionUsages extends Migration
{
    public function up()
    {
        /**
         * ── Add view_count to services ─────────────────────────────
         */
        if ($this->db->tableExists('services')) {

            // Safer + native check
            if (!$this->db->fieldExists('view_count', 'services')) {

                $this->forge->addColumn('services', [
                    'view_count' => [
                        'type'       => 'INT',
                        'constraint' => 11,
                        'unsigned'   => true,
                        'default'    => 0,
                        'after'      => 'status',
                    ],
                ]);
            }
        }

        /**
         * ── Create promotion_usages table ─────────────────────────
         */
        if (!$this->db->tableExists('promotion_usages')) {

            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'promotion_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'customer_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'job_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'payment_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'original_amount' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,2',
                    'default'    => '0.00',
                ],
                'discount_applied' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,2',
                    'default'    => '0.00',
                ],
                'final_amount' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,2',
                    'default'    => '0.00',
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            // Keys
            $this->forge->addKey('id', true);
            $this->forge->addKey('promotion_id');
            $this->forge->addKey('customer_id');
            $this->forge->addKey('job_id');

            // Prevent duplicate usage per job
            $this->forge->addUniqueKey(['promotion_id', 'customer_id', 'job_id']);

            $this->forge->createTable('promotion_usages', true);
        }
    }

    public function down()
    {
        /**
         * ── Drop promotion_usages ────────────────────────────────
         */
        if ($this->db->tableExists('promotion_usages')) {
            $this->forge->dropTable('promotion_usages', true);
        }

        /**
         * ── Remove view_count from services ──────────────────────
         */
        if ($this->db->tableExists('services')) {

            if ($this->db->fieldExists('view_count', 'services')) {
                $this->forge->dropColumn('services', 'view_count');
            }
        }
    }
}