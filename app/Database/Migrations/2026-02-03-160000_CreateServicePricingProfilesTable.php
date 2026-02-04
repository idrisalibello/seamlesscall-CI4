<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateServicePricingProfilesTable extends Migration
{
    public function up()
    {
        

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'service_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            // fixed | hourly | unit | quote_after_inspection
            'pricing_basis' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'default'    => 'quote_after_inspection',
            ],
            // Phase 1 (Discovery)
            'inspection_fee' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => '0.00',
            ],
            // Phase 2 (Execution floor)
            'minimum_job_fee' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => '0.00',
            ],
            // Expected range (band)
            'price_band_min' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => '0.00',
            ],
            'price_band_max' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => '0.00',
            ],
            'currency' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'default'    => 'NGN',
            ],
            // active | inactive
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'active',
            ],
            'notes_for_client' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'notes_for_provider' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            // Governance
            'allow_band_override' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'max_override_percent' => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 0,
            ],
            'require_admin_review' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'auto_flag_dispute_threshold' => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('service_id');
        $this->forge->addKey('status');
        $this->forge->addKey('pricing_basis');

        // NOTE: no foreign keys here (non-assumptive / non-destructive).
        $this->forge->createTable('service_pricing_profiles');
    }

    public function down()
    {
        
    }
}
