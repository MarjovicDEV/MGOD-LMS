<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\TermModel;
use App\Models\SemesterModel;
use App\Models\AcademicYearModel;

class TermSeeder extends Seeder
{
    public function run()
    {
        $termModel = new TermModel();
        $semesterModel = new SemesterModel();
        $academicYearModel = new AcademicYearModel();

        // Check if table already has data
        $existing = $termModel->countAll();
        if ($existing > 0) {
            echo "Terms table already contains data. Skipping seeder.\n";
            return;
        }

        // Get semesters
        $firstSemester = $semesterModel->where('semester_name', '1st Semester')->first();
        $secondSemester = $semesterModel->where('semester_name', '2nd Semester')->first();
        $summerSemester = $semesterModel->where('semester_name', 'Summer')->first();

        if (!$firstSemester || !$secondSemester || !$summerSemester) {
            echo "Error: Semesters not found. Please run SemesterSeeder first.\n";
            return;
        }        // Get the current active academic year
        $academicYear = $academicYearModel->where('is_active', 1)->first();
        
        if (!$academicYear) {
            echo "Error: No active academic year found. Please create an academic year first.\n";
            return;
        }

        echo "Creating terms for Academic Year: {$academicYear['year_name']}...\n";/**
         * Term Structure Explanation:
         * 
         * Each semester has three term options:
         * - Term 1: First half of semester (for subjects split into two parts)
         * - Term 2: Second half of semester (for subjects split into two parts)
         * - Term 3: FULL SEMESTER (for major subjects that run the entire semester)
         * 
         * This applies to:
         * - 1st Semester: Term 1, Term 2, Term 3 (full semester for major subjects)
         * - 2nd Semester: Term 1, Term 2, Term 3 (full semester for major subjects)
         * - Summer: Term 1 OR Term 3 (short intensive full summer term)
         * 
         * Enrollment dates (enrollment_start and enrollment_end):
         * - enrollment_start: When students can BEGIN enrolling for this term
         * - enrollment_end: Deadline for enrollment (last day to enroll)
         * - These dates are set by admin and typically occur BEFORE the term starts
         * - Example: Enrollment for 1st Sem Term 1 might be June 1-15, but classes start June 20
         */        
        $terms = [
            // ===== 1st SEMESTER =====
            // 1st Semester - Term 1 (First Half)
            [
                'academic_year_id' => $academicYear['id'],
                'semester_id'      => $firstSemester['id'],
                'term_name'        => 'Term 1',
                'term_order'       => 1,
                'start_date'       => null, // Admin will set actual dates
                'end_date'         => null,
                'enrollment_start' => null, // When enrollment opens for this term
                'enrollment_end'   => null, // When enrollment closes for this term
                'is_active'        => 1,
                'description'      => 'First half of 1st Semester'
            ],
            // 1st Semester - Term 2 (Second Half)
            [
                'academic_year_id' => $academicYear['id'],
                'semester_id'      => $firstSemester['id'],
                'term_name'        => 'Term 2',
                'term_order'       => 2,
                'start_date'       => null,
                'end_date'         => null,
                'enrollment_start' => null,
                'enrollment_end'   => null,
                'is_active'        => 1,
                'description'      => 'Second half of 1st Semester'
            ],
            // 1st Semester - Term 3 (FULL SEMESTER - for major subjects)
            [
                'academic_year_id' => $academicYear['id'],
                'semester_id'      => $firstSemester['id'],
                'term_name'        => 'Term 3',
                'term_order'       => 3,
                'start_date'       => null,
                'end_date'         => null,
                'enrollment_start' => null,
                'enrollment_end'   => null,
                'is_active'        => 1,
                'description'      => 'Full 1st Semester (for major subjects)'
            ],

            // ===== 2nd SEMESTER =====
            // 2nd Semester - Term 1 (First Half)
            [
                'academic_year_id' => $academicYear['id'],
                'semester_id'      => $secondSemester['id'],
                'term_name'        => 'Term 1',
                'term_order'       => 1,
                'start_date'       => null,
                'end_date'         => null,
                'enrollment_start' => null,
                'enrollment_end'   => null,
                'is_active'        => 1,
                'description'      => 'First half of 2nd Semester'
            ],
            // 2nd Semester - Term 2 (Second Half)
            [
                'academic_year_id' => $academicYear['id'],
                'semester_id'      => $secondSemester['id'],
                'term_name'        => 'Term 2',
                'term_order'       => 2,
                'start_date'       => null,
                'end_date'         => null,
                'enrollment_start' => null,
                'enrollment_end'   => null,
                'is_active'        => 1,
                'description'      => 'Second half of 2nd Semester'
            ],
            // 2nd Semester - Term 3 (FULL SEMESTER - for major subjects)
            [
                'academic_year_id' => $academicYear['id'],
                'semester_id'      => $secondSemester['id'],
                'term_name'        => 'Term 3',
                'term_order'       => 3,
                'start_date'       => null,
                'end_date'         => null,
                'enrollment_start' => null,
                'enrollment_end'   => null,
                'is_active'        => 1,
                'description'      => 'Full 2nd Semester (for major subjects)'
            ],

            // ===== SUMMER SEMESTER =====
            // Summer - Term 3 (Full Summer Term)
            [
                'academic_year_id' => $academicYear['id'],
                'semester_id'      => $summerSemester['id'],
                'term_name'        => 'Term 3',
                'term_order'       => 3,
                'start_date'       => null,
                'end_date'         => null,
                'enrollment_start' => null,
                'enrollment_end'   => null,
                'is_active'        => 1,
                'description'      => 'Full summer term (intensive short course)'
            ]
        ];

        echo "\nSeeding terms...\n";
        
        foreach ($terms as $term) {
            try {
                $result = $termModel->insert($term);
                
                if ($result) {
                    $semesterName = $term['semester_id'] == $firstSemester['id'] ? '1st Semester' 
                                  : ($term['semester_id'] == $secondSemester['id'] ? '2nd Semester' : 'Summer');
                    echo "✓ Created: {$semesterName} - {$term['term_name']}\n";
                } else {
                    echo "✗ Failed to create: {$term['term_name']}\n";
                    print_r($termModel->errors());
                }
            } catch (\Exception $e) {
                echo "✗ Error creating term: " . $e->getMessage() . "\n";
            }
        }

        echo "\nTerm seeding completed successfully!\n";
        echo "Total terms created: " . count($terms) . "\n";
        echo "\nNote: Dates are null. Admin should set start_date, end_date, enrollment_start, and enrollment_end via admin panel.\n";
    }
}
