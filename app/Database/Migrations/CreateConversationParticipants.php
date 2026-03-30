<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConversationParticipants extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'conversation_id' => ['type' => 'INT'],
            'user_id' => ['type' => 'INT'],
            'role' => ['type' => 'ENUM', 'constraint' => ['customer','provider','admin']],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('conversation_participants');
    }

    public function down()
    {
        $this->forge->dropTable('conversation_participants');
    }
}