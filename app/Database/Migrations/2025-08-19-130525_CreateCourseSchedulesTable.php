<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCourseSchedulesTable extends Migration
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
            'session_type' => [
                'type'       => 'ENUM',
                'constraint' => ['lecture', 'lab'],
                'default'    => 'lecture',
                'null'       => false,
            ],
            'day_of_week' => [
                'type'       => 'ENUM',
                'constraint' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                'null'       => false,
            ],
            'start_time' => [
                'type' => 'TIME',
                'null' => false,
            ],
            'end_time' => [
                'type' => 'TIME',
                'null' => false,
            ],
            'room' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('course_offering_id');
        $this->forge->addForeignKey('course_offering_id', 'course_offerings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('course_schedules');
    }

    public function down()
    {
        $this->forge->dropTable('course_schedules');
    }
}