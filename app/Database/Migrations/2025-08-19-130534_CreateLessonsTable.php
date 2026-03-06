<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLessonsTable extends Migration
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
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => false,
            ],
            'content' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'lesson_type' => [
                'type'       => 'ENUM',
                'constraint' => ['video', 'text', 'document', 'interactive', 'mixed'],
                'default'    => 'text',
            ],
            'video_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'document_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'lesson_order' => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 1,
            ],
            'duration_minutes' => [
                'type'       => 'INT',
                'constraint' => 4,
                'null'       => true,
            ],
            'is_published' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'publish_date' => [
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
        $this->forge->addKey('course_offering_id');
        $this->forge->addKey(['course_offering_id', 'lesson_order']);
        $this->forge->addForeignKey('course_offering_id', 'course_offerings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('lessons');
    }

    public function down()
    {
        $this->forge->dropTable('lessons');
    }
}