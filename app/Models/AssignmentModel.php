<?php

namespace App\Models;

use CodeIgniter\Model;

class AssignmentModel extends Model
{
    protected $table            = 'assignments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'course_offering_id',
        'grade_component_id',
        'assignment_type_id',
        'grading_period_id',
        'title',
        'description',
        'instructions',
        'attachment_path',
        'submission_type',
        'max_score',
        'due_date',
        'available_from',
        'available_until',
        'allow_late_submission',
        'late_penalty_percentage',
        'is_published',
        'is_active'
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
        'course_offering_id'      => 'required|integer',
        'title'                   => 'required|string|max_length[255]',
        'description'             => 'permit_empty|string',
        'instructions'            => 'permit_empty|string',
        'max_score'               => 'required|decimal|greater_than[0]',
        'due_date'                => 'required|valid_date',
        'available_from'          => 'permit_empty|valid_date',
        'available_until'         => 'permit_empty|valid_date',
        'allow_late_submission'   => 'permit_empty|in_list[0,1]',
        'late_penalty_percentage' => 'permit_empty|decimal',
        'is_active'               => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'title' => [
            'required' => 'Assignment title is required'
        ],
        'max_score' => [
            'required'     => 'Maximum score is required',
            'greater_than' => 'Maximum score must be greater than 0'
        ],
        'due_date' => [
            'required' => 'Due date is required'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;

    /**
     * Get all assignments for a course offering
     */
    public function getOfferingAssignments($offeringId)
    {
        return $this->select('
                assignments.*,
                at.type_name,
                (SELECT COUNT(*) FROM submissions WHERE assignment_id = assignments.id) as submission_count
            ')
            ->join('assignment_types at', 'at.id = assignments.assignment_type_id', 'left')
            ->where('assignments.course_offering_id', $offeringId)
            ->where('assignments.is_active', 1)
            ->orderBy('assignments.due_date', 'ASC')
            ->findAll();
    }

    /**
     * Get assignment with full details
     */
    public function getAssignmentWithDetails($assignmentId)
    {
        return $this->select('
                assignments.id,
                assignments.course_offering_id,
                assignments.assignment_type_id,
                assignments.grading_period_id,
                assignments.title,
                assignments.description,
                assignments.instructions,
                assignments.attachment_path,
                assignments.submission_type,
                assignments.max_score,
                assignments.due_date,
                assignments.available_from,
                assignments.available_until,
                assignments.allow_late_submission,
                assignments.late_penalty_percentage,
                assignments.is_published,
                assignments.is_active,
                at.type_name,
                at.type_code,
                co.section,
                c.course_code,
                c.title as course_title
            ')
            ->join('assignment_types at', 'at.id = assignments.assignment_type_id', 'left')
            ->join('course_offerings co', 'co.id = assignments.course_offering_id')
            ->join('courses c', 'c.id = co.course_id')
            ->find($assignmentId);
    }

    /**
     * Get upcoming assignments for a student
     */
    public function getUpcomingAssignments($studentId, $limit = 5)
    {
        $db = \Config\Database::connect();
        
        return $db->table('assignments a')
                  ->select('
                      a.*,
                      c.course_code,
                      c.title as course_title,
                      co.section,
                      s.id as submission_id,
                      s.status as submission_status
                  ')
                  ->join('course_offerings co', 'co.id = a.course_offering_id')
                  ->join('courses c', 'c.id = co.course_id')
                  ->join('enrollments e', 'e.course_offering_id = co.id')
                  ->join('submissions s', 's.assignment_id = a.id AND s.student_id = e.student_id', 'left')
                  ->where('e.student_id', $studentId)
                  ->where('e.enrollment_status', 'enrolled')
                  ->where('a.is_active', 1)
                  ->where('a.due_date >=', date('Y-m-d'))
                  ->orderBy('a.due_date', 'ASC')
                  ->limit($limit)
                  ->get()
                  ->getResultArray();
    }

    /**
     * Get overdue assignments for a student
     */
    public function getOverdueAssignments($studentId)
    {
        $db = \Config\Database::connect();
        
        return $db->table('assignments a')
                  ->select('
                      a.*,
                      c.course_code,
                      c.title as course_title,
                      co.section
                  ')
                  ->join('course_offerings co', 'co.id = a.course_offering_id')
                  ->join('courses c', 'c.id = co.course_id')
                  ->join('enrollments e', 'e.course_offering_id = co.id')
                  ->where('e.student_id', $studentId)
                  ->where('e.enrollment_status', 'enrolled')
                  ->where('a.is_active', 1)
                  ->where('a.due_date <', date('Y-m-d H:i:s'))
                  ->where('a.id NOT IN (
                      SELECT assignment_id 
                      FROM submissions 
                      WHERE student_id = ' . $studentId . ' 
                      AND status IN ("submitted", "graded")
                  )')
                  ->orderBy('a.due_date', 'ASC')
                  ->get()
                  ->getResultArray();
    }

    /**
     * Check if assignment is available for submission
     */
    public function isAvailable($assignmentId)
    {
        $assignment = $this->find($assignmentId);
        if (!$assignment) {
            return false;
        }
        
        $now = date('Y-m-d H:i:s');
        
        // Check available_from
        if ($assignment['available_from'] && $now < $assignment['available_from']) {
            return false;
        }
        
        // Check available_until
        if ($assignment['available_until'] && $now > $assignment['available_until']) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if assignment is overdue
     */
    public function isOverdue($assignmentId)
    {
        $assignment = $this->find($assignmentId);
        if (!$assignment) {
            return false;
        }
        
        return date('Y-m-d H:i:s') > $assignment['due_date'];
    }

    /**
     * Calculate late penalty
     */
    public function calculateLatePenalty($assignmentId, $submissionDate)
    {
        $assignment = $this->find($assignmentId);
        if (!$assignment) {
            return 0;
        }
        
        if (!$assignment['allow_late_submission']) {
            return 100; // 100% penalty (zero score)
        }
        
        if ($submissionDate <= $assignment['due_date']) {
            return 0; // No penalty
        }
        
        return $assignment['late_penalty_percentage'] ?? 0;
    }

    /**
     * Get assignment statistics
     */
    public function getAssignmentStats($assignmentId)
    {
        $db = \Config\Database::connect();
        
        $stats = $db->table('submissions')
                   ->select('
                       COUNT(*) as total_submissions,
                       AVG(score) as average_score,
                       MAX(score) as highest_score,
                       MIN(score) as lowest_score,
                       SUM(CASE WHEN status = "graded" THEN 1 ELSE 0 END) as graded_count,
                       SUM(CASE WHEN status = "submitted" THEN 1 ELSE 0 END) as pending_count
                   ')
                   ->where('assignment_id', $assignmentId)
                   ->get()
                   ->getRow();
        
        $assignment = $this->find($assignmentId);
        $enrollmentCount = $db->table('enrollments')
                             ->where('course_offering_id', $assignment['course_offering_id'])
                             ->where('enrollment_status', 'enrolled')
                             ->countAllResults();
        
        return [
            'total_students'      => $enrollmentCount,
            'total_submissions'   => $stats->total_submissions ?? 0,
            'submission_rate'     => $enrollmentCount > 0 ? round(($stats->total_submissions / $enrollmentCount) * 100, 2) : 0,
            'average_score'       => round($stats->average_score ?? 0, 2),
            'highest_score'       => $stats->highest_score ?? 0,
            'lowest_score'        => $stats->lowest_score ?? 0,
            'graded_count'        => $stats->graded_count ?? 0,
            'pending_count'       => $stats->pending_count ?? 0,
            'missing_submissions' => $enrollmentCount - ($stats->total_submissions ?? 0)
        ];
    }

    /**
     * Get assignments by type
     */
    public function getAssignmentsByType($offeringId, $typeId)
    {
        return $this->where('course_offering_id', $offeringId)
                    ->where('assignment_type_id', $typeId)
                    ->where('is_active', 1)
                    ->orderBy('due_date', 'ASC')
                    ->findAll();
    }
}