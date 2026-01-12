<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUsedAtToOtpsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('otps', [
            'used_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'after'      => 'created_at', // Place it after 'created_at' for logical order
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('otps', 'used_at');
    }
}
