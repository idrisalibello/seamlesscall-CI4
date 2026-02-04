<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateServicePricingAdjustmentsTable extends Migration
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
            'profile_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            // flat | percent
            'adjustment_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'flat',
            ],
            'value' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => '0.00',
            ],
            'max_allowed' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => '0.00',
            ],
            // inspection | execution
            'applies_phase' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'execution',
            ],
            'requires_client_approval' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            // active | inactive
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'active',
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
        $this->forge->addKey('profile_id');
        $this->forge->addKey('status');
        $this->forge->addKey('applies_phase');

        // NOTE: no foreign keys here (non-assumptive / non-destructive).
        $this->forge->createTable('service_pricing_adjustments');
    }

    public function down()
    {
        
    }
}
