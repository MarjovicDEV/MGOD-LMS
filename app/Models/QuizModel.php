<?php

namespace App\Models;

use CodeIgniter\Model;

class QuizModel extends Model
{
    protected $table            = 'quizzes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'assignment_id',
        'time_limit',
        'attempts_allowed',
        'shuffle_questions',
        'show_results_immediately',
        'pass_percentage'
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
        'assignment_id'            => 'required|integer|is_unique[quizzes.assignment_id,id,{id}]',
        'time_limit'               => 'permit_empty|integer',
        'attempts_allowed'         => 'permit_empty|integer',
        'shuffle_questions'        => 'permit_empty|in_list[0,1]',
        'show_results_immediately' => 'permit_empty|in_list[0,1]',
        'pass_percentage'          => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]'
    ];

    protected $validationMessages = [
        'assignment_id' => [
            'required'  => 'Assignment is required',
            'is_unique' => 'This assignment already has quiz settings'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;

    /**
     * Get quiz with assignment details
     */
    public function getQuizWithDetails($quizId)
    {
        return $this->select('
                quizzes.*,
                a.title,
                a.description,
                a.max_score,
                a.due_date,
                a.course_offering_id,
                c.course_code,
                c.title as course_title
            ')
            ->join('assignments a', 'a.id = quizzes.assignment_id')
            ->join('course_offerings co', 'co.id = a.course_offering_id')
            ->join('courses c', 'c.id = co.course_id')
            ->find($quizId);
    }

    /**
     * Get quiz by assignment ID
     */
    public function getByAssignment($assignmentId)
    {
        return $this->where('assignment_id', $assignmentId)->first();
    }

    /**
     * Check student's remaining attempts
     */
    public function getRemainingAttempts($quizId, $studentId)
    {
        $quiz = $this->find($quizId);
        if (!$quiz || !$quiz['attempts_allowed']) {
            return ['unlimited' => true];
        }
        
        $db = \Config\Database::connect();
        
        $attemptCount = $db->table('submissions')
                          ->join('assignments', 'assignments.id = submissions.assignment_id')
                          ->join('quizzes', 'quizzes.assignment_id = assignments.id')
                          ->where('quizzes.id', $quizId)
                          ->where('submissions.student_id', $studentId)
                          ->countAllResults();
        
        $remaining = $quiz['attempts_allowed'] - $attemptCount;
        
        return [
            'unlimited'       => false,
            'allowed'         => $quiz['attempts_allowed'],
            'used'            => $attemptCount,
            'remaining'       => max(0, $remaining),
            'can_attempt'     => $remaining > 0
        ];
    }

    /**
     * Get student's quiz attempts
     */
    public function getStudentAttempts($quizId, $studentId)
    {
        $db = \Config\Database::connect();
        
        $quiz = $this->find($quizId);
        if (!$quiz) {
            return [];
        }
        
        return $db->table('submissions s')
                  ->select('
                      s.*,
                      CASE 
                          WHEN s.score >= (a.max_score * (q.pass_percentage / 100)) THEN "Passed"
                          ELSE "Failed"
                      END as result
                  ')
                  ->join('assignments a', 'a.id = s.assignment_id')
                  ->join('quizzes q', 'q.assignment_id = a.id')
                  ->where('q.id', $quizId)
                  ->where('s.student_id', $studentId)
                  ->orderBy('s.submitted_at', 'DESC')
                  ->get()
                  ->getResultArray();
    }

    /**
     * Get best attempt for a student
     */
    public function getBestAttempt($quizId, $studentId)
    {
        $db = \Config\Database::connect();
        
        $quiz = $this->find($quizId);
        if (!$quiz) {
            return null;
        }
        
        return $db->table('submissions s')
                  ->select('s.*')
                  ->join('assignments a', 'a.id = s.assignment_id')
                  ->join('quizzes q', 'q.assignment_id = a.id')
                  ->where('q.id', $quizId)
                  ->where('s.student_id', $studentId)
                  ->where('s.score IS NOT NULL')
                  ->orderBy('s.score', 'DESC')
                  ->limit(1)
                  ->get()
                  ->getRow();
    }

    /**
     * Check if student passed the quiz
     */
    public function hasPassed($quizId, $studentId)
    {
        $quiz = $this->find($quizId);
        if (!$quiz || !$quiz['pass_percentage']) {
            return null; // No pass/fail criteria
        }
        
        $bestAttempt = $this->getBestAttempt($quizId, $studentId);
        if (!$bestAttempt) {
            return false;
        }
        
        $db = \Config\Database::connect();
        $assignment = $db->table('assignments')
                        ->where('id', $quiz['assignment_id'])
                        ->get()
                        ->getRow();
        
        $requiredScore = $assignment->max_score * ($quiz['pass_percentage'] / 100);
        
        return $bestAttempt->score >= $requiredScore;
    }

    /**
     * Get quiz statistics
     */
    public function getQuizStats($quizId)
    {
        $db = \Config\Database::connect();
        
        $quiz = $this->getQuizWithDetails($quizId);
        if (!$quiz) {
            return null;
        }
        
        $stats = $db->table('submissions s')
                   ->select('
                       COUNT(DISTINCT s.student_id) as total_students,
                       COUNT(*) as total_attempts,
                       AVG(s.score) as average_score,
                       MAX(s.score) as highest_score,
                       MIN(s.score) as lowest_score
                   ')
                   ->where('s.assignment_id', $quiz['assignment_id'])
                   ->where('s.score IS NOT NULL')
                   ->get()
                   ->getRow();
        
        $passCount = 0;
        if ($quiz['pass_percentage']) {
            $requiredScore = $quiz['max_score'] * ($quiz['pass_percentage'] / 100);
            $passCount = $db->table('submissions')
                           ->where('assignment_id', $quiz['assignment_id'])
                           ->where('score >=', $requiredScore)
                           ->countAllResults();
        }
        
        return [
            'total_students'  => $stats->total_students ?? 0,
            'total_attempts'  => $stats->total_attempts ?? 0,
            'average_score'   => round($stats->average_score ?? 0, 2),
            'highest_score'   => $stats->highest_score ?? 0,
            'lowest_score'    => $stats->lowest_score ?? 0,
            'pass_count'      => $passCount,
            'pass_rate'       => ($stats->total_students ?? 0) > 0 
                                ? round(($passCount / $stats->total_students) * 100, 2) 
                                : 0
        ];
    }
}