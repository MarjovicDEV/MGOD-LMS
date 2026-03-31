<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\StudentModel;
use App\Models\InstructorModel;
use App\Models\YearLevelModel;
use App\Models\CourseModel;
use App\Models\CourseInstructorModel;
use App\Models\EnrollmentModel;
use App\Models\TermModel;
use App\Models\NotificationModel;
use App\Models\RoleModel;
use App\Models\EmailVerificationModel;
use App\Models\OtpModel;
use App\Models\CaptchaModel;

// Auth Controller class - This handles all user authentication (login, register, logout, dashboard)
// This is the unified controller that manages all user account operations and role-based dashboards
class Auth extends BaseController
{
    protected $session;
    protected $validation;
    protected $db;
    protected $userModel;
    protected $studentModel;
    protected $instructorModel;
    protected $yearLevelModel;
    protected $courseModel;
    protected $courseInstructorModel;
    protected $enrollmentModel;
    protected $termModel;
    protected $notificationModel;
    protected $roleModel;
    protected $emailVerificationModel;
    protected $otpModel;
    protected $captchaModel;

    // Constructor - This runs automatically when we create Auth object
    // This sets up all the tools we need for authentication to work
    public function __construct()
    {
        // Get session service - this tracks who is logged in across pages
        // Session remembers user information even when they go to different pages
        $this->session = \Config\Services::session();
        
        // Get validation service - this checks if user fills forms correctly
        // Validation makes sure emails are valid, passwords are strong, etc.
        $this->validation = \Config\Services::validation();
        
        // Connect to database - this lets us save and find user accounts
        // Database is where all user information is permanently stored
        $this->db = \Config\Database::connect();        // Initialize all models for database operations
        $this->userModel = new UserModel();
        $this->studentModel = new StudentModel();
        $this->instructorModel = new InstructorModel();
        $this->yearLevelModel = new YearLevelModel();
        $this->courseModel = new CourseModel();
        $this->courseInstructorModel = new CourseInstructorModel();
        $this->enrollmentModel = new EnrollmentModel();
        $this->termModel = new TermModel();
        $this->notificationModel = new NotificationModel();
        $this->roleModel = new RoleModel();
        $this->emailVerificationModel = new EmailVerificationModel();
        $this->otpModel = new OtpModel();
        $this->captchaModel = new CaptchaModel();
    }// Register Method - This handles user sign-up (creating new accounts)
    // This function shows the registration form and processes new user accounts
    // Steps: 1) Check if already logged in 2) Validate form data 3) Save to database 4) Redirect
    public function register()
    {        // Step 1: Check if user is already logged in
        // If someone is already logged in, they don't need to register again
        if ($this->session->get('isLoggedIn') === true) {
            // Send them to their role-specific dashboard instead of registration page
            $userRole = $this->session->get('role');
            return redirect()->to(base_url($userRole . '/dashboard'));
        }

        // Step 2: Check if the registration form was submitted
        // This happens when user fills the form and clicks "Register" button
        if ($this->request->getMethod() === 'POST') {            // Step 2a: Set validation rules - these are the requirements for each form field
            // Each rule tells the system what to check for in the user's input
            $rules = [
                'first_name'       => 'required|min_length[2]|max_length[100]|regex_match[/^[a-zA-ZñÑ\s]+$/]',  // First name is required
                'middle_name'      => 'required|min_length[1]|max_length[100]|regex_match[/^[a-zA-ZñÑ\s]+$/]',  // Middle name is required
                'last_name'        => 'required|min_length[2]|max_length[100]|regex_match[/^[a-zA-ZñÑ\s]+$/]',  // Last name is required
                'email'            => 'required|valid_email|is_unique[users.email]|regex_match[/^[a-zA-Z0-9._]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/]',
                'password'         => 'required|min_length[6]',                 // Password must exist and be at least 6 characters
                'password_confirm' => 'required|matches[password]',             // Password confirmation must match the password
                'year_level_id'    => 'required|integer'  // Year level ID is required for students
            ];// Step 2b: Set error messages - these are shown to user if validation fails
            // Each message explains what went wrong in simple language
            $messages = [                
                'first_name' => [
                    'required'     => 'First name is required.',
                    'min_length'   => 'First name must be at least 2 characters long.',
                    'max_length'   => 'First name cannot exceed 100 characters.',
                    'regex_match'  => 'First name can only contain letters (including ñ/Ñ) and spaces.'
                ],
                'middle_name' => [
                    'required'     => 'Middle name is required.',
                    'min_length'   => 'Middle name must be at least 1 character long.',
                    'max_length'   => 'Middle name cannot exceed 100 characters.',
                    'regex_match'  => 'Middle name can only contain letters (including ñ/Ñ) and spaces.'
                ],
                'last_name' => [
                    'required'     => 'Last name is required.',
                    'min_length'   => 'Last name must be at least 2 characters long.',
                    'max_length'   => 'Last name cannot exceed 100 characters.',
                    'regex_match'  => 'Last name can only contain letters (including ñ/Ñ) and spaces.'
                ],
                'email' => [
                    'required'    => 'Email is required.',
                    'valid_email' => 'Please enter a valid email address.',
                    'is_unique'   => 'This email is already registered.',
                    'regex_match'  => 'Invalid email! Email should be like "marjovic_alejado@lms.com".'
                ],                
                'password' => [
                    'required'   => 'Password is required.',
                    'min_length' => 'Password must be at least 6 characters long.'
                ],
                'password_confirm' => [
                    'required' => 'Password confirmation is required.',
                    'matches'  => 'Password confirmation does not match.'
                ],
                'year_level_id' => [
                    'required' => 'Year level is required.',
                    'integer'  => 'Please select a valid year level.'
                ]
            ];            
            // Step 2c: Check if all validation rules pass
            // This tests all the rules against what the user typed in the form
            if ($this->validate(rules: $rules, messages: $messages)) {
                
                // Start database transaction for atomic operation
                $this->db->transStart();
                
                try {
                    // Step 3: Get name fields directly from form
                    $firstName = trim($this->request->getPost(index: 'first_name'));
                    $middleName = trim($this->request->getPost(index: 'middle_name'));
                    $lastName = trim($this->request->getPost(index: 'last_name'));
                    
                    // Step 4: Get student role ID from roles table
                    $studentRole = $this->roleModel->where('role_name', 'Student')->first();
                    if (!$studentRole) {
                        throw new \Exception('Student role not found in database');
                    }
                    
                    // Step 5: Get year level ID from form
                    $yearLevelId = $this->request->getPost(index: 'year_level_id');
                    if (!$yearLevelId) {
                        throw new \Exception('Invalid year level selected');
                    }
                    
                    // Step 6: Prepare user data to save in users table
                    $userData = [
                        'user_code'   => 'STU' . date('Ymd') . rand(1000, 9999), // Auto-generate user code
                        'first_name'  => $firstName,
                        'middle_name' => $middleName,
                        'last_name'   => $lastName,
                        'suffix'      => null,
                        'email'       => $this->request->getPost(index: 'email'),
                        'password'    => $this->request->getPost(index: 'password'), // Will be hashed by UserModel callback
                        'role_id'     => $studentRole['id'],
                        'is_active'   => 1
                    ];

                    // Step 7: Insert user data using UserModel (handles password hashing automatically)
                    $userId = $this->userModel->insert($userData);
                    
                    if (!$userId) {
                        // Get validation errors from UserModel
                        $errors = $this->userModel->errors();
                        $errorMessage = !empty($errors) ? implode(', ', $errors) : 'Failed to create user account';
                        throw new \Exception($errorMessage);
                    }
                    
                    // Step 8: Prepare student data to save in students table
                    $studentData = [
                        'user_id'           => $userId,
                        'student_id_number' => date('Y') . '-' . str_pad($userId, 5, '0', STR_PAD_LEFT), // Auto-generate student ID
                        'year_level_id'     => $yearLevelId,
                        'enrollment_date'   => date('Y-m-d'),
                        'enrollment_status' => 'enrolled',
                        'department_id'     => null, // Will be set later when student selects program
                        'section'           => null, // Will be assigned by admin
                        'guardian_name'     => null, // Can be updated in profile
                        'guardian_contact'  => null, // Can be updated in profile
                        'scholarship_status' => null,
                        'total_units'       => 0
                    ];
                    
                    // Step 9: Insert student data using StudentModel
                    $studentId = $this->studentModel->insert($studentData);
                    
                    if (!$studentId) {
                        // Get validation errors from StudentModel
                        $errors = $this->studentModel->errors();
                        $errorMessage = !empty($errors) ? implode(', ', $errors) : 'Failed to create student record';
                        throw new \Exception($errorMessage);
                    }                    
                    // Step 10: Complete transaction
                    $this->db->transComplete();
                    
                    // Check transaction status
                    if ($this->db->transStatus() === false) {
                        throw new \Exception('Transaction failed during commit');
                    }
                    
                    // Step 11: Create email verification token
                    $verification = $this->emailVerificationModel->createVerification($userId, $userData['email']);
                    
                    if (!$verification) {
                        throw new \Exception('Failed to create email verification token');
                    }
                    
                    // Step 12: Send verification email
                    $verificationLink = base_url('verify-email/' . $verification['verification_token']);
                    $emailSent = $this->sendVerificationEmail(
                        $userData['email'],
                        $firstName . ' ' . $lastName,
                        $verificationLink,
                        $studentData['student_id_number']
                    );
                    
                    if (!$emailSent) {
                        log_message('warning', "Verification email failed to send to: {$userData['email']}");
                    }
                    
                    // Step 13: Log successful registration
                    log_message('info', "New student registered: User ID {$userId}, Student ID {$studentId}, Email: {$userData['email']}");
                    
                    // Step 14: Success - Student account was created successfully
                    $this->session->setFlashdata(data: 'success', value: 'Registration successful! Please check your email to verify your account before logging in.');
                    return redirect()->to(uri: base_url(relativePath: 'login'));
                    
                } catch (\Exception $e) {
                    // Rollback transaction on error
                    $this->db->transRollback();
                    
                    // Log the error with details
                    log_message('error', 'Registration failed: ' . $e->getMessage());
                    log_message('error', 'Stack trace: ' . $e->getTraceAsString());
                    
                    // Show error message to user
                    $this->session->setFlashdata(data: 'error', value: 'Registration failed: ' . $e->getMessage());
                }} else {
                // Validation failed: User input didn't meet the requirements
                // Show all validation error messages to help user fix their input
                $this->session->setFlashdata(data: 'errors', value: $this->validation->getErrors());
            }
        }        // Step 4: Show the registration form page with year levels
        // This runs when user first visits registration page OR if there were errors
        // Get all year levels from database to populate dropdown
        $data['yearLevels'] = $this->yearLevelModel->getAllOrdered();
        return view(name: 'auth/register', data: $data);
    }    // Login Method - This handles user sign-in with 2FA OTP verification
    // This function shows login form and processes user login attempts with OTP
    // Steps: 1) Check if already logged in 2) Validate login form 3) Send OTP 4) Verify OTP 5) Create session
    public function login()
    {        
        // Step 1: Check if user is already logged in
        // If someone is already logged in, send them to their role-specific dashboard
        if ($this->session->get('isLoggedIn') === true) {
            $userRole = $this->session->get('role');
            return redirect()->to(base_url($userRole . '/dashboard'));
        }

        // Step 2: Check if login form was submitted
        // This happens when user enters email/password and clicks "Login" button
        if ($this->request->getMethod() === 'POST') {
            
            // Step 2a: Set validation rules for login form
            // Login form requires email and password
            $rules = [
                'email'    => 'required|valid_email',
                'password' => 'required',
                'captcha_token' => 'required',
                'captcha_code'  => 'required|min_length[4]|max_length[10]'
            ];

            // Step 2b: Set error messages for login validation
            $messages = [
                'email' => [
                    'required'    => 'Email is required.',
                    'valid_email' => 'Please enter a valid email address.'
                ],
                'password' => [
                    'required' => 'Password is required.'
                ],
                'captcha_token' => [
                    'required' => 'Captcha token is required.'
                ],
                'captcha_code' => [
                    'required'   => 'Captcha is required.',
                    'min_length' => 'Captcha input is too short.',
                    'max_length' => 'Captcha input is too long.'
                ]
            ];            
            
            // Step 2c: Check if validation passes
            if ($this->validate(rules: $rules, messages: $messages)) {
                $captchaToken = (string) $this->request->getPost('captcha_token');
                $captchaCode = (string) $this->request->getPost('captcha_code');
                $ipAddress = $this->request->getIPAddress();

                $isCaptchaValid = $this->captchaModel->verifyCaptcha($captchaToken, $captchaCode, $ipAddress);

                if (!$isCaptchaValid) {
                    $this->session->setFlashdata('error', 'Invalid or expired captcha. Please try again.');
                } else {
                    // Get the email and password that user typed in the form
                    $email = $this->request->getPost(index: 'email');
                    $password = $this->request->getPost(index: 'password');                
                    
                    // Step 3: Use UserModel to verify credentials
                    $user = $this->userModel->verifyCredentials($email, $password);

                    // Step 3b: Check if user exists and password is correct
                    if ($user) {
                        // Step 3c: Check if email is verified
                        if (empty($user['email_verified_at'])) {
                            $this->session->setFlashdata('error', 'Please verify your email address before logging in. Check your inbox for the verification link.');
                            $this->session->setFlashdata('show_resend', true);
                            $this->session->setFlashdata('user_email', $email);
                            return redirect()->to(base_url('login'));
                        }
                        
                        // Step 4: Generate and send OTP for 2FA
                        $otpData = $this->otpModel->createOTP($user['id'], $email, 'login');
                        
                        if ($otpData) {
                            // Send OTP via email
                            $emailSent = $this->sendOTPEmail($email, $user['first_name'] . ' ' . $user['last_name'], $otpData['otp_code']);
                            
                            if ($emailSent) {
                                // Store user data temporarily in session for OTP verification
                                $this->session->setTempdata('otp_user_id', $user['id'], 600); // 10 minutes
                                $this->session->setTempdata('otp_email', $email, 600);
                                $this->session->setTempdata('otp_user_data', $user, 600);
                                
                                $this->session->setFlashdata('success', 'OTP has been sent to your email. Please check your inbox.');
                                return redirect()->to(base_url('verify-otp'));
                            } else {
                                $this->session->setFlashdata('error', 'Failed to send OTP email. Please try again.');
                            }
                        } else {
                            $this->session->setFlashdata('error', 'Please wait before requesting another OTP.');
                        }
                        
                    } else {
                        // Step 3d: Login failed - either email doesn't exist or password is wrong
                        $this->session->setFlashdata(data: 'error', value: 'Invalid email or password.');
                    }
                }
            } else {
                // Step 2d: Validation failed - email format wrong or missing fields
                $this->session->setFlashdata(data: 'errors', value: $this->validation->getErrors());
            }
        }

        // Step 5: Show the login form page with captcha challenge
        $captchaChallenge = $this->captchaModel->createCaptcha($this->request->getIPAddress());
        $data = [
            'captchaToken' => $captchaChallenge['token'] ?? '',
            'captchaImageUrl' => !empty($captchaChallenge['token'])
                ? base_url('captcha/image/' . $captchaChallenge['token']) . '?t=' . time()
                : ''
        ];

        return view(name: 'auth/login', data: $data);
    }

