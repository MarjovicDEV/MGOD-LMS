<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\MaterialModel;
use App\Models\EnrollmentModel;

class Material extends BaseController
{
    protected $session;
    protected $materialModel;
    protected $enrollmentModel;

    public function __construct()
    {
        // Initialize session service
        $this->session = \Config\Services::session();
        
        // Initialize models
        $this->materialModel = new MaterialModel();
        $this->enrollmentModel = new EnrollmentModel();
        
        // Load helpers for file operations and form validation
        helper(['filesystem', 'form']);
    }

    /**
     * Upload Method - Display file upload form and handle file upload process
     * FIXED: Now accepts course_offering_id instead of course_id
     * 
     * @param int $course_offering_id Course Offering ID
     * @return ResponseInterface|string View or redirect response
     */
    public function upload($course_offering_id)
    {
        // 1. AUTHENTICATION CHECK
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // 2. ROLE CHECK - Only teachers and admins can upload materials
        $userRole = $this->session->get('role');
        if ($userRole !== 'teacher' && $userRole !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Access denied. Only teachers can upload materials.');
        }

        // 3. INPUT VALIDATION
        if (!$course_offering_id || !is_numeric($course_offering_id) || $course_offering_id <= 0) {
            return redirect()->to('/dashboard')->with('error', 'Invalid course offering ID.');
        }

        $course_offering_id = (int)$course_offering_id;

        // 4. VERIFY COURSE OFFERING EXISTS AND GET COURSE DETAILS
        $db = \Config\Database::connect();
        
        // Get course offering with course details
        $courseOffering = $db->table('course_offerings co')
            ->select('co.*, c.course_code, c.title, c.description, c.credits')
            ->join('courses c', 'c.id = co.course_id')
            ->where('co.id', $course_offering_id)
            ->get()
            ->getRowArray();
            
        if (!$courseOffering) {
            return redirect()->to('/dashboard')->with('error', 'Course offering not found.');
        }

        // FOR TEACHERS: Verify they are assigned to this course
        if ($userRole === 'teacher') {
            $teacherId = $this->session->get('userID');
            $isAssigned = $db->table('course_instructors ci')
                ->join('instructors i', 'i.id = ci.instructor_id')
                ->where('ci.course_offering_id', $course_offering_id)
                ->where('i.user_id', $teacherId)
                ->countAllResults() > 0;
                
            if (!$isAssigned) {
                return redirect()->to('/teacher/courses')->with('error', 'You are not assigned to this course.');
            }
        }

        // Step 4.1: Check for POST request (file upload submission)
        if ($this->request->is('post')) {
            
            // Step 4.2: Load CodeIgniter's File Uploading Library and Validation Library
            $validation = \Config\Services::validation();
            
            // Step 4.3: Configure upload preferences (upload path, allowed file types, maximum file size)
            $uploadPath = WRITEPATH . 'uploads/materials/course_offering_' . $course_offering_id . '/';
            
            // Create upload directory if it doesn't exist
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            // Configure validation rules for file upload (PDF and PPT only)
            $validationRules = [
                'material_file' => [
                    'label' => 'Material File',
                    'rules' => 'uploaded[material_file]|max_size[material_file,10240]|ext_in[material_file,pdf,ppt,pptx]'
                ]
            ];
            
            $validationMessages = [
                'material_file' => [
                    'uploaded' => 'Please select a file to upload.',
                    'max_size' => 'File size cannot exceed 10MB.',
                    'ext_in' => 'Only PDF and PowerPoint (PPT/PPTX) files are allowed.'
                ]
            ];
            
            // Validate the uploaded file
            if ($validation->setRules($validationRules, $validationMessages)->withRequest($this->request)->run()) {
                
                // Step 4.4: Perform the file upload. If successful, prepare data and save to database
                $uploadedFile = $this->request->getFile('material_file');
                
                if ($uploadedFile->isValid() && !$uploadedFile->hasMoved()) {
                    
                    // Generate unique filename to prevent conflicts
                    $originalName = $uploadedFile->getClientName();
                    $extension = $uploadedFile->getClientExtension();
                    $baseName = pathinfo($originalName, PATHINFO_FILENAME);
                    $timestamp = date('YmdHis');
                    $uniqueName = $baseName . '_' . $timestamp . '.' . $extension;
                    
                    try {
                        // Move uploaded file to destination directory
                        $uploadedFile->move($uploadPath, $uniqueName);
                        
                        // Prepare data (course_offering_id, file_name, file_path) for database
                        $materialData = [
                            'course_offering_id' => $course_offering_id,
                            'file_name' => $originalName,
                            'file_path' => 'uploads/materials/course_offering_' . $course_offering_id . '/' . $uniqueName
                        ];
                        
                        // Save to database using MaterialModel
                        $materialId = $this->materialModel->insertMaterial($materialData);
                        
                        if ($materialId) {
                            // Step 7: Generate notifications for enrolled students when material is uploaded
                            try {
                                // Get course name for notification
                                $courseName = $courseOffering['course_code'] . ' - ' . $courseOffering['title'];
                                
                                // Get all students enrolled in this course offering
                                $enrolledStudents = $this->enrollmentModel->getEnrolledStudents($course_offering_id);
                                
                                // Create notification for each enrolled student
                                if (!empty($enrolledStudents)) {
                                    $notificationModel = new \App\Models\NotificationModel();
                                    foreach ($enrolledStudents as $student) {
                                        $notificationModel->insert([
                                            'user_id'    => $student['id'],
                                            'message'    => "New material '{$originalName}' uploaded in {$courseName}",
                                            'is_read'    => 0,
                                            'created_at' => date('Y-m-d H:i:s')
                                        ]);
                                    }
                                }
                            } catch (\Exception $e) {
                                // Log notification error but don't fail the upload
                                log_message('error', 'Failed to create material upload notifications: ' . $e->getMessage());
                            }
                            
                            // Step 4.5: Set success flash message and redirect based on user role
                            $this->session->setFlashdata('success', 'Material "' . $originalName . '" uploaded successfully!');
                            
                            // Redirect back to the upload page to show the updated materials list
                            return redirect()->to('material/upload/' . $course_offering_id);
                            
                        } else {
                            // Database insert failed - remove uploaded file to clean up
                            if (file_exists($uploadPath . $uniqueName)) {
                                unlink($uploadPath . $uniqueName);
                            }
                            // Step 4.5: Set failure flash message
                            $this->session->setFlashdata('error', 'Failed to save material information. Please try again.');
                        }
                        
                    } catch (\Exception $e) {
                        log_message('error', 'Material upload error: ' . $e->getMessage());
                        // Step 4.5: Set failure flash message for server errors
                        $this->session->setFlashdata('error', 'Upload failed due to server error. Please try again.');
                    }
                    
                } else {
                    // Step 4.5: Set failure flash message for invalid files
                    $this->session->setFlashdata('error', 'Invalid file or file has already been processed.');
                }
                
            } else {
                // Step 4.5: Validation failed - set error messages
                $this->session->setFlashdata('errors', $validation->getErrors());
            }
        }

        // Display upload form (GET request or after POST processing)
        $existingMaterials = $this->materialModel->getOfferingMaterials($course_offering_id);
        
        $data = [
            'title' => 'Upload Materials - ' . $courseOffering['course_code'] . ' ' . $courseOffering['title'],
            'course' => $courseOffering,
            'course_offering_id' => $course_offering_id,
            'materials' => $existingMaterials,
            'user' => [
                'userID' => $this->session->get('userID'),
                'name'   => $this->session->get('name'),
                'email'  => $this->session->get('email'),
                'role'   => $this->session->get('role')
            ]
        ];

        return view('material/upload', $data);
    }

