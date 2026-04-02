<?php

namespace App\Models;

use CodeIgniter\Model;

class GradebookEntryModel extends Model
{
    protected $table            = 'gradebook_entries';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields = [
        'enrollment_id',
        'grading_period_id',
        'calculated_grade',
        'final_grade',
        'grade_status',
        'is_overridden',
        'override_reason',
        'overridden_by',
        'overridden_at'
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
        'enrollment_id'     => 'required|integer',
        'grading_period_id' => 'permit_empty|integer',
        'grade_status'      => 'required|in_list[calculated,incomplete,dropped,withdrawn,no_grade]',
    ];

    protected $validationMessages = [
        'enrollment_id' => [
            'required' => 'Enrollment is required'
        ],
        'grade_status' => [
            'in_list' => 'Invalid grade status'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $afterUpdate    = ['logGradeChange'];

    /**
     * Get or create a gradebook entry for an enrollment and grading period
     * 
     * @param int $enrollmentId
     * @param int|null $gradingPeriodId NULL for final course grade
     * @return array|null
     */
    public function getOrCreateEntry($enrollmentId, $gradingPeriodId = null)
    {
        // Try to find existing entry
        $builder = $this->where('enrollment_id', $enrollmentId);
        
        if ($gradingPeriodId === null) {
            $builder->where('grading_period_id IS NULL');
        } else {
            $builder->where('grading_period_id', $gradingPeriodId);
        }
        
        $entry = $builder->first();
        
        if ($entry) {
            return $entry;
        }
        
        // Create new entry if not found
        $data = [
            'enrollment_id'     => $enrollmentId,
            'grading_period_id' => $gradingPeriodId,
            'grade_status'      => 'calculated',
        ];
        
        $entryId = $this->insert($data);
        
        return $entryId ? $this->find($entryId) : null;
    }

    /**
     * Get all period grades for a student's enrollment
     * 
     * @param int $enrollmentId
     * @return array
     */
    public function getStudentCourseGrades($enrollmentId)
    {
        return $this->select('
                gradebook_entries.*,
                grading_periods.period_name,
                grading_periods.period_order,
                grading_periods.weight_percentage as period_weight
            ')
            ->join('grading_periods', 'grading_periods.id = gradebook_entries.grading_period_id', 'left')
            ->where('gradebook_entries.enrollment_id', $enrollmentId)
            ->orderBy('grading_periods.period_order', 'ASC')
            ->findAll();
    }

    /**
     * Get the final course grade entry (where grading_period_id is NULL)
     * 
     * @param int $enrollmentId
     * @return array|null
     */
    public function getFinalGrade($enrollmentId)
    {
        return $this->where('enrollment_id', $enrollmentId)
                    ->where('grading_period_id IS NULL')
                    ->first();
    }

    /**
     * Save manual grade override by teacher/admin
     * 
     * @param int $entryId
     * @param float $newGrade
     * @param string $reason
     * @param int $userId
     * @return bool
     */
    public function saveOverride($entryId, $newGrade, $reason, $userId)
    {
        $data = [
            'final_grade'     => $newGrade,
            'is_overridden'   => true,
            'override_reason' => $reason,
            'overridden_by'   => $userId,
            'overridden_at'   => date('Y-m-d H:i:s'),
        ];
        
        return $this->update($entryId, $data);
    }

    /**
     * Update calculated grade
     * If not overridden, also updates final_grade
     * 
     * @param int $entryId
     * @param float $calculatedGrade
     * @return bool
     */
    public function updateCalculatedGrade($entryId, $calculatedGrade)
    {
        $entry = $this->find($entryId);
        
        if (!$entry) {
            return false;
        }
        
        $data = [
            'calculated_grade' => $calculatedGrade,
        ];
        
        // If not overridden, update final_grade as well
        if (!$entry['is_overridden']) {
            $data['final_grade'] = $calculatedGrade;
        }
        
        return $this->update($entryId, $data);
    }

    /**
     * Get all overridden grades with student/course info
     * 
     * @param int|null $courseOfferingId Filter by course offering
     * @return array
     */
    public function getOverriddenGrades($courseOfferingId = null)
    {
        $builder = $this->select('
                gradebook_entries.*,
                grading_periods.period_name,
                enrollments.enrollment_status,
                students.student_id_number,
                CONCAT(users.first_name, " ", COALESCE(users.middle_name, ""), " ", users.last_name) as student_name,
                users.email as student_email,
                courses.course_code,
                courses.title as course_title,
                course_offerings.section,
                CONCAT(overrider.first_name, " ", COALESCE(overrider.middle_name, ""), " ", overrider.last_name) as overridden_by_name
            ')
            ->join('enrollments', 'enrollments.id = gradebook_entries.enrollment_id')
            ->join('students', 'students.id = enrollments.student_id')
            ->join('users', 'users.id = students.user_id')
            ->join('course_offerings', 'course_offerings.id = enrollments.course_offering_id')
            ->join('courses', 'courses.id = course_offerings.course_id')
            ->join('grading_periods', 'grading_periods.id = gradebook_entries.grading_period_id', 'left')
            ->join('users as overrider', 'overrider.id = gradebook_entries.overridden_by', 'left')
            ->where('gradebook_entries.is_overridden', true);
        
        if ($courseOfferingId !== null) {
            $builder->where('course_offerings.id', $courseOfferingId);
        }
        
        return $builder->orderBy('gradebook_entries.overridden_at', 'DESC')->findAll();
    }

    /**
     * Callback: Log grade changes to grade_history table
     * Triggered on afterUpdate
     * 
     * @param array $data
     * @return array
     */
    protected function logGradeChange(array $data)
    {
        // Only log if we have an ID (update operation)
        if (!isset($data['id']) || empty($data['id'])) {
            return $data;
        }
        
        $entryId = is_array($data['id']) ? $data['id'][0] : $data['id'];
        
        // Get the old entry data (before update)
        $oldEntry = $this->find($entryId);
        
        if (!$oldEntry) {
            return $data;
        }
        
        // Check if final_grade or grade_status changed
        $gradeChanged = false;
        $statusChanged = false;
        $oldGrade = $oldEntry['final_grade'];
        $newGrade = $oldGrade;
        $oldStatus = $oldEntry['grade_status'];
        $newStatus = $oldStatus;
        
        if (isset($data['data']['final_grade']) && $data['data']['final_grade'] != $oldGrade) {
            $gradeChanged = true;
            $newGrade = $data['data']['final_grade'];
        }
        
        if (isset($data['data']['grade_status']) && $data['data']['grade_status'] != $oldStatus) {
            $statusChanged = true;
            $newStatus = $data['data']['grade_status'];
        }
        
        // Only log if something actually changed
        if (!$gradeChanged && !$statusChanged) {
            return $data;
        }
        
        // Determine change type
        $changeType = 'calculated';
        $changeReason = null;
        $changedBy = null;
        
        if (isset($data['data']['is_overridden']) && $data['data']['is_overridden']) {
            $changeType = 'override';
            $changeReason = $data['data']['override_reason'] ?? null;
            $changedBy = $data['data']['overridden_by'] ?? null;
        } elseif ($statusChanged) {
            $changeType = 'status_change';
        }
        
        // Get current user ID if not provided (for calculated changes)
        if (!$changedBy) {
            $session = session();
            $changedBy = $session->get('user_id') ?? 1; // Default to system user if no session
        }
        
        // Insert into grade_history
        $db = \Config\Database::connect();
        $historyData = [
            'gradebook_entry_id' => $entryId,
            'old_grade'          => $oldGrade,
            'new_grade'          => $newGrade,
            'old_status'         => $oldStatus,
            'new_status'         => $newStatus,
            'change_type'        => $changeType,
            'changed_by'         => $changedBy,
            'change_reason'      => $changeReason,
            'changed_at'         => date('Y-m-d H:i:s'),
        ];
        
        $db->table('grade_history')->insert($historyData);
        
        return $data;
    }
}
