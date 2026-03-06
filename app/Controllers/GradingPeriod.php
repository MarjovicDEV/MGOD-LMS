<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\GradingPeriodModel;
use App\Models\TermModel;

class GradingPeriod extends BaseController
{
    protected $session;
    protected $validation;
    protected $db;
    protected $gradingPeriodModel;
    protected $termModel;

    /**
     * Constructor - Initialize models and dependencies
     */
    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->validation = \Config\Services::validation();
        $this->db = \Config\Database::connect();
        
        // Initialize models
        $this->gradingPeriodModel = new GradingPeriodModel();
        $this->termModel = new TermModel();
    }

    public function index()
    {
        return redirect()->to(base_url('admin/manage_grading_periods'));
    }

    /**
     * Manage Grading Periods Method - Handles all grading period management operations
     * Supports create, edit, delete, and display operations
     */
    public function manageGradingPeriods()
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
        $periodID = $this->request->getGet('id');

        // Route to appropriate action
        if ($action === 'create' && $this->request->getMethod() === 'POST') {
            return $this->createGradingPeriod();
        }

        if ($action === 'edit' && $periodID) {
            return $this->editGradingPeriod($periodID);
        }

        if ($action === 'delete' && $periodID) {
            return $this->deleteGradingPeriod($periodID);
        }

        if ($action === 'toggle_status' && $periodID) {
            return $this->toggleStatus($periodID);
        }

        // Display grading period management interface
        return $this->displayGradingPeriodManagement();
    }

    /**
     * Create a new grading period
     */
    private function createGradingPeriod()
    {
        // Validation rules
        $rules = [
            'term_id'           => 'required|integer',
            'period_name'       => 'required|min_length[2]|max_length[50]|regex_match[/^[a-zA-ZñÑ0-9\s]+$/u]',
            'period_order'      => 'required|integer|greater_than[0]',
            'weight_percentage' => 'required|decimal|greater_than[0]|less_than_equal_to[100]',
            'start_date'        => 'permit_empty|valid_date',
            'end_date'          => 'permit_empty|valid_date'
        ];

        $messages = [
            'term_id' => [
                'required' => 'Term is required.',
                'integer'  => 'Invalid term selected.'
            ],
            'period_name' => [
                'required'    => 'Period name is required.',
                'min_length'  => 'Period name must be at least 2 characters.',
                'max_length'  => 'Period name must not exceed 50 characters.',
                'regex_match' => 'Period name can only contain letters (including Ñ/ñ), numbers, and spaces. No special characters allowed.'
            ],
            'period_order' => [
                'required'      => 'Period order is required.',
                'integer'       => 'Period order must be a number.',
                'greater_than'  => 'Period order must be greater than 0.'
            ],
            'weight_percentage' => [
                'required'             => 'Weight percentage is required.',
                'decimal'              => 'Weight percentage must be a valid number.',
                'greater_than'         => 'Weight percentage must be greater than 0.',
                'less_than_equal_to'   => 'Weight percentage must not exceed 100.'
            ],
            'start_date' => [
                'valid_date' => 'Please provide a valid start date.'
            ],
            'end_date' => [
                'valid_date' => 'Please provide a valid end date.'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            $this->session->setFlashdata('errors', $this->validation->getErrors());
            $this->session->setFlashdata('error', 'Please fix the validation errors below.');
            return redirect()->back()->withInput();
        }

        // Additional weight validation to ensure it doesn't exceed 100%
        $weightPercentage = floatval($this->request->getPost('weight_percentage'));
        if ($weightPercentage > 100) {
            $this->session->setFlashdata('error', 'Weight percentage cannot exceed 100%. Please enter a valid value between 0.01 and 100.');
            return redirect()->back()->withInput();
        }

        // Validate date order
        $startDate = $this->request->getPost('start_date');
        $endDate = $this->request->getPost('end_date');
        
        if (!empty($startDate) && !empty($endDate) && strtotime($startDate) > strtotime($endDate)) {
            $this->session->setFlashdata('error', 'End date must be after start date.');
            return redirect()->back()->withInput();
        }

        // Start database transaction
        $this->db->transStart();
        
        try {
            $termId = $this->request->getPost('term_id');
            
            // Prepare grading period data
            $periodData = [
                'term_id'           => $termId,
                'period_name'       => $this->request->getPost('period_name'),
                'period_order'      => $this->request->getPost('period_order'),
                'weight_percentage' => $this->request->getPost('weight_percentage'),
                'start_date'        => $startDate ?: null,
                'end_date'          => $endDate ?: null,
                'is_active'         => 1
            ];

            // Insert grading period
            if (!$this->gradingPeriodModel->insert($periodData)) {
                throw new \Exception('Failed to create grading period.');
            }

            // Complete transaction
            $this->db->transComplete();
            
            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed.');
            }

            // Check if total weights are valid (warning only, not blocking)
            if (!$this->gradingPeriodModel->validateTermWeights($termId)) {
                $this->session->setFlashdata('warning', 'Grading period "' . esc($periodData['period_name']) . '" created successfully! However, total weight percentages for this term do not equal 100%.');
            } else {
                $this->session->setFlashdata('success', 'Grading period "' . esc($periodData['period_name']) . '" created successfully!');
            }
            
            return redirect()->to(base_url('admin/manage_grading_periods'));

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Grading period creation failed: ' . $e->getMessage());
            $this->session->setFlashdata('error', 'Failed to create grading period: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Edit an existing grading period
     */
    private function editGradingPeriod($periodID)
    {
        // Find grading period
        $periodToEdit = $this->gradingPeriodModel->find($periodID);

        if (!$periodToEdit) {
            $this->session->setFlashdata('error', 'Grading period not found.');
            return redirect()->to(base_url('admin/manage_grading_periods'));
        }

        // Handle POST request
        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'term_id'           => 'required|integer',
                'period_name'       => 'required|min_length[2]|max_length[50]|regex_match[/^[a-zA-ZñÑ0-9\s]+$/u]',
                'period_order'      => 'required|integer|greater_than[0]',
                'weight_percentage' => 'required|decimal|greater_than[0]|less_than_equal_to[100]',
                'start_date'        => 'permit_empty|valid_date',
                'end_date'          => 'permit_empty|valid_date'
            ];            $messages = [
                'term_id' => [
                    'required' => 'Term is required.',
                    'integer'  => 'Invalid term selected.'
                ],
                'period_name' => [
                    'required'    => 'Period name is required.',
                    'min_length'  => 'Period name must be at least 2 characters.',
                    'max_length'  => 'Period name must not exceed 50 characters.',
                    'regex_match' => 'Period name can only contain letters (including Ñ/ñ), numbers, and spaces. No special characters allowed.'
                ],
                'period_order' => [
                    'required'     => 'Period order is required.',
                    'integer'      => 'Period order must be a number.',
                    'greater_than' => 'Period order must be greater than 0.'
                ],
                'weight_percentage' => [
                    'required'             => 'Weight percentage is required.',
                    'decimal'              => 'Weight percentage must be a valid number.',
                    'greater_than'         => 'Weight percentage must be greater than 0.',
                    'less_than_equal_to'   => 'Weight percentage must not exceed 100.'
                ],
                'start_date' => [
                    'valid_date' => 'Please provide a valid start date.'
                ],
                'end_date' => [
                    'valid_date' => 'Please provide a valid end date.'
                ]
            ];

            if (!$this->validate($rules, $messages)) {
                $this->session->setFlashdata('errors', $this->validation->getErrors());
                $this->session->setFlashdata('error', 'Please fix the validation errors below.');
                return redirect()->back()->withInput();
            }

            // Additional weight validation to ensure it doesn't exceed 100%
            $weightPercentage = floatval($this->request->getPost('weight_percentage'));
            if ($weightPercentage > 100) {
                $this->session->setFlashdata('error', 'Weight percentage cannot exceed 100%. Please enter a valid value between 0.01 and 100.');
                return redirect()->back()->withInput();
            }

            // Validate date order
            $startDate = $this->request->getPost('start_date');
            $endDate = $this->request->getPost('end_date');
            
            if (!empty($startDate) && !empty($endDate) && strtotime($startDate) > strtotime($endDate)) {
                $this->session->setFlashdata('error', 'End date must be after start date.');
                return redirect()->back()->withInput();
            }

            // Start transaction
            $this->db->transStart();
            
            try {
                $termId = $this->request->getPost('term_id');
                
                // Update grading period data
                $updateData = [
                    'term_id'           => $termId,
                    'period_name'       => $this->request->getPost('period_name'),
                    'period_order'      => $this->request->getPost('period_order'),
                    'weight_percentage' => $this->request->getPost('weight_percentage'),
                    'start_date'        => $startDate ?: null,
                    'end_date'          => $endDate ?: null
                ];

                if (!$this->gradingPeriodModel->update($periodID, $updateData)) {
                    throw new \Exception('Failed to update grading period.');
                }

                $this->db->transComplete();
                
                if ($this->db->transStatus() === false) {
                    throw new \Exception('Transaction failed.');
                }

                // Check if total weights are valid (warning only)
                if (!$this->gradingPeriodModel->validateTermWeights($termId)) {
                    $this->session->setFlashdata('warning', 'Grading period updated successfully! However, total weight percentages for this term do not equal 100%.');
                } else {
                    $this->session->setFlashdata('success', 'Grading period updated successfully!');
                }
                
                return redirect()->to(base_url('admin/manage_grading_periods'));

            } catch (\Exception $e) {
                $this->db->transRollback();
                log_message('error', 'Grading period update failed: ' . $e->getMessage());
                $this->session->setFlashdata('error', 'Failed to update grading period: ' . $e->getMessage());
                return redirect()->back()->withInput();
            }
        }

        // Get all grading periods and terms for display
        $gradingPeriods = $this->getGradingPeriodsWithDetails();
        $terms = $this->termModel->getAllWithDetails();
        
        $data = [
            'user' => [
                'userID' => $this->session->get('userID'),
                'name'   => $this->session->get('name'),
                'email'  => $this->session->get('email'),
                'role'   => $this->session->get('role')
            ],
            'title'              => 'Edit Grading Period - Admin Dashboard',
            'gradingPeriods'     => $gradingPeriods,
            'terms'              => $terms,
            'editGradingPeriod'  => $periodToEdit,
            'showCreateForm'     => false,
            'showEditForm'       => true
        ];

        return view('admin/manage_grading_periods', $data);
    }

    /**
     * Delete a grading period
     */
    private function deleteGradingPeriod($periodID)
    {
        $periodToDelete = $this->gradingPeriodModel->find($periodID);

        if (!$periodToDelete) {
            $this->session->setFlashdata('error', 'Grading period not found.');
            return redirect()->to(base_url('admin/manage_grading_periods'));
        }

        // Check if grading period is being used (in assignments or grades)
        $assignmentsCount = $this->db->table('assignments')
            ->where('grading_period_id', $periodID)
            ->countAllResults();

        if ($assignmentsCount > 0) {
            $this->session->setFlashdata('error', 'Cannot delete grading period. It is being used by ' . $assignmentsCount . ' assignment(s). Please deactivate instead.');
            return redirect()->to(base_url('admin/manage_grading_periods'));
        }

        $this->db->transStart();
        
        try {
            if (!$this->gradingPeriodModel->delete($periodID)) {
                throw new \Exception('Failed to delete grading period.');
            }

            $this->db->transComplete();
            
            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed.');
            }

            $this->session->setFlashdata('success', 'Grading period "' . esc($periodToDelete['period_name']) . '" deleted successfully!');

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Grading period deletion failed: ' . $e->getMessage());
            $this->session->setFlashdata('error', 'Failed to delete grading period: ' . $e->getMessage());
        }

        return redirect()->to(base_url('admin/manage_grading_periods'));
    }

    /**
     * Toggle grading period active status
     */
    private function toggleStatus($periodID)
    {
        $gradingPeriod = $this->gradingPeriodModel->find($periodID);

        if (!$gradingPeriod) {
            $this->session->setFlashdata('error', 'Grading period not found.');
            return redirect()->to(base_url('admin/manage_grading_periods'));
        }

        try {
            $newStatus = $gradingPeriod['is_active'] ? 0 : 1;
            $this->gradingPeriodModel->update($periodID, ['is_active' => $newStatus]);

            $statusText = $newStatus ? 'activated' : 'deactivated';
            $this->session->setFlashdata('success', 'Grading period "' . esc($gradingPeriod['period_name']) . '" ' . $statusText . ' successfully!');

        } catch (\Exception $e) {
            log_message('error', 'Grading period status toggle failed: ' . $e->getMessage());
            $this->session->setFlashdata('error', 'Failed to update grading period status.');
        }

        return redirect()->to(base_url('admin/manage_grading_periods'));
    }

    /**
     * Display grading period management interface
     */
    private function displayGradingPeriodManagement()
    {
        // Get all grading periods with term details
        $gradingPeriods = $this->getGradingPeriodsWithDetails();

        // Get all terms for dropdown
        $terms = $this->termModel->getAllWithDetails();

        $data = [
            'user' => [
                'userID' => $this->session->get('userID'),
                'name'   => $this->session->get('name'),
                'email'  => $this->session->get('email'),
                'role'   => $this->session->get('role')
            ],
            'title'              => 'Manage Grading Periods - Admin Dashboard',
            'gradingPeriods'     => $gradingPeriods,
            'terms'              => $terms,
            'showCreateForm'     => $this->request->getGet('action') === 'create',
            'showEditForm'       => false
        ];

        return view('admin/manage_grading_periods', $data);
    }

    /**
     * Get grading periods with term details
     */
    private function getGradingPeriodsWithDetails()
    {
        return $this->db->table('grading_periods')
            ->select('grading_periods.*, 
                     terms.term_name,
                     academic_years.year_name,
                     semesters.semester_name')
            ->join('terms', 'terms.id = grading_periods.term_id', 'left')
            ->join('academic_years', 'academic_years.id = terms.academic_year_id', 'left')
            ->join('semesters', 'semesters.id = terms.semester_id', 'left')
            ->orderBy('grading_periods.term_id', 'DESC')
            ->orderBy('grading_periods.period_order', 'ASC')
            ->get()
            ->getResultArray();
    }
}
