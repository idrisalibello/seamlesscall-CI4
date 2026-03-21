<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJobPaymentsTable extends Migration
{
    public function up()
    {
        

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'job_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'customer_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'service_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'provider_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'null'       => true,
            ],
            'purpose' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => '0.00',
            ],
            'currency' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'default'    => 'NGN',
            ],
            'gateway' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'paystack',
            ],
            'paystack_reference' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'paystack_access_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'paystack_transaction_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'authorization_url' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'initialized',
            ],
            'gateway_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'paid_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'verified_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'metadata_json' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'webhook_payload' => [
                'type' => 'LONGTEXT',
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

        $this->forge->addKey('id', true);
        $this->forge->addKey('job_id');
        $this->forge->addKey('customer_id');
        $this->forge->addKey('service_id');
        $this->forge->addKey('purpose');
        $this->forge->addUniqueKey('paystack_reference');
        $this->forge->createTable('job_payments', true);
    }

    public function down()
    {
        $this->forge->dropTable('job_payments', true);
    }
}