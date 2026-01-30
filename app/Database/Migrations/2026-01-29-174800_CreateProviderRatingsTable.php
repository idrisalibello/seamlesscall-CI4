<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProviderRatingsTable extends Migration
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
                'unique'         => true, // one rating per job
            ],
            'provider_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
            ],
            'customer_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'null'           => true, // nullable if needed
            ],
            'rating' => [
                'type'           => 'TINYINT',
                'constraint'     => 1, // 1-5
            ],
            'comment' => [
                'type'           => 'TEXT',
                'null'           => true,
            ],
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('job_id', 'jobs', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('provider_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('customer_id', 'users', 'id', 'CASCADE', 'CASCADE'); // Assuming customer_id also references users table
        $this->forge->createTable('provider_ratings');
    }

    public function down()
    {
        $this->forge->dropTable('provider_ratings');
    }
}
