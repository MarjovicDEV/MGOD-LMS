<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProgramIdToStudentsTable extends Migration
{
    public function up()
    {
        $fields = [
            'program_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'year_level_id',
            ],
        ];

        $this->forge->addColumn('students', $fields);
        
        // Add foreign key
        $this->forge->addForeignKey(
            'program_id',
            'programs',
            'id',
            'SET NULL',
            'CASCADE',
            'students_program_id_fk'
        );
    }

    public function down()
    {
        // Drop foreign key first
        $this->forge->dropForeignKey('students', 'students_program_id_fk');
        
        // Drop column
        $this->forge->dropColumn('students', 'program_id');
    }
}