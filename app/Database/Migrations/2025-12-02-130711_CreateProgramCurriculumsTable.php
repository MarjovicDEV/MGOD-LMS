<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProgramCurriculomsTable extends Migration
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
            'program_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'course_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'year_level_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'semester_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'course_type' => [
                'type'       => 'ENUM',
                'constraint' => ['major', 'minor', 'general_education'],
                'default'    => 'major',
            ],
            'units' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 3,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
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
        $this->forge->addKey('program_id');
        $this->forge->addKey('course_id');
        $this->forge->addKey('year_level_id');
        $this->forge->addKey('semester_id');
        $this->forge->addKey(['program_id', 'year_level_id', 'semester_id']); // Composite index
        
        // Foreign keys
        $this->forge->addForeignKey('program_id', 'programs', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('course_id', 'courses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('year_level_id', 'year_levels', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('semester_id', 'semesters', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('program_curriculums');
    }

    public function down()
    {
        $this->forge->dropTable('program_curriculums');
    }
}