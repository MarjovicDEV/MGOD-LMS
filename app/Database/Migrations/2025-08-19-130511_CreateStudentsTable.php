<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStudentsTable extends Migration
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
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                
            ],
            'student_id_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                
            ],
            'department_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                
            ],
            'year_level_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Current year level (1st, 2nd, 3rd, 4th)',
            ],
            'section' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                
            ],
            'enrollment_date' => [
                'type'    => 'DATE',
                'null'    => true,
                
            ],
            'enrollment_status' => [
                'type'       => 'ENUM',
                'constraint' => ['enrolled', 'graduated', 'dropped', 'on_leave', 'suspended'],
                'default'    => 'enrolled',
                
            ],
            'guardian_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                
            ],
            'guardian_contact' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                
            ],
            'scholarship_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                
            ],
            'total_units' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'default'    => 0,
                
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        // Primary key
        $this->forge->addKey('id', true);
        
        // Unique keys
        $this->forge->addUniqueKey('user_id');
        $this->forge->addUniqueKey('student_id_number');
        
        // Foreign keys
        $this->forge->addKey('department_id');
        $this->forge->addKey('year_level_id');
        $this->forge->addKey('deleted_at');
        
        // Add foreign key constraints
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('department_id', 'departments', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('year_level_id', 'year_levels', 'id', 'SET NULL', 'CASCADE');
        
        $this->forge->createTable('students');
    }

    public function down()
    {
        $this->forge->dropTable('students');
    }
}
