<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameOtpToOtpHashInOtpsTable extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('otps', [
            'otp' => [
                'name' => 'otp_hash',
                'type' => 'VARCHAR',
                'constraint' => '255', // Assuming 255 for hashed OTP
                'null' => false,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('otps', [
            'otp_hash' => [
                'name' => 'otp',
                'type' => 'VARCHAR',
                'constraint' => '6', // Revert to original type and constraint
                'null' => false,
            ],
        ]);
    }
}
