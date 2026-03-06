<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramModel extends Model
{
    protected $table            = 'programs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'program_code',
        'program_name',
        'description',
        'department_id',
        'degree_type',
        'total_units',
        'total_years',
        'is_active'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'program_code' => 'required|min_length[2]|max_length[20]|is_unique[programs.program_code,id,{id}]',
        'program_name' => 'required|min_length[3]|max_length[200]',
        'description'  => 'permit_empty|string',
        'department_id' => 'permit_empty|integer',
        'degree_type'  => 'required|in_list[bachelor,master,doctorate,certificate,diploma]',
        'total_units'  => 'required|integer|greater_than[0]',
        'total_years'  => 'required|integer|greater_than[0]|less_than_equal_to[10]',
        'is_active'    => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'program_code' => [
            'required'   => 'Program code is required.',
            'min_length' => 'Program code must be at least 2 characters.',
            'max_length' => 'Program code cannot exceed 20 characters.',
            'is_unique'  => 'This program code already exists.'
        ],
        'program_name' => [
            'required'   => 'Program name is required.',
            'min_length' => 'Program name must be at least 3 characters.',
            'max_length' => 'Program name cannot exceed 200 characters.'
        ],
        'degree_type' => [
            'required' => 'Degree type is required.',
            'in_list'  => 'Please select a valid degree type.'
        ],
        'total_units' => [
            'required'      => 'Total units is required.',
            'integer'       => 'Total units must be a number.',
            'greater_than'  => 'Total units must be greater than 0.'
        ],
        'total_years' => [
            'required'             => 'Total years is required.',
            'integer'              => 'Total years must be a number.',
            'greater_than'         => 'Total years must be greater than 0.',
            'less_than_equal_to'   => 'Total years cannot exceed 10 years.'
        ]
    ];    /**
     * Get all active programs with department information
     */
    public function getActivePrograms()
    {
        return $this->select('
                programs.*,
                departments.department_name,
                departments.department_code
            ')
            ->join('departments', 'departments.id = programs.department_id', 'left')
            ->where('programs.is_active', 1)
            ->orderBy('programs.program_name', 'ASC')
            ->findAll();
    }    /**
     * Get program with all related data
     */    public function getProgramWithDetails($programId)
    {
        return $this->select('
                programs.*,
                departments.department_name,
                departments.department_code,
                (SELECT COUNT(*) FROM program_curriculums WHERE program_id = programs.id) as course_count,
                (SELECT COUNT(DISTINCT s.id) FROM students s JOIN users u ON u.id = s.user_id WHERE s.program_id = programs.id AND u.is_active = 1) as student_count
            ')
            ->join('departments', 'departments.id = programs.department_id', 'left')
            ->where('programs.id', $programId)
            ->first();
    }

    /**
     * Get programs by department
     */
    public function getProgramsByDepartment($departmentId)
    {
        return $this->select('
                programs.*,
                departments.department_name,
                (SELECT COUNT(*) FROM program_curriculums WHERE program_id = programs.id) as course_count
            ')
            ->join('departments', 'departments.id = programs.department_id', 'left')
            ->where('programs.department_id', $departmentId)
            ->where('programs.is_active', 1)
            ->orderBy('programs.program_name', 'ASC')
            ->findAll();
    }

    /**
     * Get programs by degree type
     */
    public function getProgramsByDegreeType($degreeType)
    {
        return $this->select('
                programs.*,
                departments.department_name,
                (SELECT COUNT(*) FROM program_curriculums WHERE program_id = programs.id) as course_count
            ')
            ->join('departments', 'departments.id = programs.department_id', 'left')
            ->where('programs.degree_type', $degreeType)
            ->where('programs.is_active', 1)
            ->orderBy('programs.program_name', 'ASC')
            ->findAll();
    }

    /**
     * Check if program code exists (case-insensitive)
     */
    public function isProgramCodeExists($programCode, $excludeId = null)
    {
        $builder = $this->where('LOWER(program_code)', strtolower($programCode));
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        
        return $builder->countAllResults() > 0;
    }

    /**
     * Get program statistics
     */
    public function getProgramStatistics($programId)
    {
        $db = \Config\Database::connect();
        
        $result = $db->query("
            SELECT 
                p.*,
                d.department_name,
                (SELECT COUNT(*) FROM program_curriculums WHERE program_id = p.id) as total_courses,                (SELECT SUM(c.credits) 
                 FROM program_curriculums pc 
                 JOIN courses c ON c.id = pc.course_id 
                 WHERE pc.program_id = p.id) as total_credits,
                (SELECT COUNT(DISTINCT s.id) 
                 FROM students s 
                 JOIN users u ON u.id = s.user_id
                 WHERE s.program_id = p.id AND u.is_active = 1) as enrolled_students,
                (SELECT COUNT(DISTINCT pc.year_level_id) 
                 FROM program_curriculums pc 
                 WHERE pc.program_id = p.id) as total_year_levels
            FROM programs p
            LEFT JOIN departments d ON d.id = p.department_id
            WHERE p.id = ?
        ", [$programId])->getRowArray();
        
        return $result;
    }

    /**
     * Activate/Deactivate program
     */
    public function toggleActiveStatus($programId)
    {
        $program = $this->find($programId);
        
        if (!$program) {
            return false;
        }
        
        return $this->update($programId, [
            'is_active' => $program['is_active'] ? 0 : 1
        ]);
    }

    /**
     * Delete program with validation (Soft Delete - check for dependencies)
     */
    public function deleteProgram($programId)
    {
        $db = \Config\Database::connect();
        
        // Get program info
        $program = $this->find($programId);
        if (!$program) {
            return [
                'success' => false,
                'message' => 'Program not found.'
            ];
        }

        // Check if program is already inactive
        if ($program['is_active'] == 0) {
            return [
                'success' => false,
                'message' => 'This program is already deactivated.'
            ];
        }
        
        // Check if program has curriculum
        $curriculumCount = $db->table('program_curriculums')
            ->where('program_id', $programId)
            ->countAllResults();
        
        if ($curriculumCount > 0) {
            return [
                'success' => false,
                'message' => 'Cannot delete program "' . $program['program_name'] . '". It has ' . $curriculumCount . ' course(s) in curriculum. Please remove curriculum entries first or deactivate instead.'
            ];
        }

        // Check if program has enrolled students
        $studentCount = $db->table('students')
            ->join('users', 'users.id = students.user_id')
            ->where('students.program_id', $programId)
            ->where('users.is_active', 1)
            ->countAllResults();
        
        if ($studentCount > 0) {
            return [
                'success' => false,
                'message' => 'Cannot delete program "' . $program['program_name'] . '". It has ' . $studentCount . ' enrolled student(s). Please deactivate instead.'
            ];
        }
        
        // Soft delete: Set is_active to 0
        if ($this->update($programId, ['is_active' => 0])) {
            return [
                'success' => true,
                'message' => 'Program "' . $program['program_name'] . '" has been deactivated successfully!'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to deactivate program.'
        ];
    }

    /**
     * Get degree type options
     */
    public function getDegreeTypes()
    {
        return [
            'bachelor'    => 'Bachelor\'s Degree',
            'master'      => 'Master\'s Degree',
            'doctorate'   => 'Doctorate Degree',
            'certificate' => 'Certificate Program',
            'diploma'     => 'Diploma Program'
        ];
    }

    /**
     * Search programs by name or code
     */
    public function searchPrograms($searchTerm)
    {
        return $this->select('
                programs.*,
                departments.department_name
            ')
            ->join('departments', 'departments.id = programs.department_id', 'left')
            ->groupStart()
                ->like('programs.program_code', $searchTerm)
                ->orLike('programs.program_name', $searchTerm)
            ->groupEnd()
            ->where('programs.is_active', 1)
            ->orderBy('programs.program_name', 'ASC')
            ->findAll();
    }
}