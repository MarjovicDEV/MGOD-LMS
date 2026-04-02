<?php

namespace App\Models;

use CodeIgniter\Model;

class GradeHistoryModel extends Model
{
    protected $table            = 'grade_history';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields = [
        'gradebook_entry_id',
        'old_grade',
        'new_grade',
        'old_status',
        'new_status',
        'change_type',
        'changed_by',
        'change_reason',
        'changed_at'
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'gradebook_entry_id' => 'required|integer',
        'change_type'        => 'required|in_list[calculated,override,status_change]',
        'changed_by'         => 'required|integer',
        'changed_at'         => 'required|valid_date',
    ];

    /**
     * Log a grade change
     */
    public function logChange($entryId, $oldGrade, $newGrade, $changeType, $userId, $reason = null)
    {
        return $this->insert([
            'gradebook_entry_id' => $entryId,
            'old_grade'          => $oldGrade,
            'new_grade'          => $newGrade,
            'change_type'        => $changeType,
            'changed_by'         => $userId,
            'change_reason'      => $reason,
            'changed_at'         => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get history for a specific gradebook entry
     */
    public function getEntryHistory($entryId)
    {
        return $this->select('
                grade_history.*,
                u.first_name,
                u.last_name,
                u.user_code
            ')
            ->join('users u', 'u.id = grade_history.changed_by')
            ->where('grade_history.gradebook_entry_id', $entryId)
            ->orderBy('grade_history.changed_at', 'DESC')
            ->findAll();
    }

    /**
     * Get audit trail with filters for admin view
     */
    public function getAuditTrail($filters = [])
    {
        $builder = $this->select('
                grade_history.*,
                ge.enrollment_id,
                ge.grading_period_id,
                e.student_id,
                st.first_name as student_first_name,
                st.last_name as student_last_name,
                st.user_code as student_code,
                c.course_code,
                c.title as course_title,
                co.section,
                gp.period_name,
                u.first_name as changer_first_name,
                u.last_name as changer_last_name,
                u.user_code as changer_code
            ')
            ->join('gradebook_entries ge', 'ge.id = grade_history.gradebook_entry_id')
            ->join('enrollments e', 'e.id = ge.enrollment_id')
            ->join('students s', 's.id = e.student_id')
            ->join('users st', 'st.id = s.user_id')
            ->join('course_offerings co', 'co.id = e.course_offering_id')
            ->join('courses c', 'c.id = co.course_id')
            ->join('grading_periods gp', 'gp.id = ge.grading_period_id', 'left')
            ->join('users u', 'u.id = grade_history.changed_by');

        // Apply filters
        if (!empty($filters['course_offering_id'])) {
            $builder->where('e.course_offering_id', $filters['course_offering_id']);
        }

        if (!empty($filters['student_id'])) {
            $builder->where('e.student_id', $filters['student_id']);
        }

        if (!empty($filters['changed_by'])) {
            $builder->where('grade_history.changed_by', $filters['changed_by']);
        }

        if (!empty($filters['change_type'])) {
            $builder->where('grade_history.change_type', $filters['change_type']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('grade_history.changed_at >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('grade_history.changed_at <=', $filters['date_to']);
        }

        return $builder->orderBy('grade_history.changed_at', 'DESC')
                       ->findAll();
    }

    /**
     * Get recent changes for dashboard
     */
    public function getRecentChanges($limit = 10, $userId = null)
    {
        $builder = $this->select('
                grade_history.*,
                ge.enrollment_id,
                st.first_name,
                st.last_name,
                c.course_code
            ')
            ->join('gradebook_entries ge', 'ge.id = grade_history.gradebook_entry_id')
            ->join('enrollments e', 'e.id = ge.enrollment_id')
            ->join('students s', 's.id = e.student_id')
            ->join('users st', 'st.id = s.user_id')
            ->join('course_offerings co', 'co.id = e.course_offering_id')
            ->join('courses c', 'c.id = co.course_id');

        if ($userId) {
            $builder->where('grade_history.changed_by', $userId);
        }

        return $builder->orderBy('grade_history.changed_at', 'DESC')
                       ->limit($limit)
                       ->findAll();
    }

    /**
     * Get statistics for a date range
     */
    public function getChangeStatistics($dateFrom, $dateTo)
    {
        $db = \Config\Database::connect();
        
        $query = $db->table($this->table)
            ->select('
                change_type,
                COUNT(*) as count,
                AVG(new_grade - old_grade) as avg_change
            ')
            ->where('changed_at >=', $dateFrom)
            ->where('changed_at <=', $dateTo)
            ->groupBy('change_type')
            ->get();

        return $query->getResultArray();
    }
}
