<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\TermModel;
use App\Models\AcademicYearModel;
use App\Models\SemesterModel;

class Term extends BaseController
{
    protected $session;
    protected $validation;
    protected $db;
    protected $termModel;
    protected $academicYearModel;
    protected $semesterModel;

    /**
     * Constructor - Initialize models and dependencies
     */
    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->validation = \Config\Services::validation();
        $this->db = \Config\Database::connect();
        
        // Initialize models
        $this->termModel = new TermModel();
        $this->academicYearModel = new AcademicYearModel();
        $this->semesterModel = new SemesterModel();
    }

    public function index()
    {
        return redirect()->to(base_url('admin/manage_terms'));
    }

    /**
     * Manage Terms Method - Handles all term management operations
     * Supports create, edit, delete, and display operations
     */
    public function manageTerms()
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
        $termID = $this->request->getGet('id');

        // Route to appropriate action
        if ($action === 'create' && $this->request->getMethod() === 'POST') {
            return $this->createTerm();
        }

        if ($action === 'edit' && $termID) {
            return $this->editTerm($termID);
        }

        if ($action === 'delete' && $termID) {
            return $this->deleteTerm($termID);
        }

        if ($action === 'toggle_status' && $termID) {
            return $this->toggleStatus($termID);
        }

        if ($action === 'set_current' && $termID) {
            return $this->setCurrentTerm($termID);
        }

        // Display term management interface
        return $this->displayTermManagement();
    }    /**
     * Create a new term
     */    private function createTerm()
    {
        // Validation rules - dates must be within Academic Year range
        $rules = [
            'academic_year_id' => 'required|integer',
            'semester_id'      => 'required|integer',
            'term_name'        => 'required|min_length[3]|max_length[100]|regex_match[/^[a-zA-ZñÑ0-9\s]+$/u]',
            'start_date'       => 'permit_empty|valid_date|check_within_academic_year[academic_year_id]|check_date_order[end_date]',
            'end_date'         => 'permit_empty|valid_date|check_within_academic_year[academic_year_id]',
            'enrollment_start' => 'permit_empty|valid_date|check_enrollment_not_past|check_within_academic_year[academic_year_id]|check_date_order[enrollment_end]',
            'enrollment_end'   => 'permit_empty|valid_date|check_enrollment_not_past|check_within_academic_year[academic_year_id]'
        ];

        $messages = [
            'academic_year_id' => [
                'required' => 'Academic year is required.',
                'integer'  => 'Please select a valid academic year.'
            ],
            'semester_id' => [
                'required' => 'Semester is required.',
                'integer'  => 'Please select a valid semester.'
            ],
            'term_name' => [
                'required'    => 'Term name is required.',
                'min_length'  => 'Term name must be at least 3 characters.',
                'max_length'  => 'Term name must not exceed 100 characters.',
                'regex_match' => 'Term name can only contain letters (including Ñ/ñ), numbers, and spaces. No special characters allowed.'
            ],
            'start_date' => [
                'check_within_academic_year' => 'Term start date must be within the selected Academic Year.',
                'check_date_order'           => 'Term start date cannot be after end date.'
            ],
            'end_date' => [
                'check_within_academic_year' => 'Term end date must be within the selected Academic Year.'
            ],
            'enrollment_start' => [
                'check_enrollment_not_past'  => 'Enrollment start date cannot be in the past.',
                'check_within_academic_year' => 'Enrollment start date must be within the selected Academic Year.',
                'check_date_order'           => 'Enrollment start date cannot be after enrollment end date.'
            ],
            'enrollment_end' => [
                'check_enrollment_not_past'  => 'Enrollment end date cannot be in the past.',
                'check_within_academic_year' => 'Enrollment end date must be within the selected Academic Year.'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            $this->session->setFlashdata('errors', $this->validator->getErrors());
            $this->session->setFlashdata('error', 'Please fix the validation errors below.');
            return redirect()->to(base_url('admin/manage_terms?action=create'))->withInput();
        }

        // Prepare term data
        $termData = [
            'academic_year_id' => $this->request->getPost('academic_year_id'),
            'semester_id'      => $this->request->getPost('semester_id'),
            'term_name'        => $this->request->getPost('term_name'),
            'start_date'       => $this->request->getPost('start_date') ?: null,
            'end_date'         => $this->request->getPost('end_date') ?: null,
            'enrollment_start' => $this->request->getPost('enrollment_start') ?: null,
            'enrollment_end'   => $this->request->getPost('enrollment_end') ?: null,
            'is_current'       => $this->request->getPost('is_current') ? 1 : 0,
            'is_active'        => 1
        ];

        // Create term
        if ($this->termModel->insert($termData)) {
            $this->session->setFlashdata('success', 'Term created successfully!');
            return redirect()->to(base_url('admin/manage_terms'));
        } else {
            $this->session->setFlashdata('errors', $this->termModel->errors());
            $this->session->setFlashdata('error', 'Failed to create term. Please try again.');
            return redirect()->to(base_url('admin/manage_terms?action=create'))->withInput();
        }
    }

    /**
     * Edit an existing term
     */
    private function editTerm($termID)
    {
        $termToEdit = $this->termModel->find($termID);        if (!$termToEdit) {
            $this->session->setFlashdata('error', 'Term not found.');
            return redirect()->to(base_url('admin/manage_terms'));
        }        // Handle POST request (update)
        if ($this->request->getMethod() === 'POST') {
            // Validation rules - dates must be within Academic Year range
            // For editing, we allow term dates in the past but enrollment dates must not be in the past
            $rules = [
                'academic_year_id' => 'required|integer',
                'semester_id'      => 'required|integer',
                'term_name'        => 'required|min_length[3]|max_length[100]|regex_match[/^[a-zA-ZñÑ0-9\s]+$/u]',
                'start_date'       => 'permit_empty|valid_date|check_within_academic_year[academic_year_id]|check_date_order[end_date]',
                'end_date'         => 'permit_empty|valid_date|check_within_academic_year[academic_year_id]',
                'enrollment_start' => 'permit_empty|valid_date|check_enrollment_not_past|check_within_academic_year[academic_year_id]|check_date_order[enrollment_end]',
                'enrollment_end'   => 'permit_empty|valid_date|check_enrollment_not_past|check_within_academic_year[academic_year_id]'
            ];

            $messages = [
                'academic_year_id' => [
                    'required' => 'Academic year is required.',
                    'integer'  => 'Please select a valid academic year.'
                ],
                'semester_id' => [
                    'required' => 'Semester is required.',
                    'integer'  => 'Please select a valid semester.'
                ],
                'term_name' => [
                    'required'    => 'Term name is required.',
                    'min_length'  => 'Term name must be at least 3 characters.',
                    'max_length'  => 'Term name must not exceed 100 characters.',
                    'regex_match' => 'Term name can only contain letters (including Ñ/ñ), numbers, and spaces. No special characters allowed.'
                ],
                'start_date' => [
                    'check_within_academic_year' => 'Term start date must be within the selected Academic Year.',
                    'check_date_order'           => 'Term start date cannot be after end date.'
                ],
                'end_date' => [
                    'check_within_academic_year' => 'Term end date must be within the selected Academic Year.'
                ],
                'enrollment_start' => [
                    'check_enrollment_not_past'  => 'Enrollment start date cannot be in the past.',
                    'check_within_academic_year' => 'Enrollment start date must be within the selected Academic Year.',
                    'check_date_order'           => 'Enrollment start date cannot be after enrollment end date.'
                ],
                'enrollment_end' => [
                    'check_enrollment_not_past'  => 'Enrollment end date cannot be in the past.',
                    'check_within_academic_year' => 'Enrollment end date must be within the selected Academic Year.'
                ]
            ];

            if (!$this->validate($rules, $messages)) {
                $this->session->setFlashdata('errors', $this->validator->getErrors());
                $this->session->setFlashdata('error', 'Please fix the validation errors below.');
                return redirect()->to(base_url('admin/manage_terms?action=edit&id=' . $termID))->withInput();
            }

            // Prepare update data
            $updateData = [
                'academic_year_id' => $this->request->getPost('academic_year_id'),
                'semester_id'      => $this->request->getPost('semester_id'),
                'term_name'        => $this->request->getPost('term_name'),
                'start_date'       => $this->request->getPost('start_date') ?: null,
                'end_date'         => $this->request->getPost('end_date') ?: null,
                'enrollment_start' => $this->request->getPost('enrollment_start') ?: null,
                'enrollment_end'   => $this->request->getPost('enrollment_end') ?: null,
                'is_current'       => $this->request->getPost('is_current') ? 1 : 0
            ];

            // Update term
            if ($this->termModel->update($termID, $updateData)) {
                $this->session->setFlashdata('success', 'Term updated successfully!');
                return redirect()->to(base_url('admin/manage_terms'));
            } else {
                $this->session->setFlashdata('errors', $this->termModel->errors());
                $this->session->setFlashdata('error', 'Failed to update term. Please try again.');
                return redirect()->to(base_url('admin/manage_terms?action=edit&id=' . $termID))->withInput();
            }
        }

        // Show edit form
        $data = [
            'user' => [
                'role' => $this->session->get('role')
            ],
            'title' => 'Edit Term - Admin Dashboard',
            'terms' => [],
            'academicYears' => $this->academicYearModel->where('is_active', 1)->findAll(),
            'semesters' => $this->semesterModel->where('is_active', 1)->orderBy('semester_order', 'ASC')->findAll(),
            'editTerm' => $termToEdit,
            'showCreateForm' => false,
            'showEditForm' => true,
            'statistics' => $this->getTermStatistics()
        ];

        return view('admin/manage_terms', $data);
    }

    /**
     * Delete a term (Soft Delete)
     */
    private function deleteTerm($termID)
    {
        $termToDelete = $this->termModel->find($termID);

        if (!$termToDelete) {
            $this->session->setFlashdata('error', 'Term not found.');
            return redirect()->to(base_url('admin/manage_terms'));
        }

        // Check if term is current
        if ($termToDelete['is_current'] == 1) {
            $this->session->setFlashdata('error', 'Cannot delete the current term. Please set another term as current first.');
            return redirect()->to(base_url('admin/manage_terms'));
        }

        // Check if term is already inactive
        if ($termToDelete['is_active'] == 0) {
            $this->session->setFlashdata('error', 'This term is already deactivated.');
            return redirect()->to(base_url('admin/manage_terms'));
        }

        // Check referential integrity - Course Offerings
        $offeringsCount = $this->db->table('course_offerings')->where('term_id', $termID)->countAllResults();
        if ($offeringsCount > 0) {
            $this->session->setFlashdata('error', 'Cannot delete term "' . esc($termToDelete['term_name']) . '". It has ' . $offeringsCount . ' course offering(s). Please deactivate instead or remove the offerings first.');
            return redirect()->to(base_url('admin/manage_terms'));
        }

        // Check referential integrity - Grading Periods
        $gradingPeriodsCount = $this->db->table('grading_periods')->where('term_id', $termID)->countAllResults();
        if ($gradingPeriodsCount > 0) {
            $this->session->setFlashdata('error', 'Cannot delete term "' . esc($termToDelete['term_name']) . '". It has ' . $gradingPeriodsCount . ' grading period(s). Please remove the grading periods first or deactivate instead.');
            return redirect()->to(base_url('admin/manage_terms'));
        }

        // Check referential integrity - Enrollments (via course_offerings)
        $enrollmentsCount = $this->db->table('enrollments e')
            ->join('course_offerings co', 'co.id = e.course_offering_id')
            ->where('co.term_id', $termID)
            ->countAllResults();
        if ($enrollmentsCount > 0) {
            $this->session->setFlashdata('error', 'Cannot delete term "' . esc($termToDelete['term_name']) . '". It has ' . $enrollmentsCount . ' student enrollment(s). Please deactivate instead.');
            return redirect()->to(base_url('admin/manage_terms'));
        }

        // Soft delete: Set is_active to 0
        if ($this->termModel->update($termID, ['is_active' => 0])) {
            $this->session->setFlashdata('success', 'Term "' . esc($termToDelete['term_name']) . '" has been deactivated successfully!');
        } else {
            $this->session->setFlashdata('error', 'Failed to deactivate term. Please try again.');
        }

        return redirect()->to(base_url('admin/manage_terms'));
    }

    /**
     * Toggle term status (active/inactive)
     */
    private function toggleStatus($termID)
    {
        $term = $this->termModel->find($termID);

        if (!$term) {
            $this->session->setFlashdata('error', 'Term not found.');
            return redirect()->to(base_url('admin/manage_terms'));
        }

        $newStatus = $term['is_active'] == 1 ? 0 : 1;

        if ($this->termModel->update($termID, ['is_active' => $newStatus])) {
            $statusText = $newStatus == 1 ? 'activated' : 'deactivated';
            $this->session->setFlashdata('success', "Term {$statusText} successfully!");
        } else {
            $this->session->setFlashdata('error', 'Failed to update term status. Please try again.');
        }

        return redirect()->to(base_url('admin/manage_terms'));
    }

    /**
     * Set term as current
     */
    private function setCurrentTerm($termID)
    {
        $term = $this->termModel->find($termID);

        if (!$term) {
            $this->session->setFlashdata('error', 'Term not found.');
            return redirect()->to(base_url('admin/manage_terms'));
        }

        if ($this->termModel->setAsCurrent($termID)) {
            $this->session->setFlashdata('success', 'Term set as current successfully!');
        } else {
            $this->session->setFlashdata('error', 'Failed to set term as current. Please try again.');
        }

        return redirect()->to(base_url('admin/manage_terms'));
    }

    /**
     * Display term management interface
     */
    private function displayTermManagement()
    {
        $showCreateForm = $this->request->getGet('action') === 'create';

        // Get all terms with details
        $terms = $this->termModel->getAllWithDetails();

        $data = [
            'user' => [
                'role' => $this->session->get('role')
            ],
            'title' => 'Manage Terms - Admin Dashboard',
            'terms' => $terms,
            'academicYears' => $this->academicYearModel->where('is_active', 1)->findAll(),
            'semesters' => $this->semesterModel->where('is_active', 1)->orderBy('semester_order', 'ASC')->findAll(),
            'showCreateForm' => $showCreateForm,
            'showEditForm' => false,
            'statistics' => $this->getTermStatistics()
        ];

        return view('admin/manage_terms', $data);
    }

    /**
     * Get term statistics
     */
    private function getTermStatistics()
    {
        return [
            'total' => $this->termModel->countAll(),
            'active' => $this->termModel->where('is_active', 1)->countAllResults(),
            'inactive' => $this->termModel->where('is_active', 0)->countAllResults(),
            'current' => $this->termModel->where('is_current', 1)->countAllResults()
        ];
    }

    /**
     * Search terms - AJAX endpoint
     * Accepts GET or POST requests with search term
     * Searches term_name, academic year, and semester
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
            // Search terms using Query Builder with LIKE queries
            $results = $this->termModel->select('
                    terms.*,
                    academic_years.year_name,
                    semesters.semester_name
                ')
                ->join('academic_years', 'academic_years.id = terms.academic_year_id', 'left')
                ->join('semesters', 'semesters.id = terms.semester_id', 'left')
                ->groupStart()
                    ->like('terms.term_name', $searchTerm)
                    ->orLike('academic_years.year_name', $searchTerm)
                    ->orLike('semesters.semester_name', $searchTerm)
                ->groupEnd()
                ->orderBy('terms.start_date', 'DESC')
                ->findAll();

            return $this->response->setJSON([
                'success' => true,
                'count' => count($results),
                'data' => $results,
                'search_term' => $searchTerm
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Term search error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while searching terms'
            ]);
        }
    }
}
