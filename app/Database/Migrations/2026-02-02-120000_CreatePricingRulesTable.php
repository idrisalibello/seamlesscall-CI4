<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePricingRulesTable extends Migration
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
            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            // category | service
            'scope' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'category',
            ],
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'service_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            // flat | percent
            'charge_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'flat',
            ],
            // money or percent value depending on charge_type
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => '0.00',
            ],
            // active | inactive
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'active',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addKey('scope');
        $this->forge->addKey('status');
        $this->forge->addKey('category_id');
        $this->forge->addKey('service_id');

        // Non-destructive: keep nullable + SET NULL on delete
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('service_id', 'services', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('pricing_rules');
    }

    public function down()
    {
        
    }
}
