<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProviderDisputesTable extends Migration
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
            'job_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'provider_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
            ],
            'raised_by' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'null'           => true, // nullable if needed
            ],
            'status' => [
                'type'           => 'ENUM',
                'constraint'     => ['pending', 'resolved', 'dismissed'],
                'default'        => 'pending',
            ],
            'reason' => [
                'type'           => 'TEXT',
            ],
            'resolved_at' => [
                'type'           => 'DATETIME',
                'null'           => true,
            ],
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('job_id', 'jobs', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('provider_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('raised_by', 'users', 'id', 'SET NULL', 'CASCADE'); // Assuming raised_by also references users table
        $this->forge->createTable('provider_disputes');
    }

    public function down()
    {
        $this->forge->dropTable('provider_disputes');
    }
}
