<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSubmissionTypeToAssignments extends Migration
{
    public function up()
    {
        $fields = [
            'submission_type' => [
                'type'       => 'ENUM',
                'constraint' => ['text', 'file', 'both'],
                'default'    => 'both',
                'null'       => false,
                'after'      => 'instructions'
            ]
        ];

        $this->forge->addColumn('assignments', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('assignments', 'submission_type');
    }
}
