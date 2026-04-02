<?php

namespace App\Libraries;

use App\Models\GradebookEntryModel;
use App\Models\EnrollmentModel;
use App\Models\SubmissionModel;

class GradeExporter
{
    protected $gradebookEntryModel;
    protected $enrollmentModel;
    protected $submissionModel;

    public function __construct()
    {
        $this->gradebookEntryModel = new GradebookEntryModel();
        $this->enrollmentModel = new EnrollmentModel();
        $this->submissionModel = new SubmissionModel();
    }

    /**
     * Export student grade report to PDF
     * - Uses TCPDF for PDF generation
     * - Includes student info, grade summary by period, assignment breakdown
     * - Returns ['success' => true, 'pdf' => $pdf, 'filename' => $filename]
     */
    public function exportStudentGradeToPDF($enrollmentId)
    {
        // Get enrollment details
        $enrollment = $this->enrollmentModel->getEnrollmentWithDetails($enrollmentId);
        if (!$enrollment) {
            return ['success' => false, 'message' => 'Enrollment not found'];
        }

        // Get grade breakdown using GradeCalculator
        $gradeCalculator = new GradeCalculator();
        $breakdown = $gradeCalculator->getGradeBreakdown($enrollmentId);

        // Get all graded submissions with assignment details
        $db = \Config\Database::connect();
        $submissions = $db->table('submissions s')
            ->select('s.*, a.title, a.max_score, a.due_date, at.type_name, gp.period_name')
            ->join('assignments a', 'a.id = s.assignment_id')
            ->join('assignment_types at', 'at.id = a.assignment_type_id', 'left')
            ->join('grading_periods gp', 'gp.id = a.grading_period_id', 'left')
            ->where('s.enrollment_id', $enrollmentId)
            ->where('s.status', 'graded')
            ->orderBy('a.due_date', 'ASC')
            ->get()
            ->getResultArray();

        // Generate PDF using TCPDF
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('MGOD LMS');
        $pdf->SetAuthor('MGOD LMS');
        $pdf->SetTitle('Grade Report');
        $pdf->SetSubject('Student Grade Report');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);

        // Add page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 10);

        // Build HTML content
        $html = $this->buildGradeReportHTML($enrollment, $breakdown, $submissions);
        
        // Write HTML
        $pdf->writeHTML($html, true, false, true, false, '');

        // Output PDF
        $filename = 'grade_report_' . $enrollment['student_id_number'] . '_' . date('Ymd') . '.pdf';
        
