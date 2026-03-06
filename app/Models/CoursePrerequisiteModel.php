<?php

namespace App\Models;

use CodeIgniter\Model;

class CoursePrerequisiteModel extends Model
{
    protected $table            = 'course_prerequisites';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'course_id',
        'prerequisite_course_id',
        'prerequisite_type',
        'minimum_grade'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';    // Validation
    protected $validationRules = [
        'course_id'              => 'required|integer',
        'prerequisite_course_id' => 'required|integer|differs[course_id]',
        'prerequisite_type'      => 'required|in_list[required,recommended,corequisite]',
        'minimum_grade'          => 'permit_empty|integer|greater_than_equal_to[75]|less_than_equal_to[100]'
    ];

    protected $validationMessages = [
        'course_id' => [
            'required' => 'Course ID is required'
        ],
        'prerequisite_course_id' => [
            'required' => 'Prerequisite course is required',
            'differs'  => 'A course cannot be its own prerequisite'
        ],
        'minimum_grade' => [
            'greater_than_equal_to' => 'Minimum grade must be at least 75 (passing score)',
            'less_than_equal_to'    => 'Minimum grade cannot exceed 100'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;

    /**
     * Get prerequisites for a course with course details
     */
    public function getPrerequisitesWithDetails($courseId)
    {
        return $this->select('
                course_prerequisites.*,
                c.course_code,
                c.title,
                c.credits
            ')
            ->join('courses c', 'c.id = course_prerequisites.prerequisite_course_id')
            ->where('course_prerequisites.course_id', $courseId)
            ->findAll();
    }

    /**
     * Check if student has completed prerequisites for a course
     */
    public function checkStudentPrerequisites($studentId, $courseId)
    {
        $db = \Config\Database::connect();
        
        // Get all prerequisites for the course
        $prerequisites = $this->where('course_id', $courseId)
                              ->where('prerequisite_type', 'required')
                              ->findAll();
        
        if (empty($prerequisites)) {
            return ['met' => true, 'missing' => []];
        }
        
        $missing = [];
        
        foreach ($prerequisites as $prereq) {
            // Check if student has completed this prerequisite
            $completed = $db->table('enrollments e')
                           ->select('e.final_grade')
                           ->join('course_offerings co', 'co.id = e.course_offering_id')
                           ->where('e.student_id', $studentId)
                           ->where('co.course_id', $prereq['prerequisite_course_id'])
                           ->where('e.enrollment_status', 'completed')
                           ->get()
                           ->getRow();
            
            if (!$completed) {
                $missing[] = $prereq['prerequisite_course_id'];
            } elseif ($prereq['minimum_grade'] && $completed->final_grade < $prereq['minimum_grade']) {
                $missing[] = $prereq['prerequisite_course_id'];
            }
        }
        
        return [
            'met' => empty($missing),
            'missing' => $missing
        ];
    }

    /**
     * Add prerequisite
     */
    public function addPrerequisite($courseId, $prerequisiteCourseId, $type = 'required', $minGrade = null)
    {
        // Check if already exists
        $exists = $this->where('course_id', $courseId)
                       ->where('prerequisite_course_id', $prerequisiteCourseId)
                       ->first();
        
        if ($exists) {
            return false;
        }
        
        return $this->insert([
            'course_id'              => $courseId,
            'prerequisite_course_id' => $prerequisiteCourseId,
            'prerequisite_type'      => $type,
            'minimum_grade'          => $minGrade
        ]);
    }

    /**
     * Remove prerequisite
     */
    public function removePrerequisite($courseId, $prerequisiteCourseId)
    {
        return $this->where('course_id', $courseId)
                    ->where('prerequisite_course_id', $prerequisiteCourseId)
                    ->delete();
    }

    /**
     * Get all required prerequisites for a course
     */
    public function getRequiredPrerequisites($courseId)
    {
        return $this->where('course_id', $courseId)
                    ->where('prerequisite_type', 'required')
                    ->findAll();
    }

    /**
     * Get all recommended prerequisites for a course
     */
    public function getRecommendedPrerequisites($courseId)
    {
        return $this->where('course_id', $courseId)
                    ->where('prerequisite_type', 'recommended')
                    ->findAll();
    }
}