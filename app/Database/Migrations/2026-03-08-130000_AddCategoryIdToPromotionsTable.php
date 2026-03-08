<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCategoryIdToPromotionsTable extends Migration
{
    public function up()
    {
        $fields = [
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'code',
            ],
        ];

        $this->forge->addColumn('promotions', $fields);
        $this->db->query('ALTER TABLE `promotions` ADD KEY `category_id` (`category_id`)');
    }

    public function down()
    {
        $this->forge->dropColumn('promotions', 'category_id');
    }
}