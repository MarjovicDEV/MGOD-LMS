<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AssignmentModel;
use App\Models\SubmissionModel;
use App\Models\CourseOfferingModel;
use App\Models\CourseInstructorModel;
use App\Models\AssignmentTypeModel;
use App\Models\GradingPeriodModel;
use App\Models\EnrollmentModel;
use App\Models\NotificationModel;
use CodeIgniter\HTTP\ResponseInterface;

class Assignment extends BaseController
{
    protected $assignmentModel;
    protected $submissionModel;
    protected $courseOfferingModel;
    protected $courseInstructorModel;
    protected $assignmentTypeModel;
    protected $gradingPeriodModel;
    protected $enrollmentModel;
    protected $notificationModel;
    
    public function __construct()
    {
        $this->assignmentModel = new AssignmentModel();
        $this->submissionModel = new SubmissionModel();
        $this->courseOfferingModel = new CourseOfferingModel();
        $this->courseInstructorModel = new CourseInstructorModel();
        $this->assignmentTypeModel = new AssignmentTypeModel();
        $this->gradingPeriodModel = new GradingPeriodModel();
        $this->enrollmentModel = new EnrollmentModel();
        $this->notificationModel = new NotificationModel();
    }

    public function teacherAssignments()
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'teacher') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        $userID = $this->session->get('userID');
        $action = $this->request->getGet('action');

        if ($this->request->getMethod() === 'POST') {
            $action = $this->request->getPost('action');

            if ($action === 'create') {
                return $this->createAssignment();
            } elseif ($action === 'edit') {
                return $this->editAssignment();
            } elseif ($action === 'delete') {
                return $this->deleteAssignment();
            } elseif ($action === 'publish') {
                return $this->publishAssignment();
            }
        }

        // Get instructor_id from user_id
        $instructorId = $this->courseInstructorModel->getInstructorIdByUserId($userID);
        
        if (!$instructorId) {
            $assignments = [];
        } else {
            $db = \Config\Database::connect();
            $assignments = $db->table('assignments a')
                ->select('a.*, c.course_code, c.title as course_title, co.section, 
                          at.type_name, gp.period_name,
                          (SELECT COUNT(*) FROM submissions WHERE assignment_id = a.id) as submission_count')
                ->join('course_offerings co', 'co.id = a.course_offering_id')
                ->join('courses c', 'c.id = co.course_id')
                ->join('course_instructors ci', 'ci.course_offering_id = co.id')
                ->join('assignment_types at', 'at.id = a.assignment_type_id', 'left')
                ->join('grading_periods gp', 'gp.id = a.grading_period_id', 'left')
                ->where('ci.instructor_id', $instructorId)
                ->where('a.is_active', 1)
                ->orderBy('a.created_at', 'DESC')
                ->get()
                ->getResultArray();
        }

        $teacherCourses = $this->courseInstructorModel->getOfferingsByUserId($userID);
        $assignmentTypes = $this->assignmentTypeModel->findAll();
        
        // Get grading periods grouped by term for dynamic loading
        $allGradingPeriods = $this->gradingPeriodModel->where('is_active', 1)->orderBy('period_order', 'ASC')->findAll();
        
        // Create a mapping of term_id to grading periods for JavaScript
        $gradingPeriodsByTerm = [];
        foreach ($allGradingPeriods as $period) {
            $termId = $period['term_id'];
            if (!isset($gradingPeriodsByTerm[$termId])) {
                $gradingPeriodsByTerm[$termId] = [];
            }
            $gradingPeriodsByTerm[$termId][] = $period;
        }

        $data = [
            'title' => 'Manage Assignments',
            'assignments' => $assignments,
            'courses' => $teacherCourses,
            'assignmentTypes' => $assignmentTypes,
            'gradingPeriods' => $allGradingPeriods,
            'gradingPeriodsByTerm' => $gradingPeriodsByTerm,
            'action' => $action
        ];

        return view('teacher/assignments', $data);
    }

    private function createAssignment()
    {
        $userID = $this->session->get('userID');
        
        $title = $this->request->getPost('title');
        $dueDate = $this->request->getPost('due_date');
        $availableFrom = $this->request->getPost('available_from');
        $availableUntil = $this->request->getPost('available_until');
        $courseOfferingId = $this->request->getPost('course_offering_id');

        // Validate title - only letters, numbers, spaces, hyphens, and commas
        if (!preg_match('/^[a-zA-Z0-9\s\-,]+$/', $title)) {
            return redirect()->back()->with('error', 'Title can only contain letters, numbers, spaces, hyphens, and commas');
        }

        // Validate due date is not in the past
        if (strtotime($dueDate) < time()) {
            return redirect()->back()->with('error', 'Due date cannot be in the past');
        }

        // Validate available_from is before due_date
        if ($availableFrom && strtotime($availableFrom) >= strtotime($dueDate)) {
            return redirect()->back()->with('error', 'Available From date must be before the Due Date');
        }

        // Validate available_until is after available_from
        if ($availableFrom && $availableUntil && strtotime($availableUntil) <= strtotime($availableFrom)) {
            return redirect()->back()->with('error', 'Available Until date must be after Available From date');
        }

        // Validate instructor authorization (convert user_id to instructor_id)
        $isInstructor = $this->courseInstructorModel->isUserAssigned($courseOfferingId, $userID);

        if (!$isInstructor) {
            return redirect()->back()->with('error', 'You are not authorized to create assignments for this course');
        }

        // Handle attachment upload
        $attachmentPath = null;
        $attachmentFile = $this->request->getFile('attachment_file');
        
        if ($attachmentFile && $attachmentFile->isValid() && !$attachmentFile->hasMoved()) {
            $allowedExtensions = ['pdf', 'ppt', 'pptx'];
            $fileExtension = $attachmentFile->getExtension();
            
            if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
                return redirect()->back()->with('error', 'Only PDF and PowerPoint documents are allowed for attachments');
            }
            
            $maxSize = 10 * 1024 * 1024; // 10MB
            if ($attachmentFile->getSize() > $maxSize) {
                return redirect()->back()->with('error', 'Attachment file size must not exceed 10MB');
            }
            
            $newName = 'assignment_' . time() . '.' . $fileExtension;
            $uploadPath = WRITEPATH . 'uploads/assignments/';
            
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }
            
            if ($attachmentFile->move($uploadPath, $newName)) {
                $attachmentPath = 'assignments/' . $newName;
            }
        }

        $data = [
            'course_offering_id' => $courseOfferingId,
            'assignment_type_id' => $this->request->getPost('assignment_type_id'),
            'grading_period_id' => $this->request->getPost('grading_period_id'),
            'title' => $title,
            'description' => $this->request->getPost('description'),
            'instructions' => $this->request->getPost('instructions'),
            'attachment_path' => $attachmentPath,
            'submission_type' => $this->request->getPost('submission_type') ?? 'both',
            'max_score' => $this->request->getPost('max_score'),
            'due_date' => $dueDate,
            'available_from' => $availableFrom,
            'available_until' => $availableUntil,
            'allow_late_submission' => $this->request->getPost('allow_late_submission') ? 1 : 0,
            'late_penalty_percentage' => $this->request->getPost('late_penalty_percentage') ?? 0,
            'is_published' => $this->request->getPost('is_published') ? 1 : 0,
            'is_active' => 1
        ];

        if ($this->assignmentModel->insert($data)) {
            $assignmentId = $this->assignmentModel->getInsertID();
            
            if ($data['is_published']) {
                $this->notifyStudentsNewAssignment($assignmentId, $courseOfferingId);
            }

            return redirect()->to(base_url('teacher/assignments'))->with('success', 'Assignment created successfully');
        }

        return redirect()->back()->with('error', 'Failed to create assignment');
    }

    private function editAssignment()
    {
        $userID = $this->session->get('userID');
        $assignmentId = $this->request->getPost('assignment_id');
        
        $assignment = $this->assignmentModel->find($assignmentId);
        if (!$assignment) {
            return redirect()->back()->with('error', 'Assignment not found');
        }

        $isInstructor = $this->courseInstructorModel->isUserAssigned($assignment['course_offering_id'], $userID);

        if (!$isInstructor) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $title = $this->request->getPost('title');
        $dueDate = $this->request->getPost('due_date');
        $availableFrom = $this->request->getPost('available_from');
        $availableUntil = $this->request->getPost('available_until');

        // Validate title - only letters, numbers, spaces, hyphens, and commas
        if (!preg_match('/^[a-zA-Z0-9\s\-,]+$/', $title)) {
            return redirect()->back()->with('error', 'Title can only contain letters, numbers, spaces, hyphens, and commas');
        }

        // Validate due date is not in the past
        if (strtotime($dueDate) < time()) {
            return redirect()->back()->with('error', 'Due date cannot be in the past');
        }

        // Validate available_from is before due_date
        if ($availableFrom && strtotime($availableFrom) >= strtotime($dueDate)) {
            return redirect()->back()->with('error', 'Available From date must be before the Due Date');
        }

        // Validate available_until is after available_from
        if ($availableFrom && $availableUntil && strtotime($availableUntil) <= strtotime($availableFrom)) {
            return redirect()->back()->with('error', 'Available Until date must be after Available From date');
        }

        $data = [
            'assignment_type_id' => $this->request->getPost('assignment_type_id'),
            'grading_period_id' => $this->request->getPost('grading_period_id'),
            'title' => $title,
            'description' => $this->request->getPost('description'),
            'instructions' => $this->request->getPost('instructions'),
            'submission_type' => $this->request->getPost('submission_type') ?? 'both',
            'max_score' => $this->request->getPost('max_score'),
            'due_date' => $dueDate,
            'available_from' => $availableFrom,
            'available_until' => $availableUntil,
            'allow_late_submission' => $this->request->getPost('allow_late_submission') ? 1 : 0,
            'late_penalty_percentage' => $this->request->getPost('late_penalty_percentage') ?? 0
        ];        
        
        if ($this->assignmentModel->update($assignmentId, $data)) {
            // Notify students about the update
            $this->notifyStudentsAssignmentUpdated($assignmentId, $assignment['course_offering_id']);
            
            return redirect()->to(base_url('teacher/assignments'))->with('success', 'Assignment updated successfully');
        }

        return redirect()->back()->with('error', 'Failed to update assignment');
    }

    private function deleteAssignment()
    {
        $userID = $this->session->get('userID');
        $assignmentId = $this->request->getPost('assignment_id');
        
        $assignment = $this->assignmentModel->find($assignmentId);
        if (!$assignment) {
            return redirect()->back()->with('error', 'Assignment not found');
        }

        $isInstructor = $this->courseInstructorModel->isUserAssigned($assignment['course_offering_id'], $userID);        if (!$isInstructor) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        // Store assignment title before deletion
        $assignmentTitle = $assignment['title'];
        $courseOfferingId = $assignment['course_offering_id'];

        if ($this->assignmentModel->update($assignmentId, ['is_active' => 0])) {
            // Notify students about deletion
            $this->notifyStudentsAssignmentDeleted($assignmentId, $courseOfferingId, $assignmentTitle);
            
            return redirect()->to(base_url('teacher/assignments'))->with('success', 'Assignment deleted successfully');
        }

        return redirect()->back()->with('error', 'Failed to delete assignment');
    }

    private function publishAssignment()
    {
        $userID = $this->session->get('userID');
        $assignmentId = $this->request->getPost('assignment_id');
        
        $assignment = $this->assignmentModel->find($assignmentId);
        if (!$assignment) {
            return redirect()->back()->with('error', 'Assignment not found');
        }

        $isInstructor = $this->courseInstructorModel->isUserAssigned($assignment['course_offering_id'], $userID);

        if (!$isInstructor) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        if ($this->assignmentModel->update($assignmentId, ['is_published' => 1])) {
            $this->notifyStudentsNewAssignment($assignmentId, $assignment['course_offering_id']);
            return redirect()->to(base_url('teacher/assignments'))->with('success', 'Assignment published successfully');
        }

        return redirect()->back()->with('error', 'Failed to publish assignment');
    }

    public function viewSubmissions($assignmentId = null)
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'teacher') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        $userID = $this->session->get('userID');

        if (!$assignmentId) {
            $assignmentId = $this->request->getGet('assignment_id');
        }

        // If no assignment ID provided, redirect to assignments page
        if (!$assignmentId) {
            return redirect()->to(base_url('teacher/assignments'))->with('error', 'Please select an assignment to view submissions');
        }

        // Debug: Check what assignmentId we received
        log_message('debug', 'Assignment ID received: ' . $assignmentId);
        
        $assignment = $this->assignmentModel->find($assignmentId);
        
        // Debug: Log what the basic find returns
        log_message('debug', 'Basic find returned: ' . json_encode($assignment));
        
        if (!$assignment) {
            return redirect()->to(base_url('teacher/assignments'))->with('error', 'Assignment not found');
        }

        // Now try the detailed query
        $assignment = $this->assignmentModel->getAssignmentWithDetails($assignmentId);
        
        // Debug: Log what the detailed query returns
        log_message('debug', 'getAssignmentWithDetails returned: ' . json_encode($assignment));
        
        if (!$assignment) {
            return redirect()->to(base_url('teacher/assignments'))->with('error', 'Assignment not found');
        }

        if (!isset($assignment['course_offering_id'])) {
            return redirect()->to(base_url('teacher/assignments'))->with('error', 'Assignment data is incomplete');
        }

        $isInstructor = $this->courseInstructorModel->isUserAssigned($assignment['course_offering_id'], $userID);

        if (!$isInstructor) {
            return redirect()->to(base_url('teacher/assignments'))->with('error', 'Unauthorized access');
        }

        $db = \Config\Database::connect();
        $submissions = $db->table('submissions s')
            ->select('s.*, CONCAT(u.first_name, " ", COALESCE(u.middle_name, ""), " ", u.last_name) as student_name, u.user_code as student_code, e.id as enrollment_id')
            ->join('enrollments e', 'e.id = s.enrollment_id')
            ->join('users u', 'u.id = e.student_id')
            ->where('s.assignment_id', $assignmentId)
            ->orderBy('s.submitted_at', 'DESC')
            ->get()
            ->getResultArray();

        $enrolledStudents = $db->table('enrollments e')
            ->select('e.id as enrollment_id, u.id as student_id, CONCAT(u.first_name, " ", COALESCE(u.middle_name, ""), " ", u.last_name) as student_name, u.user_code as student_code')
            ->join('users u', 'u.id = e.student_id')
            ->where('e.course_offering_id', $assignment['course_offering_id'])
            ->where('e.enrollment_status', 'enrolled')
            ->get()
            ->getResultArray();

        $submittedStudentIds = array_column($submissions, 'enrollment_id');
        $notSubmitted = array_filter($enrolledStudents, function($student) use ($submittedStudentIds) {
            return !in_array($student['enrollment_id'], $submittedStudentIds);
        });

        $stats = $this->assignmentModel->getAssignmentStats($assignmentId);

        $data = [
            'title' => 'View Submissions - ' . $assignment['title'],
            'assignment' => $assignment,
            'submissions' => $submissions,
            'notSubmitted' => $notSubmitted,
            'stats' => $stats
        ];

        return view('teacher/view_submissions', $data);
    }

    public function gradeSubmission()
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'teacher') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(403);
        }

        $userID = $this->session->get('userID');
        $submissionId = $this->request->getPost('submission_id');
        $score = $this->request->getPost('score');
        $feedback = $this->request->getPost('feedback');

        $submission = $this->submissionModel->getSubmissionWithDetails($submissionId);
        if (!$submission) {
            return $this->response->setJSON(['success' => false, 'message' => 'Submission not found'])->setStatusCode(404);
        }

        $assignment = $this->assignmentModel->find($submission['assignment_id']);

        // Validate score
        if (!is_numeric($score) || $score < 0 || $score > $assignment['max_score']) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Score must be a number between 0 and ' . $assignment['max_score']
            ])->setStatusCode(400);
        }

        $isInstructor = $this->courseInstructorModel->isUserAssigned($assignment['course_offering_id'], $userID);

        if (!$isInstructor) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(403);
        }        if ($this->submissionModel->gradeSubmission($submissionId, $score, $feedback, $userID)) {
            $enrollmentData = $this->enrollmentModel->find($submission['enrollment_id']);
            
            // Get student user_id from enrollment
            $db = \Config\Database::connect();
            $student = $db->table('students')
                ->where('id', $enrollmentData['student_id'])
                ->get()
                ->getRowArray();
            
            if ($student) {
                $percentage = round(($score / $assignment['max_score']) * 100, 2);
                $gradeEmoji = $percentage >= 90 ? '🌟' : ($percentage >= 80 ? '👏' : ($percentage >= 70 ? '👍' : '📝'));
                
                $message = "{$gradeEmoji} Your submission for '{$submission['assignment_title']}' has been graded. Score: {$score}/{$assignment['max_score']} ({$percentage}%)";
                
                $this->notificationModel->createNotification(
                    $student['user_id'],
                    $message,
                    'grade',
                    $submission['assignment_id'],
                    'assignment'
                );
                
                log_message('info', "Notified student (ID: {$student['user_id']}) about graded submission for assignment: {$submission['assignment_title']}");
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Submission graded successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to grade submission'])->setStatusCode(500);
    }    
    /**
     * Notify all enrolled students when a new assignment is published
     */
    private function notifyStudentsNewAssignment($assignmentId, $courseOfferingId)
    {
        $assignment = $this->assignmentModel->getAssignmentWithDetails($assignmentId);
        
        // Get all enrolled students in this course
        $enrollments = $this->enrollmentModel
            ->where('course_offering_id', $courseOfferingId)
            ->where('enrollment_status', 'enrolled')
            ->findAll();

        $db = \Config\Database::connect();
        $notificationCount = 0;
        
        foreach ($enrollments as $enrollment) {
            // Get student user_id
            $student = $db->table('students')
                ->where('id', $enrollment['student_id'])
                ->get()
                ->getRowArray();
            
            if ($student) {
                $dueDate = date('M j, Y g:i A', strtotime($assignment['due_date']));
                $message = "📚 New assignment posted: '{$assignment['title']}' in {$assignment['course_code']} - Due: {$dueDate}";
                
                $this->notificationModel->createNotification(
                    $student['user_id'],
                    $message,
                    'assignment',
                    $assignmentId,
                    'assignment'
                );
                
                $notificationCount++;
            }
        }
        
        log_message('info', "Notified {$notificationCount} student(s) about new assignment: {$assignment['title']}");
    }
    
    /**
     * Notify students when assignment is updated
     */
    private function notifyStudentsAssignmentUpdated($assignmentId, $courseOfferingId)
    {
        $assignment = $this->assignmentModel->getAssignmentWithDetails($assignmentId);
        
        // Get all enrolled students
        $enrollments = $this->enrollmentModel
            ->where('course_offering_id', $courseOfferingId)
            ->where('enrollment_status', 'enrolled')
            ->findAll();

        $db = \Config\Database::connect();
        $notificationCount = 0;
        
        foreach ($enrollments as $enrollment) {
            // Get student user_id
            $student = $db->table('students')
                ->where('id', $enrollment['student_id'])
                ->get()
                ->getRowArray();
            
            if ($student) {
                $message = "✏️ Assignment updated: '{$assignment['title']}' in {$assignment['course_code']} has been modified. Please review the changes.";
                
                $this->notificationModel->createNotification(
                    $student['user_id'],
                    $message,
                    'assignment',
                    $assignmentId,
                    'assignment'
                );
                
                $notificationCount++;
            }
        }
        
        log_message('info', "Notified {$notificationCount} student(s) about assignment update: {$assignment['title']}");
    }
    
    /**
     * Notify students when assignment is deleted
     */
    private function notifyStudentsAssignmentDeleted($assignmentId, $courseOfferingId, $assignmentTitle)
    {
        // Get all enrolled students
        $enrollments = $this->enrollmentModel
            ->where('course_offering_id', $courseOfferingId)
            ->where('enrollment_status', 'enrolled')
            ->findAll();

        $db = \Config\Database::connect();
        
        // Get course code
        $courseInfo = $db->table('course_offerings co')
            ->select('c.course_code')
            ->join('courses c', 'c.id = co.course_id')
            ->where('co.id', $courseOfferingId)
            ->get()
            ->getRowArray();
        
        $courseCode = $courseInfo ? $courseInfo['course_code'] : 'Unknown';
        $notificationCount = 0;
        
        foreach ($enrollments as $enrollment) {
            // Get student user_id
            $student = $db->table('students')
                ->where('id', $enrollment['student_id'])
                ->get()
                ->getRowArray();
            
            if ($student) {
                $message = "🗑️ Assignment deleted: '{$assignmentTitle}' in {$courseCode} has been removed by your instructor.";
                
                $this->notificationModel->createNotification(
                    $student['user_id'],
                    $message,
                    'assignment',
                    $assignmentId,
                    'assignment'
                );
                
                $notificationCount++;
            }
        }
        
        log_message('info', "Notified {$notificationCount} student(s) about assignment deletion: {$assignmentTitle}");
    }

    // Admin Assignment Management Methods
    public function manageAssignments()
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        $db = \Config\Database::connect();
        $assignments = $db->table('assignments a')
            ->select('a.*, c.course_code, c.title as course_title, co.section, 
                      at.type_name, gp.period_name,
                      CONCAT(u.first_name, " ", COALESCE(u.middle_name, ""), " ", u.last_name) as instructor_name,
                      (SELECT COUNT(*) FROM submissions WHERE assignment_id = a.id) as submission_count')
            ->join('course_offerings co', 'co.id = a.course_offering_id')
            ->join('courses c', 'c.id = co.course_id')
            ->join('assignment_types at', 'at.id = a.assignment_type_id', 'left')
            ->join('grading_periods gp', 'gp.id = a.grading_period_id', 'left')
            ->join('course_instructors ci', 'ci.course_offering_id = co.id')
            ->join('users u', 'u.id = ci.instructor_id')
            ->where('a.is_active', 1)
            ->orderBy('a.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Manage Assignments - MGOD LMS',
            'assignments' => $assignments
        ];

        return view('admin/manage_assignments', $data);
    }

    public function adminViewAssignment($assignmentId)
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        $assignment = $this->assignmentModel->getAssignmentWithDetails($assignmentId);
        if (!$assignment) {
            return redirect()->to(base_url('admin/manage_assignments'))->with('error', 'Assignment not found');
        }

        // Get all submissions for this assignment
        $db = \Config\Database::connect();
        $submissions = $db->table('submissions s')
            ->select('s.*, CONCAT(u.first_name, " ", COALESCE(u.middle_name, ""), " ", u.last_name) as student_name, 
                      u.user_code as student_code, e.id as enrollment_id')
            ->join('enrollments e', 'e.id = s.enrollment_id')
            ->join('users u', 'u.id = e.student_id')
            ->where('s.assignment_id', $assignmentId)
            ->orderBy('s.submitted_at', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'View Assignment - MGOD LMS',
            'assignment' => $assignment,
            'submissions' => $submissions
        ];

        return view('admin/view_assignment', $data);
    }

    public function adminCreateAssignment()
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        $db = \Config\Database::connect();
        $courses = $db->table('course_offerings co')
            ->select('co.id as course_offering_id, c.course_code, c.title as course_title, 
                      co.section, t.term_name, co.term_id')
            ->join('courses c', 'c.id = co.course_id')
            ->join('terms t', 't.id = co.term_id')
            ->where('co.status', 'open')
            ->orderBy('c.course_code', 'ASC')
            ->get()
            ->getResultArray();

        $assignmentTypes = $this->assignmentTypeModel->findAll();
        $gradingPeriods = $this->gradingPeriodModel->findAll();

        $data = [
            'title' => 'Create Assignment - MGOD LMS',
            'courses' => $courses,
            'assignmentTypes' => $assignmentTypes,
            'gradingPeriods' => $gradingPeriods
        ];

        return view('admin/create_assignment', $data);
    }

    public function adminStoreAssignment()
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        // Validation
        $rules = [
            'title' => 'required|min_length[3]|max_length[255]',
            'course_offering_id' => 'required|integer',
            'assignment_type_id' => 'required|integer',
            'grading_period_id' => 'required|integer',
            'max_score' => 'required|decimal|greater_than[0]',
            'due_date' => 'required|valid_date'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $title = $this->request->getPost('title');
        $courseOfferingId = $this->request->getPost('course_offering_id');
        $dueDate = $this->request->getPost('due_date');
        $availableFrom = $this->request->getPost('available_from');
        $availableUntil = $this->request->getPost('available_until');

        // Validate title pattern
        if (!preg_match('/^[a-zA-Z0-9\s\-,]+$/', $title)) {
            return redirect()->back()->with('error', 'Title can only contain letters, numbers, spaces, hyphens, and commas');
        }

        // Validate dates
        if (strtotime($dueDate) < time()) {
            return redirect()->back()->with('error', 'Due date cannot be in the past');
        }

        if ($availableFrom && strtotime($availableFrom) >= strtotime($dueDate)) {
            return redirect()->back()->with('error', 'Available From date must be before the Due Date');
        }

        if ($availableFrom && $availableUntil && strtotime($availableUntil) <= strtotime($availableFrom)) {
            return redirect()->back()->with('error', 'Available Until date must be after Available From');
        }

        // Handle attachment upload
        $attachmentPath = null;
        $attachmentFile = $this->request->getFile('attachment_file');
        
        if ($attachmentFile && $attachmentFile->isValid() && !$attachmentFile->hasMoved()) {
            $allowedExtensions = ['pdf', 'ppt', 'pptx'];
            $fileExtension = $attachmentFile->getExtension();
            
            if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
                return redirect()->back()->with('error', 'Only PDF and PowerPoint documents are allowed for attachments');
            }
            
            $maxSize = 10 * 1024 * 1024;
            if ($attachmentFile->getSize() > $maxSize) {
                return redirect()->back()->with('error', 'Attachment file size must not exceed 10MB');
            }
            
            $newName = 'assignment_' . time() . '.' . $fileExtension;
            $uploadPath = WRITEPATH . 'uploads/assignments/';
            
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }
            
            if ($attachmentFile->move($uploadPath, $newName)) {
                $attachmentPath = 'assignments/' . $newName;
            }
        }

        $data = [
            'course_offering_id' => $courseOfferingId,
            'assignment_type_id' => $this->request->getPost('assignment_type_id'),
            'grading_period_id' => $this->request->getPost('grading_period_id'),
            'title' => $title,
            'description' => $this->request->getPost('description'),
            'instructions' => $this->request->getPost('instructions'),
            'attachment_path' => $attachmentPath,
            'submission_type' => $this->request->getPost('submission_type') ?? 'both',
            'max_score' => $this->request->getPost('max_score'),
            'due_date' => $dueDate,
            'available_from' => $availableFrom,
            'available_until' => $availableUntil,
            'allow_late_submission' => $this->request->getPost('allow_late_submission') ? 1 : 0,
            'late_penalty_percentage' => $this->request->getPost('late_penalty_percentage') ?? 0,
            'is_published' => $this->request->getPost('is_published') ? 1 : 0,
            'is_active' => 1
        ];

        if ($this->assignmentModel->insert($data)) {
            return redirect()->to(base_url('admin/manage_assignments'))->with('success', 'Assignment created successfully');
        }

        return redirect()->back()->with('error', 'Failed to create assignment');
    }

    public function adminEditAssignment($assignmentId)
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        $assignment = $this->assignmentModel->getAssignmentWithDetails($assignmentId);
        if (!$assignment) {
            return redirect()->to(base_url('admin/manage_assignments'))->with('error', 'Assignment not found');
        }

        $db = \Config\Database::connect();
        $courses = $db->table('course_offerings co')
            ->select('co.id as course_offering_id, c.course_code, c.title as course_title, 
                      co.section, t.term_name, co.term_id')
            ->join('courses c', 'c.id = co.course_id')
            ->join('terms t', 't.id = co.term_id')
            ->where('co.status', 'open')
            ->orderBy('c.course_code', 'ASC')
            ->get()
            ->getResultArray();

        $assignmentTypes = $this->assignmentTypeModel->findAll();
        $gradingPeriods = $this->gradingPeriodModel->findAll();

        $data = [
            'title' => 'Edit Assignment - MGOD LMS',
            'assignment' => $assignment,
            'courses' => $courses,
            'assignmentTypes' => $assignmentTypes,
            'gradingPeriods' => $gradingPeriods
        ];

        return view('admin/edit_assignment', $data);
    }

    public function adminUpdateAssignment($assignmentId)
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        $assignment = $this->assignmentModel->find($assignmentId);
        if (!$assignment) {
            return redirect()->to(base_url('admin/manage_assignments'))->with('error', 'Assignment not found');
        }

        // Validation
        $rules = [
            'title' => 'required|min_length[3]|max_length[255]',
            'course_offering_id' => 'required|integer',
            'assignment_type_id' => 'required|integer',
            'grading_period_id' => 'required|integer',
            'max_score' => 'required|decimal|greater_than[0]',
            'due_date' => 'required|valid_date'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $title = $this->request->getPost('title');
        $dueDate = $this->request->getPost('due_date');
        $availableFrom = $this->request->getPost('available_from');
        $availableUntil = $this->request->getPost('available_until');

        // Validate title pattern
        if (!preg_match('/^[a-zA-Z0-9\s\-,]+$/', $title)) {
            return redirect()->back()->with('error', 'Title can only contain letters, numbers, spaces, hyphens, and commas');
        }

        // Validate dates
        if (strtotime($dueDate) < time()) {
            return redirect()->back()->with('error', 'Due date cannot be in the past');
        }

        if ($availableFrom && strtotime($availableFrom) >= strtotime($dueDate)) {
            return redirect()->back()->with('error', 'Available From date must be before the Due Date');
        }

        if ($availableFrom && $availableUntil && strtotime($availableUntil) <= strtotime($availableFrom)) {
            return redirect()->back()->with('error', 'Available Until date must be after Available From');
        }

        // Handle attachment upload
        $attachmentPath = $assignment['attachment_path']; // Keep existing
        $attachmentFile = $this->request->getFile('attachment_file');
        
        if ($attachmentFile && $attachmentFile->isValid() && !$attachmentFile->hasMoved()) {
            $allowedExtensions = ['pdf', 'ppt', 'pptx'];
            $fileExtension = $attachmentFile->getExtension();
            
            if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
                return redirect()->back()->with('error', 'Only PDF and PowerPoint documents are allowed for attachments');
            }
            
            $maxSize = 10 * 1024 * 1024;
            if ($attachmentFile->getSize() > $maxSize) {
                return redirect()->back()->with('error', 'Attachment file size must not exceed 10MB');
            }
            
            // Delete old attachment if exists
            if ($attachmentPath && file_exists(WRITEPATH . 'uploads/' . $attachmentPath)) {
                unlink(WRITEPATH . 'uploads/' . $attachmentPath);
            }
            
            $newName = 'assignment_' . time() . '.' . $fileExtension;
            $uploadPath = WRITEPATH . 'uploads/assignments/';
            
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }
            
            if ($attachmentFile->move($uploadPath, $newName)) {
                $attachmentPath = 'assignments/' . $newName;
            }
        }

        $data = [
            'course_offering_id' => $this->request->getPost('course_offering_id'),
            'assignment_type_id' => $this->request->getPost('assignment_type_id'),
            'grading_period_id' => $this->request->getPost('grading_period_id'),
            'title' => $title,
            'description' => $this->request->getPost('description'),
            'instructions' => $this->request->getPost('instructions'),
            'attachment_path' => $attachmentPath,
            'submission_type' => $this->request->getPost('submission_type') ?? 'both',
            'max_score' => $this->request->getPost('max_score'),
            'due_date' => $dueDate,
            'available_from' => $availableFrom,
            'available_until' => $availableUntil,
            'allow_late_submission' => $this->request->getPost('allow_late_submission') ? 1 : 0,
            'late_penalty_percentage' => $this->request->getPost('late_penalty_percentage') ?? 0,
            'is_published' => $this->request->getPost('is_published') ? 1 : 0
        ];

        if ($this->assignmentModel->update($assignmentId, $data)) {
            return redirect()->to(base_url('admin/manage_assignments'))->with('success', 'Assignment updated successfully');
        }

        return redirect()->back()->with('error', 'Failed to update assignment');
    }

    public function adminDeleteAssignment($assignmentId)
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        $assignment = $this->assignmentModel->find($assignmentId);
        if (!$assignment) {
            return redirect()->to(base_url('admin/manage_assignments'))->with('error', 'Assignment not found');
        }

        // Check if assignment has submissions
        $db = \Config\Database::connect();
        $submissionCount = $db->table('submissions')
            ->where('assignment_id', $assignmentId)
            ->countAllResults();

        if ($submissionCount > 0) {
            return redirect()->back()->with('error', 'Cannot delete assignment: It has ' . $submissionCount . ' student submission(s)');
        }

        // Check if assignment has attachment
        if (!empty($assignment['attachment_path'])) {
            return redirect()->back()->with('error', 'Cannot delete assignment: It has an attachment file. Please remove the attachment first.');
        }

        // Soft delete by setting is_active to 0
        if ($this->assignmentModel->update($assignmentId, ['is_active' => 0])) {
            return redirect()->to(base_url('admin/manage_assignments'))->with('success', 'Assignment deleted successfully');
        }

        return redirect()->back()->with('error', 'Failed to delete assignment');
    }
}
