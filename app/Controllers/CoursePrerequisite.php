<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CoursePrerequisiteModel;
use App\Models\CourseModel;

class CoursePrerequisite extends BaseController
{
    protected $session;
    protected $validation;
    protected $db;
    protected $prerequisiteModel;
    protected $courseModel;

    /**
     * Constructor - Initialize models and dependencies
     */
    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->validation = \Config\Services::validation();
        $this->db = \Config\Database::connect();
        
        // Initialize models
        $this->prerequisiteModel = new CoursePrerequisiteModel();
        $this->courseModel = new CourseModel();
    }

    public function index()
    {
        return redirect()->to(base_url('admin/manage_prerequisites'));
    }

    /**
     * Manage Course Prerequisites Method - Handles all prerequisite management operations
     * Supports create, edit, delete, and display operations
     */
    public function managePrerequisites()
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
        $prerequisiteID = $this->request->getGet('id');
        $courseID = $this->request->getGet('course_id');

        // Route to appropriate action
        if ($action === 'create' && $this->request->getMethod() === 'POST') {
            return $this->createPrerequisite();
        }

        if ($action === 'edit' && $prerequisiteID) {
            return $this->editPrerequisite($prerequisiteID);
        }

        if ($action === 'delete' && $prerequisiteID) {
            return $this->deletePrerequisite($prerequisiteID);
        }

        // Display prerequisite management interface
        return $this->displayPrerequisiteManagement($courseID);
    }

    /**
     * Create a new prerequisite
     */
    private function createPrerequisite()
    {        // Validation rules
        $rules = [
            'course_id'              => 'required|integer',
            'prerequisite_course_id' => 'required|integer|differs[course_id]',
            'prerequisite_type'      => 'required|in_list[required,recommended,corequisite]',
            'minimum_grade'          => 'permit_empty|integer|greater_than_equal_to[75]|less_than_equal_to[100]'
        ];        $messages = [
            'course_id' => [
                'required' => 'Course is required.',
                'integer'  => 'Please select a valid course.'
            ],
            'prerequisite_course_id' => [
                'required' => 'Prerequisite course is required.',
                'integer'  => 'Please select a valid prerequisite course.',
                'differs'  => 'A course cannot be its own prerequisite.'
            ],
            'prerequisite_type' => [
                'required' => 'Prerequisite type is required.',
                'in_list'  => 'Invalid prerequisite type selected.'
            ],
            'minimum_grade' => [
                'integer'               => 'Minimum grade must be a whole number.',
                'greater_than_equal_to' => 'Minimum grade must be at least 75 (passing score).',
                'less_than_equal_to'    => 'Minimum grade cannot exceed 100.'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            $this->session->setFlashdata('errors', $this->validator->getErrors());
            $this->session->setFlashdata('error', 'Please fix the validation errors below.');
            return redirect()->to(base_url('admin/manage_prerequisites?action=create'))->withInput();
        }

        $courseId = $this->request->getPost('course_id');
        $prerequisiteCourseId = $this->request->getPost('prerequisite_course_id');

        // Check if prerequisite already exists
        $exists = $this->prerequisiteModel
            ->where('course_id', $courseId)
            ->where('prerequisite_course_id', $prerequisiteCourseId)
            ->first();

        if ($exists) {
            $this->session->setFlashdata('error', 'This prerequisite already exists for the selected course.');
            return redirect()->to(base_url('admin/manage_prerequisites?action=create&course_id=' . $courseId))->withInput();
        }

        // Check for circular dependency
        if ($this->hasCircularDependency($courseId, $prerequisiteCourseId)) {
            $this->session->setFlashdata('error', 'Cannot add prerequisite: This would create a circular dependency.');
            return redirect()->to(base_url('admin/manage_prerequisites?action=create&course_id=' . $courseId))->withInput();
        }

        // Prepare prerequisite data
        $prerequisiteData = [
            'course_id'              => $courseId,
            'prerequisite_course_id' => $prerequisiteCourseId,
            'prerequisite_type'      => $this->request->getPost('prerequisite_type'),
            'minimum_grade'          => $this->request->getPost('minimum_grade') ?: null
        ];

        // Create prerequisite
        if ($this->prerequisiteModel->insert($prerequisiteData)) {
            $this->session->setFlashdata('success', 'Course prerequisite added successfully!');
            return redirect()->to(base_url('admin/manage_prerequisites?course_id=' . $courseId));
        } else {
            $this->session->setFlashdata('errors', $this->prerequisiteModel->errors());
            $this->session->setFlashdata('error', 'Failed to add prerequisite. Please try again.');
            return redirect()->to(base_url('admin/manage_prerequisites?action=create&course_id=' . $courseId))->withInput();
        }
    }

    /**
     * Edit an existing prerequisite
     */
    private function editPrerequisite($prerequisiteID)
    {
        $prerequisiteToEdit = $this->prerequisiteModel->find($prerequisiteID);

        if (!$prerequisiteToEdit) {
            $this->session->setFlashdata('error', 'Prerequisite not found.');
            return redirect()->to(base_url('admin/manage_prerequisites'));
        }

        // Handle POST request (update)
        if ($this->request->getMethod() === 'POST') {            // Validation rules
            $rules = [
                'prerequisite_type' => 'required|in_list[required,recommended,corequisite]',
                'minimum_grade'     => 'permit_empty|integer|greater_than_equal_to[75]|less_than_equal_to[100]'
            ];            $messages = [
                'prerequisite_type' => [
                    'required' => 'Prerequisite type is required.',
                    'in_list'  => 'Invalid prerequisite type selected.'
                ],
                'minimum_grade' => [
                    'integer'               => 'Minimum grade must be a whole number.',
                    'greater_than_equal_to' => 'Minimum grade must be at least 75 (passing score).',
                    'less_than_equal_to'    => 'Minimum grade cannot exceed 100.'
                ]
            ];

            if (!$this->validate($rules, $messages)) {
                $this->session->setFlashdata('errors', $this->validator->getErrors());
                $this->session->setFlashdata('error', 'Please fix the validation errors below.');
                return redirect()->to(base_url('admin/manage_prerequisites?action=edit&id=' . $prerequisiteID))->withInput();
            }

            // Prepare update data
            $updateData = [
                'prerequisite_type' => $this->request->getPost('prerequisite_type'),
                'minimum_grade'     => $this->request->getPost('minimum_grade') ?: null
            ];

            // Update prerequisite
            if ($this->prerequisiteModel->update($prerequisiteID, $updateData)) {
                $this->session->setFlashdata('success', 'Prerequisite updated successfully!');
                return redirect()->to(base_url('admin/manage_prerequisites?course_id=' . $prerequisiteToEdit['course_id']));
            } else {
                $this->session->setFlashdata('errors', $this->prerequisiteModel->errors());
                $this->session->setFlashdata('error', 'Failed to update prerequisite. Please try again.');
                return redirect()->to(base_url('admin/manage_prerequisites?action=edit&id=' . $prerequisiteID))->withInput();
            }
        }

        // Get course details
        $course = $this->courseModel->find($prerequisiteToEdit['course_id']);
        $prerequisiteCourse = $this->courseModel->find($prerequisiteToEdit['prerequisite_course_id']);

        // Show edit form
        $data = [
            'user' => [
                'role' => $this->session->get('role')
            ],
            'title' => 'Edit Prerequisite - Admin Dashboard',
            'prerequisites' => [],
            'courses' => $this->courseModel->where('is_active', 1)->orderBy('course_code', 'ASC')->findAll(),
            'editPrerequisite' => $prerequisiteToEdit,
            'course' => $course,
            'prerequisiteCourse' => $prerequisiteCourse,
            'showCreateForm' => false,
            'showEditForm' => true,
            'selectedCourseId' => $prerequisiteToEdit['course_id'],
            'statistics' => $this->getPrerequisiteStatistics()
        ];

        return view('admin/manage_prerequisites', $data);
    }

    /**
     * Delete a prerequisite
     */
    private function deletePrerequisite($prerequisiteID)
    {
        $prerequisiteToDelete = $this->prerequisiteModel->find($prerequisiteID);

        if (!$prerequisiteToDelete) {
            $this->session->setFlashdata('error', 'Prerequisite not found.');
            return redirect()->to(base_url('admin/manage_prerequisites'));
        }

        $courseId = $prerequisiteToDelete['course_id'];

        // Delete prerequisite
        if ($this->prerequisiteModel->delete($prerequisiteID)) {
            $this->session->setFlashdata('success', 'Prerequisite deleted successfully!');
        } else {
            $this->session->setFlashdata('error', 'Failed to delete prerequisite. Please try again.');
        }

        return redirect()->to(base_url('admin/manage_prerequisites?course_id=' . $courseId));
    }

    /**
     * Display prerequisite management interface
     */
    private function displayPrerequisiteManagement($courseID = null)
    {
        $showCreateForm = $this->request->getGet('action') === 'create';

        // Get all prerequisites or prerequisites for specific course
        if ($courseID) {
            $prerequisites = $this->prerequisiteModel->getPrerequisitesWithDetails($courseID);
            $selectedCourse = $this->courseModel->find($courseID);
        } else {
            // Get all prerequisites with course details
            $prerequisites = $this->db->table('course_prerequisites cp')
                ->select('cp.*, 
                         c1.course_code as course_code, 
                         c1.title as course_title,
                         c2.course_code as prereq_course_code, 
                         c2.title as prereq_course_title')
                ->join('courses c1', 'c1.id = cp.course_id')
                ->join('courses c2', 'c2.id = cp.prerequisite_course_id')
                ->orderBy('c1.course_code', 'ASC')
                ->get()
                ->getResultArray();
            $selectedCourse = null;
        }

        $data = [
            'user' => [
                'role' => $this->session->get('role')
            ],
            'title' => 'Manage Course Prerequisites - Admin Dashboard',
            'prerequisites' => $prerequisites,
            'courses' => $this->courseModel->where('is_active', 1)->orderBy('course_code', 'ASC')->findAll(),
            'showCreateForm' => $showCreateForm,
            'showEditForm' => false,
            'selectedCourseId' => $courseID,
            'selectedCourse' => $selectedCourse,
            'statistics' => $this->getPrerequisiteStatistics()
        ];

        return view('admin/manage_prerequisites', $data);
    }

    /**
     * Get prerequisite statistics
     */
    private function getPrerequisiteStatistics()
    {
        return [
            'total' => $this->prerequisiteModel->countAll(),
            'required' => $this->prerequisiteModel->where('prerequisite_type', 'required')->countAllResults(),
            'recommended' => $this->prerequisiteModel->where('prerequisite_type', 'recommended')->countAllResults(),
            'corequisite' => $this->prerequisiteModel->where('prerequisite_type', 'corequisite')->countAllResults()
        ];
    }

    /**
     * Check for circular dependency
     */
    private function hasCircularDependency($courseId, $prerequisiteCourseId, $visited = [])
    {
        // If we've already visited this course, we have a cycle
        if (in_array($prerequisiteCourseId, $visited)) {
            return true;
        }

        // Add current course to visited list
        $visited[] = $prerequisiteCourseId;

        // Get prerequisites of the prerequisite course
        $prerequisites = $this->prerequisiteModel
            ->where('course_id', $prerequisiteCourseId)
            ->findAll();

        // Check each prerequisite recursively
        foreach ($prerequisites as $prereq) {
            if ($prereq['prerequisite_course_id'] == $courseId) {
                return true; // Direct circular dependency
            }
            
            // Check for indirect circular dependency
            if ($this->hasCircularDependency($courseId, $prereq['prerequisite_course_id'], $visited)) {
                return true;
            }
        }

        return false;
    }
}
