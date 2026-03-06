<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CourseInstructorModel;
use App\Models\CourseOfferingModel;
use App\Models\InstructorModel;
use App\Models\TermModel;
use App\Models\NotificationModel;
use App\Models\UserModel;

class CourseInstructors extends BaseController
{
    protected $session;
    protected $validation;
    protected $db;
    protected $courseInstructorModel;
    protected $offeringModel;
    protected $instructorModel;
    protected $termModel;
    protected $notificationModel;
    protected $userModel;

    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->validation = \Config\Services::validation();
        $this->db = \Config\Database::connect();
        
        $this->courseInstructorModel = new CourseInstructorModel();
        $this->offeringModel = new CourseOfferingModel();
        $this->instructorModel = new InstructorModel();
        $this->termModel = new TermModel();
        $this->notificationModel = new NotificationModel();
        $this->userModel = new UserModel();
    }

    /**
     * Manage Course Instructors - Main method
     */
    public function manageInstructors()
    {
        // Security check
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
        $assignmentID = $this->request->getGet('id');
        $offeringID = $this->request->getGet('offering_id');

        // Route to appropriate action
        if ($action === 'assign' && $this->request->getMethod() === 'POST') {
            return $this->assignInstructor();
        }

        if ($action === 'remove' && $assignmentID) {
            return $this->removeInstructor($assignmentID);
        }

        if ($action === 'set_primary' && $assignmentID) {
            return $this->setPrimaryInstructor($assignmentID);
        }

        // Display instructor management interface
        return $this->displayInstructorManagement($offeringID);
    }

    /**
     * Assign instructor to course offering
     */
    private function assignInstructor()
    {
        // Validation rules
        $rules = [
            'course_offering_id' => 'required|integer',
            'instructor_id'      => 'required|integer',
            'is_primary'         => 'permit_empty|in_list[0,1]'
        ];

        $messages = [
            'course_offering_id' => [
                'required' => 'Course offering is required.',
                'integer'  => 'Please select a valid course offering.'
            ],
            'instructor_id' => [
                'required' => 'Instructor is required.',
                'integer'  => 'Please select a valid instructor.'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            $this->session->setFlashdata('errors', $this->validator->getErrors());
            $this->session->setFlashdata('error', 'Please fix the validation errors below.');
            return redirect()->to(base_url('admin/manage_course_instructors?action=assign&offering_id=' . $this->request->getPost('course_offering_id')))->withInput();
        }

        $offeringId = $this->request->getPost('course_offering_id');
        $instructorId = $this->request->getPost('instructor_id');
        $isPrimary = $this->request->getPost('is_primary') == '1';

        // Check if already assigned
        if ($this->courseInstructorModel->isAssigned($offeringId, $instructorId)) {
            $this->session->setFlashdata('error', 'This instructor is already assigned to this course offering.');
            return redirect()->to(base_url('admin/manage_course_instructors?offering_id=' . $offeringId));
        }        // Assign instructor
        if ($this->courseInstructorModel->assignInstructor($offeringId, $instructorId, $isPrimary)) {
            // Get course offering details for notification
            $courseOffering = $this->db->table('course_offerings co')
                ->select('co.*, c.course_code, c.title as course_title, t.term_name, ay.year_name as academic_year')
                ->join('courses c', 'c.id = co.course_id')
                ->join('terms t', 't.id = co.term_id')
                ->join('academic_years ay', 'ay.id = t.academic_year_id')
                ->where('co.id', $offeringId)
                ->get()
                ->getRowArray();
            
            // Get instructor details
            $instructor = $this->instructorModel->find($instructorId);
            $instructorUser = $this->userModel->find($instructor['user_id']);
            
            // Get admin user ID (current user)
            $adminUserId = $this->session->get('userID');
            $adminUser = $this->userModel->find($adminUserId);
            
            // Send notification to instructor
            $instructorMessage = sprintf(
                "You have been assigned to teach %s (%s) - Section %s for %s %s%s",
                $courseOffering['course_title'],
                $courseOffering['course_code'],
                $courseOffering['section'],
                $courseOffering['term_name'],
                $courseOffering['academic_year'],
                $isPrimary ? ' as Primary Instructor' : ''
            );
            
            $this->notificationModel->createNotification(
                $instructor['user_id'],
                $instructorMessage,
                'assignment',
                $offeringId,
                'course_offering'
            );
            
            // Send confirmation notification to admin
            $adminMessage = sprintf(
                "Successfully assigned %s to %s (%s) - Section %s%s",
                $instructorUser['first_name'] . ' ' . $instructorUser['last_name'],
                $courseOffering['course_title'],
                $courseOffering['course_code'],
                $courseOffering['section'],
                $isPrimary ? ' as Primary Instructor' : ''
            );
            
            $this->notificationModel->createNotification(
                $adminUserId,
                $adminMessage,
                'success',
                $offeringId,
                'course_offering'
            );
            
            $instructorName = $this->courseInstructorModel->getInstructorName($instructorId);
            $this->session->setFlashdata('success', "Instructor {$instructorName} assigned successfully! Notification sent.");
            return redirect()->to(base_url('admin/manage_course_instructors?offering_id=' . $offeringId));
        } else {
            // Get model errors for debugging
            $modelErrors = $this->courseInstructorModel->errors();
            $errorMessage = 'Failed to assign instructor. ';
            if (!empty($modelErrors)) {
                $errorMessage .= 'Errors: ' . implode(', ', $modelErrors);
            } else {
                $errorMessage .= 'Please check the database logs.';
            }
            
            // Log the error
            log_message('error', 'CourseInstructor Assignment Failed - Offering: ' . $offeringId . ', Instructor: ' . $instructorId . ', isPrimary: ' . ($isPrimary ? 'Yes' : 'No') . ', Errors: ' . json_encode($modelErrors));
            
            $this->session->setFlashdata('error', $errorMessage);
            return redirect()->to(base_url('admin/manage_course_instructors?action=assign&offering_id=' . $offeringId))->withInput();
        }
    }

    /**
     * Remove instructor from course offering
     */
    private function removeInstructor($assignmentID)
    {
        $assignment = $this->courseInstructorModel->find($assignmentID);

        if (!$assignment) {
            $this->session->setFlashdata('error', 'Instructor assignment not found.');
            return redirect()->to(base_url('admin/manage_course_instructors'));
        }

        $offeringId = $assignment['course_offering_id'];
        $instructorName = $this->courseInstructorModel->getInstructorName($assignment['instructor_id']);

        // Validation: Prevent removal if there are enrolled students
        $enrolledCount = $this->db->table('enrollments')
            ->where('course_offering_id', $offeringId)
            ->where('enrollment_status', 'enrolled')
            ->countAllResults();

        if ($enrolledCount > 0) {
            $this->session->setFlashdata('error', "Cannot remove instructor {$instructorName} because there are enrolled students in this course offering.");
            return redirect()->to(base_url('admin/manage_course_instructors?offering_id=' . $offeringId));
        }

        if ($this->courseInstructorModel->delete($assignmentID)) {
            $this->session->setFlashdata('success', "Instructor {$instructorName} removed successfully!");
        } else {
            $this->session->setFlashdata('error', 'Failed to remove instructor. Please try again.');
        }

        return redirect()->to(base_url('admin/manage_course_instructors?offering_id=' . $offeringId));
    }

    /**
     * Set instructor as primary
     */
    private function setPrimaryInstructor($assignmentID)
    {
        $assignment = $this->courseInstructorModel->find($assignmentID);

        if (!$assignment) {
            $this->session->setFlashdata('error', 'Instructor assignment not found.');
            return redirect()->to(base_url('admin/manage_course_instructors'));
        }

        $offeringId = $assignment['course_offering_id'];
        $instructorId = $assignment['instructor_id'];

        if ($this->courseInstructorModel->setPrimary($offeringId, $instructorId)) {
            $instructorName = $this->courseInstructorModel->getInstructorName($instructorId);
            $this->session->setFlashdata('success', "{$instructorName} set as primary instructor!");
        } else {
            $this->session->setFlashdata('error', 'Failed to set primary instructor. Please try again.');
        }

        return redirect()->to(base_url('admin/manage_course_instructors?offering_id=' . $offeringId));
    }

    /**
     * Display instructor management interface
     */
    private function displayInstructorManagement($offeringID = null)
    {
        // Get all course offerings with details
        $offerings = $this->db->table('course_offerings co')
            ->select('co.*, c.course_code, c.title, t.term_name, t.id as term_id')
            ->join('courses c', 'c.id = co.course_id')
            ->join('terms t', 't.id = co.term_id')
            ->orderBy('t.id', 'DESC')
            ->orderBy('c.course_code', 'ASC')
            ->get()
            ->getResultArray();

        // Get all active instructors
        $instructors = $this->instructorModel->getActiveInstructors();

        // Get assignments and offering details if offering is selected
        $assignments = [];
        $selectedOffering = null;
        $availableInstructors = $instructors;
        
        if ($offeringID) {
            $assignments = $this->courseInstructorModel->getOfferingInstructors($offeringID);
            
            $selectedOffering = $this->db->table('course_offerings co')
                ->select('co.*, c.course_code, c.title, c.credits, t.term_name')
                ->join('courses c', 'c.id = co.course_id')
                ->join('terms t', 't.id = co.term_id')
                ->where('co.id', $offeringID)
                ->get()
                ->getRowArray();

            // Filter out already assigned instructors
            $assignedIds = array_column($assignments, 'instructor_id');
            $availableInstructors = array_filter($instructors, function($instructor) use ($assignedIds) {
                return !in_array($instructor['id'], $assignedIds);
            });
        }

        $data = [
            'user' => [
                'userID' => $this->session->get('userID'),
                'name'   => $this->session->get('name'),
                'email'  => $this->session->get('email'),
                'role'   => $this->session->get('role')
            ],
            'title' => 'Manage Course Instructors - Admin Dashboard',
            'offerings' => $offerings,
            'instructors' => $instructors,
            'availableInstructors' => $availableInstructors,
            'assignments' => $assignments,
            'selectedOffering' => $selectedOffering,
            'selectedOfferingId' => $offeringID,
            'showAssignForm' => $this->request->getGet('action') === 'assign'
        ];

        return view('admin/manage_course_instructors', $data);
    }

    /**
     * Teacher Courses - Show courses assigned to the logged-in teacher
     */
    public function teacherCourses()
    {
        // Security check - only teachers can access
        if ($this->session->get('isLoggedIn') !== true) {
            $this->session->setFlashdata('error', 'Please login to access this page.');
            return redirect()->to(base_url('login'));
        }
        
        if ($this->session->get('role') !== 'teacher') {
            $this->session->setFlashdata('error', 'Access denied. You do not have permission to access this page.');
            $userRole = $this->session->get('role');
            return redirect()->to(base_url($userRole . '/dashboard'));
        }

        // Get the logged-in user's instructor record
        $userId = $this->session->get('userID');
        $instructor = $this->instructorModel->where('user_id', $userId)->first();

        if (!$instructor) {
            $this->session->setFlashdata('error', 'Instructor profile not found. Please contact the administrator.');
            return redirect()->to(base_url('teacher/dashboard'));
        }

        // Get all courses assigned to this instructor with full details
        $assignedCourses = $this->getTeacherAssignedCourses($instructor['id']);

        $data = [
            'title' => 'My Courses - Teacher Dashboard',
            'assignedCourses' => $assignedCourses,
            'instructor' => $instructor
        ];

        return view('teacher/courses', $data);
    }    /**
     * Get all courses assigned to a teacher with full details
     */    private function getTeacherAssignedCourses($instructorId)
    {
        $courses = $this->db->table('course_instructors ci')
            ->select('
                ci.id as assignment_id,
                ci.is_primary,
                ci.created_at as assigned_date,
                co.id as offering_id,
                co.status as offering_status,
                co.max_students,
                co.start_date,
                co.end_date,
                c.id as course_id,
                c.course_code,
                c.title,
                c.description,
                c.credits,
                cat.category_name as category,
                t.term_name,
                ay.year_name as academic_year,
                s.semester_name,
                (SELECT COUNT(*) FROM enrollments e WHERE e.course_offering_id = co.id AND e.enrollment_status = "enrolled") as enrolled_students
            ')
            ->join('course_offerings co', 'co.id = ci.course_offering_id')
            ->join('courses c', 'c.id = co.course_id')
            ->join('categories cat', 'cat.id = c.category_id', 'left')
            ->join('terms t', 't.id = co.term_id')
            ->join('academic_years ay', 'ay.id = t.academic_year_id')
            ->join('semesters s', 's.id = t.semester_id')
            ->where('ci.instructor_id', $instructorId)
            ->orderBy('ay.year_name', 'DESC')
            ->orderBy('s.id', 'DESC')
            ->orderBy('c.course_code', 'ASC')
            ->get()
            ->getResultArray();        // Get enrolled students and co-instructors for each course
        foreach ($courses as &$course) {
            // Get enrolled students
            $course['students'] = $this->db->table('enrollments e')
                ->select('
                    s.id as student_id,
                    u.id as user_id,
                    u.first_name,
                    u.middle_name,
                    u.last_name,
                    u.suffix,
                    u.email,
                    e.enrollment_status,
                    e.enrollment_date,
                    e.year_level_id,
                    e.enrollment_type,
                    CONCAT(u.first_name, " ", COALESCE(u.middle_name, ""), " ", u.last_name) as full_name
                ')
                ->join('students s', 's.id = e.student_id')
                ->join('users u', 'u.id = s.user_id')
                ->where('e.course_offering_id', $course['offering_id'])
                ->where('e.enrollment_status', 'enrolled')
                ->orderBy('u.last_name', 'ASC')
                ->orderBy('u.first_name', 'ASC')
                ->get()
                ->getResultArray();

            // Get co-instructors (other instructors assigned to same course)
            $course['co_instructors'] = $this->db->table('course_instructors ci')
                ->select('
                    i.id as instructor_id,
                    u.first_name,
                    u.middle_name,
                    u.last_name,
                    u.suffix,
                    u.email,
                    ci.is_primary,
                    CONCAT(u.first_name, " ", COALESCE(u.middle_name, ""), " ", u.last_name) as full_name
                ')
                ->join('instructors i', 'i.id = ci.instructor_id')
                ->join('users u', 'u.id = i.user_id')
                ->where('ci.course_offering_id', $course['offering_id'])
                ->where('ci.instructor_id !=', $instructorId)
                ->orderBy('u.last_name', 'ASC')
                ->orderBy('u.first_name', 'ASC')
                ->get()
                ->getResultArray();
        }

        return $courses;
    }    /**
     * View Course Details - AJAX endpoint for modal
     */
    public function viewCourse($offeringId)
    {
        // Security check
        if ($this->session->get('isLoggedIn') !== true) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please login to access this page.'
            ]);
        }
        
        if ($this->session->get('role') !== 'teacher') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied.'
            ]);
        }

        // Get teacher's instructor ID
        $userId = $this->session->get('userID');
        $instructor = $this->instructorModel->where('user_id', $userId)->first();
        
        if (!$instructor) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Instructor profile not found.'
            ]);
        }

        // Verify teacher is assigned to this course
        $assignment = $this->courseInstructorModel
            ->where('instructor_id', $instructor['id'])
            ->where('course_offering_id', $offeringId)
            ->first();

        if (!$assignment) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You are not assigned to this course.'
            ]);
        }

        // Get full course offering details
        $courseDetails = $this->db->table('course_offerings co')
            ->select('
                co.*,
                c.course_code,
                c.title,
                c.description,
                c.credits,
                c.lecture_hours,
                c.lab_hours,
                cat.category_name,
                t.term_name,
                ay.year_name as academic_year,
                s.semester_name,
                d.department_name,
                (SELECT COUNT(*) FROM enrollments e WHERE e.course_offering_id = co.id AND e.enrollment_status = "enrolled") as enrolled_count
            ')
            ->join('courses c', 'c.id = co.course_id')
            ->join('categories cat', 'cat.id = c.category_id', 'left')
            ->join('terms t', 't.id = co.term_id')
            ->join('academic_years ay', 'ay.id = t.academic_year_id')
            ->join('semesters s', 's.id = t.semester_id')
            ->join('departments d', 'd.id = c.department_id', 'left')
            ->where('co.id', $offeringId)
            ->get()
            ->getRowArray();

        if (!$courseDetails) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Course offering not found.'
            ]);
        }

        // Get enrolled students
        $students = $this->db->table('enrollments e')
            ->select('
                CONCAT(u.first_name, " ", COALESCE(u.middle_name, ""), " ", u.last_name) as full_name,
                u.email,
                s.student_id_number,
                e.enrollment_status,
                e.enrollment_date,
                yl.year_level_name,
                p.program_code
            ')
            ->join('students s', 's.id = e.student_id')
            ->join('users u', 'u.id = s.user_id')
            ->join('year_levels yl', 'yl.id = s.year_level_id', 'left')
            ->join('programs p', 'p.id = s.program_id', 'left')
            ->where('e.course_offering_id', $offeringId)
            ->where('e.enrollment_status', 'enrolled')
            ->orderBy('u.last_name', 'ASC')
            ->get()
            ->getResultArray();

        // Get co-instructors
        $coInstructors = $this->db->table('course_instructors ci')
            ->select('
                CONCAT(u.first_name, " ", COALESCE(u.middle_name, ""), " ", u.last_name) as full_name,
                u.email,
                ci.is_primary,
                i.employee_id
            ')
            ->join('instructors i', 'i.id = ci.instructor_id')
            ->join('users u', 'u.id = i.user_id')
            ->where('ci.course_offering_id', $offeringId)
            ->where('ci.instructor_id !=', $instructor['id'])
            ->orderBy('ci.is_primary', 'DESC')
            ->get()
            ->getResultArray();

        // Get material count
        $materialCount = $this->db->table('materials')
            ->where('course_offering_id', $offeringId)
            ->countAllResults();

        return $this->response->setJSON([
            'success' => true,
            'course' => $courseDetails,
            'students' => $students,
            'coInstructors' => $coInstructors,
            'materialCount' => $materialCount,
            'isPrimary' => $assignment['is_primary']
        ]);
    }

    /**
     * View Gradebook (Placeholder - to be implemented)
     */
    public function viewGradebook($offeringId)
    {
        // Security check
        if ($this->session->get('isLoggedIn') !== true) {
            $this->session->setFlashdata('error', 'Please login to access this page.');
            return redirect()->to(base_url('login'));
        }
        
        if ($this->session->get('role') !== 'teacher') {
            $this->session->setFlashdata('error', 'Access denied.');
            return redirect()->to(base_url('teacher/dashboard'));
        }

        // Placeholder: Redirect to courses page for now
        $this->session->setFlashdata('info', 'Gradebook page is under development. Please check back later.');
        return redirect()->to(base_url('teacher/courses'));
    }

    /**
     * AJAX Search Teacher Courses - Search assigned courses
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function searchTeacherCourses()
    {
        // Check if request is AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        // Security check
        if ($this->session->get('isLoggedIn') !== true) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You must be logged in to search courses.',
                'error_code' => 'NOT_AUTHENTICATED'
            ]);
        }

        if ($this->session->get('role') !== 'teacher') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Teacher privileges required.',
                'error_code' => 'NOT_AUTHORIZED'
            ]);
        }

        $userId = $this->session->get('userID');

        // Get instructor record
        $instructor = $this->instructorModel->where('user_id', $userId)->first();

        if (!$instructor) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Instructor profile not found.',
                'error_code' => 'INSTRUCTOR_NOT_FOUND'
            ]);
        }

        $instructorId = $instructor['id'];

        // Get search term
        $searchTerm = $this->request->getPost('search') ?? $this->request->getGet('search');

        if (empty($searchTerm)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Search term is required'
            ]);
        }

        try {
            // Search assigned courses with LIKE queries
            $courses = $this->db->table('course_instructors ci')
                ->select('
                    ci.id as assignment_id,
                    ci.is_primary,
                    ci.created_at as assigned_date,
                    co.id as offering_id,
                    co.section,
                    co.status as offering_status,
                    co.max_students,
                    co.start_date,
                    co.end_date,
                    c.id as course_id,
                    c.course_code,
                    c.title,
                    c.description,
                    c.credits,
                    cat.category_name as category,
                    t.term_name,
                    ay.year_name as academic_year,
                    s.semester_name,
                    (SELECT COUNT(*) FROM enrollments e WHERE e.course_offering_id = co.id AND e.enrollment_status = "enrolled") as enrolled_students
                ')
                ->join('course_offerings co', 'co.id = ci.course_offering_id')
                ->join('courses c', 'c.id = co.course_id')
                ->join('categories cat', 'cat.id = c.category_id', 'left')
                ->join('terms t', 't.id = co.term_id')
                ->join('academic_years ay', 'ay.id = t.academic_year_id')
                ->join('semesters s', 's.id = t.semester_id')
                ->where('ci.instructor_id', $instructorId)
                ->groupStart()
                    ->like('c.course_code', $searchTerm)
                    ->orLike('c.title', $searchTerm)
                    ->orLike('c.description', $searchTerm)
                    ->orLike('cat.category_name', $searchTerm)
                    ->orLike('t.term_name', $searchTerm)
                    ->orLike('ay.year_name', $searchTerm)
                    ->orLike('s.semester_name', $searchTerm)
                ->groupEnd()
                ->orderBy('ay.year_name', 'DESC')
                ->orderBy('c.course_code', 'ASC')
                ->get()
                ->getResultArray();

            return $this->response->setJSON([
                'success' => true,
                'count' => count($courses),
                'data' => $courses,
                'search_term' => $searchTerm
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Teacher courses search error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while searching courses'
            ]);
        }
    }
}
