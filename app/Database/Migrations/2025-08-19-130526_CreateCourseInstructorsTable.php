<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCourseInstructorsTable extends Migration
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
            'instructor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'is_primary' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'assigned_date' => [
                'type' => 'DATE',
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
        $this->forge->addUniqueKey(['course_offering_id', 'instructor_id']);
        $this->forge->addForeignKey('course_offering_id', 'course_offerings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('instructor_id', 'instructors', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('course_instructors');
    }

    public function down()
    {
        $this->forge->dropTable('course_instructors');
    }
}