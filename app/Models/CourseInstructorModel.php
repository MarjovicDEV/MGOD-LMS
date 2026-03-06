<?php

namespace App\Models;

use CodeIgniter\Model;

class CourseInstructorModel extends Model
{
    protected $table            = 'course_instructors';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'course_offering_id',
        'instructor_id',
        'is_primary',
        'assigned_date'
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
        'course_offering_id' => 'required|integer',
        'instructor_id'      => 'required|integer',
        'is_primary'         => 'permit_empty|in_list[0,1]',
        'assigned_date'      => 'permit_empty|valid_date'
    ];

    protected $validationMessages = [
        'course_offering_id' => [
            'required' => 'Course offering is required'
        ],
        'instructor_id' => [
            'required' => 'Instructor is required'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;    /**
     * Get all instructors for a course offering
     * Joins with instructors and users tables to get complete instructor information
     */
    public function getOfferingInstructors($offeringId)
    {
        return $this->select('
                course_instructors.*,
                instructors.employee_id,
                instructors.specialization,
                instructors.employment_status,
                users.id as user_id,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.email,
                departments.department_name
            ')
            ->join('instructors', 'instructors.id = course_instructors.instructor_id')
            ->join('users', 'users.id = instructors.user_id')
            ->join('departments', 'departments.id = instructors.department_id', 'left')
            ->where('course_instructors.course_offering_id', $offeringId)
            ->orderBy('course_instructors.is_primary', 'DESC')
            ->findAll();
    }    /**
     * Get primary instructor for an offering
     * Returns the primary instructor with complete user information
     */
    public function getPrimaryInstructor($offeringId)
    {
        return $this->select('
                course_instructors.*,
                instructors.employee_id,
                instructors.specialization,
                users.id as user_id,
                users.first_name,
                users.middle_name,
                users.last_name,
                users.email
            ')
            ->join('instructors', 'instructors.id = course_instructors.instructor_id')
            ->join('users', 'users.id = instructors.user_id')
            ->where('course_instructors.course_offering_id', $offeringId)
            ->where('course_instructors.is_primary', 1)
            ->first();
    }

    /**
     * Get all offerings for an instructor
     */
    public function getInstructorOfferings($instructorId, $termId = null)
    {
        $builder = $this->select('
                course_instructors.*,
                co.section,
                co.status,
                co.current_enrollment,
                co.max_students,
                co.term_id,
                c.course_code,
                c.title as course_title,
                t.term_name
            ')
            ->join('course_offerings co', 'co.id = course_instructors.course_offering_id')
            ->join('courses c', 'c.id = co.course_id')
            ->join('terms t', 't.id = co.term_id')
            ->where('course_instructors.instructor_id', $instructorId);
        
        if ($termId) {
            $builder->where('co.term_id', $termId);
        }
        
        return $builder->orderBy('course_instructors.is_primary', 'DESC')
                       ->findAll();
    }

    /**
     * Assign instructor to offering
     */
    public function assignInstructor($offeringId, $instructorId, $isPrimary = false)
    {
        // Convert boolean to integer for database
        $isPrimaryValue = $isPrimary ? 1 : 0;
        
        // Check if already assigned
        $exists = $this->where('course_offering_id', $offeringId)
                       ->where('instructor_id', $instructorId)
                       ->first();
        
        if ($exists) {
            // Update existing assignment
            return $this->update($exists['id'], [
                'is_primary'    => $isPrimaryValue,
                'assigned_date' => date('Y-m-d')
            ]);
        }
        
        // Get database instance
        $db = \Config\Database::connect();
        
        // Start transaction for atomic operation
        $db->transStart();
        
        // If setting as primary, remove primary flag from others first
        if ($isPrimary) {
            $db->table('course_instructors')
               ->where('course_offering_id', $offeringId)
               ->update(['is_primary' => 0]);
        }
        
        // Insert new assignment
        $result = $this->insert([
            'course_offering_id' => $offeringId,
            'instructor_id'      => $instructorId,
            'is_primary'         => $isPrimaryValue,
            'assigned_date'      => date('Y-m-d')
        ]);
        
        $db->transComplete();
        
        // Return true if transaction was successful
        return $db->transStatus() !== false && $result !== false;
    }

    /**
     * Remove instructor from offering
     */
    public function removeInstructor($offeringId, $instructorId)
    {
        return $this->where('course_offering_id', $offeringId)
                    ->where('instructor_id', $instructorId)
                    ->delete();
    }

    /**
     * Set instructor as primary
     */
    public function setPrimary($offeringId, $instructorId)
    {
        $this->db->transStart();
        
        // Remove primary flag from all instructors in this offering
        $this->where('course_offering_id', $offeringId)
             ->set('is_primary', 0)
             ->update();
        
        // Set the specified instructor as primary
        $this->where('course_offering_id', $offeringId)
             ->where('instructor_id', $instructorId)
             ->set('is_primary', 1)
             ->update();
        
        $this->db->transComplete();
        
        return $this->db->transStatus();
    }

    /**
     * Check if instructor is assigned to offering
     */
    public function isAssigned($offeringId, $instructorId)
    {
        return $this->where('course_offering_id', $offeringId)
                    ->where('instructor_id', $instructorId)
                    ->countAllResults() > 0;
    }

    /**
     * Get instructor's workload (count of offerings)
     */
    public function getInstructorWorkload($instructorId, $termId)
    {
        return $this->join('course_offerings co', 'co.id = course_instructors.course_offering_id')
                    ->where('course_instructors.instructor_id', $instructorId)
                    ->where('co.term_id', $termId)
                    ->countAllResults();
    }

    /**
     * Get instructor full name from user data
     * @param int $instructorId - The instructor ID (from instructors table)
     * @return string|null
     */
    public function getInstructorName($instructorId)
    {
        $result = $this->db->table('instructors')
            ->select('users.first_name, users.middle_name, users.last_name, users.suffix')
            ->join('users', 'users.id = instructors.user_id')
            ->where('instructors.id', $instructorId)
            ->get()
            ->getRowArray();

        if (!$result) {
            return null;
        }

        $name = trim($result['first_name'] . ' ' . ($result['middle_name'] ?? '') . ' ' . $result['last_name']);
        if (!empty($result['suffix'])) {
            $name .= ' ' . $result['suffix'];
        }

        return $name;
    }

    /**
     * Get instructor ID from user ID
     * @param int $userId - The user ID
     * @return int|null - The instructor ID or null if not found
     */
    public function getInstructorIdByUserId($userId)
    {
        $result = $this->db->table('instructors')
            ->select('id')
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();

        return $result ? $result['id'] : null;
    }

    /**
     * Get all course offerings for a user (by user_id)
     * Converts user_id to instructor_id and gets offerings
     * @param int $userId - The user ID
     * @param int|null $termId - Optional term ID filter
     * @return array
     */
    public function getOfferingsByUserId($userId, $termId = null)
    {
        $instructorId = $this->getInstructorIdByUserId($userId);
        
        if (!$instructorId) {
            return [];
        }

        return $this->getInstructorOfferings($instructorId, $termId);
    }

    /**
     * Assign instructor by user ID
     * Converts user_id to instructor_id and assigns to offering
     * @param int $offeringId - The course offering ID
     * @param int $userId - The user ID
     * @param bool $isPrimary - Whether this is the primary instructor
     * @return bool|int
     */
    public function assignInstructorByUserId($offeringId, $userId, $isPrimary = false)
    {
        $instructorId = $this->getInstructorIdByUserId($userId);
        
        if (!$instructorId) {
            return false;
        }

        return $this->assignInstructor($offeringId, $instructorId, $isPrimary);
    }

    /**
     * Remove instructor by user ID
     * @param int $offeringId - The course offering ID
     * @param int $userId - The user ID
     * @return bool
     */
    public function removeInstructorByUserId($offeringId, $userId)
    {
        $instructorId = $this->getInstructorIdByUserId($userId);
        
        if (!$instructorId) {
            return false;
        }

        return $this->removeInstructor($offeringId, $instructorId);
    }

    /**
     * Check if a user is assigned to an offering (by user_id)
     * @param int $offeringId - The course offering ID
     * @param int $userId - The user ID
     * @return bool
     */
    public function isUserAssigned($offeringId, $userId)
    {
        $instructorId = $this->getInstructorIdByUserId($userId);
        
        if (!$instructorId) {
            return false;
        }

        return $this->isAssigned($offeringId, $instructorId);
    }

    /**
     * Get instructor workload by user ID
     * @param int $userId - The user ID
     * @param int $termId - The term ID
     * @return int
     */
    public function getUserWorkload($userId, $termId)
    {
        $instructorId = $this->getInstructorIdByUserId($userId);
        
        if (!$instructorId) {
            return 0;
        }

        return $this->getInstructorWorkload($instructorId, $termId);
    }
}