    /**
     * Delete Method - Handle deletion of material record and associated file
     * 
     * @param int $material_id Material ID
     * @return ResponseInterface Redirect response
     */
    public function delete($material_id)
    {
        // 1. AUTHENTICATION CHECK
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        // 2. ROLE CHECK - Only teachers and admins can delete materials
        $userRole = $this->session->get('role');
        if ($userRole !== 'teacher' && $userRole !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Access denied. Only teachers can delete materials.');
        }

        // 3. INPUT VALIDATION
        if (!$material_id || !is_numeric($material_id) || $material_id <= 0) {
            return redirect()->to('/dashboard')->with('error', 'Invalid material ID.');
        }

        $material_id = (int)$material_id;

        try {
            // 4. CHECK IF MATERIAL EXISTS
            $material = $this->materialModel->find($material_id);
            if (!$material) {
                return redirect()->to('/dashboard')->with('error', 'Material not found.');
            }

            // Convert to array if needed (CodeIgniter models return arrays by default)
            if (is_object($material)) {
                $material = (array) $material;
            }

            // 5. DELETE FILE FROM FILESYSTEM
            $filePath = WRITEPATH . $material['file_path'];
            $fileDeleted = true;
            
            if (file_exists($filePath)) {
                if (!unlink($filePath)) {
                    $fileDeleted = false;
                    log_message('warning', 'Failed to delete file: ' . $filePath);
                }
            }

            // 6. DELETE DATABASE RECORD
            $recordDeleted = $this->materialModel->delete($material_id);

            if ($recordDeleted) {
                $message = $fileDeleted ? 
                    'Material "' . $material['file_name'] . '" deleted successfully!' :
                    'Material record deleted, but file could not be removed from server.';
                    
                $this->session->setFlashdata('success', $message);
            } else {
                $this->session->setFlashdata('error', 'Failed to delete material record.');
            }

        } catch (\Exception $e) {
            log_message('error', 'Material deletion error: ' . $e->getMessage());
            $this->session->setFlashdata('error', 'Delete failed due to server error.');
        }

        // Redirect back to the course upload page
        $course_offering_id = $material['course_offering_id'] ?? null;
        if ($course_offering_id) {
            return redirect()->to('material/upload/' . $course_offering_id);
        }
        
        // Fallback redirect based on role
        if ($userRole === 'teacher') {
            return redirect()->to('teacher/courses');
        }
        return redirect()->to('admin/manage_courses');
    }

