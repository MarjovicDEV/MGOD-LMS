<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get(from: '/', to: 'Home::index');
$routes->get(from: '/about', to: 'Home::about');
$routes->get(from: '/contact', to: 'Home::contact');

$routes->get(from: '/register', to: 'Auth::register');
$routes->post(from: '/register', to: 'Auth::register');
$routes->get(from: '/login', to: 'Auth::login');
$routes->post(from: '/login', to: 'Auth::login');
$routes->get(from: '/logout', to: 'Auth::logout');

// Captcha routes
$routes->get('/captcha/refresh', 'CaptchaController::refresh');
$routes->get('/captcha/image/(:segment)', 'CaptchaController::image/$1');

// Email verification routes
$routes->get('/verify-email/(:any)', 'Auth::verifyEmail/$1');
$routes->post('/resend-verification', 'Auth::resendVerification');

// OTP verification routes (2FA)
$routes->get('/verify-otp', 'Auth::verifyOtp');
$routes->post('/verify-otp', 'Auth::verifyOtp');
$routes->post('/resend-otp', 'Auth::resendOtp');

$routes->get(from: '/dashboard', to: 'Auth::dashboard');
$routes->post(from: '/dashboard', to: 'Auth::dashboard');

// Role-based unified dashboard routes
$routes->get(from: '/admin/dashboard', to: 'Auth::dashboard');
$routes->get(from: '/teacher/dashboard', to: 'Auth::dashboard');
$routes->get(from: '/student/dashboard', to: 'Auth::dashboard');

// Admin management routes
$routes->get(from: '/admin/manage_users', to: 'User::manageUsers');
$routes->post(from: '/admin/manage_users', to: 'User::manageUsers');
$routes->get(from: '/admin/manage_departments', to: 'Department::manageDepartments');
$routes->post(from: '/admin/manage_departments', to: 'Department::manageDepartments');
$routes->get(from: '/admin/manage_assignment_types', to: 'AssignmentType::manageAssignmentTypes');
$routes->post(from: '/admin/manage_assignment_types', to: 'AssignmentType::manageAssignmentTypes');
$routes->get(from: '/admin/manage_grading_periods', to: 'GradingPeriod::manageGradingPeriods');
$routes->post(from: '/admin/manage_grading_periods', to: 'GradingPeriod::manageGradingPeriods');
$routes->get(from: '/admin/manage_grade_components', to: 'GradeComponent::manageGradeComponents');
$routes->post(from: '/admin/manage_grade_components', to: 'GradeComponent::manageGradeComponents');
$routes->get(from: '/admin/manage_terms', to: 'Term::manageTerms');
$routes->post(from: '/admin/manage_terms', to: 'Term::manageTerms');
$routes->get(from: '/admin/manage_courses', to: 'Course::manageCourses');
$routes->post(from: '/admin/manage_courses', to: 'Course::manageCourses');
$routes->get(from: '/admin/manage_prerequisites', to: 'CoursePrerequisite::managePrerequisites');
$routes->post(from: '/admin/manage_prerequisites', to: 'CoursePrerequisite::managePrerequisites');

// Admin Assignment Management routes
$routes->get('/admin/manage_assignments', 'Assignment::manageAssignments');
$routes->get('/admin/view_assignment/(:num)', 'Assignment::adminViewAssignment/$1');
$routes->get('/admin/create_assignment', 'Assignment::adminCreateAssignment');
$routes->post('/admin/store_assignment', 'Assignment::adminStoreAssignment');
$routes->get('/admin/edit_assignment/(:num)', 'Assignment::adminEditAssignment/$1');
$routes->post('/admin/update_assignment/(:num)', 'Assignment::adminUpdateAssignment/$1');
$routes->get('/admin/delete_assignment/(:num)', 'Assignment::adminDeleteAssignment/$1');
$routes->get(from: '/admin/manage_offerings', to: 'CourseOfferings::manageOfferings');
$routes->post(from: '/admin/manage_offerings', to: 'CourseOfferings::manageOfferings');
$routes->get(from: '/admin/manage_courses_schedule', to: 'CourseSchedules::manageSchedules');
$routes->post(from: '/admin/manage_courses_schedule', to: 'CourseSchedules::manageSchedules');
$routes->get(from: '/admin/manage_course_instructors', to: 'CourseInstructors::manageInstructors');
$routes->post(from: '/admin/manage_course_instructors', to: 'CourseInstructors::manageInstructors');
$routes->get(from: '/admin/manage_programs', to: 'Program::managePrograms');
$routes->post(from: '/admin/manage_programs', to: 'Program::managePrograms');
$routes->get(from: '/admin/manage_curriculum', to: 'Program::manageCurriculum');
$routes->post(from: '/admin/manage_curriculum', to: 'Program::manageCurriculum');
$routes->get(from: '/admin/manage_enrollments', to: 'Enrollment::manageEnrollments');
$routes->post(from: '/admin/manage_enrollments', to: 'Enrollment::manageEnrollments');