    // Logout Method - This handles user sign-out (ending their session)
    // This function logs user out and sends them back to login page
    // Steps: 1) Destroy session data 2) Show logout message 3) Redirect to login
    public function logout()
    {
        // Step 1: Destroy the current session - forget all user login information
        // This completely logs the user out and clears all their session data
        $this->session->destroy();
        
        // Step 2: Show logout success message to confirm user was logged out
        $this->session->setFlashdata(data: 'success', value: 'You have been logged out successfully.');
        
        // Step 3: Send user back to login page so they can log in again if needed
        return redirect()->to(uri: base_url(relativePath: 'login'));    }
    
    // Dashboard Method - This shows unified dashboard based on user role    // This is the main dashboard that handles all user types in one place    // Only accessible to users who are logged in
    // Now includes Manage Users functionality for Admin users
    public function dashboard()
    {
        // Step 1: Check if user is logged in first
        // If not logged in, they can't access any dashboard
        if ($this->session->get('isLoggedIn') !== true) {
            // Show error message and send to login page
            $this->session->setFlashdata(data: 'error', value: 'Please login to access the dashboard.');
            return redirect()->to(uri: base_url(relativePath: 'login'));
        }

        // Step 2: Get user role from session and current URI
        $userRole = $this->session->get(key: 'role');
        $currentUri = uri_string();
        
        // Step 3: Check if user is accessing the correct role-based dashboard URL
        // If user accesses /dashboard, redirect them to their role-specific URL
        if ($currentUri === 'dashboard') {
            return redirect()->to(base_url($userRole . '/dashboard'));
        }
        
        // Step 4: Validate that user is accessing their own role dashboard
        $expectedUri = $userRole . '/dashboard';
        if ($currentUri !== $expectedUri) {
            $this->session->setFlashdata('error', 'Access denied. You can only access your own dashboard.');
            return redirect()->to(base_url($userRole . '/dashboard'));
        }
        
        // Step 5: Prepare basic user data that all roles need
        $baseData = [
            'user' => [
                'userID' => $this->session->get(key: 'userID'), // User's ID number
                'name'   => $this->session->get(key: 'name'),   // User's full name
                'email'  => $this->session->get(key: 'email'), // User's email address
                'role'   => $this->session->get(key: 'role')   // User's role
            ]
        ];        // Step 6: Get role-specific data and determine view based on user type
        switch ($userRole) {                
            case 'admin':
                // Admin gets system statistics and user management data using UserModel
                $totalUsers = $this->userModel->countAll();
                
                // Get role IDs for counting
                $adminRole = $this->roleModel->where('role_name', 'Admin')->first();
                $teacherRole = $this->roleModel->where('role_name', 'Teacher')->first();
                $studentRole = $this->roleModel->where('role_name', 'Student')->first();
                
                $totalAdmins = $adminRole ? $this->userModel->where('role_id', $adminRole['id'])->countAllResults(false) : 0;
                $totalTeachers = $teacherRole ? $this->userModel->where('role_id', $teacherRole['id'])->countAllResults(false) : 0;
                $totalStudents = $studentRole ? $this->userModel->where('role_id', $studentRole['id'])->countAllResults(false) : 0;
                  // Get course statistics for admin using CourseModel
                $totalCourses = $this->courseModel->countAll();
                $activeCourses = $this->courseModel->where('is_active', 1)->countAllResults(false);
                $draftCourses = 0; // No draft status in current schema
                $completedCourses = 0; // No completed status in current schema
                
                // Get recent users with more detailed information for activity feed using UserModel
                $recentUsers = $this->userModel
                    ->select('users.id, users.first_name, users.last_name, users.email, users.created_at, users.updated_at, roles.role_name')
                    ->join('roles', 'roles.id = users.role_id', 'left')
                    ->orderBy('users.created_at', 'DESC')
                    ->limit(10)
                    ->findAll();

                // Prepare recent activities for display
                $recentActivities = [];
                foreach ($recentUsers as $user) {
                    // Add user registration activity
                    $userName = esc($user['first_name'] . ' ' . $user['last_name']);
                    $roleName = strtolower(trim((string) ($user['role_name'] ?? 'user')));
                    if ($roleName === 'instructor') {
                        $roleName = 'teacher';
                    }
                    
                    $recentActivities[] = [
                        'type' => 'user_registration',
                        'icon' => '👤',
                        'title' => 'New User Registration',
                        'description' => $userName . ' (' . ucfirst($roleName) . ') joined the system',
                        'time' => $user['created_at'],
                        'user_name' => $userName,
                        'user_role' => $roleName
                    ];
                }
                  // Add admin-managed activities from session to recent activities
                $creationActivities = $this->session->get('creation_activities') ?? [];
                $updateActivities = $this->session->get('update_activities') ?? [];
                $deletionActivities = $this->session->get('deletion_activities') ?? [];
                $reactivationActivities = $this->session->get('reactivation_activities') ?? [];
                $assignmentActivities = $this->session->get('assignment_activities') ?? [];
                
                // Merge all admin activities
                $adminActivities = array_merge(
                    $creationActivities,
                    $updateActivities,
                    $deletionActivities,
                    $reactivationActivities,
                    $assignmentActivities
                );
                
                // Add admin activities to recent activities
                foreach ($adminActivities as $adminActivity) {
                    $recentActivities[] = $adminActivity;
                }
                
                // Sort activities by time (most recent first) and limit to 8
                usort($recentActivities, function($a, $b) {
                    return strtotime($b['time']) - strtotime($a['time']);
                });
                $recentActivities = array_slice($recentActivities, 0, 8);

                $dashboardData = array_merge($baseData, [
                    'title' => 'Admin Dashboard - MGOD LMS',
                    'totalUsers' => $totalUsers,
                    'totalAdmins' => $totalAdmins,
                    'totalTeachers' => $totalTeachers,
                    'totalStudents' => $totalStudents,
                    'totalCourses' => $totalCourses,
                    'activeCourses' => $activeCourses,
                    'draftCourses' => $draftCourses,
                    'completedCourses' => $completedCourses,
                    'recentUsers' => $recentUsers,
                    'recentActivities' => $recentActivities
                ]);
                return view('auth/dashboard', $dashboardData);                  
                case 'teacher':
                // Teacher gets course and student data using proper relational queries
                $teacherUserID = $this->session->get('userID');
                
                try {
                    // Get the instructor record for this user
                    $instructor = $this->instructorModel->where('user_id', $teacherUserID)->first();
                    
                    if (!$instructor) {
                        // If no instructor record found, show empty dashboard
                        log_message('warning', "No instructor record found for user ID: {$teacherUserID}");
                        $dashboardData = array_merge($baseData, [
                            'title' => 'Teacher Dashboard - MGOD LMS',
                            'totalCourses' => 0,
                            'activeCourses' => 0,
                            'availableCoursesCount' => 0,
                            'totalStudents' => 0,
                            'pendingAssignments' => 0,
                            'averageGrade' => 0,
                            'assignment_activities' => []
                        ]);
                        return view('auth/dashboard', $dashboardData);
                    }
                    
                    $instructorId = $instructor['id'];
                    
                    // Count total course offerings assigned to this instructor
                    $teacherCourses = $this->courseInstructorModel
                        ->where('instructor_id', $instructorId)
                        ->countAllResults(false);
                    
                    // Count active course offerings (where course_offerings.status = 'open')
                    $activeCourses = $this->courseInstructorModel
                        ->join('course_offerings', 'course_offerings.id = course_instructors.course_offering_id')
                        ->where('course_instructors.instructor_id', $instructorId)
                        ->where('course_offerings.status', 'open')
                        ->countAllResults(false);
                    
                    // Count available courses (active courses not yet assigned to this teacher)
                    $totalActiveCourses = $this->db->table('course_offerings')
                        ->where('status', 'open')
                        ->countAllResults();
                    $availableCoursesCount = max(0, $totalActiveCourses - $activeCourses);
                    
                    // Get total students enrolled in teacher's courses
                    $totalStudents = $this->db->table('course_instructors ci')
                        ->select('COUNT(DISTINCT e.student_id) as total')
                        ->join('enrollments e', 'e.course_offering_id = ci.course_offering_id')
                        ->where('ci.instructor_id', $instructorId)
                        ->where('e.enrollment_status', 'enrolled')
                        ->get()
                        ->getRow()
                        ->total ?? 0;
                    
                    // Debug log
                    log_message('debug', "Teacher Dashboard - Instructor ID: {$instructorId}, Courses: {$teacherCourses}, Active: {$activeCourses}, Students: {$totalStudents}");
                    
                    // Get assignment activities from session for recent activity display
                    $assignmentActivities = $this->session->get('assignment_activities') ?? [];
                    
                    $dashboardData = array_merge($baseData, [
                        'title' => 'Teacher Dashboard - MGOD LMS',
                        'totalCourses' => $teacherCourses,
                        'activeCourses' => $activeCourses,
                        'availableCoursesCount' => $availableCoursesCount,
                        'totalStudents' => $totalStudents,
                        'pendingAssignments' => 0, // Placeholder - implement when assignments table exists
                        'averageGrade' => 0,        // Placeholder - implement when grades table exists
                        'assignment_activities' => $assignmentActivities
                    ]);
                    
                    return view('auth/dashboard', $dashboardData);
                      } catch (\Exception $e) {
                    // If there's a database error, show a simplified teacher dashboard
                    log_message('error', 'Teacher dashboard error: ' . $e->getMessage());
                    
                    $dashboardData = array_merge($baseData, [
                        'title' => 'Teacher Dashboard - MGOD LMS',
                        'totalCourses' => 0,
                        'activeCourses' => 0,
                        'availableCoursesCount' => 0,
                        'totalStudents' => 0,
                        'pendingAssignments' => 0,
                        'averageGrade' => 0,
                        'assignment_activities' => []
                    ]);
                    
                    return view('auth/dashboard', $dashboardData);
                }            
                case 'student':
                // Student gets enrollment and course data using EnrollmentModel and CourseOfferingModel
                $userID = $this->session->get('userID');
                
                try {
                    // Get the student record for this user - Use getStudentByUserId method to avoid query builder state issues
                    $student = $this->studentModel->getStudentByUserId($userID);
                    
                    if (!$student) {
                        log_message('error', 'Student record not found for user ID: ' . $userID);
                        // Return empty dashboard if student record doesn't exist
                        $dashboardData = array_merge($baseData, [
                            'title' => 'Student Dashboard - MGOD LMS',
                            'enrolledCourses' => 0,
                            'enrolledCoursesData' => [],
                            'availableCoursesData' => [],
                            'completedAssignments' => 0,
                            'pendingAssignments' => 0,
                            'currentTerm' => null,
                            'studentInfo' => null
                        ]);
                        return view('auth/dashboard', $dashboardData);
                    }
                    
                    $studentId = $student['id'];
                    
                    // Get complete student info with program name for display
                    $studentComplete = $this->studentModel->getStudentComplete($studentId);
                    if ($studentComplete) {
                        $student = $studentComplete;
                    }
                    
                    // Initialize CourseOfferingModel
                    $courseOfferingModel = new \App\Models\CourseOfferingModel();
                    
                    // Get enrolled course offerings for this student using EnrollmentModel
                    try {
                        log_message('debug', 'Attempting to get enrollments for student ID: ' . $studentId);
                        $enrolledCourses = $this->enrollmentModel->getStudentEnrollments($studentId);
                        log_message('debug', 'Successfully retrieved ' . count($enrolledCourses) . ' enrolled courses');
                        $enrolledCoursesCount = count($enrolledCourses);
                    } catch (\Exception $e) {
                        log_message('error', 'Error getting student enrollments: ' . $e->getMessage());
                        log_message('error', 'Stack trace: ' . $e->getTraceAsString());
                        $enrolledCourses = [];
                        $enrolledCoursesCount = 0;
                    }
                      // Get current term (to show available course offerings)
                    $currentTerm = $this->termModel->where('is_current', 1)->first();
                    
                    // Log for debugging
                    log_message('debug', 'Current Term for Student Dashboard: ' . json_encode($currentTerm));
                    
                    // If no current term is set, get the most recent active term
                    if (!$currentTerm) {
                        $currentTerm = $this->termModel
                            ->where('is_active', 1)
                            ->orderBy('start_date', 'DESC')
                            ->first();
                        
                        log_message('debug', 'No current term found. Using most recent active term: ' . json_encode($currentTerm));
                    }
                      // Get available course offerings filtered by student's program, department, and year level
                    $availableOfferings = [];
                    if ($currentTerm) {
                        // Get enrolled offering IDs to exclude them
                        $enrolledOfferingIds = array_column($enrolledCourses, 'course_offering_id');
                        
                        // Log student details for debugging
                        log_message('debug', 'Student Dashboard - Student ID: ' . $studentId);
                        log_message('debug', 'Student Dashboard - Current Term ID: ' . $currentTerm['id']);
                        log_message('debug', 'Student Dashboard - Enrolled Offering IDs: ' . json_encode($enrolledOfferingIds));
                        log_message('debug', 'Student Details: ' . json_encode($student));
                        
                        // Use CourseOfferingModel's method to get filtered offerings
                        $availableOfferings = $courseOfferingModel->getAvailableOfferingsForStudent(
                            $currentTerm['id'],
                            $studentId,
                            $enrolledOfferingIds
                        );
                        
                        log_message('debug', 'Available Offerings Count (filtered by student): ' . count($availableOfferings));
                        log_message('debug', 'Available Offerings Data: ' . json_encode($availableOfferings));
                    } else {
                        log_message('warning', 'No current or active term found for student dashboard');
                    }
                    
                    // Format enrolled course data for display
                    foreach ($enrolledCourses as &$course) {
                        $course['progress'] = 0; // Default progress is 0% (no progress yet)
                    }
                    
                    // Count assignments (using AssignmentModel if exists)
                    $completedAssignments = 0;
                    $pendingAssignments = 0;
                    
                    if (class_exists('\App\Models\AssignmentModel') && class_exists('\App\Models\SubmissionModel')) {
                        $assignmentModel = new \App\Models\AssignmentModel();
                        $submissionModel = new \App\Models\SubmissionModel();
                        
                        // Get all assignments for student's enrolled courses
                        foreach ($enrolledCourses as $enrollment) {
                            $assignments = $assignmentModel->where('course_offering_id', $enrollment['course_offering_id'])
                                                          ->where('is_active', 1)
                                                          ->findAll();
                            
                            foreach ($assignments as $assignment) {
                                $submission = $submissionModel->where('assignment_id', $assignment['id'])
                                                              ->where('enrollment_id', $enrollment['id'])
                                                              ->first();
                                
                                if ($submission && in_array($submission['status'], ['submitted', 'graded'])) {
                                    $completedAssignments++;
                                } else {
                                    $pendingAssignments++;
                                }
                            }
                        }
                    }
                      $dashboardData = array_merge($baseData, [
                        'title' => 'Student Dashboard - MGOD LMS',
                        'enrolledCourses' => $enrolledCoursesCount,
                        'enrolledCoursesData' => $enrolledCourses,
                        'availableCoursesData' => $availableOfferings,
                        'completedAssignments' => $completedAssignments,
                        'pendingAssignments' => $pendingAssignments,
                        'averageGrade' => 0, // Placeholder - implement when grades are calculated
                        'currentTerm' => $currentTerm,
                        'studentInfo' => $student
                    ]);
                    
                    return view('auth/dashboard', $dashboardData);
                    
                } catch (\Exception $e) {
                    log_message('error', 'Student dashboard error: ' . $e->getMessage());
                    
                    // Return empty dashboard on error
                    $dashboardData = array_merge($baseData, [
                        'title' => 'Student Dashboard - MGOD LMS',
                        'enrolledCourses' => 0,
                        'enrolledCoursesData' => [],
                        'availableCoursesData' => [],
                        'completedAssignments' => 0,
                        'pendingAssignments' => 0,
                        'averageGrade' => 0,
                        'currentTerm' => null,
                        'studentInfo' => null
                    ]);
                    
                    return view('auth/dashboard', $dashboardData);
                }

            default:
                // If role is unknown, show generic dashboard
                return view('auth/dashboard', $baseData);
        }
    }

