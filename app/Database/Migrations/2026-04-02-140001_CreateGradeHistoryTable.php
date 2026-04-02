<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGradeHistoryTable extends Migration
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
            'gradebook_entry_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'old_grade' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
            ],
            'new_grade' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
            ],
            'old_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'new_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'change_type' => [
                'type'       => 'ENUM',
                'constraint' => ['calculated', 'override', 'status_change'],
                'null'       => false,
            ],
            'changed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'change_reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'changed_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('gradebook_entry_id');
        $this->forge->addKey('changed_by');
        $this->forge->addKey('changed_at');
        $this->forge->addKey('change_type');
        
        $this->forge->addForeignKey('gradebook_entry_id', 'gradebook_entries', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('changed_by', 'users', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('grade_history');
    }

    public function down()
    {
        $this->forge->dropTable('grade_history');
    }
}