// Course enrollment routes
$routes->post(from: '/course/enroll', to: 'Course::enroll');

// Teacher course management routes
$routes->get(from: '/teacher/courses', to: 'CourseInstructors::teacherCourses');
$routes->post(from: '/teacher/courses', to: 'CourseInstructors::teacherCourses');
$routes->get(from: '/teacher/enroll_student', to: 'Enrollment::teacherEnrollStudent');
$routes->post(from: '/teacher/enroll_student', to: 'Enrollment::teacherEnrollStudent');
$routes->get(from: '/teacher/enrolled_students', to: 'Enrollment::teacherEnrolledStudents');

// AJAX endpoint for teacher bulk enrollment
$routes->post('/teacher/ajax_enroll_students', 'Enrollment::ajaxEnrollStudents');

// Enrollment approval routes
$routes->post('/enrollment/respond', 'Enrollment::respondToEnrollment');
$routes->get('/enrollment/pending', 'Enrollment::getPendingEnrollments');

// AJAX course search routes
$routes->post('/student/search_courses', 'Auth::searchStudentCourses');
$routes->get('/student/search_courses', 'Auth::searchStudentCourses');
$routes->post('/teacher/search_courses', 'CourseInstructors::searchTeacherCourses');
$routes->get('/teacher/search_courses', 'CourseInstructors::searchTeacherCourses');


// Student course management routes
$routes->get(from: '/student/courses', to: 'Auth::studentCourses');
$routes->get('/student/materials', 'Auth::studentMaterials');
$routes->get('/student/course/(:num)/materials', 'Auth::studentCourseMaterials/$1');

// Material management routes
$routes->get('/admin/course/(:num)/upload', 'Material::upload/$1');
$routes->post('/admin/course/(:num)/upload', 'Material::upload/$1');
$routes->get('/teacher/course/(:num)/upload', 'Material::upload/$1');
$routes->post('/teacher/course/(:num)/upload', 'Material::upload/$1');

// Teacher course view routes (placeholders for future implementation)
$routes->get('/teacher/course/(:num)/view', 'CourseInstructors::viewCourse/$1');

// Legacy material upload route 
$routes->get('/material/upload/(:num)', 'Material::upload/$1');
$routes->post('/material/upload/(:num)', 'Material::upload/$1');
$routes->get(from: '/material/delete/(:num)', to: 'Material::delete/$1');
$routes->get(from: '/material/download/(:num)', to: 'Material::download/$1');

// Material download routes (with enrollment check)
$routes->get('/material/download/(:num)', 'Material::download/$1');
$routes->get('/material/view/(:num)', 'Material::view/$1');

// Notification API routes
$routes->get('/notifications', 'Notifications::get');
$routes->get('/notifications/unread', 'Notifications::getUnread');
$routes->get('/notifications/stats', 'Notifications::getStats');
$routes->get('/notifications/type/(:segment)', 'Notifications::getByType/$1');
$routes->post('/notifications/mark_read/(:num)', 'Notifications::mark_as_read/$1');
$routes->post('/notifications/mark_all_read', 'Notifications::markAllAsRead');
$routes->post('/notifications/hide/(:num)', 'Notifications::hide/$1');
$routes->post('/notifications/clear_all', 'Notifications::clearAll');
$routes->post('/notifications/create', 'Notifications::create');

