<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\SemesterModel;

class SemesterSeeder extends Seeder
{
    public function run()
    {
        $semesterModel = new SemesterModel();

        // Check if table already has data
        $existing = $semesterModel->countAll();
        if ($existing > 0) {
            echo "Semesters table already contains data. Skipping seeder.\n";
            return;
        }

        $semesters = [
            [
                'semester_name'  => '1st Semester',
                'semester_order' => 1,
                'description'    => 'First semester of the academic year',
                'is_active'      => 1
            ],
            [
                'semester_name'  => '2nd Semester',
                'semester_order' => 2,
                'description'    => 'Second semester of the academic year',
                'is_active'      => 1
            ],
            [
                'semester_name'  => 'Summer',
                'semester_order' => 3,
                'description'    => 'Summer term for special courses and make-up classes',
                'is_active'      => 1
            ]
        ];

        echo "Seeding semesters...\n";
        
        foreach ($semesters as $semester) {
            $result = $semesterModel->insert($semester);
            
            if ($result) {
                echo "✓ Created: {$semester['semester_name']}\n";
            } else {
                echo "✗ Failed to create: {$semester['semester_name']}\n";
                print_r($semesterModel->errors());
            }
        }

        echo "\nSemester seeding completed successfully!\n";
        echo "Total semesters created: " . count($semesters) . "\n";
    }
}
