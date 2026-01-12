<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUpdatedAtToOtpsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('otps', [
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'after'      => 'used_at', // Place it after 'used_at' for logical order
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('otps', 'updated_at');
    }
}
