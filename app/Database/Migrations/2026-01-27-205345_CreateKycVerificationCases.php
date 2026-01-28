<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKycVerificationCases extends Migration
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
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'verified', 'rejected', 'escalated'],
                'default'    => 'pending',
                'null'       => false,
            ],
            'decision_reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'escalation_reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'decided_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'decided_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'default' => 'CURRENT_TIMESTAMP',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id'); // Indexed
        $this->forge->createTable('verification_cases');
    }

    public function down()
    {
        $this->forge->dropTable('verification_cases');
    }
}