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
}