        return [
            'success' => true,
            'pdf' => $pdf,
            'filename' => $filename
        ];
    }

    /**
     * Build HTML for grade report PDF
     * - Student info table
     * - Grade summary table with periods and final
     * - Assignment breakdown table
     */
    protected function buildGradeReportHTML($enrollment, $breakdown, $submissions)
    {
        $html = '<h1 style="text-align: center;">Grade Report</h1>';
        $html .= '<hr>';
        
        // Student info
        $html .= '<table cellpadding="5">';
        $html .= '<tr><td width="30%"><strong>Student Name:</strong></td><td>' . 
                 $enrollment['first_name'] . ' ' . $enrollment['last_name'] . '</td></tr>';
        $html .= '<tr><td><strong>Student ID:</strong></td><td>' . $enrollment['student_id_number'] . '</td></tr>';
        $html .= '<tr><td><strong>Course:</strong></td><td>' . 
                 $enrollment['course_code'] . ' - ' . $enrollment['course_title'] . '</td></tr>';
        $html .= '<tr><td><strong>Section:</strong></td><td>' . $enrollment['section'] . '</td></tr>';
        $html .= '<tr><td><strong>Term:</strong></td><td>' . 
                 $enrollment['semester_name'] . ' ' . $enrollment['academic_year'] . '</td></tr>';
        $html .= '</table>';
        
        $html .= '<br><hr><br>';

        // Grade summary
        $html .= '<h2>Grade Summary</h2>';
        $html .= '<table border="1" cellpadding="5" style="border-collapse: collapse;">';
        $html .= '<tr style="background-color: #f0f0f0;">';
        $html .= '<th width="50%"><strong>Grading Period</strong></th>';
        $html .= '<th width="20%"><strong>Weight</strong></th>';
        $html .= '<th width="30%"><strong>Grade</strong></th>';
        $html .= '</tr>';

        foreach ($breakdown['periods'] as $period) {
            $html .= '<tr>';
            $html .= '<td>' . ($period['period_name'] ?? 'N/A') . '</td>';
            $html .= '<td style="text-align: center;">' . $period['period_weight'] . '%</td>';
            $html .= '<td style="text-align: center;"><strong>' . 
                     number_format($period['final_grade'], 2) . '</strong></td>';
            $html .= '</tr>';
        }

        if ($breakdown['final']) {
            $html .= '<tr style="background-color: #e0e0e0;">';
            $html .= '<td><strong>FINAL GRADE</strong></td>';
            $html .= '<td style="text-align: center;">100%</td>';
            $html .= '<td style="text-align: center;"><strong>' . 
                     number_format($breakdown['final']['final_grade'], 2) . '</strong></td>';
            $html .= '</tr>';
        }

        $html .= '</table>';

        $html .= '<br><hr><br>';

        // Assignment details
        $html .= '<h2>Assignment Breakdown</h2>';
        $html .= '<table border="1" cellpadding="3" style="border-collapse: collapse; font-size: 9px;">';
        $html .= '<tr style="background-color: #f0f0f0;">';
        $html .= '<th width="35%">Assignment</th>';
        $html .= '<th width="15%">Type</th>';
        $html .= '<th width="15%">Period</th>';
        $html .= '<th width="15%">Score</th>';
        $html .= '<th width="20%">Date Graded</th>';
        $html .= '</tr>';

        foreach ($submissions as $sub) {
            $html .= '<tr>';
            $html .= '<td>' . $sub['title'] . '</td>';
            $html .= '<td>' . ($sub['type_name'] ?? 'N/A') . '</td>';
            $html .= '<td>' . ($sub['period_name'] ?? 'N/A') . '</td>';
            $html .= '<td style="text-align: center;">' . 
                     $sub['score'] . ' / ' . $sub['max_score'] . '</td>';
            $html .= '<td style="text-align: center;">' . 
                     date('M d, Y', strtotime($sub['graded_at'])) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';

        $html .= '<br><br>';
        $html .= '<p style="font-size: 8px; text-align: center; color: #666;">';
        $html .= 'Generated on ' . date('F d, Y h:i A') . ' | MGOD Learning Management System';
        $html .= '</p>';

        return $html;
    }

    /**
     * Export class grades to Excel format
     * - Returns data array suitable for spreadsheet
     * - Columns: Student ID, Last Name, First Name, Prelim, Midterm, Finals, Final Grade, Status
     */
    public function exportClassGradesToExcel($courseOfferingId)
    {
        $db = \Config\Database::connect();
        
        // Get course offering details
        $courseOffering = $db->table('course_offerings co')
            ->select('co.*, c.course_code, c.title, t.term_name, ay.year_name')
            ->join('courses c', 'c.id = co.course_id')
            ->join('terms t', 't.id = co.term_id')
            ->join('academic_years ay', 'ay.id = t.academic_year_id')
            ->where('co.id', $courseOfferingId)
            ->get()
            ->getRowArray();

        // Get all enrollments
        $enrollments = $this->enrollmentModel->getCourseOfferingEnrollments($courseOfferingId);

        // Prepare data array
        $data = [];
        $data[] = ['Student ID', 'Last Name', 'First Name', 'Prelim', 'Midterm', 'Finals', 'Final Grade', 'Status'];

        foreach ($enrollments as $enrollment) {
            $grades = $this->gradebookEntryModel->getStudentCourseGrades($enrollment['id']);
            
            $row = [
                $enrollment['student_id_number'] ?? 'N/A',
                $enrollment['last_name'],
                $enrollment['first_name'],
            ];

            // Add period grades
            $periodGrades = ['', '', '']; // Prelim, Midterm, Finals
            $finalGrade = '';
            
            foreach ($grades as $grade) {
                if ($grade['grading_period_id'] === null) {
                    $finalGrade = number_format($grade['final_grade'], 2);
                } else {
                    $order = $grade['period_order'] ?? 0;
                    if ($order >= 0 && $order < 3) {
                        $periodGrades[$order] = number_format($grade['final_grade'], 2);
                    }
                }
            }

            $row = array_merge($row, $periodGrades);
            $row[] = $finalGrade;
            $row[] = $grades[0]['grade_status'] ?? 'calculated';

            $data[] = $row;
        }

        return [
            'success' => true,
            'data' => $data,
            'course_info' => $courseOffering
        ];
    }

    /**
     * Generate CSV data for class grades
     * - Uses exportClassGradesToExcel internally
     * - Converts to CSV string
     */
    public function generateClassGradeCSV($courseOfferingId)
    {
        $result = $this->exportClassGradesToExcel($courseOfferingId);
        
        if (!$result['success']) {
            return $result;
        }

        // Convert to CSV
        $output = fopen('php://temp', 'r+');
        
        foreach ($result['data'] as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        $filename = 'grades_' . $result['course_info']['course_code'] . '_' . date('Ymd') . '.csv';

        return [
            'success' => true,
            'csv' => $csv,
            'filename' => $filename
        ];
    }
}