    /**
     * Send verification email to user
     *
     * @param string $email User's email address
     * @param string $userName User's full name
     * @param string $verificationLink Verification link
     * @param string $studentId Student ID number
     * @return bool True if email sent successfully
     */
    protected function sendVerificationEmail(string $email, string $userName, string $verificationLink, string $studentId): bool
    {
        try {
            $emailService = \Config\Services::email();
            
            // Prepare email data for view
            $emailData = [
                'userName'          => $userName,
                'userEmail'         => $email,
                'verificationLink'  => $verificationLink,
                'studentId'         => $studentId
            ];
            
            // Load HTML email template
            $message = view('emails/verify_email', $emailData);
            
            // Set email parameters
            $emailService->setTo($email);
            $emailService->setSubject('Verify Your Email - MGOD LMS Account Activation');
            $emailService->setMessage($message);
            
            // Send email
            if ($emailService->send()) {
                log_message('info', "Verification email sent successfully to: {$email}");
                return true;
            } else {
                log_message('error', "Failed to send verification email to: {$email}");
                log_message('error', $emailService->printDebugger(['headers']));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', "Exception sending verification email: " . $e->getMessage());
            return false;
        }
    }    /**
     * Verify Email - This handles email verification via token
     *
     * @param string|null $token Verification token from email link
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function verifyEmail(?string $token = null)
    {
        // Check if token is provided
        if (!$token) {
            $this->session->setFlashdata('error', 'Invalid verification link. No token provided.');
            return redirect()->to(base_url('login'));
        }

        // Verify the token using EmailVerificationModel
        $result = $this->emailVerificationModel->verifyToken($token);

        if ($result['success']) {
            // Mark user's email as verified in users table
            $this->userModel->markEmailAsVerified($result['user_id']);

            // Log successful verification
            log_message('info', "Email verified successfully for User ID: {$result['user_id']}, Email: {$result['email']}");

            // Set success message
            $this->session->setFlashdata('success', 'Email verified successfully! You can now log in to your account.');
            
            // Redirect to login page
            return redirect()->to(base_url('login'));
        } else {
            // Verification failed
            log_message('warning', "Email verification failed: {$result['message']}");

            // Set error message
            $this->session->setFlashdata('error', $result['message']);

            // If token expired, offer resend option
            if (isset($result['expired']) && $result['expired']) {
                $this->session->setFlashdata('show_resend', true);
            }

            return redirect()->to(base_url('login'));
        }
    }

    /**
     * Resend Verification Email
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function resendVerification()
    {
        // Check if user is logged in (they might be trying to verify after login attempt)
        $email = $this->request->getPost('email');

        if (!$email) {
            $this->session->setFlashdata('error', 'Please provide your email address.');
            return redirect()->to(base_url('login'));
        }

        // Find user by email
        $user = $this->userModel->getUserByEmail($email);

        if (!$user) {
            $this->session->setFlashdata('error', 'No account found with this email address.');
            return redirect()->to(base_url('login'));
        }        // Check if already verified
        if ($this->userModel->isEmailVerified($user['id'])) {
            $this->session->setFlashdata('info', 'Your email is already verified. You can log in now.');
            return redirect()->to(base_url('login'));
        }

        // Get student data for the email - Use getStudentByUserId method to avoid query builder state issues
        $student = $this->studentModel->getStudentByUserId($user['id']);
        $studentId = $student ? $student['student_id_number'] : 'N/A';

        // Create new verification token
        $verification = $this->emailVerificationModel->resendVerification($user['id'], $email);

        if (!$verification) {
            $this->session->setFlashdata('error', 'Failed to generate verification link. Please try again.');
            return redirect()->to(base_url('login'));
        }

        // Send new verification email
        $verificationLink = base_url('verify-email/' . $verification['verification_token']);
        $userName = trim($user['first_name'] . ' ' . $user['last_name']);
        
        $emailSent = $this->sendVerificationEmail($email, $userName, $verificationLink, $studentId);

        if ($emailSent) {
            $this->session->setFlashdata('success', 'Verification email sent! Please check your inbox.');
        } else {
            $this->session->setFlashdata('error', 'Failed to send verification email. Please contact support.');
        }

        return redirect()->to(base_url('login'));
    }

    /**
     * Send OTP Email - Sends OTP code to user's email for 2FA
     *
     * @param string $email User's email address
     * @param string $userName User's full name
     * @param string $otpCode OTP code
     * @return bool True if email sent successfully
     */
    protected function sendOTPEmail(string $email, string $userName, string $otpCode): bool
    {
        try {
            $emailService = \Config\Services::email();
            
            // Prepare email data for view
            $emailData = [
                'userName' => $userName,
                'otpCode'  => $otpCode,
                'expiryMinutes' => 10
            ];
            
            // Load HTML email template
            $message = view('emails/otp_email', $emailData);
            
            // Set email parameters
            $emailService->setTo($email);
            $emailService->setSubject('Your Login OTP Code - MGOD LMS');
            $emailService->setMessage($message);
            
            // Send email
            if ($emailService->send()) {
                log_message('info', "OTP email sent successfully to: {$email}");
                return true;
            } else {
                log_message('error', "Failed to send OTP email to: {$email}. Error: " . $emailService->printDebugger(['headers']));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', "Exception sending OTP email: " . $e->getMessage());
            return false;
        }
    }    /**
     * Verify OTP - This handles OTP verification for 2FA login
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function verifyOtp()
    {
        // Check if user has a pending OTP session
        $userId = $this->session->getTempdata('otp_user_id');
        $email = $this->session->getTempdata('otp_email');
        $userData = $this->session->getTempdata('otp_user_data');

        if (!$userId || !$email || !$userData) {
            $this->session->setFlashdata('error', 'OTP session expired. Please login again.');
            return redirect()->to(base_url('login'));
        }

        // Handle OTP submission
        if ($this->request->getMethod() === 'POST') {
            $otpCode = $this->request->getPost('otp_code');

            if (!$otpCode) {
                $this->session->setFlashdata('error', 'Please enter the OTP code.');
                return redirect()->to(base_url('verify-otp'));
            }            // Verify OTP
            $result = $this->otpModel->verifyOTP($email, $otpCode, 'login');

            if ($result['success']) {
                // Ensure role_id exists in userData, otherwise fetch fresh user data
                if (!isset($userData['role_id'])) {
                    $freshUser = $this->userModel->find($userData['id']);
                    if ($freshUser) {
                        $userData = $freshUser;
                    }
                }
                
                // Get role name from role_id
                $role = $this->roleModel->find($userData['role_id']);
                $roleName = $role ? strtolower($role['role_name']) : 'student';
                
                // OTP verified - create session and log user in
                $sessionData = [
                    'userID'     => $userData['id'],
                    'name'       => $userData['first_name'] . ' ' . $userData['last_name'],
                    'email'      => $userData['email'],
                    'role'       => $roleName,
                    'isLoggedIn' => true
                ];

                $this->session->set($sessionData);
                
                // Clear temporary OTP session data
                $this->session->removeTempdata('otp_user_id');
                $this->session->removeTempdata('otp_email');
                $this->session->removeTempdata('otp_user_data');

                // Update last login time
                $this->userModel->update($userData['id'], ['last_login' => date('Y-m-d H:i:s')]);

                $this->session->setFlashdata('success', 'Welcome back, ' . $userData['first_name'] . '!');
                return redirect()->to(base_url($roleName . '/dashboard'));
            } else {
                // OTP verification failed
                $this->session->setFlashdata('error', $result['message']);
                return redirect()->to(base_url('verify-otp'));
            }
        }

        // Show OTP verification form
        $data = [
            'email' => $email,
            'userName' => $userData['first_name'] . ' ' . $userData['last_name']
        ];

        return view('auth/verify_otp', $data);
    }

    /**
     * Resend OTP - Resends OTP code to user's email
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function resendOtp()
    {
        // Check if user has a pending OTP session
        $userId = $this->session->getTempdata('otp_user_id');
        $email = $this->session->getTempdata('otp_email');
        $userData = $this->session->getTempdata('otp_user_data');

        if (!$userId || !$email || !$userData) {
            $this->session->setFlashdata('error', 'OTP session expired. Please login again.');
            return redirect()->to(base_url('login'));
        }

        // Resend OTP
        $result = $this->otpModel->resendOTP($userId, $email, 'login');

        if ($result && isset($result['otp_code'])) {
            // Send new OTP via email
            $emailSent = $this->sendOTPEmail($email, $userData['first_name'] . ' ' . $userData['last_name'], $result['otp_code']);

            if ($emailSent) {
                // Refresh temp session data
                $this->session->setTempdata('otp_user_id', $userId, 600);
                $this->session->setTempdata('otp_email', $email, 600);
                $this->session->setTempdata('otp_user_data', $userData, 600);

                $this->session->setFlashdata('success', 'A new OTP has been sent to your email.');
            } else {
                $this->session->setFlashdata('error', 'Failed to send OTP email. Please try again.');
            }
        } else {
            $message = isset($result['message']) ? $result['message'] : 'Please wait before requesting another OTP.';
            $this->session->setFlashdata('error', $message);
        }

        return redirect()->to(base_url('verify-otp'));
    }

    public function studentCourses()
    {
        // 1. AUTHENTICATION CHECK
        if ($this->session->get('isLoggedIn') !== true) {
            $this->session->setFlashdata('error', 'Please login to access this page.');
            return redirect()->to(base_url('login'));
        }

        // 2. ROLE CHECK - Only students can access
        if ($this->session->get('role') !== 'student') {
            $this->session->setFlashdata('error', 'Access denied. This page is for students only.');
            return redirect()->to(base_url('dashboard'));
        }

        $userID = $this->session->get('userID');

        try {
            // 3. GET STUDENT RECORD
            $student = $this->studentModel->getStudentByUserId($userID);
            
            if (!$student) {
                log_message('error', 'Student record not found for user ID: ' . $userID);
                $this->session->setFlashdata('error', 'Student profile not found.');
                return redirect()->to(base_url('dashboard'));
            }

            $studentId = $student['id'];
            
            // 4. GET ENROLLED COURSES (using EnrollmentModel method)
            // This returns courses with: course_offering_id, course_code, course_title, 
            // course_description, credits, section, start_date, end_date, term_name, 
            // academic_year, semester_name, enrollment_date, enrollment_status
            $enrolledCourses = $this->enrollmentModel->getStudentEnrollments($studentId);
            
            // 5. STEP 6 IMPLEMENTATION: Fetch downloadable materials for each enrolled course
            $materialModel = new \App\Models\MaterialModel();
              foreach ($enrolledCourses as &$course) {
                // Get materials for this course offering
                $materials = $materialModel->getOfferingMaterials($course['course_offering_id']);
                $course['materials'] = $materials; // Array of materials with download links
                
                // Add computed fields for view display
                $course['duration_weeks'] = 16; // Default semester duration
                $course['progress'] = 0; // Progress tracking (can be enhanced later)
                
                // Format dates for display
                $course['start_date_formatted'] = !empty($course['start_date']) 
                    ? date('M d, Y', strtotime($course['start_date'])) 
                    : 'TBA';
                $course['end_date_formatted'] = !empty($course['end_date']) 
                    ? date('M d, Y', strtotime($course['end_date'])) 
                    : 'TBA';
                
                // Format enrollment date (MISSING FIELD - Added here)
                $course['enrollment_date_formatted'] = !empty($course['enrollment_date']) 
                    ? date('M d, Y', strtotime($course['enrollment_date'])) 
                    : 'TBA';
                
                // Get instructor information for this course offering
                $instructors = $this->db->table('course_instructors ci')
                    ->select('CONCAT(u.first_name, " ", u.last_name) as name, u.email')
                    ->join('instructors i', 'i.id = ci.instructor_id')
                    ->join('users u', 'u.id = i.user_id')
                    ->where('ci.course_offering_id', $course['course_offering_id'])
                    ->limit(1)
                    ->get()
                    ->getRowArray();
                
                $course['instructor_name'] = $instructors ? $instructors['name'] : 'N/A';
                $course['instructor_email'] = $instructors ? $instructors['email'] : '';
                
                // Create status badge based on enrollment status
                $status = $course['enrollment_status'] ?? 'enrolled';
                $badgeClass = match($status) {
                    'pending' => 'bg-warning',
                    'dropped' => 'bg-danger',
                    'completed' => 'bg-info',
                    default => 'bg-success'
                };
                
                $course['status_badge'] = '<span class="badge ' . $badgeClass . '">' . ucfirst($status) . '</span>';
            }

            // 6. PREPARE VIEW DATA
            $data = [
                'title' => 'My Courses - Student Portal',
                'enrolledCourses' => $enrolledCourses,
                'totalEnrolled' => count($enrolledCourses),
                'totalAvailable' => 0, // Can be calculated if needed
                'student' => $student
            ];

            return view('student/courses', $data);

        } catch (\Exception $e) {
            log_message('error', 'Student courses error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            $this->session->setFlashdata('error', 'An error occurred while loading your courses.');
            return redirect()->to(base_url('dashboard'));
        }    }
    
    /**
     * Student Materials Browser - Display all materials from all enrolled courses
     * 
     * @return mixed View or redirect
     */
    public function studentMaterials()
    {
        // 1. AUTHENTICATION CHECK
        if ($this->session->get('isLoggedIn') !== true) {
            $this->session->setFlashdata('error', 'Please login to access this page.');
            return redirect()->to(base_url('login'));
        }

        // 2. ROLE CHECK - Only students can access
        if ($this->session->get('role') !== 'student') {
            $this->session->setFlashdata('error', 'Access denied. This page is for students only.');
            return redirect()->to(base_url('dashboard'));
        }

        $userID = $this->session->get('userID');

        try {
            // 3. GET STUDENT RECORD
            $student = $this->studentModel->getStudentByUserId($userID);
            
            if (!$student) {
                log_message('error', 'Student record not found for user ID: ' . $userID);
                $this->session->setFlashdata('error', 'Student profile not found.');
                return redirect()->to(base_url('dashboard'));
            }

            $studentId = $student['id'];
            
            // 4. GET ALL ENROLLED COURSES WITH DETAILS
            $enrollments = $this->enrollmentModel->getStudentEnrollments($studentId);
            
            // 5. GET MATERIALS FOR EACH COURSE
            $materialModel = new \App\Models\MaterialModel();
            $allMaterials = [];
            $courseMap = [];
            
            foreach ($enrollments as $enrollment) {
                $offeringId = $enrollment['course_offering_id'];
                
                // Get materials for this course
                $materials = $materialModel->getOfferingMaterials($offeringId);
                
                // Store course info for reference
                $courseMap[$offeringId] = [
                    'course_code' => $enrollment['course_code'],
                    'course_title' => $enrollment['course_title'],
                    'section' => $enrollment['section'],
                    'credits' => $enrollment['credits']
                ];
                
                // Add course info to each material
                foreach ($materials as $material) {
                    $material['course_info'] = $courseMap[$offeringId];
                    $material['course_offering_id'] = $offeringId;
                    $allMaterials[] = $material;
                }
            }
            
            // 6. SORT MATERIALS BY UPLOAD DATE (NEWEST FIRST)
            usort($allMaterials, function($a, $b) {
                return strtotime($b['uploaded_at']) - strtotime($a['uploaded_at']);
            });
            
            // 7. PREPARE VIEW DATA
            $data = [
                'title' => 'Course Materials',
                'materials' => $allMaterials,
                'totalMaterials' => count($allMaterials),
                'totalCourses' => count($enrollments),
                'student' => $student
            ];

            return view('student/materials', $data);

        } catch (\Exception $e) {
            log_message('error', 'Student materials browser error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            $this->session->setFlashdata('error', 'An error occurred while loading materials.');
            return redirect()->to(base_url('student/courses'));
        }
    }
    
    /**
     * Student Course Materials View - Display all materials for a specific course offering
     * 
     * @param int $offeringId Course offering ID
     * @return mixed View or redirect
     */
    public function studentCourseMaterials($offeringId)
    {
        // 1. AUTHENTICATION CHECK
        if ($this->session->get('isLoggedIn') !== true) {
            $this->session->setFlashdata('error', 'Please login to access this page.');
            return redirect()->to(base_url('login'));
        }

        // 2. ROLE CHECK - Only students can access
        if ($this->session->get('role') !== 'student') {
            $this->session->setFlashdata('error', 'Access denied. This page is for students only.');
            return redirect()->to(base_url('dashboard'));
        }

        // 3. VALIDATE OFFERING ID
        if (!$offeringId || !is_numeric($offeringId)) {
            $this->session->setFlashdata('error', 'Invalid course offering.');
            return redirect()->to(base_url('student/courses'));
        }

        $userID = $this->session->get('userID');

        try {
            // 4. GET STUDENT RECORD
            $student = $this->studentModel->getStudentByUserId($userID);
            
            if (!$student) {
                log_message('error', 'Student record not found for user ID: ' . $userID);
                $this->session->setFlashdata('error', 'Student profile not found.');
                return redirect()->to(base_url('dashboard'));
            }

            $studentId = $student['id'];
            
            // 5. VERIFY STUDENT IS ENROLLED IN THIS COURSE
            $isEnrolled = $this->enrollmentModel->isStudentEnrolled($studentId, $offeringId);
            
            if (!$isEnrolled) {
                $this->session->setFlashdata('error', 'You are not enrolled in this course.');
                return redirect()->to(base_url('student/courses'));
            }
            
            // 6. GET COURSE OFFERING DETAILS
            $course = $this->db->table('course_offerings co')
                ->select('
                    co.id as course_offering_id,
                    co.section,
                    co.start_date,
                    co.end_date,
                    c.id as course_id,
                    c.course_code,
                    c.title as course_title,
                    c.description as course_description,
                    c.credits,
                    t.term_name,
                    ay.year_name as academic_year,
                    s.semester_name
                ')
                ->join('courses c', 'c.id = co.course_id')
                ->join('terms t', 't.id = co.term_id')
                ->join('academic_years ay', 'ay.id = t.academic_year_id')
                ->join('semesters s', 's.id = t.semester_id')
                ->where('co.id', $offeringId)
                ->get()
                ->getRowArray();
            
            if (!$course) {
                $this->session->setFlashdata('error', 'Course offering not found.');
                return redirect()->to(base_url('student/courses'));
            }
            
            // 7. FORMAT DATES
            $course['start_date_formatted'] = !empty($course['start_date']) 
                ? date('M d, Y', strtotime($course['start_date'])) 
                : 'TBA';
            $course['end_date_formatted'] = !empty($course['end_date']) 
                ? date('M d, Y', strtotime($course['end_date'])) 
                : 'TBA';
            
            // 8. GET INSTRUCTOR INFORMATION
            $instructors = $this->db->table('course_instructors ci')
                ->select('CONCAT(u.first_name, " ", u.last_name) as name, u.email')
                ->join('instructors i', 'i.id = ci.instructor_id')
                ->join('users u', 'u.id = i.user_id')
                ->where('ci.course_offering_id', $offeringId)
                ->limit(1)
                ->get()
                ->getRowArray();
            
            $course['instructor_name'] = $instructors ? $instructors['name'] : 'N/A';
            $course['instructor_email'] = $instructors ? $instructors['email'] : '';
            
            // 9. GET COURSE PROGRESS (placeholder - can be calculated from assignments)
            $course['progress'] = 0;
            
            // 10. GET ALL MATERIALS FOR THIS COURSE OFFERING
            $materialModel = new \App\Models\MaterialModel();
            $materials = $materialModel->getOfferingMaterials($offeringId);
            
            // 11. PREPARE VIEW DATA
            $data = [
                'title' => $course['course_title'] . ' - Materials',
                'course' => $course,
                'materials' => $materials,
                'totalMaterials' => count($materials),
                'student' => $student
            ];

            return view('student/course_materials', $data);

        } catch (\Exception $e) {
            log_message('error', 'Student course materials error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            $this->session->setFlashdata('error', 'An error occurred while loading course materials.');
            return redirect()->to(base_url('student/courses'));
        }
    }
        /**
     * AJAX Search Student Courses - Search enrolled courses via AJAX
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface JSON response with search results
     */
    public function searchStudentCourses()
    {
        // 1. AUTHENTICATION CHECK
        if ($this->session->get('isLoggedIn') !== true) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Authentication required'
            ]);
        }

        // 2. ROLE CHECK - Only students can access
        if ($this->session->get('role') !== 'student') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied'
            ]);
        }

