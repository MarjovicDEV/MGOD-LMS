<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
use App\Models\StudentModel;
use App\Models\InstructorModel;
use App\Models\RoleModel;
use App\Models\YearLevelModel;
use App\Models\DepartmentModel;
use App\Models\ProgramModel;

class User extends BaseController
{
    protected $session;
    protected $validation;
    protected $db;
    protected $userModel;
    protected $studentModel;
    protected $instructorModel;
    protected $roleModel;
    protected $yearLevelModel;
    protected $departmentModel;
    protected $programModel;

    /**
     * Constructor - Initialize models and dependencies
     */
    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->validation = \Config\Services::validation();
        $this->db = \Config\Database::connect();
        
        // Initialize models
        $this->userModel = new UserModel();
        $this->studentModel = new StudentModel();
        $this->instructorModel = new InstructorModel();
        $this->roleModel = new RoleModel();
        $this->yearLevelModel = new YearLevelModel();
        $this->departmentModel = new DepartmentModel();
        $this->programModel = new ProgramModel();
    }

    public function index()
    {
        return redirect()->to(base_url('admin/manage_users'));
    }

    /**
     * Manage Users Method - Handles all user management operations
     * Supports create, edit, delete, and display operations
     */
    public function manageUsers()
    {
        // Security check - only admins can access
        if ($this->session->get('isLoggedIn') !== true) {
            $this->session->setFlashdata('error', 'Please login to access this page.');
            return redirect()->to(base_url('login'));
        }        
        
        if ($this->session->get('role') !== 'admin') {
            $this->session->setFlashdata('error', 'Access denied. You do not have permission to access this page.');
            $userRole = $this->session->get('role');
            return redirect()->to(base_url($userRole . '/dashboard'));
        }

        $action = $this->request->getGet('action');
        $userID = $this->request->getGet('id');
        $currentAdminID = $this->session->get('userID');

        // Route to appropriate action
        if ($action === 'create' && $this->request->getMethod() === 'POST') {
            return $this->createUser();
        }

        if ($action === 'edit' && $userID) {
            return $this->editUser($userID, $currentAdminID);
        }        if ($action === 'delete' && $userID) {
            return $this->deleteUser($userID, $currentAdminID);
        }

        if ($action === 'reactivate' && $userID) {
            return $this->reactivateUser($userID, $currentAdminID);
        }

        // Display user management interface
        return $this->displayUserManagement($currentAdminID);
    }

    /**
     * Create a new user (Admin/Instructor/Student)
     */
    private function createUser()
    {
        $role = $this->request->getPost('role');        // Validation rules
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[100]|regex_match[/^[a-zA-ZñÑ\s]+$/]',
            'last_name'  => 'required|min_length[2]|max_length[100]|regex_match[/^[a-zA-ZñÑ\s]+$/]',
            'middle_name'=> 'permit_empty|max_length[100]|regex_match[/^[a-zA-ZñÑ\s]+$/]',
            'suffix'     => 'permit_empty|max_length[10]|regex_match[/^[a-zA-Z]+$/]',
            'email'      => 'required|valid_email|is_unique[users.email]',
            'password'   => 'required|min_length[6]',
            'role'       => 'required|in_list[admin,teacher,student]'
        ];
        
        // Add role-specific validation
        if ($role === 'student') {
            $rules['year_level_id'] = 'required|integer';
            $rules['department_id'] = 'required|integer';
            $rules['program_id'] = 'required|integer';
            $rules['section'] = 'permit_empty|max_length[50]';
        } elseif ($role === 'teacher') {
            $rules['department_id'] = 'permit_empty|integer';
            $rules['specialization'] = 'permit_empty|max_length[255]';
        }        
        $messages = [
            'first_name' => [
                'required'    => 'First name is required.',
                'min_length'  => 'First name must be at least 2 characters.',
                'regex_match' => 'First name can only contain letters and spaces. Numbers and special characters are not allowed.'
            ],
            'last_name' => [
                'required'    => 'Last name is required.',
                'min_length'  => 'Last name must be at least 2 characters.',
                'regex_match' => 'Last name can only contain letters and spaces. Numbers and special characters are not allowed.'
            ],
            'middle_name' => [
                'regex_match' => 'Middle name can only contain letters and spaces. Numbers and special characters are not allowed.'
            ],
            'email' => [
                'required'    => 'Email is required.',
                'valid_email' => 'Please enter a valid email address.',
                'is_unique'   => 'This email is already registered.'
            ],
            'password' => [
                'required'   => 'Password is required.',
                'min_length' => 'Password must be at least 6 characters long.'
            ],
            'role' => [
                'required' => 'Role is required.',
                'in_list'  => 'Invalid role selected.'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            $this->session->setFlashdata('errors', $this->validation->getErrors());
            $this->session->setFlashdata('error', 'Please fix the validation errors below.');
            return redirect()->back()->withInput();
        }

        // Start database transaction
        $this->db->transStart();
        
        try {
            // Get role ID
            $roleRecord = $this->roleModel->where('role_name', ucfirst($role))->first();
            if (!$roleRecord) {
                throw new \Exception('Invalid role specified.');
            }

            // Check email uniqueness
            $email = $this->request->getPost('email');
            $existingUser = $this->userModel->where('email', $email)->first();
            if ($existingUser) {
                throw new \Exception('This email is already registered.');
            }

            // Prepare user data
            $userData = [
                'user_code'   => $this->generateUserCode($role),
                'first_name'  => $this->request->getPost('first_name'),
                'middle_name' => $this->request->getPost('middle_name'),
                'last_name'   => $this->request->getPost('last_name'),
                'suffix'      => $this->request->getPost('suffix'),
                'email'       => $email,
                'password'    => $this->request->getPost('password'), // Will be hashed by UserModel
                'role_id'     => $roleRecord['id'],
                'is_active'   => 1,
                'email_verified_at' => date('Y-m-d H:i:s') // Auto-verify for admin-created accounts
            ];// Insert user
            $userID = $this->userModel->insert($userData);
            if (!$userID) {
                $errors = $this->userModel->errors();
                $errorMessage = $errors ? implode(', ', $errors) : 'Failed to create user account.';
                throw new \Exception($errorMessage);
            }

            // Create role-specific record
            if ($role === 'student') {
                $studentData = [
                    'user_id'          => $userID,
                    'year_level_id'    => $this->request->getPost('year_level_id'),
                    'department_id'    => $this->request->getPost('department_id'),
                    'program_id'       => $this->request->getPost('program_id'),
                    'section'          => $this->request->getPost('section'),
                    'enrollment_date'  => date('Y-m-d'),
                    'enrollment_status'=> 'enrolled'
                ];
                
                if (!$this->studentModel->insert($studentData)) {
                    throw new \Exception('Failed to create student record.');
                }            
            } elseif ($role === 'teacher') {
                // Prepare instructor data - handle empty department_id
                $departmentId = $this->request->getPost('department_id');
                $instructorData = [
                    'user_id'           => $userID,
                    'department_id'     => (!empty($departmentId) && $departmentId !== '') ? $departmentId : null,
                    'specialization'    => $this->request->getPost('specialization'),
                    'hire_date'         => date('Y-m-d'),
                    'employment_status' => 'full_time'
                ];
                
                if (!$this->instructorModel->insert($instructorData)) {
                    $errors = $this->instructorModel->errors();
                    $errorMessage = $errors ? implode(', ', $errors) : 'Failed to create instructor record.';
                    throw new \Exception($errorMessage);
                }
            }

            // Complete transaction
            $this->db->transComplete();
            
            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed.');
            }

            // Log activity
            $this->logActivity('user_creation', [
                'name' => $userData['first_name'] . ' ' . $userData['last_name'],
                'role' => $role
            ]);

            $this->session->setFlashdata('success', ucfirst($role) . ' created successfully!');
            return redirect()->to(base_url('admin/manage_users'));

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'User creation failed: ' . $e->getMessage());
            $this->session->setFlashdata('error', 'Failed to create user: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Edit an existing user
     */
    private function editUser($userID, $currentAdminID)
    {
        // Find user with role information
        $userToEdit = $this->userModel->find($userID);

        if (!$userToEdit) {
            $this->session->setFlashdata('error', 'User not found.');
            return redirect()->to(base_url('admin/manage_users'));
        }

        if ($userToEdit['id'] == $currentAdminID) {
            $this->session->setFlashdata('error', 'You cannot edit your own account.');
            return redirect()->to(base_url('admin/manage_users'));
        }

        // Handle POST request
        if ($this->request->getMethod() === 'POST') {
            $role = $this->request->getPost('role');            
            $rules = [
                'first_name' => 'required|min_length[2]|max_length[100]|regex_match[/^[a-zA-ZñÑ\s]+$/]',
                'last_name'  => 'required|min_length[2]|max_length[100]|regex_match[/^[a-zA-ZñÑ\s]+$/]',
                'middle_name'=> 'permit_empty|max_length[100]|regex_match[/^[a-zA-ZñÑ\s]+$/]',
                'suffix'     => 'permit_empty|max_length[10]|regex_match[/^[a-zA-Z]+$/]',
                'email'      => "required|valid_email|is_unique[users.email,id,{$userID}]",
                'role'       => 'required|in_list[admin,teacher,student]'
            ];

            if ($this->request->getPost('password')) {
                $rules['password'] = 'min_length[6]';
            }
            
            // Add role-specific validation
            if ($role === 'student') {
                $rules['year_level_id'] = 'required|integer';
                $rules['department_id'] = 'required|integer';
                $rules['program_id'] = 'required|integer';
            } elseif ($role === 'teacher') {
                $rules['department_id'] = 'permit_empty|integer';
            }

            if (!$this->validate($rules)) {
                $this->session->setFlashdata('errors', $this->validation->getErrors());
                $this->session->setFlashdata('error', 'Please fix the validation errors below.');
                return redirect()->back()->withInput();
            }

            // Start transaction
            $this->db->transStart();
              try {
                // Get role ID
                $roleRecord = $this->roleModel->where('role_name', ucfirst($role))->first();
                if (!$roleRecord) {
                    throw new \Exception('Invalid role specified.');
                }

                // Check email uniqueness (exclude current user)
                $email = $this->request->getPost('email');
                $existingUser = $this->userModel->where('email', $email)->first();
                if ($existingUser && $existingUser['id'] != $userID) {
                    throw new \Exception('This email is already registered to another user.');
                }

                // Update user data
                $updateData = [
                    'first_name'  => $this->request->getPost('first_name'),
                    'middle_name' => $this->request->getPost('middle_name'),
                    'last_name'   => $this->request->getPost('last_name'),
                    'suffix'      => $this->request->getPost('suffix'),
                    'email'       => $email,
                    'role_id'     => $roleRecord['id']
                ];                if ($this->request->getPost('password')) {
                    $updateData['password'] = $this->request->getPost('password');
                }

                if (!$this->userModel->update($userID, $updateData)) {
                    $errors = $this->userModel->errors();
                    $errorMessage = $errors ? implode(', ', $errors) : 'Failed to update user.';
                    throw new \Exception($errorMessage);
                }                // Handle role changes
                $oldRole = $this->getRoleName($userToEdit['role_id']);
                  // If role changed, delete old role record and create new one
                if ($oldRole !== $role) {
                    // Delete any existing student or instructor records for this user (force delete to avoid soft-delete conflicts)
                    // Use purgeDeleted() to also remove any soft-deleted records that might cause unique constraint issues
                    $this->studentModel->where('user_id', $userID)->purgeDeleted();
                    $this->studentModel->where('user_id', $userID)->delete(null, true); // true = hard delete
                    
                    $this->instructorModel->where('user_id', $userID)->purgeDeleted();
                    $this->instructorModel->where('user_id', $userID)->delete(null, true); // true = hard delete

                    // Create new role record
                    if ($role === 'student') {
                        $studentData = [
                            'user_id'          => $userID,
                            'year_level_id'    => $this->request->getPost('year_level_id'),
                            'department_id'    => $this->request->getPost('department_id'),
                            'program_id'       => $this->request->getPost('program_id'),
                            'section'          => $this->request->getPost('section'),
                            'enrollment_date'  => date('Y-m-d'),
                            'enrollment_status'=> 'enrolled'
                        ];                        if (!$this->studentModel->insert($studentData)) {
                            $errors = $this->studentModel->errors();
                            $errorMessage = $errors ? implode(', ', $errors) : 'Failed to create student record.';
                            throw new \Exception($errorMessage);
                        }
                    } elseif ($role === 'teacher') {
                        $instructorData = [
                            'user_id'           => $userID,
                            'department_id'     => $this->request->getPost('department_id'),
                            'specialization'    => $this->request->getPost('specialization'),
                            'hire_date'         => date('Y-m-d'),
                            'employment_status' => 'full_time'
                        ];
                        if (!$this->instructorModel->insert($instructorData)) {
                            $errors = $this->instructorModel->errors();
                            $errorMessage = $errors ? implode(', ', $errors) : 'Failed to create instructor record.';
                            throw new \Exception($errorMessage);
                        }
                    }
                }else {
                    // Update existing role record
                    if ($role === 'student') {
                        $studentRecord = $this->studentModel->where('user_id', $userID)->first();
                        if ($studentRecord) {
                            $this->studentModel->update($studentRecord['id'], [
                                'year_level_id' => $this->request->getPost('year_level_id'),
                                'department_id' => $this->request->getPost('department_id'),
                                'program_id'    => $this->request->getPost('program_id'),
                                'section'       => $this->request->getPost('section')
                            ]);
                        }
                    } elseif ($role === 'teacher') {
                        $instructorRecord = $this->instructorModel->where('user_id', $userID)->first();
                        if ($instructorRecord) {
                            $this->instructorModel->update($instructorRecord['id'], [
                                'department_id'  => $this->request->getPost('department_id'),
                                'specialization' => $this->request->getPost('specialization')
                            ]);
                        }
                    }
                }

                $this->db->transComplete();
                
                if ($this->db->transStatus() === false) {
                    throw new \Exception('Transaction failed.');
                }

                // Log activity
                $this->logActivity('user_update', [
                    'name' => $updateData['first_name'] . ' ' . $updateData['last_name'],
                    'role' => $role
                ]);

                $this->session->setFlashdata('success', 'User updated successfully!');
                return redirect()->to(base_url('admin/manage_users'));

            } catch (\Exception $e) {
                $this->db->transRollback();
                log_message('error', 'User update failed: ' . $e->getMessage());
                $this->session->setFlashdata('error', 'Failed to update user: ' . $e->getMessage());
                return redirect()->back()->withInput();
            }
        }        // Get year levels for dropdown
        $yearLevels = $this->yearLevelModel->orderBy('year_level_order')->findAll();

        // Get departments for dropdown
        $departments = $this->departmentModel->getActiveDepartments();

        // Get all active programs for dropdown
        $programs = $this->programModel->getActivePrograms();

        // Get role-specific data
        $roleName = $this->getRoleName($userToEdit['role_id']);
        
        // Add role_name to userToEdit for the view
        $userToEdit['role_name'] = ucfirst($roleName);
        
        $roleSpecificData = null;
        if ($roleName === 'student') {
            $roleSpecificData = $this->studentModel->where('user_id', $userID)->first();
        } elseif ($roleName === 'teacher') {
            $roleSpecificData = $this->instructorModel->where('user_id', $userID)->first();
        }

        // Get all users for display
        $users = $this->getUsersWithRoles();
        
        $data = [
            'user' => [
                'userID' => $this->session->get('userID'),
                'name'   => $this->session->get('name'),
                'email'  => $this->session->get('email'),
                'role'   => $this->session->get('role')
            ],
            'title'             => 'Edit User - Admin Dashboard',
            'users'             => $users,
            'currentAdminID'    => $currentAdminID,
            'editUser'          => $userToEdit,
            'roleSpecificData'  => $roleSpecificData,
            'yearLevels'        => $yearLevels,
            'departments'       => $departments,
            'programs'          => $programs,
            'showCreateForm'    => false,
            'showEditForm'      => true
        ];

        return view('admin/manage_users', $data);
    }/**
     * Delete a user (mark as inactive - soft delete alternative)
     */
    private function deleteUser($userID, $currentAdminID)
    {
        // Get user with role name from roles table
        $userToDelete = $this->db->table('users u')
            ->select('u.*, r.role_name as role')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('u.id', $userID)
            ->get()
            ->getRowArray();

        if (!$userToDelete) {
            $this->session->setFlashdata('error', 'User not found.');
            return redirect()->to(base_url('admin/manage_users'));
        }

        if ($userToDelete['id'] == $currentAdminID) {
            $this->session->setFlashdata('error', 'You cannot deactivate your own account.');
            return redirect()->to(base_url('admin/manage_users'));
        }

        if ($userToDelete['is_active'] == 0) {
            $this->session->setFlashdata('error', 'This user is already inactive.');
            return redirect()->to(base_url('admin/manage_users'));
        }

        // Check referential integrity based on user role
        $userRole = $userToDelete['role'] ?? '';
        $userName = $userToDelete['first_name'] . ' ' . $userToDelete['last_name'];

        if ($userRole === 'student') {
            // Get student record
            $student = $this->studentModel->where('user_id', $userID)->first();
            if ($student) {
                // Check if student has active enrollments
                $enrollmentsCount = $this->db->table('enrollments')
                    ->where('student_id', $student['id'])
                    ->whereNotIn('enrollment_status', ['cancelled', 'dropped', 'completed'])
                    ->countAllResults();
                if ($enrollmentsCount > 0) {
                    $this->session->setFlashdata('error', 'Cannot deactivate student "' . esc($userName) . '". They have ' . $enrollmentsCount . ' active enrollment(s). Please cancel or complete enrollments first.');
                    return redirect()->to(base_url('admin/manage_users'));
                }
            }
        }

        if ($userRole === 'teacher') {
            // Get instructor record
            $instructor = $this->instructorModel->where('user_id', $userID)->first();
            if ($instructor) {
                // Check if instructor has assigned courses (current/active offerings)
                $assignmentsCount = $this->db->table('course_instructors ci')
                    ->join('course_offerings co', 'co.id = ci.course_offering_id')
                    ->join('terms t', 't.id = co.term_id')
                    ->where('ci.instructor_id', $instructor['id'])
                    ->where('co.status !=', 'closed')
                    ->countAllResults();
                if ($assignmentsCount > 0) {
                    $this->session->setFlashdata('error', 'Cannot deactivate instructor "' . esc($userName) . '". They have ' . $assignmentsCount . ' active course assignment(s). Please remove course assignments first.');
                    return redirect()->to(base_url('admin/manage_users'));
                }
            }
        }

        $this->db->transStart();
        
        try {
            // Mark user as inactive instead of deleting
            if (!$this->userModel->update($userID, ['is_active' => 0])) {
                throw new \Exception('Failed to deactivate user.');
            }

            $this->db->transComplete();
            
            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed.');
            }

            // Log activity
            $this->logActivity('user_deactivation', [
                'name' => $userToDelete['first_name'] . ' ' . $userToDelete['last_name'],
                'id'   => $userToDelete['id']
            ]);

            $this->session->setFlashdata('success', 'User account deactivated successfully! The user can no longer log in.');

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'User deactivation failed: ' . $e->getMessage());
            $this->session->setFlashdata('error', 'Failed to deactivate user: ' . $e->getMessage());
        }

        return redirect()->to(base_url('admin/manage_users'));
    }

    /**
     * Reactivate an inactive user
     */
    private function reactivateUser($userID, $currentAdminID)
    {
        $userToReactivate = $this->userModel->find($userID);

        if (!$userToReactivate) {
            $this->session->setFlashdata('error', 'User not found.');
            return redirect()->to(base_url('admin/manage_users'));
        }

        if ($userToReactivate['is_active'] == 1) {
            $this->session->setFlashdata('error', 'This user is already active.');
            return redirect()->to(base_url('admin/manage_users'));
        }

        $this->db->transStart();
        
        try {
            // Reactivate user
            if (!$this->userModel->update($userID, ['is_active' => 1])) {
                throw new \Exception('Failed to reactivate user.');
            }

            $this->db->transComplete();
            
            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed.');
            }

            // Log activity
            $this->logActivity('user_reactivation', [
                'name' => $userToReactivate['first_name'] . ' ' . $userToReactivate['last_name'],
                'id'   => $userToReactivate['id']
            ]);

            $this->session->setFlashdata('success', 'User account reactivated successfully! The user can now log in again.');

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'User reactivation failed: ' . $e->getMessage());
            $this->session->setFlashdata('error', 'Failed to reactivate user: ' . $e->getMessage());
        }

        return redirect()->to(base_url('admin/manage_users'));
    }

    /**
     * Display user management interface
     */
    private function displayUserManagement($currentAdminID)
    {
        // Get all users with role information
        $users = $this->getUsersWithRoles();

        // Get year levels for dropdown
        $yearLevels = $this->yearLevelModel->orderBy('year_level_order')->findAll();

        // Get departments for dropdown
        $departments = $this->departmentModel->getActiveDepartments();

        // Get all active programs for dropdown
        $programs = $this->programModel->getActivePrograms();
        
        $data = [
            'user' => [
                'userID' => $this->session->get('userID'),
                'name'   => $this->session->get('name'),
                'email'  => $this->session->get('email'),
                'role'   => $this->session->get('role')
            ],
            'title'          => 'Manage Users - Admin Dashboard',
            'users'          => $users,
            'yearLevels'     => $yearLevels,
            'departments'    => $departments,
            'programs'       => $programs,
            'currentAdminID' => $currentAdminID,
            'editUser'       => null,
            'showCreateForm' => $this->request->getGet('action') === 'create',
            'showEditForm'   => false
        ];
          
        return view('admin/manage_users', $data);
    }/**
     * Get all users with their role names
     */
    private function getUsersWithRoles()
    {
        return $this->userModel
            ->select('users.*, roles.role_name, year_levels.year_level_name, students.section, students.student_id_number')
            ->join('roles', 'roles.id = users.role_id')
            ->join('students', 'students.user_id = users.id', 'left')
            ->join('year_levels', 'year_levels.id = students.year_level_id', 'left')
            ->orderBy('users.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get role name from role ID
     */
    private function getRoleName($roleId)
    {
        $role = $this->roleModel->find($roleId);
        return $role ? strtolower($role['role_name']) : 'student';
    }

    /**
     * Generate unique user code based on role
     */
    private function generateUserCode($role)
    {
        $prefix = strtoupper(substr($role, 0, 3));
        $year = date('Y');
        $random = rand(1000, 9999);
        return "{$prefix}-{$year}-{$random}";
    }    /**
     * Log user management activity
     */
    private function logActivity($type, $data)
    {        $icons = [
            'user_creation'     => '➕',
            'user_update'       => '✏️',
            'user_deletion'     => '🗑️',
            'user_deactivation' => '🔒',
            'user_reactivation' => '🔓'
        ];

        $titles = [
            'user_creation'     => 'New User Created',
            'user_update'       => 'User Account Updated',
            'user_deletion'     => 'User Account Deleted',
            'user_deactivation' => 'User Account Deactivated',
            'user_reactivation' => 'User Account Reactivated'
        ];

        $activity = [
            'type'        => $type,
            'icon'        => $icons[$type] ?? '📝',
            'title'       => $titles[$type] ?? 'User Activity',
            'description' => $this->getActivityDescription($type, $data),
            'time'        => date('Y-m-d H:i:s'),
            'user_name'   => $data['name'] ?? 'Unknown',
            'user_role'   => $data['role'] ?? 'unknown',
            'created_by'  => $this->session->get('name')
        ];        
        $activityKey = match($type) {
            'user_creation' => 'creation_activities',
            'user_update' => 'update_activities',
            'user_deletion', 'user_deactivation' => 'deletion_activities',
            'user_reactivation' => 'reactivation_activities',
            default => 'general_activities'
        };

        $activities = $this->session->get($activityKey) ?? [];
        array_unshift($activities, $activity);
        $activities = array_slice($activities, 0, 10);
        $this->session->set($activityKey, $activities);
    }

    /**
     * Get activity description
     */
    private function getActivityDescription($type, $data)
    {
        switch ($type) {
            case 'user_creation':
                return esc($data['name']) . ' (' . ucfirst($data['role'] ?? 'User') . ') account was created by admin';
            case 'user_update':
                return esc($data['name']) . ' (' . ucfirst($data['role'] ?? 'User') . ') account was updated by admin';
            case 'user_deletion':
                return esc($data['name']) . ' (ID: ' . $data['id'] . ') account was soft deleted from the system';
            case 'user_deactivation':
                return esc($data['name']) . ' (ID: ' . $data['id'] . ') account was deactivated and can no longer log in';
            case 'user_reactivation':
                return esc($data['name']) . ' (ID: ' . $data['id'] . ') account was reactivated and can now log in';
            default:
                return 'User activity logged';
        }
    }

    /**
     * API endpoint to get programs by department
     * Returns JSON array of programs for a given department ID
     */
    public function getProgramsByDepartment($departmentId = null)
    {
        // Check if user is logged in
        if ($this->session->get('isLoggedIn') !== true) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        if (!$departmentId) {
            return $this->response->setJSON([]);
        }

        $programs = $this->programModel->getProgramsByDepartment($departmentId);
        
        return $this->response->setJSON($programs);
    }

    /**
     * Search users - AJAX endpoint
     * Accepts GET or POST requests with search term
     * Searches first_name, last_name, email, and user_code
     */
    public function search()
    {
        // Check if user is logged in and is admin
        if ($this->session->get('isLoggedIn') !== true || $this->session->get('role') !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access'
            ]);
        }

        // Get search term from GET or POST
        $searchTerm = $this->request->getGet('search') ?? $this->request->getPost('search');

        if (empty($searchTerm)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Search term is required'
            ]);
        }

        try {
            // Search users using Query Builder with LIKE queries
            $results = $this->userModel->select('
                    users.*,
                    roles.role_name,
                    students.student_id_number,
                    students.section as student_section,
                    programs.program_name,
                    year_levels.level_name as year_level_name,
                    instructors.employee_id,
                    departments.department_name
                ')
                ->join('roles', 'roles.id = users.role_id', 'left')
                ->join('students', 'students.user_id = users.id', 'left')
                ->join('programs', 'programs.id = students.program_id', 'left')
                ->join('year_levels', 'year_levels.id = students.year_level_id', 'left')
                ->join('instructors', 'instructors.user_id = users.id', 'left')
                ->join('departments', 'departments.id = instructors.department_id', 'left')
                ->groupStart()
                    ->like('users.first_name', $searchTerm)
                    ->orLike('users.last_name', $searchTerm)
                    ->orLike('users.email', $searchTerm)
                    ->orLike('users.user_code', $searchTerm)
                    ->orLike('students.student_id_number', $searchTerm)
                    ->orLike('instructors.employee_id', $searchTerm)
                    ->orLike('roles.role_name', $searchTerm)
                    ->orLike('programs.program_name', $searchTerm)
                    ->orLike('departments.department_name', $searchTerm)
                ->groupEnd()
                ->orderBy('users.last_name', 'ASC')
                ->findAll();

            return $this->response->setJSON([
                'success' => true,
                'count' => count($results),
                'data' => $results,
                'search_term' => $searchTerm
            ]);

        } catch (\Exception $e) {
            log_message('error', 'User search error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while searching users'
            ]);
        }
    }
}
