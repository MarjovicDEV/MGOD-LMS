<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProgramSeeder extends Seeder
{
    public function run()
    {
        // Get the CET department ID
        $department = $this->db->table('departments')
            ->where('department_code', 'CET')
            ->get()
            ->getRowArray();
        
        if (!$department) {
            echo "CET Department not found. Please run DepartmentSeeder first.\n";
            return;
        }
        
        $departmentId = $department['id'];
        
        $data = [
            [
                'program_code'  => 'BSCS',
                'program_name'  => 'Bachelor of Science in Computer Science',
                'description'   => 'Focuses on theoretical foundations of computation, algorithm design, software development, and advanced computing concepts',
                'department_id' => $departmentId,
                'degree_type'   => 'bachelor',
                'total_units'   => 180,
                'total_years'   => 4,
                'is_active'     => 1,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'program_code'  => 'BSIT',
                'program_name'  => 'Bachelor of Science in Information Technology',
                'description'   => 'Emphasizes practical application of technology, network administration, database management, and IT infrastructure',
                'department_id' => $departmentId,
                'degree_type'   => 'bachelor',
                'total_units'   => 180,
                'total_years'   => 4,
                'is_active'     => 1,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'program_code'  => 'BSCE',
                'program_name'  => 'Bachelor of Science in Civil Engineering',
                'description'   => 'Covers infrastructure design, structural engineering, transportation, and construction management',
                'department_id' => $departmentId,
                'degree_type'   => 'bachelor',
                'total_units'   => 195,
                'total_years'   => 4,
                'is_active'     => 1,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
        ];

        // Using Query Builder for batch insert
        $this->db->table('programs')->insertBatch($data);

        echo "Successfully seeded " . count($data) . " programs for CET department.\n";
    }
}