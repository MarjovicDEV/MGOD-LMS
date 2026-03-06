<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInstructorsTable extends Migration
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
            'employee_id' => [
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
            'hire_date' => [
                'type'    => 'DATE',
                'null'    => true,
                
            ],
            'employment_status' => [
                'type'       => 'ENUM',
                'constraint' => ['full_time', 'part_time', 'contract', 'probationary', 'retired', 'resigned'],
                'default'    => 'full_time',
                
            ],
            'specialization' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
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
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        // Primary key
        $this->forge->addKey('id', true);
        
        // Unique keys
        $this->forge->addUniqueKey('user_id');
        $this->forge->addUniqueKey('employee_id');
        
        // Foreign keys
        $this->forge->addKey('department_id');
        $this->forge->addKey('deleted_at');
        
        // Add foreign key constraints
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('department_id', 'departments', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('instructors');
    }

    public function down()
    {
        $this->forge->dropTable('instructors');
    }
}
