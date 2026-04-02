<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\GradeCalculator;
use App\Libraries\GradeExporter;
use App\Models\GradebookEntryModel;
use App\Models\GradeHistoryModel;
use App\Models\EnrollmentModel;
use App\Models\StudentModel;
use App\Models\CourseOfferingModel;
use App\Models\NotificationModel;
use CodeIgniter\HTTP\ResponseInterface;

class Gradebook extends BaseController
{
    protected $session;
    protected $gradeCalculator;
    protected $gradeExporter;
    protected $gradebookEntryModel;
    protected $gradeHistoryModel;
    protected $enrollmentModel;
    protected $studentModel;
    protected $courseOfferingModel;
    protected $notificationModel;
    protected $db;

    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->gradeCalculator = new GradeCalculator();
        $this->gradeExporter = new GradeExporter();
        $this->gradebookEntryModel = new GradebookEntryModel();
        $this->gradeHistoryModel = new GradeHistoryModel();
        $this->enrollmentModel = new EnrollmentModel();
        $this->studentModel = new StudentModel();
        $this->courseOfferingModel = new CourseOfferingModel();
        $this->notificationModel = new NotificationModel();
        $this->db = \Config\Database::connect();
    }

    //=================================================================
    // STUDENT METHODS
    //=================================================================

    /**
     * Student gradebook dashboard - shows all enrolled courses with grades
     */
    public function studentIndex()
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'student') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        $userId = $this->session->get('userID');
        $student = $this->studentModel->getStudentByUserId($userId);
        
        if (!$student) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Student record not found');
        }

        // Get all enrollments
        $enrollments = $this->enrollmentModel->getStudentEnrollments($student['id']);

        $courses = [];
        foreach ($enrollments as $enrollment) {
            // Get final grade
            $finalGrade = $this->gradebookEntryModel->getFinalGrade($enrollment['id']);
            
            // Get period grades
            $periodGrades = $this->gradebookEntryModel->getStudentCourseGrades($enrollment['id']);

            $courses[] = [
                'enrollment' => $enrollment,
                'final_grade' => $finalGrade,
                'period_grades' => $periodGrades
            ];
        }

        $data = [
            'title' => 'My Grades',
            'courses' => $courses
        ];

        return view('student/gradebook', $data);
    }

    /**
     * Student course grade details - detailed view with submissions
     */
    public function courseDetails($enrollmentId)
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'student') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        $userId = $this->session->get('userID');
        $student = $this->studentModel->getStudentByUserId($userId);

        // Verify enrollment belongs to this student
        $enrollment = $this->enrollmentModel->find($enrollmentId);
        if (!$enrollment || $enrollment['student_id'] != $student['id']) {
            return redirect()->to(base_url('student/gradebook'))->with('error', 'Invalid enrollment');
        }

        // Get enrollment details
        $enrollmentDetails = $this->enrollmentModel->getEnrollmentWithDetails($enrollmentId);

        // Get grade breakdown
        $breakdown = $this->gradeCalculator->getGradeBreakdown($enrollmentId);

        // Get all submissions with details
        $submissions = $this->db->table('submissions s')
            ->select('s.*, a.title, a.max_score, a.due_date, a.description, 
                      at.type_name, gp.period_name, gp.period_order')
            ->join('assignments a', 'a.id = s.assignment_id')
            ->join('assignment_types at', 'at.id = a.assignment_type_id', 'left')
            ->join('grading_periods gp', 'gp.id = a.grading_period_id', 'left')
            ->where('s.enrollment_id', $enrollmentId)
            ->orderBy('a.due_date', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Course Grade Details',
            'enrollment' => $enrollmentDetails,
            'breakdown' => $breakdown,
            'submissions' => $submissions
        ];

        return view('student/gradebook_course_details', $data);
    }

    /**
     * Export student grade to PDF
     */
    public function exportPDF($enrollmentId)
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'student') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        $userId = $this->session->get('userID');
        $student = $this->studentModel->getStudentByUserId($userId);

        // Verify enrollment belongs to this student
        $enrollment = $this->enrollmentModel->find($enrollmentId);
        if (!$enrollment || $enrollment['student_id'] != $student['id']) {
            return redirect()->to(base_url('student/gradebook'))->with('error', 'Invalid enrollment');
        }

        $result = $this->gradeExporter->exportStudentGradeToPDF($enrollmentId);

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        // Output PDF
        $result['pdf']->Output($result['filename'], 'D');
    }

    /**
     * Export student grade to Excel/CSV
     */
    public function exportExcel($enrollmentId)
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'student') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        $userId = $this->session->get('userID');
        $student = $this->studentModel->getStudentByUserId($userId);

        // Verify enrollment belongs to this student
        $enrollment = $this->enrollmentModel->find($enrollmentId);
        if (!$enrollment || $enrollment['student_id'] != $student['id']) {
            return redirect()->to(base_url('student/gradebook'))->with('error', 'Invalid enrollment');
        }

        // Get grade data
        $enrollmentDetails = $this->enrollmentModel->getEnrollmentWithDetails($enrollmentId);
        $breakdown = $this->gradeCalculator->getGradeBreakdown($enrollmentId);

        // Build CSV
        $output = fopen('php://temp', 'r+');
        
        // Header
        fputcsv($output, ['MGOD LMS - Grade Report']);
        fputcsv($output, ['Student: ' . $enrollmentDetails['first_name'] . ' ' . $enrollmentDetails['last_name']]);
        fputcsv($output, ['Course: ' . $enrollmentDetails['course_code'] . ' - ' . $enrollmentDetails['course_title']]);
        fputcsv($output, ['']);
        
        // Grades
        fputcsv($output, ['Grading Period', 'Weight', 'Grade']);
        foreach ($breakdown['periods'] as $period) {
            fputcsv($output, [
                $period['period_name'],
                $period['period_weight'] . '%',
                number_format($period['final_grade'], 2)
            ]);
        }
        
        if ($breakdown['final']) {
            fputcsv($output, ['FINAL GRADE', '100%', number_format($breakdown['final']['final_grade'], 2)]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        // Send as download
        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="grade_report_' . date('Ymd') . '.csv"')
            ->setBody($csv);
    }

    //=================================================================
    // TEACHER METHODS
    //=================================================================

    /**
     * Teacher gradebook dashboard - shows courses taught
     */
    public function teacherIndex()
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'teacher') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        $userId = $this->session->get('userID');
        
        // Get instructor record
        $instructor = $this->db->table('instructors')
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();

        if (!$instructor) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Instructor record not found');
        }

        // Get courses taught by this instructor
        $courses = $this->db->table('course_instructors ci')
            ->select('ci.*, co.section, c.course_code, c.title, t.term_name, ay.year_name,
                      COUNT(DISTINCT e.id) as student_count')
            ->join('course_offerings co', 'co.id = ci.course_offering_id')
            ->join('courses c', 'c.id = co.course_id')
            ->join('terms t', 't.id = co.term_id')
            ->join('academic_years ay', 'ay.id = t.academic_year_id')
            ->join('enrollments e', 'e.course_offering_id = co.id AND e.enrollment_status = "enrolled"', 'left')
            ->where('ci.instructor_id', $instructor['id'])
            ->groupBy('ci.id')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Grade Management',
            'courses' => $courses
        ];

        return view('teacher/gradebook', $data);
    }

    /**
     * Bulk grade entry grid - shows all students for a course
     */
    public function gradeEntry($courseOfferingId)
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'teacher') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        // Verify teacher teaches this course
        $userId = $this->session->get('userID');
        $instructor = $this->db->table('instructors')
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();

        $teaches = $this->db->table('course_instructors')
            ->where('instructor_id', $instructor['id'])
            ->where('course_offering_id', $courseOfferingId)
            ->countAllResults() > 0;

        if (!$teaches) {
            return redirect()->to(base_url('teacher/gradebook'))->with('error', 'Unauthorized course access');
        }

        // Get course details
        $courseOffering = $this->db->table('course_offerings co')
            ->select('co.*, c.course_code, c.title, t.term_name')
            ->join('courses c', 'c.id = co.course_id')
            ->join('terms t', 't.id = co.term_id')
            ->where('co.id', $courseOfferingId)
            ->get()
            ->getRowArray();

        // Get enrollments
        $enrollments = $this->enrollmentModel->getCourseOfferingEnrollments($courseOfferingId);

        // Get grading periods
        $gradingPeriods = $this->db->table('grading_periods')
            ->where('term_id', $courseOffering['term_id'])
            ->orderBy('period_order', 'ASC')
            ->get()
            ->getResultArray();

        // Get grades for all students
        $gradesData = [];
        foreach ($enrollments as $enrollment) {
            $grades = $this->gradebookEntryModel->getStudentCourseGrades($enrollment['id']);
            $gradesData[$enrollment['id']] = $grades;
        }

        $data = [
            'title' => 'Grade Entry',
            'course' => $courseOffering,
            'enrollments' => $enrollments,
            'grading_periods' => $gradingPeriods,
            'grades' => $gradesData
        ];

        return view('teacher/gradebook_entry', $data);
    }

    /**
     * Bulk update grades via AJAX
     */
    public function bulkUpdate()
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'teacher') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $updates = $this->request->getJSON(true);

        if (!is_array($updates)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid data format']);
        }

        $userId = $this->session->get('userID');
        $successCount = 0;

        foreach ($updates as $update) {
            $entryId = $update['entry_id'] ?? null;
            $newGrade = $update['grade'] ?? null;

            if ($entryId && $newGrade !== null) {
                $result = $this->gradebookEntryModel->updateCalculatedGrade($entryId, $newGrade);
                if ($result) {
                    $successCount++;
                }
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Updated ' . $successCount . ' grades'
        ]);
    }

    /**
     * CSV import form
     */
    public function csvImportForm($assignmentId)
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'teacher') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        // Get assignment details
        $assignment = $this->db->table('assignments a')
            ->select('a.*, c.course_code, c.title as course_title, co.section')
            ->join('course_offerings co', 'co.id = a.course_offering_id')
            ->join('courses c', 'c.id = co.course_id')
            ->where('a.id', $assignmentId)
            ->get()
            ->getRowArray();

        if (!$assignment) {
            return redirect()->to(base_url('teacher/gradebook'))->with('error', 'Assignment not found');
        }

        $data = [
            'title' => 'Import Grades',
            'assignment' => $assignment
        ];

        return view('teacher/gradebook_import', $data);
    }

    /**
     * Process CSV import
     */
    public function csvImportProcess($assignmentId)
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'teacher') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        $file = $this->request->getFile('csv_file');

        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'Please upload a valid CSV file');
        }

        // Parse CSV
        $csvData = [];
        if ($csv = fopen($file->getTempName(), 'r')) {
            $header = fgetcsv($csv); // Skip header
            
            while ($row = fgetcsv($csv)) {
                if (count($row) >= 2) {
                    $csvData[] = [
                        'student_code' => $row[0],
                        'score' => $row[1]
                    ];
                }
            }
            fclose($csv);
        }

        $userId = $this->session->get('userID');
        $successCount = 0;
        $errors = [];

        foreach ($csvData as $item) {
            // Find student
            $student = $this->db->table('users')
                ->where('user_code', $item['student_code'])
                ->get()
                ->getRowArray();

            if (!$student) {
                $errors[] = 'Student not found: ' . $item['student_code'];
                continue;
            }

            // Find enrollment
            $enrollment = $this->db->table('enrollments e')
                ->join('students s', 's.id = e.student_id')
                ->where('s.user_id', $student['id'])
                ->where('e.course_offering_id', $this->request->getPost('course_offering_id'))
                ->select('e.id')
                ->get()
                ->getRowArray();

            if (!$enrollment) {
                $errors[] = 'Enrollment not found for: ' . $item['student_code'];
                continue;
            }

            // Find or create submission
            $submission = $this->db->table('submissions')
                ->where('assignment_id', $assignmentId)
                ->where('enrollment_id', $enrollment['id'])
                ->get()
                ->getRowArray();

            if ($submission) {
                // Update existing
                $this->db->table('submissions')
                    ->where('id', $submission['id'])
                    ->update([
                        'score' => $item['score'],
                        'graded_by' => $userId,
                        'graded_at' => date('Y-m-d H:i:s'),
                        'status' => 'graded'
                    ]);
            } else {
                // Create new
                $this->db->table('submissions')->insert([
                    'assignment_id' => $assignmentId,
                    'enrollment_id' => $enrollment['id'],
                    'score' => $item['score'],
                    'graded_by' => $userId,
                    'graded_at' => date('Y-m-d H:i:s'),
                    'status' => 'graded',
                    'submitted_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Recalculate grades
            $this->gradeCalculator->recalculateEnrollmentGrades($enrollment['id']);
            $successCount++;
        }

        if ($successCount > 0) {
            return redirect()->back()->with('success', 'Imported ' . $successCount . ' grades successfully');
        } else {
            return redirect()->back()->with('error', 'Import failed: ' . implode(', ', $errors));
        }
    }

    /**
     * Save grade override with justification
     */
    public function saveOverride($entryId)
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'teacher') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $newGrade = $this->request->getPost('new_grade');
        $reason = $this->request->getPost('reason');
        $userId = $this->session->get('userID');

        if ($newGrade === null || empty($reason)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Grade and reason are required'
            ]);
        }

        $result = $this->gradebookEntryModel->saveOverride($entryId, $newGrade, $reason, $userId);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Grade override saved successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to save override'
            ]);
        }
    }

    /**
     * Export class grades to CSV
     */
    public function exportClassGrades($courseOfferingId)
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'teacher') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        $result = $this->gradeExporter->generateClassGradeCSV($courseOfferingId);

        if (!$result['success']) {
            return redirect()->back()->with('error', 'Export failed');
        }

        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $result['filename'] . '"')
            ->setBody($result['csv']);
    }

    //=================================================================
    // ADMIN METHODS
    //=================================================================

    /**
     * Admin grade analytics dashboard with filters and statistics
     */
    public function analytics()
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        // Get filter parameters
        $termId = $this->request->getGet('term_id');
        $courseId = $this->request->getGet('course_id');

        // Get terms for filter
        $terms = $this->db->table('terms t')
            ->select('t.*, ay.year_name, s.semester_name')
            ->join('academic_years ay', 'ay.id = t.academic_year_id')
            ->join('semesters s', 's.id = t.semester_id')
            ->orderBy('t.created_at', 'DESC')
            ->get()
            ->getResultArray();

        // Get courses for filter
        $courses = $this->db->table('courses')
            ->orderBy('course_code', 'ASC')
            ->get()
            ->getResultArray();

        // Build query for grade statistics
        $builder = $this->db->table('gradebook_entries ge')
            ->select('ge.final_grade, ge.grade_status, 
                      c.course_code, c.title as course_title,
                      co.section, t.term_name')
            ->join('enrollments e', 'e.id = ge.enrollment_id')
            ->join('course_offerings co', 'co.id = e.course_offering_id')
            ->join('courses c', 'c.id = co.course_id')
            ->join('terms t', 't.id = co.term_id')
            ->where('ge.grading_period_id', null); // Only final grades

        if ($termId) {
            $builder->where('co.term_id', $termId);
        }

        if ($courseId) {
            $builder->where('co.course_id', $courseId);
        }

        $grades = $builder->get()->getResultArray();

        // Calculate statistics
        $stats = [
            'total_students' => count($grades),
            'average_grade' => 0,
            'passing_count' => 0,
            'failing_count' => 0,
            'incomplete_count' => 0,
            'distribution' => [
                '90-100' => 0,
                '80-89' => 0,
                '75-79' => 0,
                'Below 75' => 0
            ]
        ];

        $gradeSum = 0;
        foreach ($grades as $grade) {
            if ($grade['grade_status'] !== 'calculated') {
                $stats['incomplete_count']++;
                continue;
            }

            $finalGrade = $grade['final_grade'];
            $gradeSum += $finalGrade;

            if ($finalGrade >= 75) {
                $stats['passing_count']++;
            } else {
                $stats['failing_count']++;
            }

            if ($finalGrade >= 90) {
                $stats['distribution']['90-100']++;
            } elseif ($finalGrade >= 80) {
                $stats['distribution']['80-89']++;
            } elseif ($finalGrade >= 75) {
                $stats['distribution']['75-79']++;
            } else {
                $stats['distribution']['Below 75']++;
            }
        }

        if ($stats['total_students'] > 0) {
            $stats['average_grade'] = $gradeSum / $stats['total_students'];
        }

        $data = [
            'title' => 'Grade Analytics',
            'terms' => $terms,
            'courses' => $courses,
            'stats' => $stats,
            'selected_term' => $termId,
            'selected_course' => $courseId
        ];

        return view('admin/gradebook_analytics', $data);
    }

    /**
     * Admin audit trail - shows all grade changes with filters
     */
    public function auditTrail()
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        // Get filter parameters
        $filters = [
            'course_offering_id' => $this->request->getGet('course_offering_id'),
            'student_id' => $this->request->getGet('student_id'),
            'changed_by' => $this->request->getGet('changed_by'),
            'change_type' => $this->request->getGet('change_type'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to')
        ];

        // Get audit trail
        $auditTrail = $this->gradeHistoryModel->getAuditTrail($filters);

        // Get filter options
        $courses = $this->db->table('course_offerings co')
            ->select('co.id, c.course_code, co.section, t.term_name')
            ->join('courses c', 'c.id = co.course_id')
            ->join('terms t', 't.id = co.term_id')
            ->orderBy('t.created_at', 'DESC')
            ->get()
            ->getResultArray();

        // Get instructors by joining users with roles table
        $instructors = $this->db->table('users u')
            ->select('u.id, u.first_name, u.middle_name, u.last_name, u.email')
            ->join('roles r', 'r.id = u.role_id')
            ->where('r.role_name', 'teacher')
            ->orderBy('u.last_name', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Grade Change Audit Trail',
            'audit_trail' => $auditTrail,
            'courses' => $courses,
            'instructors' => $instructors,
            'filters' => $filters
        ];

        return view('admin/gradebook_audit', $data);
    }

    /**
     * Admin system-wide gradebook overview - grouped by department
     */
    public function systemOverview()
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        $termId = $this->request->getGet('term_id');

        // Get terms
        $terms = $this->db->table('terms t')
            ->select('t.*, ay.year_name, s.semester_name')
            ->join('academic_years ay', 'ay.id = t.academic_year_id')
            ->join('semesters s', 's.id = t.semester_id')
            ->orderBy('t.created_at', 'DESC')
            ->get()
            ->getResultArray();

        // If no term selected, use most recent
        if (!$termId && !empty($terms)) {
            $termId = $terms[0]['id'];
        }

        // Get course offerings for selected term
        $courseOfferings = $this->db->table('course_offerings co')
            ->select('co.*, c.course_code, c.title, d.department_name,
                      COUNT(DISTINCT e.id) as student_count')
            ->join('courses c', 'c.id = co.course_id')
            ->join('departments d', 'd.id = c.department_id', 'left')
            ->join('enrollments e', 'e.course_offering_id = co.id AND e.enrollment_status = "enrolled"', 'left')
            ->where('co.term_id', $termId)
            ->groupBy('co.id')
            ->orderBy('d.department_name', 'ASC')
            ->orderBy('c.course_code', 'ASC')
            ->get()
            ->getResultArray();

        // Group by department
        $departmentData = [];
        foreach ($courseOfferings as $offering) {
            $dept = $offering['department_name'] ?? 'No Department';
            if (!isset($departmentData[$dept])) {
                $departmentData[$dept] = [];
            }
            $departmentData[$dept][] = $offering;
        }

        $data = [
            'title' => 'System-Wide Gradebook',
            'terms' => $terms,
            'selected_term' => $termId,
            'department_data' => $departmentData
        ];

        return view('admin/gradebook_overview', $data);
    }

    /**
     * Get student grades for admin view (AJAX)
     */
    public function getStudentGrades($enrollmentId)
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $grades = $this->gradebookEntryModel->getStudentCourseGrades($enrollmentId);
        $finalGrade = $this->gradebookEntryModel->getFinalGrade($enrollmentId);

        return $this->response->setJSON([
            'success' => true,
            'grades' => $grades,
            'final_grade' => $finalGrade
        ]);
    }

    /**
     * Recalculate all grades for a course (admin function)
     */
    public function recalculateCourseGrades($courseOfferingId)
    {
        if (!$this->session->get('isLoggedIn') || $this->session->get('role') !== 'admin') {
            return redirect()->to(base_url('login'))->with('error', 'Unauthorized access');
        }

        $result = $this->gradeCalculator->recalculateCourseGrades($courseOfferingId);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }
}
