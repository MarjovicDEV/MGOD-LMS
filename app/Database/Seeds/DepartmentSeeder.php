<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'department_code' => 'CET',
                'department_name' => 'College of Engineering and Technology',
                'description'     => 'Focuses on engineering and technological disciplines including computer science, information technology, and related fields',
                'head_user_id'    => null,
                'is_active'       => 1,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('departments')->insertBatch($data);

    }
}