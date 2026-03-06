<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProgramModel;
use App\Models\DepartmentModel;
use App\Models\ProgramCurriculumModel;
use App\Models\CourseModel;
use App\Models\YearLevelModel;
use App\Models\SemesterModel;
use CodeIgniter\HTTP\ResponseInterface;

class Program extends BaseController
{
    protected $programModel;
    protected $departmentModel;
    protected $curriculumModel;
    protected $courseModel;
    protected $yearLevelModel;
    protected $semesterModel;
    protected $session;

    public function __construct()
    {
        $this->programModel = new ProgramModel();
        $this->departmentModel = new DepartmentModel();
        $this->curriculumModel = new ProgramCurriculumModel();
        $this->courseModel = new CourseModel();
        $this->yearLevelModel = new YearLevelModel();
        $this->semesterModel = new SemesterModel();
        $this->session = \Config\Services::session();
    }

    /**
     * Main manage programs view with CRUD operations
     */
    public function managePrograms()
    {
        // Check if user is admin
        if ($this->session->get('role') !== 'admin') {
            return redirect()->to('/login')->with('error', 'Access denied. Admin privileges required.');
        }

        $action = $this->request->getGet('action');
        $programId = $this->request->getGet('id');

        $data = [
            'title' => 'Manage Programs',
            'programs' => [],
            'departments' => $this->departmentModel->findAll(),
            'degreeTypes' => $this->programModel->getDegreeTypes(),
            'showCreateForm' => false,
            'showEditForm' => false,
            'editProgram' => null
        ];

        // ===== CREATE PROGRAM =====
        if ($action === 'create') {
            if ($this->request->getMethod() === 'POST') {
                return $this->createProgram();
            }
            // Show create form
            $data['showCreateForm'] = true;
            $data['programs'] = $this->getProgramsList();
            return view('admin/manage_programs', $data);
        }

        // ===== EDIT PROGRAM =====
        if ($action === 'edit' && $programId) {
            $programToEdit = $this->programModel->find($programId);

            if (!$programToEdit) {
                $this->session->setFlashdata('error', 'Program not found.');
                return redirect()->to('/admin/manage_programs');
            }

            if ($this->request->getMethod() === 'POST') {
                return $this->editProgram();
            }

            // Show edit form
            $data['showEditForm'] = true;
            $data['editProgram'] = $programToEdit;
            $data['programs'] = $this->getProgramsList();
            return view('admin/manage_programs', $data);
        }

        // Handle POST requests for other CRUD operations (delete, toggle_status)
        if ($this->request->getMethod() === 'POST') {
            $postAction = $this->request->getPost('action');

            switch ($postAction) {
                case 'delete':
                    return $this->deleteProgram();
                case 'toggle_status':
                    return $this->toggleStatus();
                default:
                    $this->session->setFlashdata('error', 'Invalid action: ' . ($postAction ?? 'none'));
                    return redirect()->to('/admin/manage_programs');
            }
        }

        // Default: Show programs list
        $data['programs'] = $this->getProgramsList();
        return view('admin/manage_programs', $data);
    }

