<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AssignmentModel;
use App\Models\SubmissionModel;
use App\Models\EnrollmentModel;
use App\Models\NotificationModel;
use App\Models\CourseInstructorModel;
use App\Models\StudentModel;
use App\Models\InstructorModel;
use CodeIgniter\HTTP\ResponseInterface;

class Submission extends BaseController
{
    protected $assignmentModel;
    protected $submissionModel;
    protected $enrollmentModel;
    protected $notificationModel;
    protected $courseInstructorModel;
    protected $studentModel;
    protected $instructorModel;
    
    public function __construct()
    {
        $this->assignmentModel = new AssignmentModel();
        $this->submissionModel = new SubmissionModel();
        $this->enrollmentModel = new EnrollmentModel();
        $this->notificationModel = new NotificationModel();
        $this->courseInstructorModel = new CourseInstructorModel();
        $this->studentModel = new StudentModel();
        $this->instructorModel = new InstructorModel();
    }

    public function studentAssignments()
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'student') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        // Get student record from user_id
        $student = $this->studentModel->getStudentByUserId($this->session->get('userID'));
        if (!$student) {
            return redirect()->to(base_url('login'))->with('error', 'Student record not found');
        }
        $studentId = $student['id'];

        $db = \Config\Database::connect();
        $assignments = $db->table('assignments a')
            ->select('a.*, c.course_code, c.title as course_title, co.section, 
                      at.type_name, gp.period_name,
                      s.id as submission_id, s.status as submission_status, 
                      s.score, s.submitted_at, s.is_late,
                      e.id as enrollment_id')
            ->join('course_offerings co', 'co.id = a.course_offering_id')
            ->join('courses c', 'c.id = co.course_id')
            ->join('enrollments e', 'e.course_offering_id = co.id')
            ->join('assignment_types at', 'at.id = a.assignment_type_id', 'left')
            ->join('grading_periods gp', 'gp.id = a.grading_period_id', 'left')
            ->join('submissions s', 's.assignment_id = a.id AND s.enrollment_id = e.id', 'left')
            ->where('e.student_id', $studentId)
            ->where('e.enrollment_status', 'enrolled')
            ->where('a.is_active', 1)
            ->where('a.is_published', 1)
            ->orderBy('a.due_date', 'ASC')
            ->get()
            ->getResultArray();

        $upcoming = [];
        $overdue = [];
        $submitted = [];
        $graded = [];

        $now = date('Y-m-d H:i:s');

        foreach ($assignments as $assignment) {
            if ($assignment['submission_status'] === 'graded') {
                $graded[] = $assignment;
            } elseif ($assignment['submission_status'] === 'submitted') {
                $submitted[] = $assignment;
            } elseif ($assignment['due_date'] < $now) {
                $overdue[] = $assignment;
            } else {
                $upcoming[] = $assignment;
            }
        }

        $data = [
            'title' => 'My Assignments',
            'upcoming' => $upcoming,
            'overdue' => $overdue,
            'submitted' => $submitted,
            'graded' => $graded
        ];

        return view('student/assignments', $data);
    }

    public function viewAssignment($assignmentId)
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'student') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        // Get student record
        $student = $this->studentModel->getStudentByUserId($this->session->get('userID'));
        if (!$student) {
            return redirect()->to(base_url('login'))->with('error', 'Student record not found');
        }
        $studentId = $student['id'];

        // Get assignment with details
        $assignment = $this->assignmentModel->getAssignmentWithDetails($assignmentId);
        if (!$assignment) {
            return redirect()->to(base_url('student/assignments'))->with('error', 'Assignment not found');
        }

        // Check if student is enrolled in the course
        $db = \Config\Database::connect();
        $enrollment = $db->table('enrollments')
            ->where('student_id', $studentId)
            ->where('course_offering_id', $assignment['course_offering_id'])
            ->where('enrollment_status', 'enrolled')
            ->get()
            ->getRowArray();

        if (!$enrollment) {
            return redirect()->to(base_url('student/assignments'))->with('error', 'You are not enrolled in this course');
        }

        // Get existing submission if any
        $submission = $db->table('submissions')
            ->where('assignment_id', $assignmentId)
            ->where('enrollment_id', $enrollment['id'])
            ->get()
            ->getRowArray();

        // Check if assignment is available for submission
        $now = date('Y-m-d H:i:s');
        $isAvailable = true;
        $availabilityMessage = '';

        if ($assignment['available_from'] && $now < $assignment['available_from']) {
            $isAvailable = false;
            $availabilityMessage = 'This assignment will be available on ' . date('M j, Y g:i A', strtotime($assignment['available_from']));
        }

        if ($assignment['available_until'] && $now > $assignment['available_until']) {
            $isAvailable = false;
            $availabilityMessage = 'Submission window closed on ' . date('M j, Y g:i A', strtotime($assignment['available_until']));
        }

        $data = [
            'title' => 'Assignment - ' . $assignment['title'],
            'assignment' => $assignment,
            'submission' => $submission,
            'enrollment' => $enrollment,
            'isAvailable' => $isAvailable,
            'availabilityMessage' => $availabilityMessage,
            'isOverdue' => $now > $assignment['due_date'],
            'canSubmitLate' => $assignment['allow_late_submission'] && $now > $assignment['due_date']
        ];

        return view('student/assignment_detail', $data);
    }

    public function downloadAttachment($assignmentId)
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'student') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        // Get student record
        $student = $this->studentModel->getStudentByUserId($this->session->get('userID'));
        if (!$student) {
            return redirect()->to(base_url('login'))->with('error', 'Student record not found');
        }
        $studentId = $student['id'];

        // Get assignment with details
        $assignment = $this->assignmentModel->find($assignmentId);
        if (!$assignment || !$assignment['attachment_path']) {
            return redirect()->back()->with('error', 'Attachment not found');
        }

        // Check if student is enrolled in the course
        $db = \Config\Database::connect();
        $enrollment = $db->table('enrollments')
            ->where('student_id', $studentId)
            ->where('course_offering_id', $assignment['course_offering_id'])
            ->where('enrollment_status', 'enrolled')
            ->get()
            ->getRowArray();

        if (!$enrollment) {
            return redirect()->back()->with('error', 'You are not enrolled in this course');
        }

        $filePath = WRITEPATH . 'uploads/' . $assignment['attachment_path'];
        
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found');
        }

        // Set appropriate content type based on file extension
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        switch(strtolower($fileExtension)) {
            case 'pdf':
                $contentType = 'application/pdf';
                break;
            case 'doc':
                $contentType = 'application/msword';
                break;
            case 'docx':
                $contentType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                break;
            default:
                $contentType = 'application/octet-stream';
        }

        return $this->response
            ->setHeader('Content-Type', $contentType)
            ->setHeader('Content-Disposition', 'inline; filename="' . basename($filePath) . '"')
            ->download($filePath, null);
    }

    public function submit()
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'student') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(403);
        }

        // Get student record from user_id
        $student = $this->studentModel->getStudentByUserId($this->session->get('userID'));
        if (!$student) {
            return $this->response->setJSON(['success' => false, 'message' => 'Student record not found'])->setStatusCode(404);
        }
        $studentId = $student['id'];
          $assignmentId = $this->request->getPost('assignment_id');
        $enrollmentId = $this->request->getPost('enrollment_id');
        $submissionText = $this->request->getPost('submission_text');

        // Get assignment with course details
        $db = \Config\Database::connect();
        $assignment = $db->table('assignments a')
            ->select('a.*, c.course_code, c.title as course_title')
            ->join('course_offerings co', 'co.id = a.course_offering_id')
            ->join('courses c', 'c.id = co.course_id')
            ->where('a.id', $assignmentId)
            ->get()
            ->getRowArray();
            
        if (!$assignment) {
            return $this->response->setJSON(['success' => false, 'message' => 'Assignment not found'])->setStatusCode(404);
        }

        $enrollment = $this->enrollmentModel->find($enrollmentId);
        if (!$enrollment || $enrollment['student_id'] != $studentId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid enrollment'])->setStatusCode(403);
        }

        $submissionType = $assignment['submission_type'] ?? 'both';
        $file = $this->request->getFile('submission_file');
        $hasFile = $file && $file->isValid() && !$file->hasMoved();

        if ($submissionType === 'text' && empty($submissionText)) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Text submission is required for this assignment'
            ])->setStatusCode(400);
        }

        if ($submissionType === 'file' && !$hasFile) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'File upload is required for this assignment'
            ])->setStatusCode(400);
        }

        if ($submissionType === 'both' && empty($submissionText) && !$hasFile) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Please provide either a text submission or upload a file'
            ])->setStatusCode(400);
        }

        $filePath = null;
        
        if ($hasFile) {
            $allowedExtensions = ['pdf', 'doc', 'docx'];
            $fileExtension = $file->getExtension();
            
            if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'Only PDF and Word documents are allowed'
                ])->setStatusCode(400);
            }

            $maxSize = 10 * 1024 * 1024;
            if ($file->getSize() > $maxSize) {
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'File size must not exceed 10MB'
                ])->setStatusCode(400);
            }

            $newName = 'submission_' . $assignmentId . '_' . $enrollmentId . '_' . time() . '.' . $fileExtension;
            $uploadPath = WRITEPATH . 'uploads/submissions/';
            
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            if ($file->move($uploadPath, $newName)) {
                $filePath = 'submissions/' . $newName;
            } else {
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'Failed to upload file'
                ])->setStatusCode(500);
            }
        }

        if (!$submissionText && !$filePath) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Please provide either text submission or upload a file'
            ])->setStatusCode(400);
        }

        $now = date('Y-m-d H:i:s');
        $isLate = ($now > $assignment['due_date']) ? 1 : 0;

        if ($isLate && !$assignment['allow_late_submission']) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Late submissions are not allowed for this assignment'
            ])->setStatusCode(400);
        }

        $db = \Config\Database::connect();
        $existingSubmission = $db->table('submissions')
            ->where('assignment_id', $assignmentId)
            ->where('enrollment_id', $enrollmentId)
            ->get()
            ->getRowArray();

        $submissionData = [
            'enrollment_id' => $enrollmentId,
            'assignment_id' => $assignmentId,
            'submission_text' => $submissionText,
            'file_path' => $filePath,
            'submitted_at' => $now,
            'is_late' => $isLate,
            'status' => 'submitted'
        ];        if ($existingSubmission) {
            if ($existingSubmission['status'] === 'graded') {
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'Cannot resubmit a graded assignment'
                ])->setStatusCode(400);
            }

            if ($this->submissionModel->update($existingSubmission['id'], $submissionData)) {
                // Notify teacher about resubmission
                $this->notifyTeacherNewSubmission($assignmentId, $assignment['course_offering_id'], $studentId);
                
                // Notify student about successful resubmission
                $db = \Config\Database::connect();
                $studentUser = $db->table('students')->where('id', $studentId)->get()->getRowArray();
                if ($studentUser) {
                    $statusMessage = $isLate ? '⚠️ Late resubmission accepted' : '✅ Resubmission successful';
                    $message = "{$statusMessage} for assignment '{$assignment['title']}' in {$assignment['course_code']}";
                    
                    $this->notificationModel->createNotification(
                        $studentUser['user_id'],
                        $message,
                        'submission',
                        $assignmentId,
                        'assignment'
                    );
                }
                
                return $this->response->setJSON(['success' => true, 'message' => 'Assignment resubmitted successfully']);
            }
        } else {
            if ($this->submissionModel->insert($submissionData)) {
                // Notify teacher about new submission
                $this->notifyTeacherNewSubmission($assignmentId, $assignment['course_offering_id'], $studentId);
                
                // Notify student about successful submission
                $db = \Config\Database::connect();
                $studentUser = $db->table('students')->where('id', $studentId)->get()->getRowArray();
                if ($studentUser) {
                    if ($isLate) {
                        $lateMessage = $assignment['allow_late_submission'] 
                            ? "⚠️ Late submission accepted for assignment '{$assignment['title']}' in {$assignment['course_code']}. Late penalty may apply." 
                            : "⚠️ Late submission recorded for assignment '{$assignment['title']}' in {$assignment['course_code']}'";
                    } else {
                        $lateMessage = "✅ Assignment '{$assignment['title']}' in {$assignment['course_code']}' submitted successfully on time!";
                    }
                    
                    $this->notificationModel->createNotification(
                        $studentUser['user_id'],
                        $lateMessage,
                        'submission',
                        $assignmentId,
                        'assignment'
                    );
                }
                
                return $this->response->setJSON(['success' => true, 'message' => 'Assignment submitted successfully']);
            }
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to submit assignment'])->setStatusCode(500);
    }

    public function downloadSubmission($submissionId)
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        $userRole = $this->session->get('role');
        $userId = $this->session->get('userID');

        $submission = $this->submissionModel->getSubmissionWithDetails($submissionId);
        if (!$submission) {
            return redirect()->back()->with('error', 'Submission not found');
        }

        $db = \Config\Database::connect();
        $enrollment = $db->table('enrollments')->where('id', $submission['enrollment_id'])->get()->getRowArray();

        if ($userRole === 'student' && $enrollment['student_id'] != $userId) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        if ($userRole === 'teacher') {
            // Get instructor record from user_id
            $instructor = $this->instructorModel->getInstructorByUserId($userId);
            if (!$instructor) {
                return redirect()->back()->with('error', 'Instructor record not found');
            }
            
            $assignment = $this->assignmentModel->find($submission['assignment_id']);
            $isInstructor = $this->courseInstructorModel
                ->where('course_offering_id', $assignment['course_offering_id'])
                ->where('instructor_id', $instructor['id'])
                ->first();

            if (!$isInstructor) {
                return redirect()->back()->with('error', 'Unauthorized access');
            }
        }

        if (!$submission['file_path']) {
            return redirect()->back()->with('error', 'No file attached to this submission');
        }

        $filePath = WRITEPATH . 'uploads/' . $submission['file_path'];
        
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found');
        }

        return $this->response->download($filePath, null)->setFileName(basename($filePath));
    }    /**
     * Notify teachers when a student submits an assignment
     */
    private function notifyTeacherNewSubmission($assignmentId, $courseOfferingId, $studentId)
    {
        $assignment = $this->assignmentModel->getAssignmentWithDetails($assignmentId);
        
        $db = \Config\Database::connect();
        
        // Get student info
        $student = $db->table('students s')
            ->select("CONCAT(u.first_name, ' ', COALESCE(u.middle_name, ''), ' ', u.last_name) as full_name, s.student_id_number")
            ->join('users u', 'u.id = s.user_id')
            ->where('s.id', $studentId)
            ->get()
            ->getRowArray();
        
        if (!$student) {
            return;
        }
        
        // Get all instructors for this course
        $instructors = $db->table('course_instructors ci')
            ->select('i.user_id, CONCAT(u.first_name, " ", u.last_name) as instructor_name')
            ->join('instructors i', 'i.id = ci.instructor_id')
            ->join('users u', 'u.id = i.user_id')
            ->where('ci.course_offering_id', $courseOfferingId)
            ->get()
            ->getResultArray();

        // Notify each instructor
        foreach ($instructors as $instructor) {
            $message = "📝 New submission from {$student['full_name']} ({$student['student_id_number']}) for assignment '{$assignment['title']}' in {$assignment['course_code']}";
            
            $this->notificationModel->createNotification(
                $instructor['user_id'],
                $message,
                'assignment',
                $assignmentId,
                'assignment'
            );
        }
        
        log_message('info', "Notified " . count($instructors) . " instructor(s) about new submission for assignment ID: {$assignmentId}");
    }
    
    /**
     * Notify student when assignment is overdue (called by cron job or scheduler)
     */
    public function notifyOverdueAssignments()
    {
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');
        
        // Get all overdue assignments that haven't been submitted
        $overdueAssignments = $db->table('assignments a')
            ->select('a.id as assignment_id, a.title, a.due_date, a.course_offering_id,
                      c.course_code, e.id as enrollment_id, s.user_id as student_user_id,
                      CONCAT(u.first_name, " ", u.last_name) as student_name')
            ->join('course_offerings co', 'co.id = a.course_offering_id')
            ->join('courses c', 'c.id = co.course_id')
            ->join('enrollments e', 'e.course_offering_id = co.id')
            ->join('students s', 's.id = e.student_id')
            ->join('users u', 'u.id = s.user_id')
            ->where('a.is_active', 1)
            ->where('a.is_published', 1)
            ->where('a.due_date <', $now)
            ->where('e.enrollment_status', 'enrolled')
            ->where('NOT EXISTS (
                SELECT 1 FROM submissions sub 
                WHERE sub.assignment_id = a.id 
                AND sub.enrollment_id = e.id
            )')
            ->get()
            ->getResultArray();
        
        $notificationCount = 0;
        
        foreach ($overdueAssignments as $assignment) {
            // Check if student was already notified about this overdue assignment
            $existingNotification = $db->table('notifications')
                ->like('message', "⚠️ OVERDUE: Assignment '{$assignment['title']}'")
                ->where('user_id', $assignment['student_user_id'])
                ->where('created_at >', date('Y-m-d H:i:s', strtotime('-24 hours')))
                ->get()
                ->getRowArray();
            
            if (!$existingNotification) {
                $daysOverdue = floor((strtotime($now) - strtotime($assignment['due_date'])) / 86400);
                $message = "⚠️ OVERDUE: Assignment '{$assignment['title']}' in {$assignment['course_code']} was due " . 
                          ($daysOverdue == 0 ? 'today' : "{$daysOverdue} day(s) ago") . ". Please submit as soon as possible!";
                
                $this->notificationModel->createNotification(
                    $assignment['student_user_id'],
                    $message,
                    'warning',
                    $assignment['assignment_id'],
                    'assignment'
                );
                
                $notificationCount++;
            }
        }
        
        log_message('info', "Sent {$notificationCount} overdue assignment notification(s)");
        
        return [
            'success' => true,
            'message' => "Sent {$notificationCount} overdue assignment notification(s)",
            'count' => $notificationCount
        ];
    }
    
    /**
     * Notify students about upcoming assignment deadlines (24 hours before due)
     */
    public function notifyUpcomingDeadlines()
    {
        $db = \Config\Database::connect();
        $tomorrow = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $now = date('Y-m-d H:i:s');
        
        // Get assignments due in the next 24 hours
        $upcomingAssignments = $db->table('assignments a')
            ->select('a.id as assignment_id, a.title, a.due_date, a.course_offering_id,
                      c.course_code, e.id as enrollment_id, s.user_id as student_user_id,
                      CONCAT(u.first_name, " ", u.last_name) as student_name')
            ->join('course_offerings co', 'co.id = a.course_offering_id')
            ->join('courses c', 'c.id = co.course_id')
            ->join('enrollments e', 'e.course_offering_id = co.id')
            ->join('students s', 's.id = e.student_id')
            ->join('users u', 'u.id = s.user_id')
            ->where('a.is_active', 1)
            ->where('a.is_published', 1)
            ->where('a.due_date >', $now)
            ->where('a.due_date <=', $tomorrow)
            ->where('e.enrollment_status', 'enrolled')
            ->where('NOT EXISTS (
                SELECT 1 FROM submissions sub 
                WHERE sub.assignment_id = a.id 
                AND sub.enrollment_id = e.id
            )')
            ->get()
            ->getResultArray();
        
        $notificationCount = 0;
        
        foreach ($upcomingAssignments as $assignment) {
            // Check if already notified about this deadline
            $existingNotification = $db->table('notifications')
                ->like('message', "⏰ REMINDER: Assignment '{$assignment['title']}'")
                ->where('user_id', $assignment['student_user_id'])
                ->where('created_at >', date('Y-m-d H:i:s', strtotime('-12 hours')))
                ->get()
                ->getRowArray();
            
            if (!$existingNotification) {
                $hoursRemaining = round((strtotime($assignment['due_date']) - time()) / 3600);
                $message = "⏰ REMINDER: Assignment '{$assignment['title']}' in {$assignment['course_code']} is due in {$hoursRemaining} hour(s). Don't forget to submit!";
                
                $this->notificationModel->createNotification(
                    $assignment['student_user_id'],
                    $message,
                    'reminder',
                    $assignment['assignment_id'],
                    'assignment'
                );
                
                $notificationCount++;
            }
        }
        
        log_message('info', "Sent {$notificationCount} deadline reminder notification(s)");
        
        return [
            'success' => true,
            'message' => "Sent {$notificationCount} deadline reminder notification(s)",
            'count' => $notificationCount
        ];
    }
}
