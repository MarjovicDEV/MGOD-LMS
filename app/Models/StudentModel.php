<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentModel extends Model
{
    protected $table            = 'students';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'user_id',
        'student_id_number',
        'department_id',
        'year_level_id',
        'program_id',
        'section',
        'enrollment_date',
        'enrollment_status',
        'guardian_name',
        'guardian_contact',
        'scholarship_status',
        'total_units'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';    // Validation
    protected $validationRules = [
        'user_id'            => 'required|integer|is_unique[students.user_id,id,{id},deleted_at,NULL]',
        'student_id_number'  => 'permit_empty|string|max_length[50]|is_unique[students.student_id_number,id,{id},deleted_at,NULL]',
        'department_id'      => 'permit_empty|integer',
        'year_level_id'      => 'permit_empty|integer',
        'program_id'         => 'permit_empty|integer',
        'section'            => 'permit_empty|string|max_length[50]',
        'enrollment_date'    => 'permit_empty|valid_date',
        'enrollment_status'  => 'permit_empty|in_list[enrolled,graduated,dropped,on_leave,suspended]',
        'guardian_name'      => 'permit_empty|string|max_length[255]',
        'guardian_contact'   => 'permit_empty|string|max_length[20]',
        'scholarship_status' => 'permit_empty|string|max_length[100]',
        'total_units'        => 'permit_empty|integer'
    ];protected $validationMessages = [
        'user_id' => [
            'required'  => 'User ID is required',
            'is_unique' => 'This user is already registered as a student'
        ],
        'student_id_number' => [
            'is_unique' => 'This student ID number already exists'
        ],
        'enrollment_status' => [
            'in_list' => 'Invalid enrollment status'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateStudentId'];
    protected $beforeUpdate   = [];

    /**
     * Auto-generate student ID number if not provided
     */
    protected function generateStudentId(array $data)
    {
        if (!isset($data['data']['student_id_number']) || empty($data['data']['student_id_number'])) {
            $year = date('Y');
            $lastStudent = $this->orderBy('id', 'DESC')->first();
            $nextNumber = $lastStudent ? intval(substr($lastStudent['student_id_number'], -5)) + 1 : 1;
            $data['data']['student_id_number'] = $year . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        }
        return $data;
    }

    /**
     * Get student with complete user information
     * @param int $studentId - The student ID
     * @return array|null
     */
    public function getStudentComplete($studentId)
    {
        return $this->select('
                students.*,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.suffix,
                users.email,
                users.user_code,
                users.is_active,
                departments.department_name,
                departments.department_code,
                year_levels.year_level_name,
                programs.program_code,
                programs.program_name
            ')
            ->join('users', 'users.id = students.user_id', 'left')
            ->join('departments', 'departments.id = students.department_id', 'left')
            ->join('year_levels', 'year_levels.id = students.year_level_id', 'left')
            ->join('programs', 'programs.id = students.program_id', 'left')
            ->find($studentId);
    }

    /**
     * Get student by user ID
     * @param int $userId - The user ID
     * @return array|null
     */
    public function getStudentByUserId($userId)
    {
        return $this->where('user_id', $userId)->first();
    }

    /**
     * Get student by student ID number
     * @param string $studentIdNumber - The student ID number
     * @return array|null
     */
    public function getStudentByIdNumber($studentIdNumber)
    {
        return $this->where('student_id_number', $studentIdNumber)->first();
    }

    /**
     * Get all students with complete information
     * @return array
     */
    public function getAllStudentsComplete()
    {
        return $this->select('
                students.*,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.suffix,
                users.email,
                users.user_code,
                users.is_active,
                departments.department_name,
                departments.department_code,
                year_levels.year_level_name,
                programs.program_code,
                programs.program_name
            ')
            ->join('users', 'users.id = students.user_id', 'left')
            ->join('departments', 'departments.id = students.department_id', 'left')
            ->join('year_levels', 'year_levels.id = students.year_level_id', 'left')
            ->join('programs', 'programs.id = students.program_id', 'left')
            ->where('users.is_active', 1)
            ->orderBy('students.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get students by department
     * @param int $departmentId - The department ID
     * @return array
     */
    public function getStudentsByDepartment($departmentId)
    {
        return $this->select('
                students.*,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.email,
                year_levels.year_level_name
            ')
            ->join('users', 'users.id = students.user_id', 'left')
            ->join('year_levels', 'year_levels.id = students.year_level_id', 'left')
            ->where('students.department_id', $departmentId)
            ->where('users.is_active', 1)
            ->orderBy('year_levels.year_level_name', 'ASC')
            ->orderBy('students.section', 'ASC')
            ->findAll();
    }

    /**
     * Get students by year level
     * @param int $yearLevelId - The year level ID
     * @return array
     */
    public function getStudentsByYearLevel($yearLevelId)
    {
        return $this->select('
                students.*,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.email,
                departments.department_name,
                programs.program_code,
                programs.program_name
            ')
            ->join('users', 'users.id = students.user_id', 'left')
            ->join('departments', 'departments.id = students.department_id', 'left')
            ->join('programs', 'programs.id = students.program_id', 'left')
            ->where('students.year_level_id', $yearLevelId)
            ->where('users.is_active', 1)
            ->orderBy('departments.department_name', 'ASC')
            ->orderBy('students.section', 'ASC')
            ->findAll();
    }

    /**
     * Get students by program
     * @param int $programId - The program ID
     * @return array
     */
    public function getStudentsByProgram($programId)
    {
        return $this->select('
                students.*,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.email,
                departments.department_name,
                year_levels.year_level_name
            ')
            ->join('users', 'users.id = students.user_id', 'left')
            ->join('departments', 'departments.id = students.department_id', 'left')
            ->join('year_levels', 'year_levels.id = students.year_level_id', 'left')
            ->where('students.program_id', $programId)
            ->where('users.is_active', 1)
            ->orderBy('year_levels.year_level_order', 'ASC')
            ->orderBy('students.section', 'ASC')
            ->findAll();
    }

    /**
     * Get students by department and year level
     * @param int $departmentId - The department ID
     * @param int $yearLevelId - The year level ID
     * @return array
     */
    public function getStudentsByDepartmentAndYear($departmentId, $yearLevelId)
    {
        return $this->select('
                students.*,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.email
            ')
            ->join('users', 'users.id = students.user_id', 'left')
            ->where('students.department_id', $departmentId)
            ->where('students.year_level_id', $yearLevelId)
            ->where('users.is_active', 1)
            ->orderBy('students.section', 'ASC')
            ->orderBy('users.last_name', 'ASC')
            ->findAll();
    }

    /**
     * Get students by section
     * @param string $section - The section name
     * @param int|null $yearLevelId - Optional year level ID
     * @return array
     */
    public function getStudentsBySection($section, $yearLevelId = null)
    {
        $builder = $this->select('
                students.*,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.email,
                departments.department_name,
                year_levels.year_level_name
            ')
            ->join('users', 'users.id = students.user_id', 'left')
            ->join('departments', 'departments.id = students.department_id', 'left')
            ->join('year_levels', 'year_levels.id = students.year_level_id', 'left')
            ->where('students.section', $section)
            ->where('users.is_active', 1);

        if ($yearLevelId) {
            $builder->where('students.year_level_id', $yearLevelId);
        }

        return $builder->orderBy('users.last_name', 'ASC')->findAll();
    }

    /**
     * Get students by enrollment status
     * @param string $status - The enrollment status
     * @return array
     */
    public function getStudentsByStatus($status)
    {
        return $this->select('
                students.*,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.email,
                departments.department_name,
                year_levels.year_level_name,
                programs.program_code,
                programs.program_name
            ')
            ->join('users', 'users.id = students.user_id', 'left')
            ->join('departments', 'departments.id = students.department_id', 'left')
            ->join('year_levels', 'year_levels.id = students.year_level_id', 'left')
            ->join('programs', 'programs.id = students.program_id', 'left')
            ->where('students.enrollment_status', $status)
            ->orderBy('students.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get student enrollments (courses they're enrolled in)
     * @param int $studentId - The student ID
     * @return array
     */
    public function getStudentEnrollments($studentId)
    {
        $db = \Config\Database::connect();
        return $db->table('enrollments')
            ->select('
                enrollments.*,
                course_offerings.id as offering_id,
                courses.title as course_title,
                courses.course_code,
                courses.credits,
                courses.description as course_description,
                terms.term_name,
                semesters.semester_name,
                academic_years.year_name
            ')
            ->join('course_offerings', 'course_offerings.id = enrollments.course_offering_id', 'left')
            ->join('courses', 'courses.id = course_offerings.course_id', 'left')
            ->join('terms', 'terms.id = course_offerings.term_id', 'left')
            ->join('semesters', 'semesters.id = terms.semester_id', 'left')
            ->join('academic_years', 'academic_years.id = terms.academic_year_id', 'left')
            ->where('enrollments.student_id', $studentId)
            ->orderBy('enrollments.enrollment_date', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Update student enrollment status
     * @param int $studentId - The student ID
     * @param string $status - The new status
     * @return bool
     */
    public function updateEnrollmentStatus($studentId, $status)
    {
        return $this->update($studentId, [
            'enrollment_status' => $status
        ]);
    }

    /**
     * Get student statistics
     * @param int $studentId - The student ID
     * @return array
     */
    public function getStudentStatistics($studentId)
    {
        $db = \Config\Database::connect();
        
        // Count total enrollments
        $totalEnrollments = $db->table('enrollments')
            ->where('student_id', $studentId)
            ->countAllResults();
        
        // Count completed courses
        $completedCourses = $db->table('enrollments')
            ->where('student_id', $studentId)
            ->where('enrollment_status', 'completed')
            ->countAllResults();
        
        // Count active enrollments
        $activeEnrollments = $db->table('enrollments')
            ->where('student_id', $studentId)
            ->where('enrollment_status', 'enrolled')
            ->countAllResults();
        
        // Get total units earned
        $student = $this->find($studentId);
        $totalUnits = $student['total_units'] ?? 0;
        
        return [
            'total_enrollments' => $totalEnrollments,
            'completed_courses' => $completedCourses,
            'active_enrollments' => $activeEnrollments,
            'total_units' => $totalUnits
        ];
    }

    /**
     * Get student full name
     * @param int $studentId - The student ID
     * @return string|null
     */
    public function getStudentFullName($studentId)
    {
        $student = $this->select('
                users.first_name,
                users.middle_name,
                users.last_name,
                users.suffix
            ')
            ->join('users', 'users.id = students.user_id', 'left')
            ->find($studentId);

        if (!$student) {
            return null;
        }

        $name = trim($student['first_name'] . ' ' . ($student['middle_name'] ?? '') . ' ' . $student['last_name']);
        if (!empty($student['suffix'])) {
            $name .= ' ' . $student['suffix'];
        }

        return $name;
    }

    /**
     * Check if student has scholarship
     * @param int $studentId - The student ID
     * @return bool
     */
    public function hasScholarship($studentId)
    {
        $student = $this->find($studentId);
        return $student && !empty($student['scholarship_status']);
    }

    /**
     * Get students with scholarships
     * @return array
     */
    public function getScholarshipStudents()
    {
        return $this->select('
                students.*,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.email,
                departments.department_name,
                year_levels.year_level_name
            ')
            ->join('users', 'users.id = students.user_id', 'left')
            ->join('departments', 'departments.id = students.department_id', 'left')
            ->join('year_levels', 'year_levels.id = students.year_level_id', 'left')
            ->where('students.scholarship_status IS NOT NULL')
            ->where('students.scholarship_status !=', '')
            ->orderBy('students.scholarship_status', 'ASC')
            ->findAll();
    }

    /**
     * Promote students to next year level
     * @param array $studentIds - Array of student IDs to promote
     * @return bool
     */
    public function promoteStudents(array $studentIds)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($studentIds as $studentId) {
            $student = $this->find($studentId);
            if ($student && $student['year_level_id']) {
                // Get next year level
                $nextYearLevel = $db->table('year_levels')
                    ->where('id >', $student['year_level_id'])
                    ->orderBy('id', 'ASC')
                    ->get()
                    ->getRowArray();

                if ($nextYearLevel) {
                    $this->update($studentId, [
                        'year_level_id' => $nextYearLevel['id']
                    ]);
                } else {
                    // If no next year level, mark as graduated
                    $this->update($studentId, [
                        'enrollment_status' => 'graduated'
                    ]);
                }
            }
        }

        $db->transComplete();
        return $db->transStatus();
    }

    /**
     * Search students by name or student ID
     * @param string $query - Search query
     * @return array
     */
    public function searchStudents($query)
    {
        return $this->select('
                students.*,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.email,
                departments.department_name,
                year_levels.year_level_name,
                programs.program_code,
                programs.program_name
            ')
            ->join('users', 'users.id = students.user_id', 'left')
            ->join('departments', 'departments.id = students.department_id', 'left')
            ->join('year_levels', 'year_levels.id = students.year_level_id', 'left')
            ->join('programs', 'programs.id = students.program_id', 'left')
            ->groupStart()
                ->like('users.first_name', $query)
                ->orLike('users.last_name', $query)
                ->orLike('users.email', $query)
                ->orLike('students.student_id_number', $query)
                ->orLike('programs.program_code', $query)
                ->orLike('programs.program_name', $query)
            ->groupEnd()
            ->where('users.is_active', 1)
            ->orderBy('users.last_name', 'ASC')
            ->findAll();
    }
}
