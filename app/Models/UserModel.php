<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;    protected $allowedFields    = [
        'user_code',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'email',
        'password',
        'role_id',
        'is_active',
        'last_login',
        'email_verified_at'
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
        'user_code'      => 'permit_empty|string|max_length[50]',
        'email'          => 'permit_empty|valid_email|max_length[100]',
        'password'       => 'permit_empty|min_length[6]',
        'first_name'     => 'permit_empty|string|max_length[100]',
        'middle_name'    => 'permit_empty|string|max_length[100]',
        'last_name'      => 'permit_empty|string|max_length[100]',
        'suffix'         => 'permit_empty|string|max_length[20]',
        'role_id'        => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'user_code' => [
            'is_unique' => 'This user code already exists'
        ],
        'email' => [
            'valid_email' => 'Please provide a valid email address',
            'is_unique'   => 'This email is already registered'
        ],
        'password' => [
            'min_length' => 'Password must be at least 6 characters'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['hashPassword'];
    protected $beforeUpdate   = ['hashPassword'];

    /**
     * Hash password before saving
     */
    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    /**
     * Get user with role information
     */
    public function getUserWithRole($userId)
    {
        return $this->select('users.*, roles.role_name')
                    ->join('roles', 'roles.id = users.role_id')
                    ->find($userId);
    }    /**
     * Get user with all relationships (including student/instructor data)
     */
    public function getUserComplete($userId)
    {
        return $this->select('
                users.*, 
                roles.role_name,
                students.student_id_number,
                students.department_id as student_department_id,
                students.year_level_id,
                students.section,
                students.enrollment_status,
                instructors.employee_id,
                instructors.department_id as instructor_department_id,
                instructors.specialization,
                instructors.employment_status,
                departments.department_name,
                year_levels.year_level_name
            ')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->join('students', 'students.user_id = users.id', 'left')
            ->join('instructors', 'instructors.user_id = users.id', 'left')
            ->join('departments', 'departments.id = COALESCE(students.department_id, instructors.department_id)', 'left')
            ->join('year_levels', 'year_levels.id = students.year_level_id', 'left')
            ->find($userId);
    }

    /**
     * Get all users by role
     */
    public function getUsersByRole($roleId)
    {
        return $this->where('role_id', $roleId)
                    ->where('is_active', 1)
                    ->findAll();
    }    /**
     * Get all students
     */
    public function getStudents()
    {
        return $this->select('
                users.*,
                students.student_id_number,
                students.section,
                students.enrollment_status,
                year_levels.year_level_name,
                departments.department_name
            ')
            ->join('roles', 'roles.id = users.role_id')
            ->join('students', 'students.user_id = users.id', 'left')
            ->join('year_levels', 'year_levels.id = students.year_level_id', 'left')
            ->join('departments', 'departments.id = students.department_id', 'left')
            ->where('roles.role_name', 'Student')
            ->where('users.is_active', 1)
            ->findAll();
    }    /**
     * Get all instructors
     */
    public function getInstructors()
    {
        return $this->select('
                users.*,
                instructors.employee_id,
                instructors.department_id,
                instructors.specialization,
                instructors.employment_status,
                departments.department_name
            ')
            ->join('roles', 'roles.id = users.role_id')
            ->join('instructors', 'instructors.user_id = users.id', 'left')
            ->join('departments', 'departments.id = instructors.department_id', 'left')
            ->whereIn('roles.role_name', ['Teacher', 'Instructor'])
            ->where('users.is_active', 1)
            ->findAll();
    }

    /**
     * Verify user credentials
     */
    public function verifyCredentials($email, $password)
    {
        $user = $this->where('email', $email)
                     ->where('is_active', 1)
                     ->first();

        if ($user && password_verify($password, $user['password'])) {
            // Update last login
            $this->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
            return $user;
        }

        return false;
    }

    /**
     * Get user by email
     */
    public function getUserByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Get user by user code
     */
    public function getUserByCode($userCode)
    {
        return $this->where('user_code', $userCode)->first();
    }

    /**
     * Update user profile
     */
    public function updateProfile($userId, $data)
    {
        // Remove password from data if empty
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        }

        return $this->update($userId, $data);
    }

    /**
     * Check if user has role
     */
    public function hasRole($userId, $roleName)
    {
        $user = $this->select('users.*, roles.role_name')
                     ->join('roles', 'roles.id = users.role_id')
                     ->find($userId);

        return $user && $user['role_name'] === $roleName;
    }    /**
     * Get students by department and year level
     */
    public function getStudentsByDepartmentAndYear($departmentId, $yearLevelId)
    {
        return $this->select('users.*, students.student_id_number, students.section')
                    ->join('roles', 'roles.id = users.role_id')
                    ->join('students', 'students.user_id = users.id')
                    ->where('roles.role_name', 'Student')
                    ->where('students.department_id', $departmentId)
                    ->where('students.year_level_id', $yearLevelId)
                    ->where('users.is_active', 1)
                    ->findAll();
    }

    /**
     * Get full name of user
     */
    public function getFullName($userId)
    {
        $user = $this->find($userId);
        if (!$user) {
            return null;
        }

        $name = trim($user['first_name'] . ' ' . ($user['middle_name'] ?? '') . ' ' . $user['last_name']);
        if (!empty($user['suffix'])) {
            $name .= ' ' . $user['suffix'];
        }

        return $name;
    }

    /**
     * Verify user credentials for OTP login
     * Returns user data if credentials are valid, false otherwise
     */
    public function verifyCredentialsForOTP($email, $password)
    {
        $user = $this->where('email', $email)
                     ->where('is_active', 1)
                     ->first();

        if ($user && password_verify($password, $user['password'])) {
            return $user; // Don't update last_login yet, wait for OTP verification
        }

        return false;
    }

    /**
     * Mark email as verified
     */
    public function markEmailAsVerified($userId)
    {
        return $this->update($userId, [
            'email_verified_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Check if email is verified
     */
    public function isEmailVerified($userId)
    {
        $user = $this->find($userId);
        return $user && !empty($user['email_verified_at']);
    }
}
