<?php

namespace App\Libraries;

use App\Models\GradebookEntryModel;
use App\Models\SubmissionModel;
use App\Models\AssignmentModel;
use App\Models\GradeComponentModel;
use App\Models\GradingPeriodModel;
use App\Models\EnrollmentModel;

class GradeCalculator
{
    protected $gradebookEntryModel;
    protected $submissionModel;
    protected $assignmentModel;
    protected $gradeComponentModel;
    protected $gradingPeriodModel;
    protected $enrollmentModel;
    protected $db;

    public function __construct()
    {
        $this->gradebookEntryModel = new GradebookEntryModel();
        $this->submissionModel = new SubmissionModel();
        $this->assignmentModel = new AssignmentModel();
        $this->gradeComponentModel = new GradeComponentModel();
        $this->gradingPeriodModel = new GradingPeriodModel();
        $this->enrollmentModel = new EnrollmentModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Calculate grade for a specific grading period
     * - Gets grade components (Quiz 30%, Assignment 30%, Exam 40%)
     * - Calculates each component's grade from submissions
     * - Returns weighted average
     */
    public function calculatePeriodGrade($enrollmentId, $gradingPeriodId)
    {
        $enrollment = $this->enrollmentModel->find($enrollmentId);
        if (!$enrollment) {
            return ['success' => false, 'message' => 'Enrollment not found'];
        }

        $courseOfferingId = $enrollment['course_offering_id'];

        // Get grade components for this period
        $components = $this->gradeComponentModel
            ->where('course_offering_id', $courseOfferingId)
            ->where('grading_period_id', $gradingPeriodId)
            ->where('is_active', 1)
            ->findAll();

        if (empty($components)) {
            return ['success' => false, 'message' => 'No grade components configured'];
        }

        $periodGrade = 0.00;
        $totalWeight = 0.00;

        foreach ($components as $component) {
            $componentGrade = $this->calculateComponentGrade(
                $enrollmentId,
                $component['assignment_type_id'],
                $gradingPeriodId
            );

            $weightedGrade = $componentGrade * ($component['weight_percentage'] / 100);
            $periodGrade += $weightedGrade;
            $totalWeight += $component['weight_percentage'];
        }

        // Normalize if total weight is not 100%
        if ($totalWeight > 0 && abs($totalWeight - 100) > 0.01) {
            $periodGrade = ($periodGrade / $totalWeight) * 100;
        }

        // Get or create gradebook entry
        $entry = $this->gradebookEntryModel->getOrCreateEntry($enrollmentId, $gradingPeriodId);

        // Update calculated grade
        $this->gradebookEntryModel->updateCalculatedGrade($entry['id'], round($periodGrade, 2));

        return [
            'success' => true,
            'grade' => round($periodGrade, 2),
            'entry_id' => $entry['id']
        ];
    }

    /**
     * Calculate component grade (e.g., all quizzes, all assignments)
     * - Gets all assignments of this type for the period
     * - Sums scores and max_scores from submissions
     * - Returns percentage (0-100)
     */
    protected function calculateComponentGrade($enrollmentId, $assignmentTypeId, $gradingPeriodId)
    {
        $enrollment = $this->enrollmentModel->find($enrollmentId);
        $courseOfferingId = $enrollment['course_offering_id'];

        // Get all assignments for this component
        $assignments = $this->assignmentModel
            ->where('course_offering_id', $courseOfferingId)
            ->where('assignment_type_id', $assignmentTypeId)
            ->where('grading_period_id', $gradingPeriodId)
            ->where('is_active', 1)
            ->where('is_published', 1)
            ->findAll();

        if (empty($assignments)) {
            return 0.00;
        }

        $totalScore = 0;
        $totalMaxScore = 0;

        foreach ($assignments as $assignment) {
            // Get student's submission for this assignment
            $submission = $this->db->table('submissions')
                ->where('assignment_id', $assignment['id'])
                ->where('enrollment_id', $enrollmentId)
                ->where('status', 'graded')
                ->get()
                ->getRowArray();

            if ($submission && $submission['score'] !== null) {
                $totalScore += $submission['score'];
                $totalMaxScore += $assignment['max_score'];
            } else {
                // Missing submission counts as 0
                $totalMaxScore += $assignment['max_score'];
            }
        }

        if ($totalMaxScore == 0) {
            return 0.00;
        }

        return ($totalScore / $totalMaxScore) * 100;
    }

    /**
     * Calculate final course grade (weighted average of periods)
     * - Prelim 30%, Midterm 30%, Finals 40%
     * - Gets period grades from gradebook_entries
     * - Stores result with grading_period_id = NULL
     */
    public function calculateFinalGrade($enrollmentId)
    {
        $enrollment = $this->enrollmentModel->find($enrollmentId);
        if (!$enrollment) {
            return ['success' => false, 'message' => 'Enrollment not found'];
        }

        $courseOfferingId = $enrollment['course_offering_id'];

        // Get course offering details to find term
        $courseOffering = $this->db->table('course_offerings')
            ->where('id', $courseOfferingId)
            ->get()
            ->getRowArray();

        if (!$courseOffering) {
            return ['success' => false, 'message' => 'Course offering not found'];
        }

        // Get grading periods for this term
        $gradingPeriods = $this->gradingPeriodModel
            ->where('term_id', $courseOffering['term_id'])
            ->orderBy('period_order', 'ASC')
            ->findAll();

        if (empty($gradingPeriods)) {
            return ['success' => false, 'message' => 'No grading periods configured'];
        }

        $finalGrade = 0.00;
        $totalWeight = 0.00;

        foreach ($gradingPeriods as $period) {
            // Get period grade from gradebook
            $periodEntry = $this->gradebookEntryModel
                ->where('enrollment_id', $enrollmentId)
                ->where('grading_period_id', $period['id'])
                ->first();

            if ($periodEntry && $periodEntry['final_grade'] !== null) {
                $weightedGrade = $periodEntry['final_grade'] * ($period['weight_percentage'] / 100);
                $finalGrade += $weightedGrade;
                $totalWeight += $period['weight_percentage'];
            }
        }

        // Normalize if needed
        if ($totalWeight > 0 && abs($totalWeight - 100) > 0.01) {
            $finalGrade = ($finalGrade / $totalWeight) * 100;
        }

        // Get or create final grade entry (grading_period_id = NULL)
        $finalEntry = $this->gradebookEntryModel->getOrCreateEntry($enrollmentId, null);

        // Update final grade
        $this->gradebookEntryModel->updateCalculatedGrade($finalEntry['id'], round($finalGrade, 2));

        return [
            'success' => true,
            'grade' => round($finalGrade, 2),
            'entry_id' => $finalEntry['id']
        ];
    }

    /**
     * Recalculate all grades for an enrollment
     * - Recalculates each period grade
     * - Then recalculates final grade
     */
    public function recalculateEnrollmentGrades($enrollmentId)
    {
        $enrollment = $this->enrollmentModel->find($enrollmentId);
        if (!$enrollment) {
            return ['success' => false, 'message' => 'Enrollment not found'];
        }

        $courseOfferingId = $enrollment['course_offering_id'];

        // Get course offering to find term
        $courseOffering = $this->db->table('course_offerings')
            ->where('id', $courseOfferingId)
            ->get()
            ->getRowArray();

        // Get grading periods
        $gradingPeriods = $this->gradingPeriodModel
            ->where('term_id', $courseOffering['term_id'])
            ->findAll();

        // Recalculate each period
        foreach ($gradingPeriods as $period) {
            $this->calculatePeriodGrade($enrollmentId, $period['id']);
        }

        // Recalculate final grade
        $result = $this->calculateFinalGrade($enrollmentId);

        return $result;
    }

    /**
     * Recalculate grades for all students in a course offering
     */
    public function recalculateCourseGrades($courseOfferingId)
    {
        $enrollments = $this->enrollmentModel
            ->where('course_offering_id', $courseOfferingId)
            ->where('enrollment_status', 'enrolled')
            ->findAll();

        $results = [];
        foreach ($enrollments as $enrollment) {
            $results[] = $this->recalculateEnrollmentGrades($enrollment['id']);
        }

        return [
            'success' => true,
            'message' => 'Recalculated grades for ' . count($enrollments) . ' students',
            'results' => $results
        ];
    }

    /**
     * Get grade breakdown for display
     * - Separates period grades from final grade
     * - Returns structured array for views
     */
    public function getGradeBreakdown($enrollmentId)
    {
        $grades = $this->gradebookEntryModel->getStudentCourseGrades($enrollmentId);
        
        $breakdown = [
            'periods' => [],
            'final' => null
        ];

        foreach ($grades as $grade) {
            if ($grade['grading_period_id'] === null) {
                $breakdown['final'] = $grade;
            } else {
                $breakdown['periods'][] = $grade;
            }
        }

        return $breakdown;
    }
}