    /**
     * Download Method - Handle file download for enrolled students
     * 
     * @param int $material_id Material ID
     * @return ResponseInterface File download or error redirect
     */
    public function download($material_id)
    {
        // 1. AUTHENTICATION CHECK
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Please login to download materials.');
        }

        // 2. INPUT VALIDATION
        if (!$material_id || !is_numeric($material_id) || $material_id <= 0) {
            return redirect()->to('/dashboard')->with('error', 'Invalid material ID.');
        }

        $material_id = (int)$material_id;
        $userID = $this->session->get('userID');
        $userRole = $this->session->get('role');

        try {
            // 3. CHECK IF MATERIAL EXISTS
            $material = $this->materialModel->find($material_id);
            if (!$material) {
                return redirect()->to('/dashboard')->with('error', 'Material not found.');
            }

            // Convert to array if needed
            if (is_object($material)) {
                $material = (array) $material;
            }

            $course_offering_id = $material['course_offering_id'];

            // 4. AUTHORIZATION CHECK - Verify user can download this material
            $canDownload = false;
            
            // Admins and teachers can download any material
            if ($userRole === 'admin' || $userRole === 'teacher') {
                $canDownload = true;
            }
            // Students can only download materials from courses they're enrolled in
            elseif ($userRole === 'student') {
                $isEnrolled = $this->enrollmentModel->isAlreadyEnrolled($userID, $course_offering_id);
                if ($isEnrolled) {
                    $canDownload = true;
                }
            }

            if (!$canDownload) {
                return redirect()->to('/dashboard')->with('error', 'Access denied. You must be enrolled in this course to download materials.');
            }

            // 5. CHECK IF FILE EXISTS ON SERVER
            $filePath = WRITEPATH . $material['file_path'];
            
            // SECURITY: Validate path to prevent path traversal
            $realPath = realpath($filePath);
            if (!$realPath || strpos($realPath, realpath(WRITEPATH)) !== 0) {
                log_message('error', 'Path traversal attempt detected: ' . $filePath);
                return redirect()->to('/dashboard')->with('error', 'Invalid file path.');
            }
            
            if (!file_exists($realPath)) {
                log_message('error', 'Material file not found: ' . $realPath);
                return redirect()->to('/dashboard')->with('error', 'File not found on server.');
            }

            // 6. SERVE FILE DOWNLOAD
            $fileName = $material['file_name'];
            $mimeType = mime_content_type($realPath) ?: 'application/octet-stream';
            
            // Set appropriate headers for file download
            return $this->response->download($realPath, null)->setFileName($fileName);

        } catch (\Exception $e) {
            log_message('error', 'Material download error: ' . $e->getMessage());
            return redirect()->to('/dashboard')->with('error', 'Download failed due to server error.');
        }
    }

    /**
     * View/Preview Method - Handle material preview for enrolled students
     * 
     * @param int $material_id Material ID
     * @return ResponseInterface File preview or error redirect
     */
    public function view($material_id)
    {
        // 1. AUTHENTICATION CHECK
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Please login to view materials.');
        }

        // 2. INPUT VALIDATION
        if (!$material_id || !is_numeric($material_id) || $material_id <= 0) {
            return redirect()->to('/dashboard')->with('error', 'Invalid material ID.');
        }

        $material_id = (int)$material_id;
        $userID = $this->session->get('userID');
        $userRole = $this->session->get('role');

        try {
            // 3. CHECK IF MATERIAL EXISTS
            $material = $this->materialModel->find($material_id);
            if (!$material) {
                return redirect()->to('/dashboard')->with('error', 'Material not found.');
            }

            // Convert to array if needed
            if (is_object($material)) {
                $material = (array) $material;
            }

            $course_offering_id = $material['course_offering_id'];

            // 4. AUTHORIZATION CHECK - Verify user can view this material
            $canView = false;
            
            // Admins and teachers can view any material
            if ($userRole === 'admin' || $userRole === 'teacher') {
                $canView = true;
            }
            // Students can only view materials from courses they're enrolled in
            elseif ($userRole === 'student') {
                $isEnrolled = $this->enrollmentModel->isAlreadyEnrolled($userID, $course_offering_id);
                if ($isEnrolled) {
                    $canView = true;
                }
            }

            if (!$canView) {
                return redirect()->to('/dashboard')->with('error', 'Access denied. You must be enrolled in this course to view materials.');
            }

            // 5. CHECK IF FILE EXISTS ON SERVER
            $filePath = WRITEPATH . $material['file_path'];
            
            // SECURITY: Validate path to prevent path traversal
            $realPath = realpath($filePath);
            if (!$realPath || strpos($realPath, realpath(WRITEPATH)) !== 0) {
                log_message('error', 'Path traversal attempt detected: ' . $filePath);
                return redirect()->to('/dashboard')->with('error', 'Invalid file path.');
            }
            
            if (!file_exists($realPath)) {
                log_message('error', 'Material file not found: ' . $realPath);
                return redirect()->to('/dashboard')->with('error', 'File not found on server.');
            }

            // 6. DETERMINE FILE TYPE AND SERVE APPROPRIATELY
            $fileName = $material['file_name'];
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $mimeType = mime_content_type($realPath) ?: 'application/octet-stream';
            
            // For viewable files (images, PDFs), display inline
            $inlineViewable = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
            
            if (in_array($extension, $inlineViewable)) {
                // Set headers for inline viewing
                $this->response->setHeader('Content-Type', $mimeType);
                $this->response->setHeader('Content-Disposition', 'inline; filename="' . $fileName . '"');
                $this->response->setHeader('Content-Length', (string)filesize($realPath));
                $this->response->setHeader('Cache-Control', 'private, max-age=3600');
                
                // Stream file content for inline viewing
                return $this->response->setBody(file_get_contents($realPath));
            } else {
                // For non-viewable files, force download
                return $this->response->download($realPath, null)->setFileName($fileName);
            }

        } catch (\Exception $e) {
            log_message('error', 'Material view error: ' . $e->getMessage());
            return redirect()->to('/dashboard')->with('error', 'View failed due to server error.');
        }
    }
}