<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAttachmentToAssignments extends Migration
{
    public function up()
    {
        $fields = [
            'attachment_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'after'      => 'instructions'
            ]
        ];

        $this->forge->addColumn('assignments', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('assignments', 'attachment_path');
    }
}
