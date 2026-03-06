<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\GradeComponentModel;
use App\Models\CourseOfferingModel;
use App\Models\GradingPeriodModel;
use App\Models\AssignmentTypeModel;

class GradeComponent extends BaseController
{
    protected $session;
    protected $validation;
    protected $db;
    protected $gradeComponentModel;
    protected $courseOfferingModel;
    protected $gradingPeriodModel;
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
        $this->gradeComponentModel = new GradeComponentModel();
        $this->courseOfferingModel = new CourseOfferingModel();
        $this->gradingPeriodModel = new GradingPeriodModel();
        $this->assignmentTypeModel = new AssignmentTypeModel();
    }

    public function index()
    {
        return redirect()->to(base_url('admin/manage_grade_components'));
    }

    /**
     * Manage Grade Components Method - Handles all grade component management operations
     * Supports create, edit, delete, and display operations
     */
    public function manageGradeComponents()
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
        $componentID = $this->request->getGet('id');

        // Route to appropriate action
        if ($action === 'create' && $this->request->getMethod() === 'POST') {
            return $this->createGradeComponent();
        }

        if ($action === 'edit' && $componentID) {
            return $this->editGradeComponent($componentID);
        }

        if ($action === 'delete' && $componentID) {
            return $this->deleteGradeComponent($componentID);
        }

        if ($action === 'toggle_status' && $componentID) {
            return $this->toggleStatus($componentID);
        }

        // Display grade component management interface
        return $this->displayGradeComponentManagement();
    }

    /**
     * Create a new grade component
     */    private function createGradeComponent()
    {
        // Validation rules
        $rules = [
            'course_offering_id' => 'required|integer',
            'assignment_type_id' => 'required|integer',
            'grading_period_id'  => 'permit_empty|integer',
            'weight_percentage'  => 'required|decimal|greater_than[0]|less_than_equal_to[100]'
        ];

        $messages = [
            'course_offering_id' => [
                'required' => 'Course offering is required.',
                'integer'  => 'Invalid course offering selected.'
            ],
            'assignment_type_id' => [
                'required' => 'Assignment type is required.',
                'integer'  => 'Invalid assignment type selected.'
            ],
            'weight_percentage' => [
                'required'             => 'Weight percentage is required.',
                'decimal'              => 'Weight percentage must be a valid number.',
                'greater_than'         => 'Weight percentage must be greater than 0.',
                'less_than_equal_to'   => 'Weight percentage must not exceed 100.'
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

        // Check for duplicate assignment type in the same grading period for this course offering
        $courseOfferingId = $this->request->getPost('course_offering_id');
        $assignmentTypeId = $this->request->getPost('assignment_type_id');
        $gradingPeriodId = $this->request->getPost('grading_period_id') ?: null;

        $duplicateQuery = $this->db->table('grade_components')
            ->where('course_offering_id', $courseOfferingId)
            ->where('assignment_type_id', $assignmentTypeId);
        
        if ($gradingPeriodId) {
            $duplicateQuery->where('grading_period_id', $gradingPeriodId);
        } else {
            $duplicateQuery->where('grading_period_id IS NULL');
        }
        
        $existingComponent = $duplicateQuery->get()->getRow();

        if ($existingComponent) {
            $this->session->setFlashdata('error', 'This assignment type already exists for the selected grading period. Each assignment type can only be added once per grading period.');
            return redirect()->back()->withInput();
        }

        // Start database transaction
        $this->db->transStart();
        
        try {
            // Prepare grade component data (use already validated variables)
            $componentData = [
                'course_offering_id' => $courseOfferingId,
                'assignment_type_id' => $assignmentTypeId,
                'grading_period_id'  => $gradingPeriodId,
                'weight_percentage'  => $weightPercentage,
                'is_active'          => 1
            ];            // Insert grade component
            if (!$this->gradeComponentModel->insert($componentData)) {
                $errors = $this->gradeComponentModel->errors();
                $errorMsg = !empty($errors) ? implode(', ', $errors) : 'Unknown error occurred.';
                throw new \Exception('Failed to create grade component: ' . $errorMsg);
            }

            // Complete transaction
            $this->db->transComplete();
            
            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed.');
            }

            // Check if total weights are valid (warning only)
            $offeringId = $componentData['course_offering_id'];
            $periodId = $componentData['grading_period_id'];
            
            $weightCheck = $this->gradeComponentModel->validateTotalWeight($offeringId, $periodId);
            
            if (!$weightCheck['valid']) {
                $this->session->setFlashdata('warning', 'Grade component created successfully! However, total weight percentages do not equal 100% (Current: ' . number_format($weightCheck['total_weight'], 2) . '%).');
            } else {
                $this->session->setFlashdata('success', 'Grade component created successfully!');
            }
            
            return redirect()->to(base_url('admin/manage_grade_components'));

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Grade component creation failed: ' . $e->getMessage());
            $this->session->setFlashdata('error', 'Failed to create grade component: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Edit an existing grade component
     */
    private function editGradeComponent($componentID)
    {
        // Find grade component
        $componentToEdit = $this->gradeComponentModel->getComponentWithDetails($componentID);

        if (!$componentToEdit) {
            $this->session->setFlashdata('error', 'Grade component not found.');
            return redirect()->to(base_url('admin/manage_grade_components'));
        }        // Handle POST request
        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'course_offering_id' => 'required|integer',
                'assignment_type_id' => 'required|integer',
                'grading_period_id'  => 'permit_empty|integer',
                'weight_percentage'  => 'required|decimal|greater_than[0]|less_than_equal_to[100]'
            ];

            $messages = [
                'course_offering_id' => [
                    'required' => 'Course offering is required.',
                    'integer'  => 'Invalid course offering selected.'
                ],
                'assignment_type_id' => [
                    'required' => 'Assignment type is required.',
                    'integer'  => 'Invalid assignment type selected.'
                ],
                'weight_percentage' => [
                    'required'             => 'Weight percentage is required.',
                    'decimal'              => 'Weight percentage must be a valid number.',
                    'greater_than'         => 'Weight percentage must be greater than 0.',
                    'less_than_equal_to'   => 'Weight percentage must not exceed 100.'
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

            // Check for duplicate assignment type in the same grading period (excluding current record)
            $courseOfferingId = $this->request->getPost('course_offering_id');
            $assignmentTypeId = $this->request->getPost('assignment_type_id');
            $gradingPeriodId = $this->request->getPost('grading_period_id') ?: null;

            $duplicateQuery = $this->db->table('grade_components')
                ->where('course_offering_id', $courseOfferingId)
                ->where('assignment_type_id', $assignmentTypeId)
                ->where('id !=', $componentID);
            
            if ($gradingPeriodId) {
                $duplicateQuery->where('grading_period_id', $gradingPeriodId);
            } else {
                $duplicateQuery->where('grading_period_id IS NULL');
            }
            
            $existingComponent = $duplicateQuery->get()->getRow();

            if ($existingComponent) {
                $this->session->setFlashdata('error', 'This assignment type already exists for the selected grading period. Each assignment type can only be added once per grading period.');
                return redirect()->back()->withInput();
            }

            // Start transaction
            $this->db->transStart();
              try {
                // Update grade component data
                $updateData = [
                    'course_offering_id' => $courseOfferingId,
                    'assignment_type_id' => $assignmentTypeId,
                    'grading_period_id'  => $gradingPeriodId,
                    'weight_percentage'  => $weightPercentage
                ];

                if (!$this->gradeComponentModel->update($componentID, $updateData)) {
                    throw new \Exception('Failed to update grade component.');
                }

                $this->db->transComplete();
                
                if ($this->db->transStatus() === false) {
                    throw new \Exception('Transaction failed.');
                }

                // Check if total weights are valid (warning only)
                $offeringId = $updateData['course_offering_id'];
                $periodId = $updateData['grading_period_id'];
                
                $weightCheck = $this->gradeComponentModel->validateTotalWeight($offeringId, $periodId);
                
                if (!$weightCheck['valid']) {
                    $this->session->setFlashdata('warning', 'Grade component updated successfully! However, total weight percentages do not equal 100% (Current: ' . number_format($weightCheck['total_weight'], 2) . '%).');
                } else {
                    $this->session->setFlashdata('success', 'Grade component updated successfully!');
                }
                
                return redirect()->to(base_url('admin/manage_grade_components'));

            } catch (\Exception $e) {
                $this->db->transRollback();
                log_message('error', 'Grade component update failed: ' . $e->getMessage());
                $this->session->setFlashdata('error', 'Failed to update grade component: ' . $e->getMessage());
                return redirect()->back()->withInput();
            }
        }        // Get all data for display
        $gradeComponents = $this->getGradeComponentsWithDetails();
        $courseOfferings = $this->getCourseOfferingsWithDetails();
        $gradingPeriods = $this->getGradingPeriodsWithDetails();
        $assignmentTypes = $this->assignmentTypeModel->where('is_active', 1)->findAll();
        
        $data = [
            'title' => 'Edit Grade Component',
            'gradeComponents' => $gradeComponents,
            'courseOfferings' => $courseOfferings,
            'gradingPeriods' => $gradingPeriods,
            'assignmentTypes' => $assignmentTypes,
            'showCreateForm' => false,
            'showEditForm' => true,
            'editGradeComponent' => $componentToEdit
        ];

        return view('admin/manage_grade_components', $data);
    }

    /**
     * Delete a grade component
     */
    private function deleteGradeComponent($componentID)
    {
        $component = $this->gradeComponentModel->find($componentID);

        if (!$component) {
            $this->session->setFlashdata('error', 'Grade component not found.');
            return redirect()->to(base_url('admin/manage_grade_components'));
        }

        // Check if component has associated assignments
        $assignmentCount = $this->db->table('assignments')
                                    ->where('grading_period_id', $componentID)
                                    ->countAllResults();        if ($assignmentCount > 0) {
            $this->session->setFlashdata('error', 'Cannot delete grade component because it has ' . $assignmentCount . ' associated assignment(s). Please delete or reassign the assignments first.');
            return redirect()->to(base_url('admin/manage_grade_components'));
        }

        // Delete the component
        if ($this->gradeComponentModel->delete($componentID)) {
            $this->session->setFlashdata('success', 'Grade component deleted successfully!');
        } else {
            $this->session->setFlashdata('error', 'Failed to delete grade component.');
        }

        return redirect()->to(base_url('admin/manage_grade_components'));
    }

    /**
     * Toggle grade component status (active/inactive)
     */
    private function toggleStatus($componentID)
    {
        $component = $this->gradeComponentModel->find($componentID);

        if (!$component) {
            $this->session->setFlashdata('error', 'Grade component not found.');
            return redirect()->to(base_url('admin/manage_grade_components'));
        }        $newStatus = $component['is_active'] == 1 ? 0 : 1;
        $statusText = $newStatus == 1 ? 'activated' : 'deactivated';

        if ($this->gradeComponentModel->update($componentID, ['is_active' => $newStatus])) {
            $this->session->setFlashdata('success', 'Grade component ' . $statusText . ' successfully!');
        } else {
            $this->session->setFlashdata('error', 'Failed to update grade component status.');
        }

        return redirect()->to(base_url('admin/manage_grade_components'));
    }    /**
     * Display grade component management interface
     */
    private function displayGradeComponentManagement()
    {
        $showCreateForm = $this->request->getGet('action') === 'create';
        
        $gradeComponents = $this->getGradeComponentsWithDetails();
        $courseOfferings = $this->getCourseOfferingsWithDetails();
        $gradingPeriods = $this->getGradingPeriodsWithDetails();
        $assignmentTypes = $this->assignmentTypeModel->where('is_active', 1)->findAll();
        
        $data = [
            'title' => 'Manage Grade Components',
            'gradeComponents' => $gradeComponents,
            'courseOfferings' => $courseOfferings,
            'gradingPeriods' => $gradingPeriods,
            'assignmentTypes' => $assignmentTypes,
            'showCreateForm' => $showCreateForm,
            'showEditForm' => false
        ];

        return view('admin/manage_grade_components', $data);
    }/**
     * Get all grade components with related details
     */    private function getGradeComponentsWithDetails()
    {
        return $this->db->table('grade_components')
            ->select('
                grade_components.*,
                courses.course_code,
                courses.title as course_name,
                terms.term_name,
                academic_years.year_name,
                semesters.semester_name,
                grading_periods.period_name,
                assignment_types.type_name,
                assignment_types.type_code
            ')
            ->join('course_offerings', 'course_offerings.id = grade_components.course_offering_id', 'left')
            ->join('courses', 'courses.id = course_offerings.course_id', 'left')
            ->join('terms', 'terms.id = course_offerings.term_id', 'left')
            ->join('academic_years', 'academic_years.id = terms.academic_year_id', 'left')
            ->join('semesters', 'semesters.id = terms.semester_id', 'left')            ->join('grading_periods', 'grading_periods.id = grade_components.grading_period_id', 'left')
            ->join('assignment_types', 'assignment_types.id = grade_components.assignment_type_id', 'left')
            ->orderBy('courses.course_code', 'ASC')
            ->orderBy('grading_periods.period_order', 'ASC')
            ->orderBy('grade_components.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get course offerings with course and term details
     */    private function getCourseOfferingsWithDetails()
    {
        return $this->db->table('course_offerings')
            ->select('
                course_offerings.id,
                courses.course_code,
                courses.title as course_name,
                terms.term_name,
                academic_years.year_name,
                semesters.semester_name
            ')            ->join('courses', 'courses.id = course_offerings.course_id', 'left')
            ->join('terms', 'terms.id = course_offerings.term_id', 'left')
            ->join('academic_years', 'academic_years.id = terms.academic_year_id', 'left')
            ->join('semesters', 'semesters.id = terms.semester_id', 'left')
            ->whereIn('course_offerings.status', ['open', 'draft'])
            ->orderBy('academic_years.year_name', 'DESC')
            ->orderBy('courses.course_code', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get grading periods with term details
     */
    private function getGradingPeriodsWithDetails()
    {
        return $this->db->table('grading_periods')
            ->select('
                grading_periods.*,
                terms.term_name,
                academic_years.year_name,
                semesters.semester_name
            ')
            ->join('terms', 'terms.id = grading_periods.term_id', 'left')
            ->join('academic_years', 'academic_years.id = terms.academic_year_id', 'left')
            ->join('semesters', 'semesters.id = terms.semester_id', 'left')
            ->where('grading_periods.is_active', 1)
            ->orderBy('terms.id', 'DESC')
            ->orderBy('grading_periods.period_order', 'ASC')
            ->get()
            ->getResultArray();
    }
}
