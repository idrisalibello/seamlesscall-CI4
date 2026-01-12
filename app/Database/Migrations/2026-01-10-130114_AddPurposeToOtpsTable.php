<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPurposeToOtpsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('otps', [
            'purpose' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => false,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('otps', 'purpose');
    }
}
