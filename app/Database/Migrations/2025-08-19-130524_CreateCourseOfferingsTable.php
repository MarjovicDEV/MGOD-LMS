<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCourseOfferingsTable extends Migration
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
            'term_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'section' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'max_students' => [
                'type'       => 'INT',
                'constraint' => 4,
                'default'    => 30,
            ],
            'current_enrollment' => [
                'type'       => 'INT',
                'constraint' => 4,
                'default'    => 0,
            ],
            'room' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'open', 'closed', 'cancelled', 'completed'],
                'default'    => 'draft',
            ],
            'start_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'end_date' => [
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
        $this->forge->addKey(['course_id', 'term_id']);
        $this->forge->addForeignKey('course_id', 'courses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('term_id', 'terms', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('course_offerings');
    }

    public function down()
    {
        $this->forge->dropTable('course_offerings');
    }
}