        $userID = $this->session->get('userID');

        try {
            // 3. GET SEARCH TERM
            $searchTerm = $this->request->getGet('search') ?? '';
            
            if (empty($searchTerm)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Search term is required'
                ]);
            }

            // 4. GET STUDENT RECORD
            $student = $this->studentModel->getStudentByUserId($userID);
            
            if (!$student) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Student profile not found'
                ]);
            }

            $studentId = $student['id'];
            
            // 5. SEARCH ENROLLED COURSES
            $searchResults = $this->db->table('enrollments e')
                ->select('
                    co.id as course_offering_id,
                    c.course_code,
                    c.title as course_title,
                    c.description as course_description,
                    c.credits,
                    co.section,
                    co.start_date,
                    co.end_date,
                    t.term_name,
                    ay.year_name as academic_year,
                    s.semester_name,
                    e.enrollment_date,
                    e.enrollment_status,
                    CONCAT(u.first_name, " ", u.last_name) as instructor_name,
                    u.email as instructor_email
                ')
                ->join('course_offerings co', 'co.id = e.course_offering_id')
                ->join('courses c', 'c.id = co.course_id')
                ->join('terms t', 't.id = co.term_id')
                ->join('academic_years ay', 'ay.id = t.academic_year_id')
                ->join('semesters s', 's.id = t.semester_id')
                ->join('course_instructors ci', 'ci.course_offering_id = co.id', 'left')
                ->join('instructors i', 'i.id = ci.instructor_id', 'left')
                ->join('users u', 'u.id = i.user_id', 'left')
                ->where('e.student_id', $studentId)
                ->groupStart()
                    ->like('c.course_code', $searchTerm)
                    ->orLike('c.title', $searchTerm)
                    ->orLike('c.description', $searchTerm)
                    ->orLike('co.section', $searchTerm)
                    ->orLike('t.term_name', $searchTerm)
                    ->orLike('ay.year_name', $searchTerm)
                    ->orLike('s.semester_name', $searchTerm)
                    ->orLike('CONCAT(u.first_name, " ", u.last_name)', $searchTerm)
                ->groupEnd()
                ->orderBy('e.enrollment_date', 'DESC')
                ->get()
                ->getResultArray();

            // 6. FORMAT RESULTS
            $materialModel = new \App\Models\MaterialModel();
            
            foreach ($searchResults as &$course) {
                // Get materials for this course
                $materials = $materialModel->getOfferingMaterials($course['course_offering_id']);
                $course['materials'] = $materials;
                
                // Format dates
                $course['start_date_formatted'] = !empty($course['start_date']) 
                    ? date('M d, Y', strtotime($course['start_date'])) 
                    : 'TBA';
                $course['end_date_formatted'] = !empty($course['end_date']) 
                    ? date('M d, Y', strtotime($course['end_date'])) 
                    : 'TBA';
                $course['enrollment_date_formatted'] = !empty($course['enrollment_date']) 
                    ? date('M d, Y', strtotime($course['enrollment_date'])) 
                    : 'TBA';
                
                // Create status badge
                $status = $course['enrollment_status'] ?? 'enrolled';
                $badgeClass = match($status) {
                    'pending' => 'bg-warning',
                    'dropped' => 'bg-danger',
                    'completed' => 'bg-info',
                    default => 'bg-success'
                };
                
                $course['status_badge'] = '<span class="badge ' . $badgeClass . '">' . ucfirst($status) . '</span>';
                $course['progress'] = 0;
            }

            // 7. RETURN JSON RESPONSE
            return $this->response->setJSON([
                'success' => true,
                'count' => count($searchResults),
                'search_term' => $searchTerm,
                'data' => $searchResults
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Student course search error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while searching courses'
            ]);
        }
    }
}