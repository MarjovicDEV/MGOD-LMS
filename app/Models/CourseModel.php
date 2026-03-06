<?php

namespace App\Models;

use CodeIgniter\Model;

class CourseModel extends Model
{
    protected $table            = 'courses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;    
    protected $allowedFields    = [
        'course_code',
        'title',
        'description',
        'credits',
        'lecture_hours',
        'lab_hours',
        'department_id',
        'category_id',
        'year_level_id',
        'is_active'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';    // Validation
    protected $validationRules = [
        'course_code'   => 'required|string|min_length[2]|max_length[20]|is_unique[courses.course_code,id,{id}]|regex_match[/^[A-Z]{2,10}-[0-9]{1,5}$/]',
        'title'         => 'required|string|min_length[3]|max_length[255]|regex_match[/^[a-zA-ZñÑ0-9\s]+$/u]',
        'description'   => 'permit_empty|string',
        'credits'       => 'required|integer',
        'lecture_hours' => 'permit_empty|decimal',
        'lab_hours'     => 'permit_empty|decimal',
        'department_id' => 'permit_empty|integer',
        'category_id'   => 'permit_empty|integer',
        'year_level_id' => 'permit_empty|integer',
        'is_active'     => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'course_code' => [
            'required'    => 'Course code is required.',
            'min_length'  => 'Course code must be at least 2 characters.',
            'max_length'  => 'Course code must not exceed 20 characters.',
            'is_unique'   => 'This course code already exists.',
            'regex_match' => 'Course code must follow the format: XX-000 (e.g., CC-101, IT-201, BSIT-301). Use uppercase letters, hyphen, then numbers.'
        ],
        'title' => [
            'required'    => 'Course title is required.',
            'min_length'  => 'Course title must be at least 3 characters.',
            'max_length'  => 'Course title must not exceed 255 characters.',
            'regex_match' => 'Course title can only contain letters (including Ñ/ñ), numbers, and spaces. No special characters allowed.'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;    /**
     * Get course with all details
     */
    public function getCourseWithDetails($courseId)
    {
        return $this->select('
                courses.*,
                departments.department_name,
                categories.category_name,
                year_levels.year_level_name
            ')
            ->join('departments', 'departments.id = courses.department_id', 'left')
            ->join('categories', 'categories.id = courses.category_id', 'left')
            ->join('year_levels', 'year_levels.id = courses.year_level_id', 'left')
            ->find($courseId);
    }/**
     * Get all courses with details
     */
    public function getAllCoursesWithDetails()
    {
        return $this->select('
                courses.*,
                departments.department_name,
                categories.category_name,
                year_levels.year_level_name
            ')
            ->join('departments', 'departments.id = courses.department_id', 'left')
            ->join('categories', 'categories.id = courses.category_id', 'left')
            ->join('year_levels', 'year_levels.id = courses.year_level_id', 'left')
            ->findAll();
    }

    /**
     * Get courses by department
     */
    public function getCoursesByDepartment($departmentId)
    {
        return $this->where('department_id', $departmentId)
                    ->where('is_active', 1)
                    ->findAll();
    }    /**
     * Get courses by year level
     */
    public function getCoursesByYearLevel($yearLevelId)
    {
        return $this->where('year_level_id', $yearLevelId)
                    ->where('is_active', 1)
                    ->findAll();
    }

    /**
     * Get course prerequisites
     */
    public function getCoursePrerequisites($courseId)
    {
        $db = \Config\Database::connect();
        
        return $db->table('course_prerequisites cp')
                  ->select('c.*, cp.prerequisite_type, cp.minimum_grade')
                  ->join('courses c', 'c.id = cp.prerequisite_course_id')
                  ->where('cp.course_id', $courseId)
                  ->get()
                  ->getResultArray();
    }

    /**
     * Get courses that have this course as prerequisite
     */
    public function getCoursesRequiringThis($courseId)
    {
        $db = \Config\Database::connect();
        
        return $db->table('course_prerequisites cp')
                  ->select('c.*, cp.prerequisite_type')
                  ->join('courses c', 'c.id = cp.course_id')
                  ->where('cp.prerequisite_course_id', $courseId)
                  ->get()
                  ->getResultArray();
    }

    /**
     * Search courses
     */
    public function searchCourses($keyword)
    {
        return $this->like('course_code', $keyword)
                    ->orLike('title', $keyword)
                    ->orLike('description', $keyword)
                    ->where('is_active', 1)
                    ->findAll();
    }

    /**
     * Get course by code
     */
    public function getCourseByCode($courseCode)
    {
        return $this->where('course_code', $courseCode)->first();
    }
}
