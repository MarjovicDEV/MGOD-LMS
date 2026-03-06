<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\AssignmentTypeModel;

class AssignmentTypeSeeder extends Seeder
{
    public function run()
    {
        $assignmentTypeModel = new AssignmentTypeModel();

        // Check if table already has data
        $existing = $assignmentTypeModel->countAll();
        if ($existing > 0) {
            echo "Assignment Types table already contains data. Skipping seeder.\n";
            return;
        }

        /**
         * Assignment Types Explanation:
         * - type_name: Display name of the assignment type
         * - type_code: Unique code identifier for the type
         * - default_weight: Default percentage weight in grading (totals should = 100%)
         * - description: Brief explanation of the assignment type
         * - is_active: Whether this type is available for use
         * 
         * These weights can be customized per course via Grade Components table
         */

        $assignmentTypes = [
            // Quiz - 16.67%
            [
                'type_name'      => 'Quiz',
                'type_code'      => 'QZ',
                'default_weight' => 16.67,
                'description'    => 'Short quizzes and knowledge checks',
                'is_active'      => 1
            ],
            // Assignments - 15%
            [
                'type_name'      => 'Assignments',
                'type_code'      => 'ASG',
                'default_weight' => 15.00,
                'description'    => 'Homework and other assignments',
                'is_active'      => 1
            ],
            // Participation - 10%
            [
                'type_name'      => 'Participation',
                'type_code'      => 'PART',
                'default_weight' => 10.00,
                'description'    => 'Class participation and attendance',
                'is_active'      => 1
            ],
            // Exam - 33.33%
            [
                'type_name'      => 'Exam',
                'type_code'      => 'EXAM',
                'default_weight' => 33.33,
                'description'    => 'Major written/oral examinations',
                'is_active'      => 1
            ],
            // Laboratories - 25%
            [
                'type_name'      => 'Laboratories',
                'type_code'      => 'LAB',
                'default_weight' => 25.00,
                'description'    => 'Laboratory exercises and reports',
                'is_active'      => 1
            ]
        ];

        echo "Seeding assignment types...\n";
        echo "Note: Default weights total 100% (can be customized per course)\n\n";
        
        foreach ($assignmentTypes as $type) {
            try {
                $result = $assignmentTypeModel->insert($type);
                
                if ($result) {
                    $status = $type['is_active'] ? '[ACTIVE]' : '[Inactive]';
                    echo "✓ Created: {$type['type_name']} ({$type['type_code']}) - {$type['default_weight']}% {$status}\n";
                } else {
                    echo "✗ Failed to create: {$type['type_name']}\n";
                    print_r($assignmentTypeModel->errors());
                }
            } catch (\Exception $e) {
                echo "✗ Error creating assignment type {$type['type_name']}: " . $e->getMessage() . "\n";
            }
        }
    }
}
