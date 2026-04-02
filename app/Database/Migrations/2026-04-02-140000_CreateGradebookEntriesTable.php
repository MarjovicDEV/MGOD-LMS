<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGradebookEntriesTable extends Migration
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
            'enrollment_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'grading_period_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'NULL for final course grade, otherwise period-specific grade',
            ],
            'calculated_grade' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
                'comment'    => 'Auto-calculated grade from submissions',
            ],
            'final_grade' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
                'comment'    => 'Actual grade (may include overrides)',
            ],
            'grade_status' => [
                'type'       => 'ENUM',
                'constraint' => ['calculated', 'incomplete', 'dropped', 'withdrawn', 'no_grade'],
                'default'    => 'calculated',
            ],
            'is_overridden' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'override_reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'overridden_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'overridden_at' => [
                'type' => 'DATETIME',
                'null' => true,
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
        $this->forge->addKey(['enrollment_id', 'grading_period_id']);
        $this->forge->addKey('enrollment_id');
        $this->forge->addKey('grade_status');
        
        $this->forge->addForeignKey('enrollment_id', 'enrollments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('grading_period_id', 'grading_periods', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('overridden_by', 'users', 'id', 'SET NULL', 'CASCADE');
        
        $this->forge->createTable('gradebook_entries');
    }

    public function down()
    {
        $this->forge->dropTable('gradebook_entries');
    }
}
