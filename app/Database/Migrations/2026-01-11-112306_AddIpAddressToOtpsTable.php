<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIpAddressToOtpsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('otps', [
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => '45',
                'null'       => true,
                'after'      => 'updated_at', // Place it after 'updated_at' for logical order
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('otps', 'ip_address');
    }
}
