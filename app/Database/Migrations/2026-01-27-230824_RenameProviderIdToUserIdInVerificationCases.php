<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class RenameProviderIdToUserIdInVerificationCases extends Migration
{
    public function up()
    {
        // Check if 'provider_id' column exists and 'user_id' does not
        if ($this->db->fieldExists('provider_id', 'verification_cases') && !$this->db->fieldExists('user_id', 'verification_cases')) {
            // Rename provider_id to user_id
            $this->forge->modifyColumn('verification_cases', [
                'provider_id' => [
                    'name'       => 'user_id',
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => false,
                ],
            ]);

            // Ensure an index exists on user_id
            if (!$this->db->getIndexData('verification_cases', 'user_id')) { // Check if index exists by column name
                $this->forge->addKey('user_id', false, false, 'user_id'); // Add an index named 'user_id'
                $this->forge->processIndexes('verification_cases');
            }
        }
    }

    public function down()
    {
        // Check if 'user_id' column exists and 'provider_id' does not
        if ($this->db->fieldExists('user_id', 'verification_cases') && !$this->db->fieldExists('provider_id', 'verification_cases')) {
            // Rename user_id back to provider_id
            $this->forge->modifyColumn('verification_cases', [
                'user_id' => [
                    'name'       => 'provider_id',
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => false,
                ],
            ]);

            // Ensure an index exists on provider_id
            if (!$this->db->getIndexData('verification_cases', 'provider_id')) { // Check if index exists by column name
                $this->forge->addKey('provider_id', false, false, 'provider_id'); // Add an index named 'provider_id'
                $this->forge->processIndexes('verification_cases');
            }
        }
    }
}
