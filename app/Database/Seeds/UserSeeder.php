<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\StudentModel;
use App\Models\InstructorModel;

class UserSeeder extends Seeder
{
    public function run()
    {
        $userModel = new UserModel();
        $roleModel = new RoleModel();
        $studentModel = new StudentModel();
        $instructorModel = new InstructorModel();

        // Get role IDs
        $adminRole = $roleModel->where('role_name', 'Admin')->first();
        $studentRole = $roleModel->where('role_name', 'Student')->first();
        $teacherRole = $roleModel->where('role_name', 'Teacher')->first();

        if (!$adminRole || !$studentRole || !$teacherRole) {
            echo "Roles not found. Please run RoleSeeder first.\n";
            return;
        }

        echo "====================\n";
        echo "Starting User Seeder\n";
        echo "====================\n\n";

        // ============ ADMIN USER ============
        $existingAdmin = $userModel->where('email', 'marjovicalejado1232@gmail.com')->first();
        if (!$existingAdmin) {
            $adminData = [
                'user_code'         => 'ADM-' . date('Ymd') . '-0001',
                'first_name'        => 'Marjovic',
                'middle_name'       => 'Prato',
                'last_name'         => 'Alejado',
                'suffix'            => null,
                'email'             => 'marjovicalejado1232@gmail.com',
                'password'          => 'admin123',
                'role_id'           => $adminRole['id'],
                'is_active'         => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'last_login'        => null
            ];

            try {
                if ($userModel->insert($adminData)) {
                    echo "✓ Admin user created successfully!\n";
                    echo "  Email: marjovicalejado1232@gmail.com\n";
                    echo "  Password: admin123\n";
                    echo "  Name: Marjovic Prato Alejado\n\n";
                } else {
                    echo "✗ Failed to create admin user\n";
                    print_r($userModel->errors());
                }
            } catch (\Exception $e) {
                echo "✗ Error creating admin user: " . $e->getMessage() . "\n";
            }
        } else {
            echo "→ Admin user already exists. Skipping.\n\n";
        }

        // ============ STUDENT USER ============
        $existingStudent = $userModel->where('email', 'junjun100486@gmail.com')->first();
        if (!$existingStudent) {
            $studentUserData = [
                'user_code'         => 'STU-' . date('Ymd') . '-0001',
                'first_name'        => 'Marj',
                'middle_name'       => 'God',
                'last_name'         => 'Alej',
                'suffix'            => null,
                'email'             => 'junjun100486@gmail.com',
                'password'          => 'student123',
                'role_id'           => $studentRole['id'],
                'is_active'         => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'last_login'        => null
            ];

            try {
                $studentUserId = $userModel->insert($studentUserData);
                if ($studentUserId) {
                    // Create student record
                    $studentRecord = [
                        'user_id'           => $studentUserId,
                        'year_level_id'     => 1,
                        'department_id'     => null,
                        'program_id'        => null,
                        'section'           => null,
                        'enrollment_date'   => date('Y-m-d'),
                        'enrollment_status' => 'enrolled'
                    ];
                    $studentModel->insert($studentRecord);

                    echo "✓ Student user created successfully!\n";
                    echo "  Email: junjun100486@gmail.com\n";
                    echo "  Password: student123\n";
                    echo "  Name: Marj God Alej\n\n";
                } else {
                    echo "✗ Failed to create student user\n";
                    print_r($userModel->errors());
                }
            } catch (\Exception $e) {
                echo "✗ Error creating student user: " . $e->getMessage() . "\n";
            }
        } else {
            echo "→ Student user already exists. Skipping.\n\n";
        }

        // ============ TEACHER USER ============
        $existingTeacher = $userModel->where('email', 'marjovicalejado123@gmail.com')->first();
        if (!$existingTeacher) {
            $teacherUserData = [
                'user_code'         => 'TCH-' . date('Ymd') . '-0001',
                'first_name'        => 'Ammar',
                'middle_name'       => 'Jo',
                'last_name'         => 'Vic',
                'suffix'            => null,
                'email'             => 'marjovicalejado123@gmail.com',
                'password'          => 'teacher123',
                'role_id'           => $teacherRole['id'],
                'is_active'         => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'last_login'        => null
            ];

            try {
                $teacherUserId = $userModel->insert($teacherUserData);
                if ($teacherUserId) {
                    // Create instructor record
                    $instructorRecord = [
                        'user_id'           => $teacherUserId,
                        'department_id'     => null,
                        'specialization'    => null,
                        'hire_date'         => date('Y-m-d'),
                        'employment_status' => 'full_time'
                    ];
                    $instructorModel->insert($instructorRecord);

                    echo "✓ Teacher user created successfully!\n";
                    echo "  Email: marjovicalejado123@gmail.com\n";
                    echo "  Password: teacher123\n";
                    echo "  Name: Ammar Jo Vic\n\n";
                } else {
                    echo "✗ Failed to create teacher user\n";
                    print_r($userModel->errors());
                }
            } catch (\Exception $e) {
                echo "✗ Error creating teacher user: " . $e->getMessage() . "\n";
            }
        } else {
            echo "→ Teacher user already exists. Skipping.\n\n";
        }

        echo "====================\n";
        echo "User Seeder Complete!\n";
        echo "====================\n";
    }
}