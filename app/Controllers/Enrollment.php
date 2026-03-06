<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EnrollmentModel;
use App\Models\StudentModel;
use App\Models\CourseOfferingModel;
use App\Models\YearLevelModel;
use App\Models\TermModel;
use App\Models\InstructorModel;
use App\Models\CourseInstructorModel;
use App\Models\ProgramModel;
use App\Models\DepartmentModel;
use App\Models\NotificationModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class Enrollment extends BaseController
{
    protected $enrollmentModel;
    protected $studentModel;
    protected $courseOfferingModel;
    protected $yearLevelModel;
    protected $termModel;
    protected $instructorModel;    protected $courseInstructorModel;
    protected $programModel;
    protected $departmentModel;
    protected $notificationModel;
    protected $userModel;
    protected $session;
    protected $db;

    public function __construct()
    {
        $this->enrollmentModel = new EnrollmentModel();
        $this->studentModel = new StudentModel();
        $this->courseOfferingModel = new CourseOfferingModel();
        $this->yearLevelModel = new YearLevelModel();
        $this->termModel = new TermModel();
        $this->instructorModel = new InstructorModel();
        $this->courseInstructorModel = new CourseInstructorModel();
        $this->programModel = new ProgramModel();
        $this->departmentModel = new DepartmentModel();
        $this->notificationModel = new NotificationModel();
        $this->userModel = new UserModel();
        $this->session = \Config\Services::session();
        $this->db = \Config\Database::connect();
    }

    /**
     * Main manage enrollments view with CRUD operations
     */
    public function manageEnrollments()
    {
        // Check if user is admin
        if ($this->session->get('role') !== 'admin') {
            return redirect()->to('/login')->with('error', 'Access denied. Admin privileges required.');
        }

        $action = $this->request->getGet('action');
        $enrollmentId = $this->request->getGet('id');
        $termId = $this->request->getGet('term_id');

        $data = [
            'title' => 'Manage Enrollments',
            'enrollments' => [],
            'students' => $this->getActiveStudents(),
            'courseOfferings' => $this->getActiveCourseOfferings(),
            'yearLevels' => $this->yearLevelModel->findAll(),
            'terms' => $this->getTermsList(),
            'enrollmentStatuses' => $this->getEnrollmentStatuses(),
            'enrollmentTypes' => $this->getEnrollmentTypes(),
            'paymentStatuses' => $this->getPaymentStatuses(),
            'showCreateForm' => false,
            'showEditForm' => false,
            'editEnrollment' => null,
            'selectedTermId' => $termId
        ];

        // ===== CREATE ENROLLMENT =====
        if ($action === 'create') {
            if ($this->request->getMethod() === 'POST') {
                return $this->createEnrollment();
            }
            // Show create form
            $data['showCreateForm'] = true;
            $data['enrollments'] = $this->getEnrollmentsList($termId);
            return view('admin/manage_enrollments', $data);
        }

        // ===== EDIT ENROLLMENT =====
        if ($action === 'edit' && $enrollmentId) {
            $enrollmentToEdit = $this->enrollmentModel->find($enrollmentId);

            if (!$enrollmentToEdit) {
                $this->session->setFlashdata('error', 'Enrollment not found.');
                return redirect()->to('/admin/manage_enrollments');
            }

            if ($this->request->getMethod() === 'POST') {
                return $this->editEnrollment();
            }

            // Show edit form
            $data['showEditForm'] = true;
            $data['editEnrollment'] = $enrollmentToEdit;
            $data['enrollments'] = $this->getEnrollmentsList($termId);
            return view('admin/manage_enrollments', $data);
        }

        // Handle POST requests for other CRUD operations (delete, update_status)
        if ($this->request->getMethod() === 'POST') {
            $postAction = $this->request->getPost('action');

            switch ($postAction) {
                case 'delete':
                    return $this->deleteEnrollment();
                case 'update_status':
                    return $this->updateEnrollmentStatus();
                default:
                    $this->session->setFlashdata('error', 'Invalid action.');
            }
        }

        // Default: Show enrollments list
        $data['enrollments'] = $this->getEnrollmentsList($termId);
        return view('admin/manage_enrollments', $data);
    }

    /**
     * Get active students with user information
     */
    private function getActiveStudents()
    {
        return $this->studentModel->select('
                students.*,
                users.first_name,
                users.last_name,
                users.email,
                year_levels.year_level_name
            ')
            ->join('users', 'users.id = students.user_id')
            ->join('year_levels', 'year_levels.id = students.year_level_id', 'left')
            ->where('users.is_active', 1)
            ->orderBy('users.last_name', 'ASC')
            ->findAll();
    }

    /**
     * Get active course offerings with course information
     */
    private function getActiveCourseOfferings()
    {
        return $this->courseOfferingModel->select('
                course_offerings.*,
                courses.course_code,
                courses.title as course_title,
                courses.credits,
                terms.term_name,
                semesters.semester_name,
                academic_years.year_name as academic_year
            ')
            ->join('courses', 'courses.id = course_offerings.course_id')
            ->join('terms', 'terms.id = course_offerings.term_id')
            ->join('semesters', 'semesters.id = terms.semester_id')
            ->join('academic_years', 'academic_years.id = terms.academic_year_id')
            ->where('course_offerings.status', 'open')
            ->orderBy('courses.course_code', 'ASC')
            ->findAll();
    }

    /**
     * Get terms list
     */
    private function getTermsList()
    {
        return $this->termModel->select('
                terms.*,
                semesters.semester_name,
                academic_years.year_name as academic_year
            ')
            ->join('semesters', 'semesters.id = terms.semester_id')
            ->join('academic_years', 'academic_years.id = terms.academic_year_id')
            ->orderBy('terms.id', 'DESC')
            ->findAll();
    }

    /**
     * Get enrollment statuses
     */
    private function getEnrollmentStatuses()
    {
        return [
            'pending' => 'Pending',
            'enrolled' => 'Enrolled',
            'dropped' => 'Dropped',
            'withdrawn' => 'Withdrawn',
            'completed' => 'Completed'
        ];
    }

    /**
     * Get enrollment types
     */
    private function getEnrollmentTypes()
    {
        return [
            'regular' => 'Regular',
            'irregular' => 'Irregular',
            'retake' => 'Retake',
            'cross_enroll' => 'Cross Enrollment',
            'special' => 'Special'
        ];
    }

    /**
     * Get payment statuses
     */
    private function getPaymentStatuses()
    {
        return [
            'unpaid' => 'Unpaid',
            'partial' => 'Partial Payment',
            'paid' => 'Fully Paid',
            'scholarship' => 'Scholarship',
            'waived' => 'Waived'
        ];
    }

    /**
     * Get enrollments list with all related information
     */
    private function getEnrollmentsList($termId = null)
    {
        $builder = $this->enrollmentModel->select('
                enrollments.*,
                students.student_id_number,
                users.first_name,
                users.last_name,
                users.email,
                courses.course_code,
                courses.title as course_title,
                courses.credits,
                course_offerings.section,
                terms.term_name,
                semesters.semester_name,
                academic_years.year_name as academic_year,
                year_levels.year_level_name
            ')
            ->join('students', 'students.id = enrollments.student_id')
            ->join('users', 'users.id = students.user_id')
            ->join('course_offerings', 'course_offerings.id = enrollments.course_offering_id')
            ->join('courses', 'courses.id = course_offerings.course_id')
            ->join('terms', 'terms.id = course_offerings.term_id')
            ->join('semesters', 'semesters.id = terms.semester_id')
            ->join('academic_years', 'academic_years.id = terms.academic_year_id')
            ->join('year_levels', 'year_levels.id = enrollments.year_level_id', 'left');

        if ($termId) {
            $builder->where('course_offerings.term_id', $termId);
        }

        return $builder->orderBy('enrollments.created_at', 'DESC')
                      ->findAll();
    }

    /**
     * Create new enrollment
     */
    private function createEnrollment()
    {
        $studentId = $this->request->getPost('student_id');
        $courseOfferingId = $this->request->getPost('course_offering_id');

        // Check if student is already enrolled in this course offering
        if ($this->enrollmentModel->isStudentEnrolled($studentId, $courseOfferingId)) {
            $this->session->setFlashdata('error', 'This student is already enrolled in this course offering.');
            return redirect()->back()->withInput();
        }

        $validationRules = [
            'student_id'         => 'required|integer',
            'course_offering_id' => 'required|integer',
            'enrollment_date'    => 'required|valid_date',
            'enrollment_status'  => 'required|in_list[pending,pending_student_approval,pending_teacher_approval,enrolled,rejected,dropped,withdrawn,completed]',
            'enrollment_type'    => 'required|in_list[regular,irregular,retake,cross_enroll,special]',
            'payment_status'     => 'required|in_list[unpaid,partial,paid,scholarship,waived]'
        ];

        $validationMessages = [
            'student_id' => [
                'required' => 'Please select a student.',
                'integer'  => 'Invalid student selected.'
            ],
            'course_offering_id' => [
                'required' => 'Please select a course offering.',
                'integer'  => 'Invalid course offering selected.'
            ],
            'enrollment_date' => [
                'required'   => 'Enrollment date is required.',
                'valid_date' => 'Please enter a valid date.'
            ],
            'enrollment_status' => [
                'required' => 'Please select an enrollment status.',
                'in_list'  => 'Invalid enrollment status selected.'
            ],
            'enrollment_type' => [
                'required' => 'Please select an enrollment type.',
                'in_list'  => 'Invalid enrollment type selected.'
            ],
            'payment_status' => [
                'required' => 'Please select a payment status.',
                'in_list'  => 'Invalid payment status selected.'
            ]
        ];

        if (!$this->validate($validationRules, $validationMessages)) {
            $errors = $this->validator->getErrors();
            $errorMessage = implode('<br>', $errors);
            $this->session->setFlashdata('error', $errorMessage);
            return redirect()->back()->withInput();
        }

        $enrollmentData = [
            'student_id'         => $studentId,
            'course_offering_id' => $courseOfferingId,
            'enrollment_date'    => $this->request->getPost('enrollment_date'),
            'enrollment_status'  => $this->request->getPost('enrollment_status'),
            'enrollment_type'    => $this->request->getPost('enrollment_type'),
            'year_level_id'      => $this->request->getPost('year_level_id') ?: null,
            'payment_status'     => $this->request->getPost('payment_status'),
            'enrolled_by'        => $this->session->get('user_id'),
            'notes'              => $this->request->getPost('notes')
        ];

        if ($this->enrollmentModel->insert($enrollmentData)) {
            $this->session->setFlashdata('success', 'Enrollment created successfully!');
        } else {
            $errors = $this->enrollmentModel->errors();
            $errorMessage = $errors ? implode('<br>', $errors) : 'Failed to create enrollment. Please try again.';
            $this->session->setFlashdata('error', $errorMessage);
        }

        $termId = $this->request->getGet('term_id');
        return redirect()->to('/admin/manage_enrollments' . ($termId ? '?term_id=' . $termId : ''));
    }

    /**
     * Edit existing enrollment
     */
    private function editEnrollment()
    {
        $enrollmentId = $this->request->getGet('id');

        if (!$enrollmentId) {
            $this->session->setFlashdata('error', 'Invalid enrollment ID.');
            return redirect()->to('/admin/manage_enrollments');
        }

        $studentId = $this->request->getPost('student_id');
        $courseOfferingId = $this->request->getPost('course_offering_id');

        // Get existing enrollment to check if course offering changed
        $existingEnrollment = $this->enrollmentModel->find($enrollmentId);
        
        // If course offering changed, check for duplicate
        if ($existingEnrollment['course_offering_id'] != $courseOfferingId) {
            $duplicateCheck = $this->enrollmentModel
                ->where('student_id', $studentId)
                ->where('course_offering_id', $courseOfferingId)
                ->where('id !=', $enrollmentId)
                ->whereNotIn('enrollment_status', ['dropped', 'withdrawn'])
                ->countAllResults();
                
            if ($duplicateCheck > 0) {
                $this->session->setFlashdata('error', 'This student is already enrolled in the selected course offering.');
                return redirect()->back()->withInput();
            }
        }

        $validationRules = [
            'student_id'         => 'required|integer',
            'course_offering_id' => 'required|integer',
            'enrollment_date'    => 'required|valid_date',
            'enrollment_status'  => 'required|in_list[pending,pending_student_approval,pending_teacher_approval,enrolled,rejected,dropped,withdrawn,completed]',
            'enrollment_type'    => 'required|in_list[regular,irregular,retake,cross_enroll,special]',
            'payment_status'     => 'required|in_list[unpaid,partial,paid,scholarship,waived]'
        ];

        $validationMessages = [
            'student_id' => [
                'required' => 'Please select a student.',
                'integer'  => 'Invalid student selected.'
            ],
            'course_offering_id' => [
                'required' => 'Please select a course offering.',
                'integer'  => 'Invalid course offering selected.'
            ],
            'enrollment_date' => [
                'required'   => 'Enrollment date is required.',
                'valid_date' => 'Please enter a valid date.'
            ],
            'enrollment_status' => [
                'required' => 'Please select an enrollment status.',
                'in_list'  => 'Invalid enrollment status selected.'
            ],
            'enrollment_type' => [
                'required' => 'Please select an enrollment type.',
                'in_list'  => 'Invalid enrollment type selected.'
            ],
            'payment_status' => [
                'required' => 'Please select a payment status.',
                'in_list'  => 'Invalid payment status selected.'
            ]
        ];

        if (!$this->validate($validationRules, $validationMessages)) {
            $errors = $this->validator->getErrors();
            $errorMessage = implode('<br>', $errors);
            $this->session->setFlashdata('error', $errorMessage);
            return redirect()->back()->withInput();
        }

        $enrollmentData = [
            'student_id'         => $studentId,
            'course_offering_id' => $courseOfferingId,
            'enrollment_date'    => $this->request->getPost('enrollment_date'),
            'enrollment_status'  => $this->request->getPost('enrollment_status'),
            'enrollment_type'    => $this->request->getPost('enrollment_type'),
            'year_level_id'      => $this->request->getPost('year_level_id') ?: null,
            'payment_status'     => $this->request->getPost('payment_status'),
            'notes'              => $this->request->getPost('notes')
        ];

        // If status changed, update status_changed_at
        if ($existingEnrollment['enrollment_status'] !== $enrollmentData['enrollment_status']) {
            $enrollmentData['status_changed_at'] = date('Y-m-d H:i:s');
        }

        if ($this->enrollmentModel->update($enrollmentId, $enrollmentData)) {
            $this->session->setFlashdata('success', 'Enrollment updated successfully!');
        } else {
            $errors = $this->enrollmentModel->errors();
            $errorMessage = $errors ? implode('<br>', $errors) : 'Failed to update enrollment. Please try again.';
            $this->session->setFlashdata('error', $errorMessage);
        }

        $termId = $this->request->getGet('term_id');
        return redirect()->to('/admin/manage_enrollments' . ($termId ? '?term_id=' . $termId : ''));
    }

    /**
     * Delete enrollment (Soft Delete - Cancel Enrollment)
     */
    private function deleteEnrollment()
    {
        $enrollmentId = $this->request->getPost('enrollment_id');
        $termId = $this->request->getPost('term_id');

        if (!$enrollmentId) {
            $this->session->setFlashdata('error', 'Invalid enrollment ID.');
            return redirect()->to('/admin/manage_enrollments');
        }

        $enrollment = $this->enrollmentModel->find($enrollmentId);
        if (!$enrollment) {
            $this->session->setFlashdata('error', 'Enrollment not found.');
            return redirect()->to('/admin/manage_enrollments' . ($termId ? '?term_id=' . $termId : ''));
        }

        // Check if enrollment already cancelled/dropped
        if (in_array($enrollment['enrollment_status'], ['cancelled', 'dropped'])) {
            $this->session->setFlashdata('error', 'This enrollment is already cancelled/dropped.');
            return redirect()->to('/admin/manage_enrollments' . ($termId ? '?term_id=' . $termId : ''));
        }

        // Check referential integrity - Grades (only if table exists)
        if ($this->db->tableExists('grades')) {
            $gradesCount = $this->db->table('grades')->where('enrollment_id', $enrollmentId)->countAllResults();
            if ($gradesCount > 0) {
                $this->session->setFlashdata('error', 'Cannot delete this enrollment. It has ' . $gradesCount . ' grade record(s). Please change the status to "Dropped" or "Cancelled" instead.');
                return redirect()->to('/admin/manage_enrollments' . ($termId ? '?term_id=' . $termId : ''));
            }
        }

        // Check referential integrity - Attendance (only if table exists)
        if ($this->db->tableExists('attendances')) {
            $attendanceCount = $this->db->table('attendances')->where('enrollment_id', $enrollmentId)->countAllResults();
            if ($attendanceCount > 0) {
                $this->session->setFlashdata('error', 'Cannot delete this enrollment. It has ' . $attendanceCount . ' attendance record(s). Please change the status to "Dropped" or "Cancelled" instead.');
                return redirect()->to('/admin/manage_enrollments' . ($termId ? '?term_id=' . $termId : ''));
            }
        }

        // Soft delete: Change status to 'cancelled'
        $updateData = [
            'enrollment_status' => 'cancelled',
            'status_changed_at' => date('Y-m-d H:i:s')
        ];

        if ($this->enrollmentModel->update($enrollmentId, $updateData)) {
            $this->session->setFlashdata('success', 'Enrollment has been cancelled successfully!');
        } else {
            $this->session->setFlashdata('error', 'Failed to cancel enrollment.');
        }

        return redirect()->to('/admin/manage_enrollments' . ($termId ? '?term_id=' . $termId : ''));
    }

    /**
     * Update enrollment status (quick action)
     */
    private function updateEnrollmentStatus()
    {
        $enrollmentId = $this->request->getPost('enrollment_id');
        $newStatus = $this->request->getPost('new_status');
        $termId = $this->request->getPost('term_id');

        if (!$enrollmentId || !$newStatus) {
            $this->session->setFlashdata('error', 'Invalid enrollment ID or status.');
            return redirect()->to('/admin/manage_enrollments');
        }

        $updateData = [
            'enrollment_status' => $newStatus,
            'status_changed_at' => date('Y-m-d H:i:s')
        ];

        if ($this->enrollmentModel->update($enrollmentId, $updateData)) {
            $this->session->setFlashdata('success', 'Enrollment status updated to "' . ucfirst($newStatus) . '"!');
        } else {
            $this->session->setFlashdata('error', 'Failed to update enrollment status.');
        }

        return redirect()->to('/admin/manage_enrollments' . ($termId ? '?term_id=' . $termId : ''));
    }    /**
     * Teacher Enroll Student - Allow teachers to enroll students in their assigned courses
     */
    public function teacherEnrollStudent()
    {
        // Check if user is teacher
        if ($this->session->get('role') !== 'teacher') {
            return redirect()->to('/login')->with('error', 'Access denied. Teacher privileges required.');
        }

        $teacherUserId = $this->session->get('userID');
        
        // Get instructor record
        $instructor = $this->instructorModel->where('user_id', $teacherUserId)->first();
        if (!$instructor) {
            return redirect()->to('/teacher/dashboard')->with('error', 'Instructor profile not found.');
        }

        $instructorId = $instructor['id'];        // Handle POST request - Process enrollment
        if ($this->request->getMethod() === 'POST') {
            log_message('debug', '=== POST REQUEST RECEIVED ===');
            log_message('debug', 'Instructor ID: ' . $instructorId);
            log_message('debug', 'Request Method: ' . $this->request->getMethod());
            return $this->processTeacherEnrollment($instructorId);
        }

        // Get teacher's assigned course offerings
        $assignedCourses = $this->getTeacherAssignedCourseOfferings($instructorId);

        // Get students filtered by program and department if course is selected
        $students = [];
        $selectedCourseId = $this->request->getGet('course_offering_id');
        
        if ($selectedCourseId) {
            $students = $this->getStudentsForCourseOffering($selectedCourseId);
        }

        $data = [
            'title' => 'Enroll Student',
            'assignedCourses' => $assignedCourses,
            'students' => $students,
            'selectedCourseId' => $selectedCourseId,
            'enrollmentStatuses' => ['pending', 'enrolled'],
            'enrollmentTypes' => ['regular', 'irregular', 'retake', 'cross_enroll', 'special'],
            'paymentStatuses' => ['unpaid', 'partial', 'paid']
        ];

        return view('teacher/enroll_student', $data);
    }    /**
     * Process teacher enrollment submission (supports bulk enrollment)
     */
    private function processTeacherEnrollment($instructorId)
    {
        $validation = \Config\Services::validation();
        
        // Debug: Log all POST data
        log_message('debug', 'POST Data: ' . json_encode($this->request->getPost()));
        
        // Check if bulk enrollment
        $studentIds = $this->request->getPost('student_ids'); // Array of student IDs
        $singleStudentId = $this->request->getPost('student_id'); // Single student ID
        
        // Debug: Log student IDs
        log_message('debug', 'Student IDs Array: ' . json_encode($studentIds));
        log_message('debug', 'Single Student ID: ' . $singleStudentId);
        
        if ($studentIds && is_array($studentIds)) {
            // Bulk enrollment
            log_message('debug', 'Processing bulk enrollment for ' . count($studentIds) . ' students');
            return $this->processBulkEnrollment($instructorId, $studentIds);
        } else if ($singleStudentId) {
            // Single enrollment
            log_message('debug', 'Processing single enrollment');
            return $this->processSingleEnrollment($instructorId, $singleStudentId);
        } else {
            log_message('debug', 'No students selected - redirecting with error');
            return redirect()->back()->withInput()->with('error', 'Please select at least one student.');
        }
    }

    /**
     * Process single student enrollment
     */
    private function processSingleEnrollment($instructorId, $studentId)
    {
        $rules = [
            'course_offering_id' => 'required|integer',
            'enrollment_type' => 'required|in_list[regular,irregular,retake,cross_enroll,special]',
            'payment_status' => 'required|in_list[unpaid,partial,paid]',
            'enrollment_date' => 'required|valid_date'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $courseOfferingId = $this->request->getPost('course_offering_id');
        
        // Verify teacher is assigned to this course
        $isAssigned = $this->courseInstructorModel
            ->where('instructor_id', $instructorId)
            ->where('course_offering_id', $courseOfferingId)
            ->first();

        if (!$isAssigned) {
            return redirect()->back()->withInput()->with('error', 'You are not assigned to this course offering.');
        }

        // Check if student is already enrolled
        $existingEnrollment = $this->enrollmentModel
            ->where('student_id', $studentId)
            ->where('course_offering_id', $courseOfferingId)
            ->first();

        if ($existingEnrollment) {
            return redirect()->back()->withInput()->with('error', 'Student is already enrolled in this course offering.');
        }

        // Get course offering to check capacity
        $courseOffering = $this->courseOfferingModel->find($courseOfferingId);
        $enrolledCount = $this->enrollmentModel
            ->where('course_offering_id', $courseOfferingId)
            ->where('enrollment_status', 'enrolled')
            ->countAllResults();

        if ($courseOffering['max_students'] && $enrolledCount >= $courseOffering['max_students']) {
            return redirect()->back()->withInput()->with('error', 'Course offering has reached maximum capacity.');
        }

        // Create enrollment with approval status
        $enrollmentData = [
            'student_id' => $studentId,
            'course_offering_id' => $courseOfferingId,
            'enrollment_status' => 'pending_student_approval', // Teacher enrollment needs student approval
            'enrollment_type' => $this->request->getPost('enrollment_type'),
            'enrollment_date' => $this->request->getPost('enrollment_date'),
            'payment_status' => $this->request->getPost('payment_status'),
            'enrolled_by' => $this->session->get('userID'), // Teacher who enrolled
            'notes' => $this->request->getPost('notes')
        ];

        if ($this->enrollmentModel->insert($enrollmentData)) {
            return redirect()->to('/teacher/enroll_student')->with('success', 'Student enrolled successfully! Awaiting student approval.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to enroll student. Please try again.');
        }
    }

    /**
     * Process bulk student enrollment
     */
    private function processBulkEnrollment($instructorId, $studentIds)
    {
        $rules = [
            'course_offering_id' => 'required|integer',
            'enrollment_type' => 'required|in_list[regular,irregular,retake,cross_enroll,special]',
            'payment_status' => 'required|in_list[unpaid,partial,paid]',
            'enrollment_date' => 'required|valid_date'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $courseOfferingId = $this->request->getPost('course_offering_id');
        
        // Verify teacher is assigned to this course
        $isAssigned = $this->courseInstructorModel
            ->where('instructor_id', $instructorId)
            ->where('course_offering_id', $courseOfferingId)
            ->first();

        if (!$isAssigned) {
            return redirect()->back()->withInput()->with('error', 'You are not assigned to this course offering.');
        }

        // Get course offering to check capacity
        $courseOffering = $this->courseOfferingModel->find($courseOfferingId);
        $enrolledCount = $this->enrollmentModel
            ->where('course_offering_id', $courseOfferingId)
            ->where('enrollment_status', 'enrolled')
            ->countAllResults();

        // Calculate available slots
        $availableSlots = $courseOffering['max_students'] ? ($courseOffering['max_students'] - $enrolledCount) : PHP_INT_MAX;
        
        if ($availableSlots <= 0) {
            return redirect()->back()->withInput()->with('error', 'Course offering has reached maximum capacity.');
        }

        // Common enrollment data
        $enrollmentType = $this->request->getPost('enrollment_type');
        $enrollmentDate = $this->request->getPost('enrollment_date');
        $paymentStatus = $this->request->getPost('payment_status');
        $notes = $this->request->getPost('notes');

        $successCount = 0;
        $skippedCount = 0;
        $failedCount = 0;
        $skippedStudents = [];
        $capacityReached = false;

        foreach ($studentIds as $studentId) {
            // Check capacity before each enrollment
            if ($courseOffering['max_students'] && $successCount >= $availableSlots) {
                $capacityReached = true;
                $skippedCount++;
                continue;
            }

            // Check if student is already enrolled
            $existingEnrollment = $this->enrollmentModel
                ->where('student_id', $studentId)
                ->where('course_offering_id', $courseOfferingId)
                ->first();

            if ($existingEnrollment) {
                $skippedCount++;
                // Get student name for reporting
                $student = $this->studentModel
                    ->select('CONCAT(u.first_name, " ", u.last_name) as full_name')
                    ->join('users u', 'u.id = students.user_id')
                    ->find($studentId);
                if ($student) {
                    $skippedStudents[] = $student['full_name'];
                }
                continue;
            }

            // Create enrollment with approval status
            $enrollmentData = [
                'student_id' => $studentId,
                'course_offering_id' => $courseOfferingId,
                'enrollment_status' => 'pending_student_approval', // Teacher enrollment needs student approval
                'enrollment_type' => $enrollmentType,
                'enrollment_date' => $enrollmentDate,
                'payment_status' => $paymentStatus,
                'enrolled_by' => $this->session->get('userID'), // Teacher who enrolled
                'notes' => $notes
            ];            if ($this->enrollmentModel->insert($enrollmentData)) {
                $successCount++;
                
                // Send notification to student
                $student = $this->studentModel->find($studentId);
                if ($student) {
                    $studentUser = $this->userModel->find($student['user_id']);
                    
                    // Get course details
                    $courseDetails = $this->db->table('course_offerings co')
                        ->select('co.*, c.course_code, c.title as course_title, t.term_name, ay.year_name as academic_year')
                        ->join('courses c', 'c.id = co.course_id')
                        ->join('terms t', 't.id = co.term_id')
                        ->join('academic_years ay', 'ay.id = t.academic_year_id')
                        ->where('co.id', $courseOfferingId)
                        ->get()
                        ->getRowArray();
                    
                    // Get teacher info
                    $teacherUserId = $this->session->get('userID');
                    $teacherUser = $this->userModel->find($teacherUserId);
                    
                    if ($studentUser && $courseDetails && $teacherUser) {
                        $studentMessage = sprintf(
                            "You have been enrolled in %s (%s) - Section %s for %s %s by %s %s",
                            $courseDetails['course_title'],
                            $courseDetails['course_code'],
                            $courseDetails['section'],
                            $courseDetails['term_name'],
                            $courseDetails['academic_year'],
                            $teacherUser['first_name'],
                            $teacherUser['last_name']
                        );
                        
                        $this->notificationModel->createNotification(
                            $student['user_id'],
                            $studentMessage,
                            'enrollment',
                            $courseOfferingId,
                            'course_offering'
                        );
                    }
                }
            } else {
                $failedCount++;
            }
        }
        
        // Send summary notification to teacher
        if ($successCount > 0) {
            $teacherUserId = $this->session->get('userID');
            $courseDetails = $this->db->table('course_offerings co')
                ->select('co.*, c.course_code, c.title as course_title')
                ->join('courses c', 'c.id = co.course_id')
                ->where('co.id', $courseOfferingId)
                ->get()
                ->getRowArray();
            
            if ($courseDetails) {
                $teacherMessage = sprintf(
                    "Successfully enrolled %d student(s) in %s (%s) - Section %s",
                    $successCount,
                    $courseDetails['course_title'],
                    $courseDetails['course_code'],
                    $courseDetails['section']
                );
                
                $this->notificationModel->createNotification(
                    $teacherUserId,
                    $teacherMessage,
                    'success',
                    $courseOfferingId,
                    'course_offering'
                );
            }
        }

        // Build success message
        $messages = [];
        if ($successCount > 0) {
            $messages[] = "Successfully enrolled {$successCount} student(s).";
        }
        if ($skippedCount > 0) {
            $msg = "{$skippedCount} student(s) were skipped";
            if (!empty($skippedStudents)) {
                $msg .= " (" . implode(', ', array_slice($skippedStudents, 0, 3));
                if (count($skippedStudents) > 3) {
                    $msg .= " and " . (count($skippedStudents) - 3) . " more";
                }
                $msg .= ")";
            }
            $messages[] = $msg . " - already enrolled.";
        }
        if ($capacityReached) {
            $messages[] = "Course capacity reached. Some students were not enrolled.";
        }
        if ($failedCount > 0) {
            $messages[] = "{$failedCount} enrollment(s) failed.";
        }        $messageType = $successCount > 0 ? 'success' : 'error';
        $message = implode(' ', $messages);

        return redirect()->to('/teacher/enroll_student')->with($messageType, $message);
    }

    /**
     * AJAX endpoint for teacher bulk enrollment
     * Returns JSON response for better error handling and user feedback
     */
    public function ajaxEnrollStudents()
    {
        // Set response header
        $this->response->setContentType('application/json');
        
        // Check if user is teacher
        if ($this->session->get('role') !== 'teacher') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Teacher privileges required.'
            ]);
        }

        $teacherUserId = $this->session->get('userID');
        
        // Get instructor record
        $instructor = $this->instructorModel->where('user_id', $teacherUserId)->first();
        if (!$instructor) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Instructor profile not found.'
            ]);
        }

        $instructorId = $instructor['id'];

        // Get POST data
        $studentIds = $this->request->getPost('student_ids');
        $courseOfferingId = $this->request->getPost('course_offering_id');
        $enrollmentStatus = $this->request->getPost('enrollment_status');
        $enrollmentType = $this->request->getPost('enrollment_type');
        $enrollmentDate = $this->request->getPost('enrollment_date');
        $paymentStatus = $this->request->getPost('payment_status');
        $notes = $this->request->getPost('notes');

        // Debug log
        log_message('debug', 'AJAX Enrollment - Student IDs: ' . json_encode($studentIds));
        log_message('debug', 'AJAX Enrollment - Course Offering ID: ' . $courseOfferingId);

        // Validate required fields
        if (empty($studentIds) || !is_array($studentIds)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please select at least one student.'
            ]);
        }

        if (empty($courseOfferingId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Course offering is required.'
            ]);
        }

        if (empty($enrollmentStatus) || empty($enrollmentType) || empty($enrollmentDate) || empty($paymentStatus)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please fill in all required fields.'
            ]);
        }

        // Verify teacher is assigned to this course
        $isAssigned = $this->courseInstructorModel
            ->where('instructor_id', $instructorId)
            ->where('course_offering_id', $courseOfferingId)
            ->first();

        if (!$isAssigned) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You are not assigned to this course offering.'
            ]);
        }

        // Get course offering to check capacity
        $courseOffering = $this->courseOfferingModel->find($courseOfferingId);
        if (!$courseOffering) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Course offering not found.'
            ]);
        }

        $enrolledCount = $this->enrollmentModel
            ->where('course_offering_id', $courseOfferingId)
            ->where('enrollment_status', 'enrolled')
            ->countAllResults();

        // Calculate available slots
        $availableSlots = $courseOffering['max_students'] ? ($courseOffering['max_students'] - $enrolledCount) : PHP_INT_MAX;
        
        if ($availableSlots <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Course offering has reached maximum capacity.'
            ]);
        }

        // Process enrollments
        $successCount = 0;
        $skippedCount = 0;
        $failedCount = 0;
        $skippedStudents = [];
        $enrolledStudents = [];
        $capacityReached = false;

        foreach ($studentIds as $studentId) {
            // Check capacity before each enrollment
            if ($courseOffering['max_students'] && $successCount >= $availableSlots) {
                $capacityReached = true;
                $skippedCount++;
                continue;
            }

            // Check if student is already enrolled
            $existingEnrollment = $this->enrollmentModel
                ->where('student_id', $studentId)
                ->where('course_offering_id', $courseOfferingId)
                ->first();

            if ($existingEnrollment) {
                $skippedCount++;
                // Get student name for reporting
                $student = $this->studentModel
                    ->select('CONCAT(u.first_name, " ", u.last_name) as full_name')
                    ->join('users u', 'u.id = students.user_id')
                    ->find($studentId);
                if ($student) {
                    $skippedStudents[] = $student['full_name'];
                }
                continue;
            }

            // Create enrollment
            $enrollmentData = [
                'student_id' => $studentId,
                'course_offering_id' => $courseOfferingId,
                'enrollment_status' => $enrollmentStatus,
                'enrollment_type' => $enrollmentType,
                'enrollment_date' => $enrollmentDate,
                'payment_status' => $paymentStatus,
                'notes' => $notes
            ];            if ($this->enrollmentModel->insert($enrollmentData)) {
                $successCount++;
                
                // Get student details
                $student = $this->studentModel->find($studentId);
                if ($student) {
                    // Get student user for name reporting
                    $studentUser = $this->userModel->find($student['user_id']);
                    if ($studentUser) {
                        $enrolledStudents[] = $studentUser['first_name'] . ' ' . $studentUser['last_name'];
                    }
                    
                    // Send notification to student
                    $courseDetails = $this->db->table('course_offerings co')
                        ->select('co.*, c.course_code, c.title as course_title, t.term_name, ay.year_name as academic_year')
                        ->join('courses c', 'c.id = co.course_id')
                        ->join('terms t', 't.id = co.term_id')
                        ->join('academic_years ay', 'ay.id = t.academic_year_id')
                        ->where('co.id', $courseOfferingId)
                        ->get()
                        ->getRowArray();
                    
                    // Get teacher info
                    $teacherUserId = $this->session->get('userID');
                    $teacherUser = $this->userModel->find($teacherUserId);
                    
                    if ($courseDetails && $teacherUser) {
                        $studentMessage = sprintf(
                            "You have been enrolled in %s (%s) - Section %s for %s %s by %s %s",
                            $courseDetails['course_title'],
                            $courseDetails['course_code'],
                            $courseDetails['section'],
                            $courseDetails['term_name'],
                            $courseDetails['academic_year'],
                            $teacherUser['first_name'],
                            $teacherUser['last_name']
                        );
                        
                        $this->notificationModel->createNotification(
                            $student['user_id'],
                            $studentMessage
                        );
                    }
                }
            } else {
                $failedCount++;
                log_message('error', 'Failed to enroll student ID: ' . $studentId . '. Errors: ' . json_encode($this->enrollmentModel->errors()));
            }
        }
        
        // Send summary notification to teacher
        if ($successCount > 0) {
            $teacherUserId = $this->session->get('userID');
            $courseDetails = $this->db->table('course_offerings co')
                ->select('co.*, c.course_code, c.title as course_title')
                ->join('courses c', 'c.id = co.course_id')
                ->where('co.id', $courseOfferingId)
                ->get()
                ->getRowArray();
            
            if ($courseDetails) {
                $teacherMessage = sprintf(
                    "Successfully enrolled %d student(s) in %s (%s) - Section %s",
                    $successCount,
                    $courseDetails['course_title'],
                    $courseDetails['course_code'],
                    $courseDetails['section']
                );
                
                $this->notificationModel->createNotification(
                    $teacherUserId,
                    $teacherMessage
                );
            }
        }

        // Build response message
        $messages = [];
        if ($successCount > 0) {
            $messages[] = "Successfully enrolled {$successCount} student(s).";
        }
        if ($skippedCount > 0) {
            $msg = "{$skippedCount} student(s) were skipped";
            if (!empty($skippedStudents)) {
                $msg .= " (" . implode(', ', array_slice($skippedStudents, 0, 3));
                if (count($skippedStudents) > 3) {
                    $msg .= " and " . (count($skippedStudents) - 3) . " more";
                }
                $msg .= ")";
            }
            $messages[] = $msg . " - already enrolled.";
        }
        if ($capacityReached) {
            $messages[] = "Course capacity reached. Some students were not enrolled.";
        }
        if ($failedCount > 0) {
            $messages[] = "{$failedCount} enrollment(s) failed.";
        }

        $overallSuccess = $successCount > 0;
        $message = implode(' ', $messages);

        return $this->response->setJSON([
            'success' => $overallSuccess,
            'message' => $message,
            'data' => [
                'enrolled_count' => $successCount,
                'skipped_count' => $skippedCount,
                'failed_count' => $failedCount,
                'enrolled_students' => $enrolledStudents,
                'skipped_students' => $skippedStudents
            ]
        ]);
    }

    /**
     * Get teacher's assigned course offerings
     */    private function getTeacherAssignedCourseOfferings($instructorId)
    {
        return $this->courseInstructorModel
            ->select('
                co.id,
                co.course_id,
                co.section,
                c.course_code,
                c.title as course_title,
                t.term_name,
                ay.year_name as academic_year,
                co.max_students,
                co.status,
                d.department_name,
                d.department_code,
                c.department_id,
                (SELECT COUNT(*) FROM enrollments e 
                 WHERE e.course_offering_id = co.id 
                 AND e.enrollment_status = "enrolled") as enrolled_count
            ')
            ->from('course_instructors ci')
            ->join('course_offerings co', 'co.id = ci.course_offering_id')
            ->join('courses c', 'c.id = co.course_id')
            ->join('terms t', 't.id = co.term_id')
            ->join('academic_years ay', 'ay.id = t.academic_year_id', 'left')
            ->join('departments d', 'd.id = c.department_id', 'left')
            ->where('ci.instructor_id', $instructorId)
            ->where('co.status', 'open')
            ->groupBy('co.id')  // GROUP BY to prevent duplicates when course has multiple instructors
            ->orderBy('ay.year_name', 'DESC')
            ->orderBy('c.course_code', 'ASC')
            ->findAll();
    }

    /**
     * Get students eligible for a specific course offering
     * Filtered by program and department
     */    private function getStudentsForCourseOffering($courseOfferingId)
    {
        // Get course offering with department info (courses table has department_id, NOT program_id)
        $courseOffering = $this->courseOfferingModel
            ->select('c.department_id')
            ->join('courses c', 'c.id = course_offerings.course_id')
            ->find($courseOfferingId);

        if (!$courseOffering) {
            return [];
        }        $query = $this->studentModel
            ->select('
                students.id,
                students.student_id_number,
                CONCAT(u.first_name, " ", COALESCE(u.middle_name, ""), " ", u.last_name) as full_name,
                u.first_name,
                u.middle_name,
                u.last_name,
                u.suffix,
                u.email,
                p.program_code,
                p.program_name,
                d.department_name,
                yl.year_level_name as year_level,
                students.section
            ')
            ->join('users u', 'u.id = students.user_id')
            ->join('programs p', 'p.id = students.program_id', 'left')
            ->join('departments d', 'd.id = students.department_id', 'left')
            ->join('year_levels yl', 'yl.id = students.year_level_id', 'left')
            ->where('students.enrollment_status', 'enrolled');

        // Filter by department if course has a specific department
        // Note: courses table does NOT have program_id, only department_id
        if ($courseOffering['department_id']) {
            $query->where('students.department_id', $courseOffering['department_id']);
        }

        // Exclude students already enrolled in this course offering
        $query->where('students.id NOT IN (
            SELECT student_id FROM enrollments 
            WHERE course_offering_id = ' . $courseOfferingId . '
        )');

        return $query
            ->orderBy('u.last_name', 'ASC')
            ->orderBy('u.first_name', 'ASC')
            ->findAll();
    }/**
     * View enrolled students for teacher's courses
     */
    public function teacherEnrolledStudents()
    {
        // Check if user is teacher
        if ($this->session->get('role') !== 'teacher') {
            return redirect()->to('/login')->with('error', 'Access denied. Teacher privileges required.');
        }

        $teacherUserId = $this->session->get('userID');
        
        // Get instructor record
        $instructor = $this->instructorModel->where('user_id', $teacherUserId)->first();
        if (!$instructor) {
            return redirect()->to('/teacher/dashboard')->with('error', 'Instructor profile not found.');
        }

        $instructorId = $instructor['id'];

        // Get filter parameters
        $courseOfferingId = $this->request->getGet('course_offering_id');
        $enrollmentStatus = $this->request->getGet('enrollment_status');

        // Get teacher's assigned course offerings
        $assignedCourses = $this->getTeacherAssignedCourseOfferings($instructorId);

        // Get enrolled students
        $enrolledStudents = $this->getTeacherEnrolledStudents($instructorId, $courseOfferingId, $enrollmentStatus);

        $data = [
            'title' => 'Enrolled Students',
            'assignedCourses' => $assignedCourses,
            'enrolledStudents' => $enrolledStudents,
            'selectedCourseId' => $courseOfferingId,
            'selectedStatus' => $enrollmentStatus,
            'enrollmentStatuses' => ['pending', 'enrolled', 'dropped', 'completed']
        ];

        return view('teacher/enrolled_students', $data);
    }    /**
     * Get enrolled students for teacher's courses
     */    private function getTeacherEnrolledStudents($instructorId, $courseOfferingId = null, $enrollmentStatus = null)
    {        $query = $this->enrollmentModel
            ->distinct()
            ->select('
                e.id as enrollment_id,
                e.enrollment_status,
                e.enrollment_type,
                e.enrollment_date,
                e.payment_status,
                e.notes,
                s.id as student_id,
                s.student_id_number,
                CONCAT(u.first_name, " ", COALESCE(u.middle_name, ""), " ", u.last_name) as full_name,
                u.first_name,
                u.last_name,
                u.email,
                c.course_code,
                c.title as course_title,
                t.term_name,
                ay.year_name as academic_year,
                p.program_code,
                yl.year_level_name as year_level,
                s.section
            ')
            ->from('enrollments e')
            ->join('course_offerings co', 'co.id = e.course_offering_id')
            ->join('course_instructors ci', 'ci.course_offering_id = co.id')
            ->join('students s', 's.id = e.student_id')
            ->join('users u', 'u.id = s.user_id')
            ->join('courses c', 'c.id = co.course_id')
            ->join('terms t', 't.id = co.term_id')
            ->join('academic_years ay', 'ay.id = t.academic_year_id', 'left')
            ->join('programs p', 'p.id = s.program_id', 'left')
            ->join('year_levels yl', 'yl.id = s.year_level_id', 'left')
            ->where('ci.instructor_id', $instructorId);

        if ($courseOfferingId) {
            $query->where('e.course_offering_id', $courseOfferingId);
        }

        if ($enrollmentStatus) {
            $query->where('e.enrollment_status', $enrollmentStatus);
        }

        return $query
            ->orderBy('ay.year_name', 'DESC')
            ->orderBy('e.enrollment_date', 'DESC')
            ->orderBy('u.last_name', 'ASC')
            ->findAll();
    }

    /**
     * Accept or reject enrollment (for both students and teachers)
     */
    public function respondToEnrollment()
    {
        if ($this->request->getMethod() !== 'post' && $this->request->getMethod() !== 'POST') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method.'
            ]);
        }

        $enrollmentId = $this->request->getPost('enrollment_id');
        $action = $this->request->getPost('action'); // 'accept' or 'reject'
        $userRole = $this->session->get('role');
        $userId = $this->session->get('userID');

        if (!$enrollmentId || !in_array($action, ['accept', 'reject'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid parameters.'
            ]);
        }

        // Get enrollment details
        $enrollment = $this->enrollmentModel->find($enrollmentId);
        if (!$enrollment) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Enrollment not found.'
            ]);
        }

        // Check if user can respond to this enrollment
        $canRespond = false;
        $newStatus = null;

        if ($userRole === 'student') {
            // Student can respond to pending_student_approval
            if ($enrollment['enrollment_status'] === 'pending_student_approval') {
                $student = $this->studentModel->where('user_id', $userId)->first();
                if ($student && $student['id'] == $enrollment['student_id']) {
                    $canRespond = true;
                    $newStatus = $action === 'accept' ? 'enrolled' : 'rejected';
                }
            }
        } elseif ($userRole === 'teacher') {
            // Teacher can respond to pending_teacher_approval
            if ($enrollment['enrollment_status'] === 'pending_teacher_approval') {
                $instructor = $this->instructorModel->where('user_id', $userId)->first();
                if ($instructor) {
                    // Check if teacher is assigned to this course
                    $isAssigned = $this->courseInstructorModel
                        ->where('instructor_id', $instructor['id'])
                        ->where('course_offering_id', $enrollment['course_offering_id'])
                        ->first();
                    
                    if ($isAssigned) {
                        $canRespond = true;
                        $newStatus = $action === 'accept' ? 'enrolled' : 'rejected';
                    }
                }
            }
        }

        if (!$canRespond) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You are not authorized to respond to this enrollment.'
            ]);
        }

        // Update enrollment status
        $updateData = [
            'enrollment_status' => $newStatus,
            'status_changed_at' => date('Y-m-d H:i:s')
        ];

        if ($this->enrollmentModel->update($enrollmentId, $updateData)) {
            // If accepted, increment course offering enrollment count
            if ($newStatus === 'enrolled') {
                $this->courseOfferingModel->incrementEnrollment($enrollment['course_offering_id']);
            }

            // Send notification (optional - can be implemented later)
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Enrollment ' . $action . 'ed successfully!'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update enrollment. Please try again.'
            ]);
        }
    }

    /**
     * Get pending enrollments for dashboard
     */
    public function getPendingEnrollments()
    {
        $userRole = $this->session->get('role');
        $userId = $this->session->get('userID');
        
        // Debug logging
        log_message('debug', 'getPendingEnrollments - Role: ' . $userRole . ', UserID: ' . $userId);
        
        $pendingEnrollments = [];

        if ($userRole === 'student') {
            // Get enrollments pending student approval
            $student = $this->studentModel->where('user_id', $userId)->first();
            if ($student) {
                $pendingEnrollments = $this->db->table('enrollments e')
                    ->select('
                        e.id as enrollment_id,
                        e.enrollment_date,
                        co.section,
                        c.course_code,
                        c.title as course_title,
                        c.credits,
                        t.term_name,
                        ay.year_name as academic_year,
                        CONCAT(u.first_name, " ", u.last_name) as enrolled_by_name
                    ')
                    ->join('course_offerings co', 'co.id = e.course_offering_id')
                    ->join('courses c', 'c.id = co.course_id')
                    ->join('terms t', 't.id = co.term_id')
                    ->join('academic_years ay', 'ay.id = t.academic_year_id')
                    ->join('users u', 'u.id = e.enrolled_by')
                    ->where('e.student_id', $student['id'])
                    ->where('e.enrollment_status', 'pending_student_approval')
                    ->orderBy('e.enrollment_date', 'DESC')
                    ->get()
                    ->getResultArray();
            }
        } elseif ($userRole === 'teacher') {
            // Get ALL enrollments pending teacher approval (temporary solution)
            // TODO: Filter by teacher's assigned courses once course_instructors is properly populated
            log_message('debug', 'Fetching all pending enrollments for teacher approval');
            
            $pendingEnrollments = $this->db->table('enrollments e')
                ->select('
                    e.id as enrollment_id,
                    e.enrollment_date,
                    e.notes,
                    co.section,
                    c.course_code,
                    c.title as course_title,
                    c.credits,
                    t.term_name,
                    ay.year_name as academic_year,
                    s.student_id_number,
                    CONCAT(stu.first_name, " ", stu.last_name) as student_name
                ')
                ->join('course_offerings co', 'co.id = e.course_offering_id')
                ->join('courses c', 'c.id = co.course_id')
                ->join('terms t', 't.id = co.term_id')
                ->join('academic_years ay', 'ay.id = t.academic_year_id')
                ->join('students s', 's.id = e.student_id')
                ->join('users stu', 'stu.id = s.user_id')
                ->where('e.enrollment_status', 'pending_teacher_approval')
                ->orderBy('e.enrollment_date', 'DESC')
                ->get()
                ->getResultArray();
            
            log_message('debug', 'Pending enrollments found: ' . count($pendingEnrollments));
        }

        return $this->response->setJSON([
            'success' => true,
            'pending_enrollments' => $pendingEnrollments
        ]);
    }
}
