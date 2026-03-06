<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoursePrerequisitesTable extends Migration
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
            'course_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'prerequisite_course_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],            'prerequisite_type' => [
                'type'       => 'ENUM',
                'constraint' => ['required', 'recommended', 'corequisite'],
                'default'    => 'required',
            ],
            'minimum_grade' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'null'       => true,
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
        $this->forge->addKey(['course_id', 'prerequisite_course_id']);
        $this->forge->addForeignKey('course_id', 'courses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('prerequisite_course_id', 'courses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('course_prerequisites');
    }

    public function down()
    {
        $this->forge->dropTable('course_prerequisites');
    }
}