// Teacher Assignment routes
$routes->get('/teacher/assignments', 'Assignment::teacherAssignments');
$routes->post('/teacher/assignments', 'Assignment::teacherAssignments');
$routes->get('/teacher/submissions', 'Assignment::viewSubmissions');
$routes->post('/teacher/grade_submission', 'Assignment::gradeSubmission');

// Student Assignment and Submission routes
$routes->get('/student/assignments', 'Submission::studentAssignments');
$routes->get('/student/assignment/(:num)', 'Submission::viewAssignment/$1');
$routes->post('/student/submit_assignment', 'Submission::submit');
$routes->get('/student/download_attachment/(:num)', 'Submission::downloadAttachment/$1');
$routes->get('/submission/download/(:num)', 'Submission::downloadSubmission/$1');

// Automated Notification Routes (for Cron Jobs/Schedulers)
$routes->get('/cron/notify-overdue-assignments', 'Submission::notifyOverdueAssignments');
$routes->get('/cron/notify-upcoming-deadlines', 'Submission::notifyUpcomingDeadlines');
$routes->cli('/cron/notify-overdue-assignments', 'Submission::notifyOverdueAssignments');
$routes->cli('/cron/notify-upcoming-deadlines', 'Submission::notifyUpcomingDeadlines');

// API routes for dynamic data
$routes->get('/api/programs/by-department/(:num)', 'User::getProgramsByDepartment/$1');

// Search routes for Admin functionality
$routes->get('/admin/search/courses', 'Course::search');
$routes->post('/admin/search/courses', 'Course::search');
$routes->get('/admin/search/terms', 'Term::search');
$routes->post('/admin/search/terms', 'Term::search');
$routes->get('/admin/search/departments', 'Department::search');
$routes->post('/admin/search/departments', 'Department::search');
$routes->get('/admin/search/programs', 'Program::search');
$routes->post('/admin/search/programs', 'Program::search');
$routes->get('/admin/search/users', 'User::search');
$routes->post('/admin/search/users', 'User::search');
$routes->get('student/search/courses', 'Auth::searchStudentCourses');

// =============================================================================
// GRADEBOOK ROUTES
// =============================================================================

// Gradebook Routes - Student
$routes->get('student/gradebook', 'Gradebook::studentIndex', ['filter' => 'auth']);
$routes->get('student/gradebook/course/(:num)', 'Gradebook::courseDetails/$1', ['filter' => 'auth']);
$routes->get('student/gradebook/export/pdf/(:num)', 'Gradebook::exportPDF/$1', ['filter' => 'auth']);
$routes->get('student/gradebook/export/excel/(:num)', 'Gradebook::exportExcel/$1', ['filter' => 'auth']);

// Gradebook Routes - Teacher
$routes->get('teacher/gradebook', 'Gradebook::teacherIndex', ['filter' => 'auth']);
$routes->get('teacher/gradebook/entry/(:num)', 'Gradebook::gradeEntry/$1', ['filter' => 'auth']);
$routes->post('teacher/gradebook/bulk-update', 'Gradebook::bulkUpdate', ['filter' => 'auth']);
$routes->get('teacher/gradebook/import/(:num)', 'Gradebook::csvImportForm/$1', ['filter' => 'auth']);
$routes->post('teacher/gradebook/import/(:num)', 'Gradebook::csvImportProcess/$1', ['filter' => 'auth']);
$routes->post('teacher/gradebook/override/(:num)', 'Gradebook::saveOverride/$1', ['filter' => 'auth']);
$routes->get('teacher/gradebook/export/(:num)', 'Gradebook::exportClassGrades/$1', ['filter' => 'auth']);

// Gradebook Routes - Admin
$routes->get('admin/gradebook/analytics', 'Gradebook::analytics', ['filter' => 'auth']);
$routes->get('admin/gradebook/audit', 'Gradebook::auditTrail', ['filter' => 'auth']);
$routes->get('admin/gradebook/overview', 'Gradebook::systemOverview', ['filter' => 'auth']);
$routes->get('admin/gradebook/student-grades/(:num)', 'Gradebook::getStudentGrades/$1', ['filter' => 'auth']);
$routes->post('admin/gradebook/recalculate/(:num)', 'Gradebook::recalculateCourseGrades/$1', ['filter' => 'auth']);
