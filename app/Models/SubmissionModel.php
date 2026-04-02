<?php

namespace App\Models;

use App\Libraries\GradeCalculator;
use CodeIgniter\Model;
use Throwable;

class SubmissionModel extends Model
{
    protected $table            = 'submissions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'assignment_id',
        'enrollment_id',
        'submission_text',
        'file_path',
        'submitted_at',
        'attempt_number',
        'is_late',
        'status',
        'score',
        'feedback',
        'graded_by',
        'graded_at'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'assignment_id'   => 'required|integer',
        'enrollment_id'   => 'required|integer',
        'submission_text' => 'permit_empty|string',
        'file_path'       => 'permit_empty|string|max_length[500]',
        'submitted_at'    => 'permit_empty|valid_date',
        'attempt_number'  => 'permit_empty|integer',
        'is_late'         => 'permit_empty|in_list[0,1]',
        'status'          => 'required|in_list[draft,submitted,graded,returned]'
    ];

    protected $validationMessages = [
        'assignment_id' => [
            'required' => 'Assignment is required'
        ],
        'enrollment_id' => [
            'required' => 'Enrollment is required'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setSubmittedDate'];
    protected $afterUpdate    = ['triggerGradebookRecalculation'];

    /**
     * Set submitted_at when status is submitted
     */
    protected function setSubmittedDate(array $data)
    {
        if (isset($data['data']['status']) && $data['data']['status'] === 'submitted') {
            if (!isset($data['data']['submitted_at']) || empty($data['data']['submitted_at'])) {
                $data['data']['submitted_at'] = date('Y-m-d H:i:s');
            }
        }
        return $data;
    }

    /**
     * Trigger gradebook recalculation after grading updates.
     */
    protected function triggerGradebookRecalculation(array $data)
    {
        try {
            $id = $data['id'][0] ?? $data['id'] ?? null;
            if (!$id) {
                log_message(
                    'warning',
                    'SubmissionModel grade recalculation skipped: missing submission id in callback payload'
                );
                return $data;
            }

            $submission = $this->find($id);
            if (!$submission) {
                log_message(
                    'warning',
                    'SubmissionModel grade recalculation skipped: submission {submissionId} not found after update',
                    ['submissionId' => $id]
                );
                return $data;
            }

            if (empty($submission['enrollment_id'])) {
                log_message(
                    'warning',
                    'SubmissionModel grade recalculation skipped: submission {submissionId} has no enrollment_id',
                    ['submissionId' => $id]
                );
                return $data;
            }

            $updatedData = $data['data'] ?? [];
            $gradingFields = ['score', 'status', 'graded_at', 'graded_by'];
            $hasGradingUpdate = !empty(array_intersect($gradingFields, array_keys($updatedData)));

            if (!$hasGradingUpdate) {
                return $data;
            }

            (new GradeCalculator())->recalculateEnrollmentGrades((int) $submission['enrollment_id']);
        } catch (Throwable $e) {
            log_message(
                'error',
                'SubmissionModel grade recalculation failed for submission {submissionId}: {error}',
                [
                    'submissionId' => $data['id'][0] ?? $data['id'] ?? 'unknown',
                    'error'        => $e->getMessage(),
                ]
            );
        }

        return $data;
    }

    /**
     * Get submission with full details
     */
    public function getSubmissionWithDetails($submissionId)
    {
        return $this->select('
                submissions.*,
                a.title as assignment_title,
                a.max_score,
                a.due_date,
                CONCAT(u.first_name, " ", COALESCE(u.middle_name, ""), " ", u.last_name) as student_name,
                u.user_code as student_code,
                CONCAT(g.first_name, " ", COALESCE(g.middle_name, ""), " ", g.last_name) as grader_name,
                c.course_code,
                c.title as course_title
            ')
            ->join('assignments a', 'a.id = submissions.assignment_id')
            ->join('enrollments e', 'e.id = submissions.enrollment_id')
            ->join('students s', 's.id = e.student_id')
            ->join('users u', 'u.id = s.user_id')
            ->join('users g', 'g.id = submissions.graded_by', 'left')
            ->join('course_offerings co', 'co.id = a.course_offering_id')
            ->join('courses c', 'c.id = co.course_id')
            ->find($submissionId);
    }

    /**
     * Get student's submission for an assignment
     */
    public function getStudentSubmission($assignmentId, $studentId)
    {
        return $this->select('submissions.*')
                    ->join('enrollments e', 'e.id = submissions.enrollment_id')
                    ->where('submissions.assignment_id', $assignmentId)
                    ->where('e.student_id', $studentId)
                    ->orderBy('submitted_at', 'DESC')
                    ->first();
    }

    /**
     * Get all submissions for an assignment
     */
    public function getAssignmentSubmissions($assignmentId)
    {
        return $this->select('
                submissions.*,
                CONCAT(u.first_name, " ", COALESCE(u.middle_name, ""), " ", u.last_name) as student_name,
                u.user_code as student_code
            ')
            ->join('enrollments e', 'e.id = submissions.enrollment_id')
            ->join('students s', 's.id = e.student_id')
            ->join('users u', 'u.id = s.user_id')
            ->where('submissions.assignment_id', $assignmentId)
            ->orderBy('submissions.submitted_at', 'DESC')
            ->findAll();
    }

    /**
     * Get pending submissions (need grading)
     */
    public function getPendingSubmissions($instructorId = null)
    {
        $builder = $this->select('
                submissions.*,
                a.title as assignment_title,
                a.max_score,
                CONCAT(u.first_name, " ", COALESCE(u.middle_name, ""), " ", u.last_name) as student_name,
                c.course_code,
                c.title as course_title,
                co.section
            ')
            ->join('assignments a', 'a.id = submissions.assignment_id')
            ->join('enrollments e', 'e.id = submissions.enrollment_id')
            ->join('students s', 's.id = e.student_id')
            ->join('users u', 'u.id = s.user_id')
            ->join('course_offerings co', 'co.id = a.course_offering_id')
            ->join('courses c', 'c.id = co.course_id')
            ->where('submissions.status', 'submitted');
        
        if ($instructorId) {
            $builder->join('course_instructors ci', 'ci.course_offering_id = co.id')
                   ->where('ci.instructor_id', $instructorId);
        }
        
        return $builder->orderBy('submissions.submitted_at', 'ASC')
                       ->findAll();
    }

    /**
     * Grade a submission
     */
    public function gradeSubmission($submissionId, $score, $feedback, $graderId)
    {
        return $this->update($submissionId, [
            'score'      => $score,
            'feedback'   => $feedback,
            'graded_by'  => $graderId,
            'graded_at'  => date('Y-m-d H:i:s'),
            'status'     => 'graded'
        ]);
    }

    /**
     * Get student's submissions for a course
     */
    public function getStudentCourseSubmissions($studentId, $courseOfferingId)
    {
        return $this->select('
                submissions.*,
                a.title as assignment_title,
                a.max_score,
                a.due_date,
                at.type_name
            ')
            ->join('assignments a', 'a.id = submissions.assignment_id')
            ->join('assignment_types at', 'at.id = a.assignment_type_id', 'left')
            ->join('enrollments e', 'e.id = submissions.enrollment_id')
            ->where('e.student_id', $studentId)
            ->where('a.course_offering_id', $courseOfferingId)
            ->orderBy('a.due_date', 'DESC')
            ->findAll();
    }

    /**
     * Check if submission is late
     */
    public function isLate($submissionId)
    {
        $submission = $this->select('
                submissions.submitted_at,
                a.due_date
            ')
            ->join('assignments a', 'a.id = submissions.assignment_id')
            ->find($submissionId);
        
        if (!$submission || !$submission['submitted_at']) {
            return false;
        }
        
        return $submission['submitted_at'] > $submission['due_date'];
    }

    /**
     * Get submission statistics for a student
     */
    public function getStudentStats($studentId, $courseOfferingId = null)
    {
        $builder = $this->select('
                COUNT(*) as total_submissions,
                SUM(CASE WHEN status = "graded" THEN 1 ELSE 0 END) as graded_count,
                SUM(CASE WHEN status = "submitted" THEN 1 ELSE 0 END) as pending_count,
                AVG(score) as average_score
            ')
            ->join('assignments a', 'a.id = submissions.assignment_id')
            ->join('enrollments e', 'e.id = submissions.enrollment_id')
            ->where('e.student_id', $studentId);
        
        if ($courseOfferingId) {
            $builder->where('a.course_offering_id', $courseOfferingId);
        }
        
        return $builder->get()->getRow();
    }

    /**
     * Get late submissions
     */
    public function getLateSubmissions($assignmentId)
    {
        return $this->select('
                submissions.*,
                CONCAT(u.first_name, " ", COALESCE(u.middle_name, ""), " ", u.last_name) as student_name,
                a.due_date
            ')
            ->join('assignments a', 'a.id = submissions.assignment_id')
            ->join('enrollments e', 'e.id = submissions.enrollment_id')
            ->join('students s', 's.id = e.student_id')
            ->join('users u', 'u.id = s.user_id')
            ->where('submissions.assignment_id', $assignmentId)
            ->where('submissions.submitted_at > a.due_date')
            ->findAll();
    }

    /**
     * Submit assignment
     */
    public function submitAssignment($assignmentId, $studentId, $data)
    {
        $assignment = $this->db->table('assignments')
            ->select('course_offering_id')
            ->where('id', $assignmentId)
            ->get()
            ->getRowArray();

        if (!$assignment || empty($assignment['course_offering_id'])) {
            log_message(
                'warning',
                'SubmissionModel submitAssignment skipped: assignment {assignmentId} not found or has no course offering',
                ['assignmentId' => $assignmentId]
            );
            return false;
        }

        $enrollment = $this->db->table('enrollments')
            ->select('id')
            ->where('student_id', $studentId)
            ->where('course_offering_id', $assignment['course_offering_id'])
            ->where('enrollment_status', 'enrolled')
            ->get()
            ->getRowArray();

        if (!$enrollment || empty($enrollment['id'])) {
            log_message(
                'warning',
                'SubmissionModel submitAssignment skipped: no enrollment for student {studentId} in assignment {assignmentId}',
                ['studentId' => $studentId, 'assignmentId' => $assignmentId]
            );
            return false;
        }

        $existing = $this->where('assignment_id', $assignmentId)
            ->where('enrollment_id', $enrollment['id'])
            ->orderBy('submitted_at', 'DESC')
            ->first();
        
        $submissionData = [
            'assignment_id'   => $assignmentId,
            'enrollment_id'   => $enrollment['id'],
            'submission_text' => $data['submission_text'] ?? null,
            'file_path'       => $data['file_path'] ?? null,
            'submitted_at'    => date('Y-m-d H:i:s'),
            'status'          => 'submitted'
        ];
        
        if ($existing) {
            return $this->update($existing['id'], $submissionData);
        }
        
        return $this->insert($submissionData);
    }
}