    /**
     * Helper method to get programs list with department information
     */
    private function getProgramsList()
    {
        return $this->programModel->select('
                programs.*,
                departments.department_name,
                departments.department_code,
                (SELECT COUNT(*) FROM program_curriculums WHERE program_id = programs.id) as course_count,
                (SELECT COUNT(DISTINCT s.id) FROM students s JOIN users u ON u.id = s.user_id WHERE s.program_id = programs.id AND u.is_active = 1) as student_count
            ')
            ->join('departments', 'departments.id = programs.department_id', 'left')
            ->orderBy('programs.program_name', 'ASC')
            ->findAll();
    }

    /**
     * Validate program code format (uppercase letters only, e.g., BSIT)
     */
    private function validateProgramCode($code)
    {
        // Must be uppercase letters only, no numbers or symbols
        if (!preg_match('/^[A-Z]+$/', $code)) {
            return false;
        }
        return true;
    }

    /**
     * Validate program name format (letters and spaces only, e.g., Bachelor of Science in Information Technology)
     */
    private function validateProgramName($name)
    {
        // Must be letters (including Ññ) and spaces only, no numbers or special symbols
        if (!preg_match('/^[A-Za-zñÑ\s]+$/u', $name)) {
            return false;
        }
        return true;
    }

    /**
     * Create new program
     */
    private function createProgram()
    {
        // Get and sanitize input
        $programCode = strtoupper(trim($this->request->getPost('program_code')));
        $programName = trim($this->request->getPost('program_name'));

        // Custom validation for program code format
        if (!$this->validateProgramCode($programCode)) {
            $this->session->setFlashdata('error', 'Program code must contain uppercase letters only (e.g., BSIT). No numbers or special characters allowed.');
            return redirect()->back()->withInput();
        }

        // Custom validation for program name format
        if (!$this->validateProgramName($programName)) {
            $this->session->setFlashdata('error', 'Program name must contain letters (including Ñ/ñ) and spaces only (e.g., Bachelor of Science in Information Technology). No numbers or special characters allowed.');
            return redirect()->back()->withInput();
        }

        $validationRules = [
            'program_code' => 'required|min_length[2]|max_length[20]|is_unique[programs.program_code]',
            'program_name' => 'required|min_length[3]|max_length[200]',
            'description'  => 'permit_empty|string',
            'department_id' => 'permit_empty|integer',
            'degree_type'  => 'required|in_list[bachelor,master,doctorate,certificate,diploma]',
            'total_units'  => 'required|integer|greater_than[0]',
            'total_years'  => 'required|integer|greater_than[0]|less_than_equal_to[10]',
            'is_active'    => 'permit_empty|in_list[0,1]'
        ];

        $validationMessages = [
            'program_code' => [
                'required'   => 'Program code is required.',
                'min_length' => 'Program code must be at least 2 characters (e.g., IT, BSIT).',
                'max_length' => 'Program code cannot exceed 20 characters.',
                'is_unique'  => 'This program code already exists. Please use a different code.'
            ],
            'program_name' => [
                'required'   => 'Program name is required.',
                'min_length' => 'Program name must be at least 3 characters.',
                'max_length' => 'Program name cannot exceed 200 characters.'
            ],
            'degree_type' => [
                'required' => 'Please select a degree type.',
                'in_list'  => 'Please select a valid degree type from the options.'
            ],
            'total_units' => [
                'required'     => 'Total units is required.',
                'integer'      => 'Total units must be a whole number.',
                'greater_than' => 'Total units must be greater than 0.'
            ],
            'total_years' => [
                'required'           => 'Total years is required.',
                'integer'            => 'Total years must be a whole number.',
                'greater_than'       => 'Total years must be greater than 0.',
                'less_than_equal_to' => 'Total years cannot exceed 10 years.'
            ]
        ];

        if (!$this->validate($validationRules, $validationMessages)) {
            $errors = $this->validator->getErrors();
            $errorMessage = implode('<br>', $errors);
            $this->session->setFlashdata('error', $errorMessage);
            return redirect()->back()->withInput();
        }

        $programData = [
            'program_code' => $programCode,
            'program_name' => $programName,
            'description'  => trim($this->request->getPost('description')),
            'department_id' => $this->request->getPost('department_id') ?: null,
            'degree_type'  => $this->request->getPost('degree_type'),
            'total_units'  => $this->request->getPost('total_units'),
            'total_years'  => $this->request->getPost('total_years'),
            'is_active'    => 1 // Default to active
        ];

        if ($this->programModel->insert($programData)) {
            $this->session->setFlashdata('success', 'Program "' . $programName . '" created successfully!');
        } else {
            $errors = $this->programModel->errors();
            $errorMessage = $errors ? implode('<br>', $errors) : 'Failed to create program. Please try again.';
            $this->session->setFlashdata('error', $errorMessage);
        }

        return redirect()->to('/admin/manage_programs');
    }

    /**
     * Edit existing program
     */
    private function editProgram()
    {
        $programId = $this->request->getGet('id');

        if (!$programId) {
            $this->session->setFlashdata('error', 'Invalid program ID.');
            return redirect()->to('/admin/manage_programs');
        }

        // Get and sanitize input
        $programCode = strtoupper(trim($this->request->getPost('program_code')));
        $programName = trim($this->request->getPost('program_name'));

        // Custom validation for program code format
        if (!$this->validateProgramCode($programCode)) {
            $this->session->setFlashdata('error', 'Program code must contain uppercase letters only (e.g., BSIT). No numbers or special characters allowed.');
            return redirect()->back()->withInput();
        }

        // Custom validation for program name format
        if (!$this->validateProgramName($programName)) {
            $this->session->setFlashdata('error', 'Program name must contain letters (including Ñ/ñ) and spaces only (e.g., Bachelor of Science in Information Technology). No numbers or special characters allowed.');
            return redirect()->back()->withInput();
        }

        $validationRules = [
            'program_code' => "required|min_length[2]|max_length[20]|is_unique[programs.program_code,id,{$programId}]",
            'program_name' => 'required|min_length[3]|max_length[200]',
            'description'  => 'permit_empty|string',
            'department_id' => 'permit_empty|integer',
            'degree_type'  => 'required|in_list[bachelor,master,doctorate,certificate,diploma]',
            'total_units'  => 'required|integer|greater_than[0]',
            'total_years'  => 'required|integer|greater_than[0]|less_than_equal_to[10]'
        ];

        $validationMessages = [
            'program_code' => [
                'required'   => 'Program code is required.',
                'min_length' => 'Program code must be at least 2 characters (e.g., IT, BSIT).',
                'max_length' => 'Program code cannot exceed 20 characters.',
                'is_unique'  => 'This program code already exists. Please use a different code.'
            ],
            'program_name' => [
                'required'   => 'Program name is required.',
                'min_length' => 'Program name must be at least 3 characters.',
                'max_length' => 'Program name cannot exceed 200 characters.'
            ],
            'degree_type' => [
                'required' => 'Please select a degree type.',
                'in_list'  => 'Please select a valid degree type from the options.'
            ],
            'total_units' => [
                'required'     => 'Total units is required.',
                'integer'      => 'Total units must be a whole number.',
                'greater_than' => 'Total units must be greater than 0.'
            ],
            'total_years' => [
                'required'           => 'Total years is required.',
                'integer'            => 'Total years must be a whole number.',
                'greater_than'       => 'Total years must be greater than 0.',
                'less_than_equal_to' => 'Total years cannot exceed 10 years.'
            ]
        ];

        if (!$this->validate($validationRules, $validationMessages)) {
            $errors = $this->validator->getErrors();
            $errorMessage = implode('<br>', $errors);
            $this->session->setFlashdata('error', $errorMessage);
            return redirect()->back()->withInput();
        }

        $programData = [
            'program_code' => $programCode,
            'program_name' => $programName,
            'description'  => trim($this->request->getPost('description')),
            'department_id' => $this->request->getPost('department_id') ?: null,
            'degree_type'  => $this->request->getPost('degree_type'),
            'total_units'  => $this->request->getPost('total_units'),
            'total_years'  => $this->request->getPost('total_years')
        ];

        if ($this->programModel->update($programId, $programData)) {
            $this->session->setFlashdata('success', 'Program "' . $programName . '" updated successfully!');
        } else {
            $errors = $this->programModel->errors();
            $errorMessage = $errors ? implode('<br>', $errors) : 'Failed to update program. Please try again.';
            $this->session->setFlashdata('error', $errorMessage);
        }

        return redirect()->to('/admin/manage_programs');
    }

    /**
     * Delete program with dependency validation
     */
    private function deleteProgram()
    {
        $programId = $this->request->getPost('program_id');

        if (!$programId) {
            $this->session->setFlashdata('error', 'Invalid program ID.');
            return redirect()->to('/admin/manage_programs');
        }

        $result = $this->programModel->deleteProgram($programId);

        if ($result['success']) {
            $this->session->setFlashdata('success', $result['message']);
        } else {
            $this->session->setFlashdata('error', $result['message']);
        }

        return redirect()->to('/admin/manage_programs');
    }

    /**
     * Toggle program active status
     */
    private function toggleStatus()
    {
        $programId = $this->request->getPost('program_id');

        if (!$programId) {
            $this->session->setFlashdata('error', 'Invalid program ID.');
            return redirect()->to('/admin/manage_programs');
        }

        $program = $this->programModel->find($programId);
        if (!$program) {
            $this->session->setFlashdata('error', 'Program not found.');
            return redirect()->to('/admin/manage_programs');
        }

        $newStatus = $program['is_active'] ? 0 : 1;

        // If deactivating, check for constraints first
        if ($newStatus === 0) {
            $db = \Config\Database::connect();

            // Check if program has curriculum entries
            $curriculumCount = $db->table('program_curriculums')
                ->where('program_id', $programId)
                ->countAllResults();
            if ($curriculumCount > 0) {
                $this->session->setFlashdata('error', 'Cannot deactivate program "' . $program['program_name'] . '". It has ' . $curriculumCount . ' course(s) in curriculum. Please remove curriculum entries first.');
                return redirect()->to('/admin/manage_programs');
            }

            // Check if program has enrolled students
            $studentCount = $db->table('students')
                ->join('users', 'users.id = students.user_id')
                ->where('students.program_id', $programId)
                ->where('users.is_active', 1)
                ->countAllResults();
            if ($studentCount > 0) {
                $this->session->setFlashdata('error', 'Cannot deactivate program "' . $program['program_name'] . '". It has ' . $studentCount . ' enrolled student(s). Please reassign students first.');
                return redirect()->to('/admin/manage_programs');
            }
        }

        if ($this->programModel->update($programId, ['is_active' => $newStatus])) {
            $statusText = $newStatus ? 'activated' : 'deactivated';
            $this->session->setFlashdata('success', 'Program "' . $program['program_name'] . '" ' . $statusText . ' successfully!');
        } else {
            $this->session->setFlashdata('error', 'Failed to update program status.');
        }

        return redirect()->to('/admin/manage_programs');
    }

    // ==========================================
    // PROGRAM CURRICULUM MANAGEMENT
    // ==========================================

    /**
     * Main manage program curriculum view with CRUD operations
     */
    public function manageCurriculum()
    {
        // Check if user is admin
        if ($this->session->get('role') !== 'admin') {
            return redirect()->to('/login')->with('error', 'Access denied. Admin privileges required.');
        }

        $action = $this->request->getGet('action');
        $curriculumId = $this->request->getGet('id');
        $programId = $this->request->getGet('program_id');

        $data = [
            'title' => 'Manage Program Curriculum',
            'curriculum' => [],
            'programs' => $this->programModel->where('is_active', 1)->findAll(),
            'courses' => $this->courseModel->where('is_active', 1)->findAll(),
            'yearLevels' => $this->yearLevelModel->findAll(),
            'semesters' => $this->semesterModel->findAll(),
            'courseTypes' => $this->getCourseTypes(),
            'showCreateForm' => false,
            'showEditForm' => false,
            'editCurriculum' => null,
            'selectedProgramId' => $programId
        ];

        // ===== CREATE CURRICULUM ENTRY =====
        if ($action === 'create') {
            if ($this->request->getMethod() === 'POST') {
                return $this->createCurriculum();
            }
            // Show create form
            $data['showCreateForm'] = true;
            $data['curriculum'] = $this->getCurriculumList($programId);
            return view('admin/manage_curriculum', $data);
        }

        // ===== EDIT CURRICULUM ENTRY =====
        if ($action === 'edit' && $curriculumId) {
            $curriculumToEdit = $this->curriculumModel->find($curriculumId);

            if (!$curriculumToEdit) {
                $this->session->setFlashdata('error', 'Curriculum entry not found.');
                return redirect()->to('/admin/manage_curriculum');
            }

            if ($this->request->getMethod() === 'POST') {
                return $this->editCurriculum();
            }

            // Show edit form
            $data['showEditForm'] = true;
            $data['editCurriculum'] = $curriculumToEdit;
            $data['selectedProgramId'] = $curriculumToEdit['program_id'];
            $data['curriculum'] = $this->getCurriculumList($curriculumToEdit['program_id']);
            return view('admin/manage_curriculum', $data);
        }

        // Handle POST requests for other CRUD operations (delete, toggle_status)
        if ($this->request->getMethod() === 'POST') {
            $postAction = $this->request->getPost('action');

            switch ($postAction) {
                case 'delete':
                    return $this->deleteCurriculum();
                case 'toggle_status':
                    return $this->toggleCurriculumStatus();
                default:
                    $this->session->setFlashdata('error', 'Invalid action: ' . ($postAction ?? 'none'));
                    return redirect()->to('/admin/manage_curriculum');
            }
        }

        // Default: Show curriculum list
        $data['curriculum'] = $this->getCurriculumList($programId);
        return view('admin/manage_curriculum', $data);
    }

    /**
     * Get course type options
     */
    private function getCourseTypes()
    {
        return [
            'major' => 'Major Course',
            'minor' => 'Minor Course',
            'general_education' => 'General Education'
        ];
    }    /**
     * Helper method to get curriculum list with all related information
     */
    private function getCurriculumList($programId = null)
    {
        $builder = $this->curriculumModel->select('
                program_curriculums.*,
                programs.program_code,
                programs.program_name,
                courses.course_code,
                courses.title as course_title,
                courses.credits as course_credits,
                year_levels.year_level_name as year_level_name,
                semesters.semester_name
            ')
            ->join('programs', 'programs.id = program_curriculums.program_id')
            ->join('courses', 'courses.id = program_curriculums.course_id')
            ->join('year_levels', 'year_levels.id = program_curriculums.year_level_id')
            ->join('semesters', 'semesters.id = program_curriculums.semester_id');

        if ($programId) {
            $builder->where('program_curriculums.program_id', $programId);
        }

        return $builder->orderBy('programs.program_name', 'ASC')
                      ->orderBy('year_levels.id', 'ASC')
                      ->orderBy('semesters.id', 'ASC')
                      ->orderBy('courses.course_code', 'ASC')
                      ->findAll();
    }

    /**
     * Create new curriculum entry
     */
    private function createCurriculum()
    {
        $programId = $this->request->getPost('program_id');
        $courseId = $this->request->getPost('course_id');
        $yearLevelId = $this->request->getPost('year_level_id');
        $semesterId = $this->request->getPost('semester_id');

        // Check if course already exists in this program's curriculum for the same year/semester
        if ($this->curriculumModel->isCourseInCurriculum($programId, $courseId, $yearLevelId, $semesterId)) {
            $this->session->setFlashdata('error', 'This course is already in the curriculum for the selected year level and semester.');
            return redirect()->back()->withInput();
        }

        $validationRules = [
            'program_id'    => 'required|integer',
            'course_id'     => 'required|integer',
            'year_level_id' => 'required|integer',
            'semester_id'   => 'required|integer',
            'course_type'   => 'required|in_list[major,minor,general_education]',
            'units'         => 'required|integer|greater_than[0]|less_than_equal_to[12]'
        ];

        $validationMessages = [
            'program_id' => [
                'required' => 'Please select a program.',
                'integer'  => 'Invalid program selected.'
            ],
            'course_id' => [
                'required' => 'Please select a course.',
                'integer'  => 'Invalid course selected.'
            ],
            'year_level_id' => [
                'required' => 'Please select a year level.',
                'integer'  => 'Invalid year level selected.'
            ],
            'semester_id' => [
                'required' => 'Please select a semester.',
                'integer'  => 'Invalid semester selected.'
            ],
            'course_type' => [
                'required' => 'Please select a course type.',
                'in_list'  => 'Invalid course type selected.'
            ],
            'units' => [
                'required'           => 'Units is required.',
                'integer'            => 'Units must be a whole number.',
                'greater_than'       => 'Units must be greater than 0.',
                'less_than_equal_to' => 'Units cannot exceed 12.'
            ]
        ];

        if (!$this->validate($validationRules, $validationMessages)) {
            $errors = $this->validator->getErrors();
            $errorMessage = implode('<br>', $errors);
            $this->session->setFlashdata('error', $errorMessage);
            return redirect()->back()->withInput();
        }

        $curriculumData = [
            'program_id'    => $programId,
            'course_id'     => $courseId,
            'year_level_id' => $yearLevelId,
            'semester_id'   => $semesterId,
            'course_type'   => $this->request->getPost('course_type'),
            'units'         => $this->request->getPost('units'),
            'is_active'     => 1
        ];

        if ($this->curriculumModel->insert($curriculumData)) {
            $this->session->setFlashdata('success', 'Curriculum entry added successfully!');
        } else {
            $errors = $this->curriculumModel->errors();
            $errorMessage = $errors ? implode('<br>', $errors) : 'Failed to add curriculum entry. Please try again.';
            $this->session->setFlashdata('error', $errorMessage);
        }

        return redirect()->to('/admin/manage_curriculum' . ($programId ? '?program_id=' . $programId : ''));
    }

    /**
     * Edit existing curriculum entry
     */
    private function editCurriculum()
    {
        $curriculumId = $this->request->getGet('id');

        if (!$curriculumId) {
            $this->session->setFlashdata('error', 'Invalid curriculum entry ID.');
            return redirect()->to('/admin/manage_curriculum');
        }

        $programId = $this->request->getPost('program_id');
        $courseId = $this->request->getPost('course_id');
        $yearLevelId = $this->request->getPost('year_level_id');
        $semesterId = $this->request->getPost('semester_id');

        // Check if course already exists (excluding current entry)
        if ($this->curriculumModel->isCourseInCurriculum($programId, $courseId, $yearLevelId, $semesterId, $curriculumId)) {
            $this->session->setFlashdata('error', 'This course is already in the curriculum for the selected year level and semester.');
            return redirect()->back()->withInput();
        }

        $validationRules = [
            'program_id'    => 'required|integer',
            'course_id'     => 'required|integer',
            'year_level_id' => 'required|integer',
            'semester_id'   => 'required|integer',
            'course_type'   => 'required|in_list[major,minor,general_education]',
            'units'         => 'required|integer|greater_than[0]|less_than_equal_to[12]'
        ];

        $validationMessages = [
            'program_id' => [
                'required' => 'Please select a program.',
                'integer'  => 'Invalid program selected.'
            ],
            'course_id' => [
                'required' => 'Please select a course.',
                'integer'  => 'Invalid course selected.'
            ],
            'year_level_id' => [
                'required' => 'Please select a year level.',
                'integer'  => 'Invalid year level selected.'
            ],
            'semester_id' => [
                'required' => 'Please select a semester.',
                'integer'  => 'Invalid semester selected.'
            ],
            'course_type' => [
                'required' => 'Please select a course type.',
                'in_list'  => 'Invalid course type selected.'
            ],
            'units' => [
                'required'           => 'Units is required.',
                'integer'            => 'Units must be a whole number.',
                'greater_than'       => 'Units must be greater than 0.',
                'less_than_equal_to' => 'Units cannot exceed 12.'
            ]
        ];

        if (!$this->validate($validationRules, $validationMessages)) {
            $errors = $this->validator->getErrors();
            $errorMessage = implode('<br>', $errors);
            $this->session->setFlashdata('error', $errorMessage);
            return redirect()->back()->withInput();
        }

        $curriculumData = [
            'program_id'    => $programId,
            'course_id'     => $courseId,
            'year_level_id' => $yearLevelId,
            'semester_id'   => $semesterId,
            'course_type'   => $this->request->getPost('course_type'),
            'units'         => $this->request->getPost('units')
        ];

        if ($this->curriculumModel->update($curriculumId, $curriculumData)) {
            $this->session->setFlashdata('success', 'Curriculum entry updated successfully!');
        } else {
            $errors = $this->curriculumModel->errors();
            $errorMessage = $errors ? implode('<br>', $errors) : 'Failed to update curriculum entry. Please try again.';
            $this->session->setFlashdata('error', $errorMessage);
        }

        return redirect()->to('/admin/manage_curriculum' . ($programId ? '?program_id=' . $programId : ''));
    }

    /**
     * Delete curriculum entry
     */
    private function deleteCurriculum()
    {
        $curriculumId = $this->request->getPost('curriculum_id');
        $programId = $this->request->getPost('program_id');

        if (!$curriculumId) {
            $this->session->setFlashdata('error', 'Invalid curriculum entry ID.');
            return redirect()->to('/admin/manage_curriculum');
        }

        // Get the curriculum entry to find the program_id if not provided
        $curriculum = $this->curriculumModel->find($curriculumId);
        $targetProgramId = $programId ?: ($curriculum['program_id'] ?? null);

        // Validation: Prevent deletion if there are students enrolled in this program
        if ($targetProgramId) {
            $db = \Config\Database::connect();
            $studentCount = $db->table('students')
                ->join('users', 'users.id = students.user_id')
                ->where('students.program_id', $targetProgramId)
                ->where('users.is_active', 1)
                ->countAllResults();

            if ($studentCount > 0) {
                $this->session->setFlashdata('error', 'Cannot delete this curriculum entry because there are ' . $studentCount . ' active student(s) enrolled in the program. Please reassign or remove students first.');
                return redirect()->to('/admin/manage_curriculum' . ($targetProgramId ? '?program_id=' . $targetProgramId : ''));
            }
        }

        if ($this->curriculumModel->delete($curriculumId)) {
            $this->session->setFlashdata('success', 'Curriculum entry deleted successfully!');
        } else {
            $this->session->setFlashdata('error', 'Failed to delete curriculum entry.');
        }

        return redirect()->to('/admin/manage_curriculum' . ($targetProgramId ? '?program_id=' . $targetProgramId : ''));
    }

    /**
     * Toggle curriculum entry active status
     */
    private function toggleCurriculumStatus()
    {
        $curriculumId = $this->request->getPost('curriculum_id');
        $programId = $this->request->getPost('program_id');

        if (!$curriculumId) {
            $this->session->setFlashdata('error', 'Invalid curriculum entry ID.');
            return redirect()->to('/admin/manage_curriculum');
        }

        $curriculum = $this->curriculumModel->find($curriculumId);
        
        if ($curriculum) {
            $newStatus = $curriculum['is_active'] ? 0 : 1;
            if ($this->curriculumModel->update($curriculumId, ['is_active' => $newStatus])) {
                $this->session->setFlashdata('success', 'Curriculum entry status updated successfully!');
            } else {
                $this->session->setFlashdata('error', 'Failed to update curriculum entry status.');
            }
        } else {
            $this->session->setFlashdata('error', 'Curriculum entry not found.');
        }

        return redirect()->to('/admin/manage_curriculum' . ($programId ? '?program_id=' . $programId : ''));
    }

    /**
     * Search programs - AJAX endpoint
     * Accepts GET or POST requests with search term
     * Searches program_code, program_name, and description
     */
    public function search()
    {
        // Check if user is logged in and is admin
        if ($this->session->get('isLoggedIn') !== true || $this->session->get('role') !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access'
            ]);
        }

        // Get search term from GET or POST
        $searchTerm = $this->request->getGet('search') ?? $this->request->getPost('search');

        if (empty($searchTerm)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Search term is required'
            ]);
        }

        try {
            // Search programs using Query Builder with LIKE queries
            $results = $this->programModel->select('
                    programs.*,
                    departments.department_name,
                    departments.department_code
                ')
                ->join('departments', 'departments.id = programs.department_id', 'left')
                ->groupStart()
                    ->like('programs.program_code', $searchTerm)
                    ->orLike('programs.program_name', $searchTerm)
                    ->orLike('programs.description', $searchTerm)
                    ->orLike('departments.department_name', $searchTerm)
                    ->orLike('departments.department_code', $searchTerm)
                ->groupEnd()
                ->orderBy('programs.program_name', 'ASC')
                ->findAll();

            return $this->response->setJSON([
                'success' => true,
                'count' => count($results),
                'data' => $results,
                'search_term' => $searchTerm
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Program search error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while searching programs'
            ]);
        }
    }
}
