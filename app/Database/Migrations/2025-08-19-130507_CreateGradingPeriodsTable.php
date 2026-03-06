<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGradingPeriodsTable extends Migration
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
            'term_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'period_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'period_order' => [
                'type'       => 'INT',
                'constraint' => 2,
                'null'       => false,
            ],
            'weight_percentage' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 25.00,
            ],
            'start_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'end_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'is_active' => [
                'type'    => 'BOOLEAN',
                'default' => true,
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
        $this->forge->addKey('term_id');
        $this->forge->addForeignKey('term_id', 'terms', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('grading_periods');
    }

    public function down()
    {
        $this->forge->dropTable('grading_periods');
    }
}