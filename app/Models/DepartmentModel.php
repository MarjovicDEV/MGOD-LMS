<?php

namespace App\Models;

use CodeIgniter\Model;

class DepartmentModel extends Model
{
    protected $table            = 'departments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'department_code',
        'department_name',
        'description',
        'head_user_id',
        'is_active'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'department_code' => 'required|string|max_length[20]|is_unique[departments.department_code,id,{id}]',
        'department_name' => 'required|string|max_length[150]',
        'description'     => 'permit_empty|string',
        'head_user_id'    => 'permit_empty|integer',
        'is_active'       => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'department_code' => [
            'required'  => 'Department code is required',
            'is_unique' => 'This department code already exists'
        ],
        'department_name' => [
            'required' => 'Department name is required'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;    /**
     * Get all departments with head information
     * Joins with instructors and users tables to get department head details
     */
    public function getDepartmentsWithHead()
    {
        return $this->select('
                departments.*,
                instructors.id as head_instructor_id,
                instructors.employee_id,
                users.id as head_user_id,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.email,
                CONCAT(users.first_name, " ", users.last_name) as head_name
            ')
            ->join('instructors', 'instructors.id = departments.head_user_id', 'left')
            ->join('users', 'users.id = instructors.user_id', 'left')
            ->where('departments.is_active', 1)
            ->findAll();
    }

    /**
     * Get department by code
     */
    public function getDepartmentByCode($code)
    {
        return $this->where('department_code', $code)->first();
    }    /**
     * Assign department head
     * @param int $departmentId - The department ID
     * @param int $instructorId - The instructor ID (from instructors table, not users table)
     * @return bool
     */
    public function assignHead($departmentId, $instructorId)
    {
        return $this->update($departmentId, ['head_user_id' => $instructorId]);
    }

    /**
     * Remove department head
     * @param int $departmentId - The department ID
     * @return bool
     */
    public function removeHead($departmentId)
    {
        return $this->update($departmentId, ['head_user_id' => null]);
    }

    /**
     * Get department with complete head information
     * @param int $departmentId - The department ID
     * @return array|null
     */
    public function getDepartmentWithHead($departmentId)
    {
        return $this->select('
                departments.*,
                instructors.id as head_instructor_id,
                instructors.employee_id,
                instructors.specialization,
                users.id as head_user_id,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.email,
                CONCAT(users.first_name, " ", users.last_name) as head_name
            ')
            ->join('instructors', 'instructors.id = departments.head_user_id', 'left')
            ->join('users', 'users.id = instructors.user_id', 'left')
            ->find($departmentId);
    }

    /**
     * Get all courses in department
     */
    public function getDepartmentCourses($departmentId)
    {
        $db = \Config\Database::connect();
        return $db->table('courses')
                  ->where('department_id', $departmentId)
                  ->where('is_active', 1)
                  ->get()
                  ->getResultArray();
    }

    /**
     * Get all active departments
     * @return array
     */
    public function getActiveDepartments()
    {
        return $this->where('is_active', 1)
                    ->orderBy('department_name', 'ASC')
                    ->findAll();
    }

    /**
     * Get department statistics
     * @param int $departmentId - The department ID
     * @return array
     */
    public function getDepartmentStatistics($departmentId)
    {
        $db = \Config\Database::connect();
        
        // Count total students in department
        $totalStudents = $db->table('students')
            ->where('department_id', $departmentId)
            ->where('deleted_at', null)
            ->countAllResults();
        
        // Count total instructors in department
        $totalInstructors = $db->table('instructors')
            ->where('department_id', $departmentId)
            ->where('deleted_at', null)
            ->countAllResults();
        
        // Count total courses in department
        $totalCourses = $db->table('courses')
            ->where('department_id', $departmentId)
            ->countAllResults();
        
        return [
            'total_students' => $totalStudents,
            'total_instructors' => $totalInstructors,
            'total_courses' => $totalCourses
        ];
    }

    /**
     * Get all students in department
     * @param int $departmentId - The department ID
     * @return array
     */
    public function getDepartmentStudents($departmentId)
    {
        $db = \Config\Database::connect();
        return $db->table('students')
            ->select('students.*, users.first_name, users.middle_name, users.last_name, users.email, year_levels.year_level_name')
            ->join('users', 'users.id = students.user_id', 'left')
            ->join('year_levels', 'year_levels.id = students.year_level_id', 'left')
            ->where('students.department_id', $departmentId)
            ->where('students.deleted_at', null)
            ->get()
            ->getResultArray();
    }

    /**
     * Get all instructors in department
     * @param int $departmentId - The department ID
     * @return array
     */
    public function getDepartmentInstructors($departmentId)
    {
        $db = \Config\Database::connect();
        return $db->table('instructors')
            ->select('instructors.*, users.first_name, users.middle_name, users.last_name, users.email')
            ->join('users', 'users.id = instructors.user_id', 'left')
            ->where('instructors.department_id', $departmentId)
            ->where('instructors.deleted_at', null)
            ->get()
            ->getResultArray();
    }

    /**
     * Check if department has any students
     * @param int $departmentId - The department ID
     * @return bool
     */
    public function hasStudents($departmentId)
    {
        $db = \Config\Database::connect();
        $count = $db->table('students')
            ->where('department_id', $departmentId)
            ->where('deleted_at', null)
            ->countAllResults();
        
        return $count > 0;
    }

    /**
     * Check if department has any instructors
     * @param int $departmentId - The department ID
     * @return bool
     */
    public function hasInstructors($departmentId)
    {
        $db = \Config\Database::connect();
        $count = $db->table('instructors')
            ->where('department_id', $departmentId)
            ->where('deleted_at', null)
            ->countAllResults();
        
        return $count > 0;
    }

    /**
     * Check if department has any courses
     * @param int $departmentId - The department ID
     * @return bool
     */
    public function hasCourses($departmentId)
    {
        $db = \Config\Database::connect();
        $count = $db->table('courses')
            ->where('department_id', $departmentId)
            ->countAllResults();
        
        return $count > 0;
    }

    /**
     * Deactivate department (soft disable)
     * @param int $departmentId - The department ID
     * @return bool
     */
    public function deactivate($departmentId)
    {
        return $this->update($departmentId, ['is_active' => 0]);
    }

    /**
     * Activate department
     * @param int $departmentId - The department ID
     * @return bool
     */
    public function activate($departmentId)
    {
        return $this->update($departmentId, ['is_active' => 1]);
    }
}