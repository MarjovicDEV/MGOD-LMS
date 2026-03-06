<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAssignmentsTable extends Migration
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
            'course_offering_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'lesson_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'assignment_type_id' => [
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
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => false,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'instructions' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'max_score' => [
                'type'       => 'DECIMAL',
                'constraint' => '6,2',
                'default'    => 100.00,
            ],
            'due_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'available_from' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'available_until' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'attempts_allowed' => [
                'type'       => 'INT',
                'constraint' => 2,
                'default'    => 1,
            ],
            'is_published' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'allow_late_submission' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'late_penalty_percentage' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00,
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
        $this->forge->addKey('course_offering_id');
        $this->forge->addKey('assignment_type_id');
        $this->forge->addForeignKey('course_offering_id', 'course_offerings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('lesson_id', 'lessons', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('assignment_type_id', 'assignment_types', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('grading_period_id', 'grading_periods', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('assignments');
    }

    public function down()
    {
        $this->forge->dropTable('assignments');
    }
}