<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\AssignmentTypeModel;

class AssignmentType extends BaseController
{
    protected $session;
    protected $validation;
    protected $db;
    protected $assignmentTypeModel;

    /**
     * Constructor - Initialize models and dependencies
     */
    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->validation = \Config\Services::validation();
        $this->db = \Config\Database::connect();
        
        // Initialize models
        $this->assignmentTypeModel = new AssignmentTypeModel();
    }

    public function index()
    {
        return redirect()->to(base_url('admin/manage_assignment_types'));
    }

    /**
     * Manage Assignment Types Method - Handles all assignment type management operations
     * Supports create, edit, delete, and display operations
     */
    public function manageAssignmentTypes()
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
        $typeID = $this->request->getGet('id');

        // Route to appropriate action
        if ($action === 'create' && $this->request->getMethod() === 'POST') {
            return $this->createAssignmentType();
        }

        if ($action === 'edit' && $typeID) {
            return $this->editAssignmentType($typeID);
        }

        if ($action === 'delete' && $typeID) {
            return $this->deleteAssignmentType($typeID);
        }

        if ($action === 'toggle_status' && $typeID) {
            return $this->toggleStatus($typeID);
        }

        // Display assignment type management interface
        return $this->displayAssignmentTypeManagement();
    }

    /**
     * Create a new assignment type
     */
    private function createAssignmentType()
    {
        // Validation rules
        $rules = [
            'type_name'      => 'required|min_length[2]|max_length[50]|regex_match[/^[a-zA-ZñÑ\s]+$/u]',
            'type_code'      => 'required|min_length[2]|max_length[20]|is_unique[assignment_types.type_code]|regex_match[/^[A-Z]{2,10}-[0-9]{1,5}$|^[A-Z0-9_]+$/]',
            'description'    => 'permit_empty|max_length[255]',
            'default_weight' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]'
        ];

        $messages = [
            'type_name' => [
                'required'    => 'Assignment type name is required.',
                'min_length'  => 'Assignment type name must be at least 2 characters.',
                'max_length'  => 'Assignment type name must not exceed 50 characters.',
                'regex_match' => 'Assignment type name can only contain letters (including Ñ/ñ) and spaces. No numbers or special characters allowed.'
            ],
            'type_code' => [
                'required'    => 'Type code is required.',
                'min_length'  => 'Type code must be at least 2 characters.',
                'max_length'  => 'Type code must not exceed 20 characters.',
                'is_unique'   => 'This type code already exists.',
                'regex_match' => 'Type code must follow format: XX-000 (e.g., AT-101) or use uppercase letters, numbers, and underscores (e.g., QUIZ_TYPE).'
            ],
            'description' => [
                'max_length' => 'Description must not exceed 255 characters.'
            ],
            'default_weight' => [
                'decimal'                => 'Default weight must be a valid number.',
                'greater_than_equal_to'  => 'Default weight must be between 0 and 100.',
                'less_than_equal_to'     => 'Default weight must be between 0 and 100.'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            $this->session->setFlashdata('errors', $this->validation->getErrors());
            $this->session->setFlashdata('error', 'Please fix the validation errors below.');
            return redirect()->back()->withInput();
        }

        // Additional weight validation to ensure it doesn't exceed 100%
        $defaultWeight = $this->request->getPost('default_weight');
        if (!empty($defaultWeight)) {
            $weightValue = floatval($defaultWeight);
            if ($weightValue > 100) {
                $this->session->setFlashdata('error', 'Default weight cannot exceed 100%. Please enter a valid value between 0 and 100.');
                return redirect()->back()->withInput();
            }
        }

        // Start database transaction
        $this->db->transStart();
        
        try {
            // Prepare assignment type data
            $typeData = [
                'type_name'      => $this->request->getPost('type_name'),
                'type_code'      => strtoupper($this->request->getPost('type_code')),
                'description'    => $this->request->getPost('description'),
                'default_weight' => $this->request->getPost('default_weight') ?: null,
                'is_active'      => 1
            ];

            // Insert assignment type
            if (!$this->assignmentTypeModel->insert($typeData)) {
                throw new \Exception('Failed to create assignment type.');
            }

            // Complete transaction
            $this->db->transComplete();
            
            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed.');
            }

            $this->session->setFlashdata('success', 'Assignment type "' . esc($typeData['type_name']) . '" created successfully!');
            return redirect()->to(base_url('admin/manage_assignment_types'));

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Assignment type creation failed: ' . $e->getMessage());
            $this->session->setFlashdata('error', 'Failed to create assignment type: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Edit an existing assignment type
     */
    private function editAssignmentType($typeID)
    {
        // Find assignment type
        $typeToEdit = $this->assignmentTypeModel->find($typeID);

        if (!$typeToEdit) {
            $this->session->setFlashdata('error', 'Assignment type not found.');
            return redirect()->to(base_url('admin/manage_assignment_types'));
        }

        // Handle POST request
        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'type_name'      => 'required|min_length[2]|max_length[50]|regex_match[/^[a-zA-ZñÑ\s]+$/u]',
                'type_code'      => "required|min_length[2]|max_length[20]|is_unique[assignment_types.type_code,id,{$typeID}]|regex_match[/^[A-Z]{2,10}-[0-9]{1,5}$|^[A-Z0-9_]+$/]",
                'description'    => 'permit_empty|max_length[255]',
                'default_weight' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]'
            ];            $messages = [
                'type_name' => [
                    'required'    => 'Assignment type name is required.',
                    'min_length'  => 'Assignment type name must be at least 2 characters.',
                    'max_length'  => 'Assignment type name must not exceed 50 characters.',
                    'regex_match' => 'Assignment type name can only contain letters (including Ñ/ñ) and spaces. No numbers or special characters allowed.'
                ],
                'type_code' => [
                    'required'    => 'Type code is required.',
                    'min_length'  => 'Type code must be at least 2 characters.',
                    'max_length'  => 'Type code must not exceed 20 characters.',
                    'is_unique'   => 'This type code already exists.',
                    'regex_match' => 'Type code must follow format: XX-000 (e.g., AT-101) or use uppercase letters, numbers, and underscores (e.g., QUIZ_TYPE).'
                ],
                'description' => [
                    'max_length' => 'Description must not exceed 255 characters.'
                ],
                'default_weight' => [
                    'decimal'                => 'Default weight must be a valid number.',
                    'greater_than_equal_to'  => 'Default weight must be between 0 and 100.',
                    'less_than_equal_to'     => 'Default weight must be between 0 and 100.'
                ]
            ];

            if (!$this->validate($rules, $messages)) {
                $this->session->setFlashdata('errors', $this->validation->getErrors());
                $this->session->setFlashdata('error', 'Please fix the validation errors below.');
                return redirect()->back()->withInput();
            }

            // Additional weight validation to ensure it doesn't exceed 100%
            $defaultWeight = $this->request->getPost('default_weight');
            if (!empty($defaultWeight)) {
                $weightValue = floatval($defaultWeight);
                if ($weightValue > 100) {
                    $this->session->setFlashdata('error', 'Default weight cannot exceed 100%. Please enter a valid value between 0 and 100.');
                    return redirect()->back()->withInput();
                }
            }

            // Start transaction
            $this->db->transStart();
            
            try {
                // Update assignment type data
                $updateData = [
                    'type_name'      => $this->request->getPost('type_name'),
                    'type_code'      => strtoupper($this->request->getPost('type_code')),
                    'description'    => $this->request->getPost('description'),
                    'default_weight' => $this->request->getPost('default_weight') ?: null
                ];

                if (!$this->assignmentTypeModel->update($typeID, $updateData)) {
                    throw new \Exception('Failed to update assignment type.');
                }

                $this->db->transComplete();
                
                if ($this->db->transStatus() === false) {
                    throw new \Exception('Transaction failed.');
                }

                $this->session->setFlashdata('success', 'Assignment type updated successfully!');
                return redirect()->to(base_url('admin/manage_assignment_types'));

            } catch (\Exception $e) {
                $this->db->transRollback();
                log_message('error', 'Assignment type update failed: ' . $e->getMessage());
                $this->session->setFlashdata('error', 'Failed to update assignment type: ' . $e->getMessage());
                return redirect()->back()->withInput();
            }
        }

        // Get all assignment types for display
        $assignmentTypes = $this->assignmentTypeModel->findAll();
        
        $data = [
            'user' => [
                'userID' => $this->session->get('userID'),
                'name'   => $this->session->get('name'),
                'email'  => $this->session->get('email'),
                'role'   => $this->session->get('role')
            ],
            'title'              => 'Edit Assignment Type - Admin Dashboard',
            'assignmentTypes'    => $assignmentTypes,
            'editAssignmentType' => $typeToEdit,
            'showCreateForm'     => false,
            'showEditForm'       => true
        ];

        return view('admin/manage_assignment_types', $data);
    }

    /**
     * Delete an assignment type
     */
    private function deleteAssignmentType($typeID)
    {
        $typeToDelete = $this->assignmentTypeModel->find($typeID);

        if (!$typeToDelete) {
            $this->session->setFlashdata('error', 'Assignment type not found.');
            return redirect()->to(base_url('admin/manage_assignment_types'));
        }

        // Check if assignment type is being used
        $assignmentsCount = $this->db->table('assignments')
            ->where('assignment_type_id', $typeID)
            ->countAllResults();

        if ($assignmentsCount > 0) {
            $this->session->setFlashdata('error', 'Cannot delete assignment type "' . esc($typeToDelete['type_name']) . '". It is being used by ' . $assignmentsCount . ' assignment(s). Please deactivate instead.');
            return redirect()->to(base_url('admin/manage_assignment_types'));
        }

        // Check referential integrity - Grade Components
        $gradeComponentsCount = $this->db->table('grade_components')->where('assignment_type_id', $typeID)->countAllResults();
        if ($gradeComponentsCount > 0) {
            $this->session->setFlashdata('error', 'Cannot delete assignment type "' . esc($typeToDelete['type_name']) . '". It is used in ' . $gradeComponentsCount . ' grade component(s). Please deactivate instead.');
            return redirect()->to(base_url('admin/manage_assignment_types'));
        }

        // Check if assignment type is already inactive
        if ($typeToDelete['is_active'] == 0) {
            $this->session->setFlashdata('error', 'This assignment type is already deactivated.');
            return redirect()->to(base_url('admin/manage_assignment_types'));
        }

        // Soft delete: Set is_active to 0
        $this->db->transStart();
        
        try {
            if (!$this->assignmentTypeModel->update($typeID, ['is_active' => 0])) {
                throw new \Exception('Failed to deactivate assignment type.');
            }

            $this->db->transComplete();
            
            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed.');
            }

            $this->session->setFlashdata('success', 'Assignment type "' . esc($typeToDelete['type_name']) . '" has been deactivated successfully!');

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Assignment type deactivation failed: ' . $e->getMessage());
            $this->session->setFlashdata('error', 'Failed to deactivate assignment type: ' . $e->getMessage());
        }

        return redirect()->to(base_url('admin/manage_assignment_types'));
    }

    /**
     * Toggle assignment type active status
     */
    private function toggleStatus($typeID)
    {
        $assignmentType = $this->assignmentTypeModel->find($typeID);

        if (!$assignmentType) {
            $this->session->setFlashdata('error', 'Assignment type not found.');
            return redirect()->to(base_url('admin/manage_assignment_types'));
        }

        try {
            $newStatus = $assignmentType['is_active'] ? 0 : 1;
            $this->assignmentTypeModel->update($typeID, ['is_active' => $newStatus]);

            $statusText = $newStatus ? 'activated' : 'deactivated';
            $this->session->setFlashdata('success', 'Assignment type "' . esc($assignmentType['type_name']) . '" ' . $statusText . ' successfully!');

        } catch (\Exception $e) {
            log_message('error', 'Assignment type status toggle failed: ' . $e->getMessage());
            $this->session->setFlashdata('error', 'Failed to update assignment type status.');
        }

        return redirect()->to(base_url('admin/manage_assignment_types'));
    }

    /**
     * Display assignment type management interface
     */
    private function displayAssignmentTypeManagement()
    {
        // Get all assignment types
        $assignmentTypes = $this->assignmentTypeModel->findAll();

        $data = [
            'user' => [
                'userID' => $this->session->get('userID'),
                'name'   => $this->session->get('name'),
                'email'  => $this->session->get('email'),
                'role'   => $this->session->get('role')
            ],
            'title'              => 'Manage Assignment Types - Admin Dashboard',
            'assignmentTypes'    => $assignmentTypes,
            'showCreateForm'     => $this->request->getGet('action') === 'create',
            'showEditForm'       => false
        ];

        return view('admin/manage_assignment_types', $data);
    }
}
