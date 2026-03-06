<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramCurriculumModel extends Model
{
    protected $table            = 'program_curriculums';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'program_id',
        'course_id',
        'year_level_id',
        'semester_id',
        'course_type',
        'units',
        'is_active'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'program_id'    => 'required|integer',
        'course_id'     => 'required|integer',
        'year_level_id' => 'required|integer',
        'semester_id'   => 'required|integer',
        'course_type'   => 'required|in_list[major,minor,general_education]',
        'units'         => 'required|integer|greater_than[0]|less_than_equal_to[12]',
        'is_active'     => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'program_id' => [
            'required' => 'Program is required.',
            'integer'  => 'Please select a valid program.'
        ],
        'course_id' => [
            'required' => 'Course is required.',
            'integer'  => 'Please select a valid course.'
        ],
        'year_level_id' => [
            'required' => 'Year level is required.',
            'integer'  => 'Please select a valid year level.'
        ],
        'semester_id' => [
            'required' => 'Semester is required.',
            'integer'  => 'Please select a valid semester.'
        ],
        'course_type' => [
            'required' => 'Course type is required.',
            'in_list'  => 'Please select a valid course type.'
        ],
        'units' => [
            'required'             => 'Units is required.',
            'integer'              => 'Units must be a number.',
            'greater_than'         => 'Units must be greater than 0.',
            'less_than_equal_to'   => 'Units cannot exceed 12.'
        ]
    ];    /**
     * Get curriculum by program with all details
     */
    public function getCurriculumByProgram($programId, $yearLevelId = null, $semesterId = null)
    {
        $builder = $this->select('
                program_curriculums.*,
                courses.course_code,
                courses.title as course_title,
                courses.credits as course_credits,
                courses.lecture_hours,
                courses.lab_hours,
                year_levels.year_level_name as year_level_name,
                semesters.semester_name,
                programs.program_name
            ')
            ->join('courses', 'courses.id = program_curriculums.course_id')
            ->join('year_levels', 'year_levels.id = program_curriculums.year_level_id')
            ->join('semesters', 'semesters.id = program_curriculums.semester_id')
            ->join('programs', 'programs.id = program_curriculums.program_id')
            ->where('program_curriculums.program_id', $programId);
        
        if ($yearLevelId) {
            $builder->where('program_curriculums.year_level_id', $yearLevelId);
        }
        
        if ($semesterId) {
            $builder->where('program_curriculums.semester_id', $semesterId);
        }
        
        return $builder->orderBy('year_levels.id', 'ASC')
                       ->orderBy('semesters.id', 'ASC')
                       ->orderBy('courses.course_code', 'ASC')
                       ->findAll();
    }

    /**
     * Check if course already exists in program curriculum
     */
    public function isCourseInCurriculum($programId, $courseId, $yearLevelId, $semesterId, $excludeId = null)
    {
        $builder = $this->where('program_id', $programId)
                       ->where('course_id', $courseId)
                       ->where('year_level_id', $yearLevelId)
                       ->where('semester_id', $semesterId);
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        
        return $builder->countAllResults() > 0;
    }

    /**
     * Get curriculum grouped by year and semester
     */
    public function getCurriculumGrouped($programId)
    {
        $curriculum = $this->getCurriculumByProgram($programId);
        
        $grouped = [];
        foreach ($curriculum as $course) {
            $yearLevel = $course['year_level_name'];
            $semester = $course['semester_name'];
            
            if (!isset($grouped[$yearLevel])) {
                $grouped[$yearLevel] = [];
            }
            
            if (!isset($grouped[$yearLevel][$semester])) {
                $grouped[$yearLevel][$semester] = [];
            }
            
            $grouped[$yearLevel][$semester][] = $course;
        }
        
        return $grouped;
    }

    /**
     * Get total units for a program
     */
    public function getTotalUnits($programId, $yearLevelId = null, $semesterId = null)
    {
        $builder = $this->selectSum('units')
                       ->where('program_id', $programId)
                       ->where('is_active', 1);
        
        if ($yearLevelId) {
            $builder->where('year_level_id', $yearLevelId);
        }
        
        if ($semesterId) {
            $builder->where('semester_id', $semesterId);
        }
        
        $result = $builder->first();
        return $result['units'] ?? 0;
    }

    /**
     * Get course count by type
     */
    public function getCourseCountByType($programId, $courseType)
    {
        return $this->where('program_id', $programId)
                   ->where('course_type', $courseType)
                   ->where('is_active', 1)
                   ->countAllResults();
    }

    /**
     * Get curriculum statistics for a program
     */
    public function getCurriculumStatistics($programId)
    {
        $db = \Config\Database::connect();
        
        $stats = [
            'total_courses' => $this->where('program_id', $programId)->countAllResults(),
            'total_units'   => $this->getTotalUnits($programId),
            'major_courses' => $this->getCourseCountByType($programId, 'major'),
            'minor_courses' => $this->getCourseCountByType($programId, 'minor'),
            'ge_courses'    => $this->getCourseCountByType($programId, 'general_education'),
        ];
          // Get year-by-year breakdown
        $yearBreakdown = $db->query("
            SELECT 
                yl.year_level_name,
                yl.id as year_level_id,
                COUNT(pc.id) as course_count,
                SUM(pc.units) as total_units
            FROM year_levels yl
            LEFT JOIN program_curriculums pc ON pc.year_level_id = yl.id AND pc.program_id = ?
            GROUP BY yl.id, yl.year_level_name
            ORDER BY yl.id
        ", [$programId])->getResultArray();
        
        $stats['year_breakdown'] = $yearBreakdown;
        
        return $stats;
    }

    /**
     * Add course to program curriculum (with duplicate check)
     */
    public function addCourse($data)
    {
        // Check for duplicate
        if ($this->isCourseInCurriculum(
            $data['program_id'], 
            $data['course_id'], 
            $data['year_level_id'], 
            $data['semester_id']
        )) {
            return [
                'success' => false,
                'message' => 'This course is already in the curriculum for this year level and semester.'
            ];
        }
        
        if ($this->insert($data)) {
            return [
                'success' => true,
                'message' => 'Course added to curriculum successfully.'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to add course to curriculum.',
            'errors'  => $this->errors()
        ];
    }

    /**
     * Remove course from curriculum (with validation)
     */
    public function removeCourse($curriculumId)
    {
        $db = \Config\Database::connect();
        
        // Check if students are enrolled in this course
        $enrollmentCount = $db->query("
            SELECT COUNT(*) as count
            FROM enrollments e
            JOIN course_offerings co ON co.id = e.course_offering_id
            JOIN program_curriculums pc ON pc.course_id = co.course_id
            WHERE pc.id = ? AND e.enrollment_status = 'enrolled'
        ", [$curriculumId])->getRow()->count;
        
        if ($enrollmentCount > 0) {
            return [
                'success' => false,
                'message' => 'Cannot remove course from curriculum. Students are currently enrolled in this course.'
            ];
        }
        
        if ($this->delete($curriculumId)) {
            return [
                'success' => true,
                'message' => 'Course removed from curriculum successfully.'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to remove course from curriculum.'
        ];
    }

    /**
     * Get prerequisites for courses in curriculum
     */    public function getCurriculumWithPrerequisites($programId)
    {
        return $this->select('
                program_curriculums.*,
                courses.course_code,
                courses.title as course_title,
                year_levels.year_level_name,
                semesters.semester_name,
                (SELECT GROUP_CONCAT(c2.course_code SEPARATOR ", ")
                 FROM course_prerequisites cp
                 JOIN courses c2 ON c2.id = cp.prerequisite_id
                 WHERE cp.course_id = program_curriculums.course_id) as prerequisites
            ')
            ->join('courses', 'courses.id = program_curriculums.course_id')
            ->join('year_levels', 'year_levels.id = program_curriculums.year_level_id')
            ->join('semesters', 'semesters.id = program_curriculums.semester_id')
            ->where('program_curriculums.program_id', $programId)
            ->orderBy('year_levels.id', 'ASC')
            ->orderBy('semesters.id', 'ASC')
            ->findAll();
    }

    /**
     * Get course type options
     */
    public function getCourseTypes()
    {
        return [
            'major'              => 'Major Course',
            'minor'              => 'Minor Course',
            'general_education'  => 'General Education'
        ];
    }

    /**
     * Bulk add courses to curriculum
     */
    public function bulkAddCourses($programId, $courses)
    {
        $db = \Config\Database::connect();
        $db->transStart();
        
        $inserted = 0;
        $skipped = 0;
        $errors = [];
        
        foreach ($courses as $course) {
            $course['program_id'] = $programId;
            
            // Check for duplicate
            if ($this->isCourseInCurriculum(
                $programId, 
                $course['course_id'], 
                $course['year_level_id'], 
                $course['semester_id']
            )) {
                $skipped++;
                continue;
            }
            
            if ($this->insert($course)) {
                $inserted++;
            } else {
                $errors[] = $this->errors();
            }
        }
        
        $db->transComplete();
        
        if ($db->transStatus() === false) {
            return [
                'success' => false,
                'message' => 'Transaction failed. No courses were added.',
                'errors'  => $errors
            ];
        }
        
        return [
            'success' => true,
            'message' => "Successfully added {$inserted} course(s). {$skipped} duplicate(s) skipped.",
            'inserted' => $inserted,
            'skipped'  => $skipped
        ];
    }
}