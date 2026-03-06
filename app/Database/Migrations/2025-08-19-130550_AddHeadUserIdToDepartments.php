<?php


namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHeadUserIdToDepartments extends Migration
{
    public function up()
    {
        $this->forge->addColumn('departments', [
            'head_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'description',
            ],
        ]);
        
        $this->forge->addForeignKey('head_user_id', 'instructors', 'id', 'SET NULL', 'CASCADE', 'departments');
    }    public function down()
    {
        // Check if foreign key exists before dropping
        $db = \Config\Database::connect();
        
        // Get the actual foreign key name
        $keys = $db->getForeignKeyData('departments');
        foreach ($keys as $key) {
            if ($key->column_name === 'head_user_id') {
                $this->forge->dropForeignKey('departments', $key->constraint_name);
                break;
            }
        }
        
        $this->forge->dropColumn('departments', 'head_user_id');
    }
}