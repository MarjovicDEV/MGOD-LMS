<?php

namespace App\Models;

use CodeIgniter\Model;

class InstructorModel extends Model
{
    protected $table            = 'instructors';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'user_id',
        'employee_id',
        'department_id',
        'hire_date',
        'employment_status',
        'specialization'
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
        'user_id'            => 'required|integer',
        'employee_id'        => 'permit_empty|string|max_length[50]',
        'department_id'      => 'permit_empty|integer',
        'hire_date'          => 'permit_empty|valid_date',
        'employment_status'  => 'permit_empty|in_list[full_time,part_time,contract,probationary,retired,resigned]',
        'specialization'     => 'permit_empty|string|max_length[255]'
    ];protected $validationMessages = [
        'user_id' => [
            'required'  => 'User ID is required',
            'is_unique' => 'This user is already registered as an instructor'
        ],
        'employee_id' => [
            'is_unique' => 'This employee ID already exists'
        ],
        'employment_status' => [
            'in_list' => 'Invalid employment status'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateEmployeeId'];
    protected $beforeUpdate   = [];

    /**
     * Auto-generate employee ID if not provided
     */
    protected function generateEmployeeId(array $data)
    {
        if (!isset($data['data']['employee_id']) || empty($data['data']['employee_id'])) {
            // Generate employee ID: EMP-YYYY-XXXXX (e.g., EMP-2025-00001)
            $year = date('Y');
            $lastInstructor = $this->orderBy('id', 'DESC')->first();
            $nextNumber = $lastInstructor ? intval(substr($lastInstructor['employee_id'], -5)) + 1 : 1;
            $data['data']['employee_id'] = 'EMP-' . $year . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        }
        return $data;
    }

    /**
     * Get instructor with complete user information
     * @param int $instructorId - The instructor ID
     * @return array|null
     */
    public function getInstructorComplete($instructorId)
    {
        return $this->select('
                instructors.*,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.suffix,
                users.email,
                users.is_active,
                users.last_login,
                departments.department_name,
                departments.department_code
            ')
            ->join('users', 'users.id = instructors.user_id')
            ->join('departments', 'departments.id = instructors.department_id', 'left')
            ->find($instructorId);
    }

    /**
     * Get instructor by user ID
     * @param int $userId - The user ID
     * @return array|null
     */
    public function getInstructorByUserId($userId)
    {
        return $this->where('user_id', $userId)->first();
    }

    /**
     * Get instructor by employee ID
     * @param string $employeeId - The employee ID
     * @return array|null
     */
    public function getInstructorByEmployeeId($employeeId)
    {
        return $this->where('employee_id', $employeeId)->first();
    }

    /**
     * Get all instructors with complete information
     * @return array
     */
    public function getAllInstructorsComplete()
    {
        return $this->select('
                instructors.*,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.suffix,
                users.email,
                users.is_active,
                users.last_login,
                departments.department_name,
                departments.department_code
            ')
            ->join('users', 'users.id = instructors.user_id')
            ->join('departments', 'departments.id = instructors.department_id', 'left')
            ->orderBy('users.last_name', 'ASC')
            ->findAll();
    }

    /**
     * Get all instructors with user information
     * Alias for getAllInstructorsComplete() for consistency
     * @return array
     */
    public function getInstructorsWithUser()
    {
        return $this->select('
                instructors.*,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.suffix,
                users.email,
                users.is_active,
                departments.department_name,
                departments.department_code
            ')
            ->join('users', 'users.id = instructors.user_id')
            ->join('departments', 'departments.id = instructors.department_id', 'left')
            ->where('users.is_active', 1)
            ->orderBy('users.last_name', 'ASC')
            ->findAll();
    }

    /**
     * Get instructors by department
     * @param int $departmentId - The department ID
     * @return array
     */
    public function getInstructorsByDepartment($departmentId)
    {
        return $this->select('
                instructors.*,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.suffix,
                users.email,
                users.is_active
            ')
            ->join('users', 'users.id = instructors.user_id')
            ->where('instructors.department_id', $departmentId)
            ->where('users.is_active', 1)
            ->orderBy('users.last_name', 'ASC')
            ->findAll();
    }

    /**
     * Get instructors by employment status
     * @param string $status - The employment status
     * @return array
     */
    public function getInstructorsByStatus($status)
    {
        return $this->select('
                instructors.*,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.suffix,
                users.email,
                departments.department_name
            ')
            ->join('users', 'users.id = instructors.user_id')
            ->join('departments', 'departments.id = instructors.department_id', 'left')
            ->where('instructors.employment_status', $status)
            ->orderBy('users.last_name', 'ASC')
            ->findAll();
    }

    /**
     * Get instructor's assigned courses
     * @param int $instructorId - The instructor ID
     * @return array
     */
    public function getInstructorCourses($instructorId)
    {
        $db = \Config\Database::connect();
        return $db->table('course_instructors')
            ->select('
                course_offerings.*,
                courses.title as course_title,
                courses.course_code,
                courses.units,
                terms.term_name,
                academic_years.year_name,
                course_instructors.is_primary
            ')
            ->join('course_offerings', 'course_offerings.id = course_instructors.course_offering_id')
            ->join('courses', 'courses.id = course_offerings.course_id')
            ->join('terms', 'terms.id = course_offerings.term_id', 'left')
            ->join('academic_years', 'academic_years.id = course_offerings.academic_year_id', 'left')
            ->where('course_instructors.instructor_id', $instructorId)
            ->orderBy('course_instructors.is_primary', 'DESC')
            ->orderBy('course_offerings.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get instructor's workload (total courses and units)
     * @param int $instructorId - The instructor ID
     * @param int|null $termId - Optional term ID filter
     * @return array
     */
    public function getInstructorWorkload($instructorId, $termId = null)
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('course_instructors')
            ->select('
                COUNT(DISTINCT course_instructors.course_offering_id) as total_courses,
                SUM(courses.units) as total_units
            ')
            ->join('course_offerings', 'course_offerings.id = course_instructors.course_offering_id')
            ->join('courses', 'courses.id = course_offerings.course_id')
            ->where('course_instructors.instructor_id', $instructorId);
        
        if ($termId) {
            $builder->where('course_offerings.term_id', $termId);
        }
        
        return $builder->get()->getRowArray();
    }

    /**
     * Get instructor's students (all students in their courses)
     * @param int $instructorId - The instructor ID
     * @return array
     */
    public function getInstructorStudents($instructorId)
    {
        $db = \Config\Database::connect();
        return $db->table('course_instructors')
            ->select('
                DISTINCT students.*,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.email,
                year_levels.year_level_name,
                departments.department_name
            ')
            ->join('enrollments', 'enrollments.course_offering_id = course_instructors.course_offering_id')
            ->join('students', 'students.id = enrollments.student_id')
            ->join('users', 'users.id = students.user_id')
            ->join('year_levels', 'year_levels.id = students.year_level_id', 'left')
            ->join('departments', 'departments.id = students.department_id', 'left')
            ->where('course_instructors.instructor_id', $instructorId)
            ->where('enrollments.enrollment_status', 'enrolled')
            ->orderBy('users.last_name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Update instructor's employment status
     * @param int $instructorId - The instructor ID
     * @param string $status - The new employment status
     * @return bool
     */
    public function updateEmploymentStatus($instructorId, $status)
    {
        return $this->update($instructorId, [
            'employment_status' => $status
        ]);
    }

    /**
     * Get instructor statistics
     * @param int $instructorId - The instructor ID
     * @return array
     */
    public function getInstructorStatistics($instructorId)
    {
        $db = \Config\Database::connect();
        
        // Count total courses taught
        $totalCourses = $db->table('course_instructors')
            ->where('instructor_id', $instructorId)
            ->countAllResults();
        
        // Count active courses
        $activeCourses = $db->table('course_instructors')
            ->join('course_offerings', 'course_offerings.id = course_instructors.course_offering_id')
            ->where('course_instructors.instructor_id', $instructorId)
            ->where('course_offerings.status', 'active')
            ->countAllResults();
        
        // Count total students
        $totalStudents = $db->table('course_instructors')
            ->select('COUNT(DISTINCT enrollments.student_id) as total')
            ->join('enrollments', 'enrollments.course_offering_id = course_instructors.course_offering_id')
            ->where('course_instructors.instructor_id', $instructorId)
            ->where('enrollments.enrollment_status', 'enrolled')
            ->get()
            ->getRow()
            ->total ?? 0;
        
        return [
            'total_courses' => $totalCourses,
            'active_courses' => $activeCourses,
            'total_students' => $totalStudents
        ];
    }

    /**
     * Get instructor's full name
     * @param int $instructorId - The instructor ID
     * @return string|null
     */
    public function getInstructorFullName($instructorId)
    {
        $instructor = $this->select('
                users.first_name,
                users.middle_name,
                users.last_name,
                users.suffix
            ')
            ->join('users', 'users.id = instructors.user_id')
            ->find($instructorId);
        
        if (!$instructor) {
            return null;
        }
        
        $name = trim($instructor['first_name'] . ' ' . ($instructor['middle_name'] ?? '') . ' ' . $instructor['last_name']);
        if (!empty($instructor['suffix'])) {
            $name .= ' ' . $instructor['suffix'];
        }
        
        return $name;
    }

    /**
     * Check if instructor is assigned to any courses
     * @param int $instructorId - The instructor ID
     * @return bool
     */
    public function hasAssignedCourses($instructorId)
    {
        $db = \Config\Database::connect();
        $count = $db->table('course_instructors')
            ->where('instructor_id', $instructorId)
            ->countAllResults();
        
        return $count > 0;
    }

    /**
     * Get active instructors (all currently employed, excluding retired and resigned)
     * @return array
     */
    public function getActiveInstructors()
    {
        return $this->select('
                instructors.*,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.suffix,
                users.email,
                departments.department_name
            ')
            ->join('users', 'users.id = instructors.user_id')
            ->join('departments', 'departments.id = instructors.department_id', 'left')
            ->whereIn('instructors.employment_status', ['full_time', 'part_time', 'contract', 'probationary'])
            ->where('users.is_active', 1)
            ->orderBy('users.last_name', 'ASC')
            ->findAll();
    }

    /**
     * Search instructors by name, employee ID, or specialization
     * @param string $query - The search query
     * @return array
     */
    public function searchInstructors($query)
    {
        return $this->select('
                instructors.*,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.suffix,
                users.email,
                departments.department_name
            ')
            ->join('users', 'users.id = instructors.user_id')
            ->join('departments', 'departments.id = instructors.department_id', 'left')
            ->groupStart()
                ->like('users.first_name', $query)
                ->orLike('users.last_name', $query)
                ->orLike('instructors.employee_id', $query)
                ->orLike('instructors.specialization', $query)
            ->groupEnd()
            ->orderBy('users.last_name', 'ASC')
            ->findAll();
    }

    /**
     * Get instructors by specialization
     * @param string $specialization - The specialization to search for
     * @return array
     */
    public function getInstructorsBySpecialization($specialization)
    {
        return $this->select('
                instructors.*,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.suffix,
                users.email,
                departments.department_name
            ')
            ->join('users', 'users.id = instructors.user_id')
            ->join('departments', 'departments.id = instructors.department_id', 'left')
            ->like('instructors.specialization', $specialization)
            ->where('users.is_active', 1)
            ->orderBy('users.last_name', 'ASC')
            ->findAll();
    }    /**
     * Get department head instructors
     * @return array
     */
    public function getDepartmentHeads()
    {
        $db = \Config\Database::connect();
        return $db->table('departments')
            ->select('
                departments.*,
                instructors.employee_id,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.suffix,
                users.email
            ')
            ->join('instructors', 'instructors.id = departments.head_user_id', 'left')
            ->join('users', 'users.id = instructors.user_id', 'left')
            ->where('departments.head_user_id IS NOT NULL')
            ->get()
            ->getResultArray();
    }
}
