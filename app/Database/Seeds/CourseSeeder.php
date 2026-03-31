<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run()
    {
        $this->call(CategorySeeder::class);

        // Sample course data - Updated for 2025-2026 Academic Year
        $courses = [
            [
                'title' => 'Introduction to Programming',
                'description' => 'Learn the fundamentals of programming with hands-on examples and projects.',
                'course_code' => 'CS101',
                'category_id' => 1, // Computer Science
                'credits' => 3,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],            [
                'title' => 'Web Development Basics',
                'description' => 'Master HTML, CSS, and JavaScript to build modern web applications.',
                'course_code' => 'WEB101',
                'category_id' => 2, // Web Development
                'credits' => 4,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'title' => 'Database Design',
                'description' => 'Learn relational database concepts, SQL, and database optimization.',
                'course_code' => 'DB201',
                'category_id' => 1, // Computer Science
                'credits' => 3,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'title' => 'Data Structures and Algorithms',
                'description' => 'Master fundamental data structures and algorithms for efficient problem solving.',
                'course_code' => 'CS201',
                'category_id' => 1, // Computer Science
                'credits' => 4,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'title' => 'Software Engineering Principles',
                'description' => 'Learn software development lifecycle, design patterns, and best practices.',
                'course_code' => 'SE301',
                'category_id' => 1, // Computer Science
                'credits' => 3,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ];

        // Insert courses into database
        foreach ($courses as $course) {
            $this->db->table('courses')->insert($course);
        }

        echo "Courses seeded successfully!\n";
    }
}