<?php

namespace App\Models;

use CodeIgniter\Model;

class EnrollmentModel extends Model
{
    protected $table            = 'enrollments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields = [
        'student_id',
        'course_offering_id',
        'enrollment_date',
        'enrollment_status',
        'enrollment_type',
        'year_level_id',
        'payment_status',
        'enrolled_by',
        'status_changed_at',
        'notes'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'student_id'         => 'required|integer',
        'course_offering_id' => 'required|integer',
        'enrollment_date'    => 'required|valid_date',
        'enrollment_status'  => 'required|in_list[pending,pending_student_approval,pending_teacher_approval,enrolled,rejected,dropped,withdrawn,completed]',
        'enrollment_type'    => 'required|in_list[regular,irregular,retake,cross_enroll,special]',
        'payment_status'     => 'required|in_list[unpaid,partial,paid,scholarship,waived]',
    ];

    protected $validationMessages = [
        'student_id' => [
            'required' => 'Student is required',
        ],
        'course_offering_id' => [
            'required' => 'Course offering is required',
        ],
    ];

    /**
     * Get enrollment with full details
     */
    public function getEnrollmentWithDetails($enrollmentId)
    {
        return $this->select('
                enrollments.*,
                students.student_id_number,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.email,
                courses.course_code,
                courses.title as course_title,
                courses.credits,
                course_offerings.section,
                terms.term_name,
                academic_years.year_name as academic_year,
                semesters.semester_name,
                year_levels.year_level_name
            ')
            ->join('students', 'students.id = enrollments.student_id')
            ->join('users', 'users.id = students.user_id')
            ->join('course_offerings', 'course_offerings.id = enrollments.course_offering_id')
            ->join('courses', 'courses.id = course_offerings.course_id')
            ->join('terms', 'terms.id = course_offerings.term_id')
            ->join('academic_years', 'academic_years.id = terms.academic_year_id')
            ->join('semesters', 'semesters.id = terms.semester_id')
            ->join('year_levels', 'year_levels.id = enrollments.year_level_id', 'left')
            ->where('enrollments.id', $enrollmentId)
            ->first();
    }    /**
     * Get all enrollments for a student with course materials
     */
    public function getStudentEnrollments($studentId, $termId = null)
    {
        $builder = $this->select('
                enrollments.*,
                enrollments.enrollment_date,
                enrollments.enrollment_status,
                course_offerings.id as course_offering_id,
                courses.id as course_id,
                courses.course_code,
                courses.title as course_title,
                courses.description as course_description,
                courses.credits,
                course_offerings.section,
                course_offerings.start_date,
                course_offerings.end_date,
                terms.term_name,
                academic_years.year_name as academic_year,
                semesters.semester_name
            ')
            ->join('course_offerings', 'course_offerings.id = enrollments.course_offering_id')
            ->join('courses', 'courses.id = course_offerings.course_id')
            ->join('terms', 'terms.id = course_offerings.term_id')
            ->join('academic_years', 'academic_years.id = terms.academic_year_id')
            ->join('semesters', 'semesters.id = terms.semester_id')
            ->where('enrollments.student_id', $studentId)
            ->where('enrollments.enrollment_status', 'enrolled'); // Only show enrolled courses

        if ($termId) {
            $builder->where('course_offerings.term_id', $termId);
        }

        return $builder->orderBy('enrollments.enrollment_date', 'DESC')->findAll();
    }

    /**
     * Get enrollments for a course offering
     */
    public function getCourseOfferingEnrollments($courseOfferingId)
    {
        return $this->select('
                enrollments.*,
                students.student_id_number,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.email,
                year_levels.year_level_name
            ')
            ->join('students', 'students.id = enrollments.student_id')
            ->join('users', 'users.id = students.user_id')
            ->join('year_levels', 'year_levels.id = enrollments.year_level_id', 'left')
            ->where('enrollments.course_offering_id', $courseOfferingId)
            ->where('enrollments.enrollment_status', 'enrolled')
            ->orderBy('users.last_name', 'ASC')
            ->findAll();
    }

    /**
     * Check if student is already enrolled in a course offering
     */
    public function isStudentEnrolled($studentId, $courseOfferingId)
    {
        return $this->where('student_id', $studentId)
                    ->where('course_offering_id', $courseOfferingId)
                    ->whereNotIn('enrollment_status', ['dropped', 'withdrawn'])
                    ->countAllResults() > 0;
    }

    /**
     * Check if student is enrolled (alias method for Material controller)
     * This method accepts user_id and course_offering_id
     */
    public function isAlreadyEnrolled($userId, $courseOfferingId)
    {
        // Get student record from user_id
        $db = \Config\Database::connect();
        $student = $db->table('students')
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();
        
        if (!$student) {
            return false;
        }
        
        // Check if student is enrolled using student_id
        return $this->isStudentEnrolled($student['id'], $courseOfferingId);
    }

    /**
     * Get total units enrolled for a student in a term
     */
    public function getStudentTotalUnits($studentId, $termId)
    {
        $result = $this->select('SUM(courses.credits) as total_units')
            ->join('course_offerings', 'course_offerings.id = enrollments.course_offering_id')
            ->join('courses', 'courses.id = course_offerings.course_id')
            ->where('enrollments.student_id', $studentId)
            ->where('course_offerings.term_id', $termId)
            ->where('enrollments.enrollment_status', 'enrolled')
            ->first();

        return $result['total_units'] ?? 0;
    }

    /**
     * Get enrollment statistics for a term
     */
    public function getTermEnrollmentStats($termId)
    {
        $db = \Config\Database::connect();
        
        return [
            'total_enrollments' => $this->join('course_offerings', 'course_offerings.id = enrollments.course_offering_id')
                                        ->where('course_offerings.term_id', $termId)
                                        ->countAllResults(),
            'enrolled' => $this->join('course_offerings', 'course_offerings.id = enrollments.course_offering_id')
                               ->where('course_offerings.term_id', $termId)
                               ->where('enrollments.enrollment_status', 'enrolled')
                               ->countAllResults(),
            'pending' => $this->join('course_offerings', 'course_offerings.id = enrollments.course_offering_id')
                              ->where('course_offerings.term_id', $termId)
                              ->where('enrollments.enrollment_status', 'pending')
                              ->countAllResults(),
            'dropped' => $this->join('course_offerings', 'course_offerings.id = enrollments.course_offering_id')
                              ->where('course_offerings.term_id', $termId)
                              ->where('enrollments.enrollment_status', 'dropped')
                              ->countAllResults(),
        ];
    }

    /**
     * Update enrollment status
     */
    public function updateStatus($enrollmentId, $status, $userId = null)
    {
        $data = [
            'enrollment_status' => $status,
            'status_changed_at' => date('Y-m-d H:i:s'),
        ];

        return $this->update($enrollmentId, $data);
    }

    /**
     * Get pending enrollments for approval
     */
    public function getPendingEnrollments($termId = null)
    {
        $builder = $this->select('
                enrollments.*,
                students.student_id_number,
                users.first_name,
                users.middle_name,
                users.last_name,
                courses.course_code,
                courses.title as course_title,
                course_offerings.section
            ')
            ->join('students', 'students.id = enrollments.student_id')
            ->join('users', 'users.id = students.user_id')
            ->join('course_offerings', 'course_offerings.id = enrollments.course_offering_id')
            ->join('courses', 'courses.id = course_offerings.course_id')
            ->where('enrollments.enrollment_status', 'pending');

        if ($termId) {
            $builder->where('course_offerings.term_id', $termId);
        }

        return $builder->orderBy('enrollments.created_at', 'ASC')->findAll();
    }
}
