<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\RoleModel;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Initialize RoleModel
        $roleModel = new RoleModel();
        
        // Roles data for the system
        $roles = [
            [
                'role_name'   => 'admin',
                'description' => 'System Administrator - Full access to all features and settings',
                'is_active'   => 1
            ],
            [
                'role_name'   => 'teacher',
                'description' => 'Teacher/Instructor - Can manage courses, students, and assignments',
                'is_active'   => 1
            ],
            [
                'role_name'   => 'student',
                'description' => 'Student - Can enroll in courses and access learning materials',
                'is_active'   => 1
            ]
        ];

        // Check if roles table is empty before inserting
        if ($roleModel->countAll() == 0) {
            // Insert roles using RoleModel
            foreach ($roles as $role) {
                if ($roleModel->insert($role)) {
                    echo "✓ {$role['role_name']} role created successfully\n";
                } else {
                    echo "✗ Failed to create {$role['role_name']} role\n";
                    // Log validation errors if any
                    $errors = $roleModel->errors();
                    if (!empty($errors)) {
                        echo "  Errors: " . implode(', ', $errors) . "\n";
                    }
                }
            }
            
            echo "\nRoles seeded successfully!\n";
            echo "Total roles created: " . count($roles) . "\n";
        } else {
            echo "Roles table already contains data. Skipping seeding.\n";
            echo "Current roles count: " . $roleModel->countAll() . "\n";
            echo "To re-seed, truncate the table first or delete existing roles.\n";
        }
    }
}
