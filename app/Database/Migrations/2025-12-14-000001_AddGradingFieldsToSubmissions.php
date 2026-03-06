<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGradingFieldsToSubmissions extends Migration
{
    public function up()
    {
        $fields = [
            'score' => [
                'type'       => 'DECIMAL',
                'constraint' => '6,2',
                'null'       => true,
                'after'      => 'status'
            ],
            'feedback' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'score'
            ],
            'graded_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'feedback'
            ],
            'graded_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'graded_by'
            ]
        ];

        $this->forge->addColumn('submissions', $fields);
        
        // Add foreign key for graded_by
        $this->forge->addForeignKey('graded_by', 'users', 'id', 'SET NULL', 'CASCADE', 'submissions');
    }

    public function down()
    {
        $this->forge->dropForeignKey('submissions', 'submissions_graded_by_foreign');
        $this->forge->dropColumn('submissions', ['score', 'feedback', 'graded_by', 'graded_at']);
    }
}
