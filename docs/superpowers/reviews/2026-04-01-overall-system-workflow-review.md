# MGOD-LMS Overall System Workflow Review

## Strengths
- Role-aware access controls are consistently enforced at controller entry points and assignment operations (`app/Controllers/Assignment.php:145`, `app/Controllers/Assignment.php:441`, `app/Controllers/Enrollment.php:1358`).
- Core auth and registration logic uses transactional boundaries and validation checks (`app/Controllers/Auth.php:134`, `app/Controllers/Auth.php:137`, `app/Controllers/Auth.php:205`).
- Model-layer guardrails are present via `allowedFields` and `validationRules` for key data entities (`app/Models/AssignmentModel.php:15`, `app/Models/SubmissionModel.php:15`, `app/Models/SubmissionModel.php:40`).
- File handling includes extension checks, controlled upload directories, enrollment checks, and realpath-based traversal protection in material endpoints (`app/Controllers/Material.php:92`, `app/Controllers/Material.php:346`, `app/Controllers/Material.php:361`, `app/Controllers/Material.php:444`).

## Workflow Map

| Workflow | Route | Controller::method | Models | View/API | Migration/Schema Notes |
|---|---|---|---|---|---|
| Authentication lifecycle | `/register`, `/login`, `/verify-email/(:any)`, `/verify-otp`, `/resend-otp` (`app/Config/Routes.php:12-29`) | `Auth::register -> Auth::verifyEmail -> Auth::login -> Auth::verifyOtp` (`app/Controllers/Auth.php:71`, `813`, `262`, `951`) | `UserModel`, `RoleModel`, `EmailVerificationModel`, `OtpModel`, `CaptchaModel` (`app/Controllers/Auth.php:55-67`) | `app/Views/auth/register.php`, `app/Views/auth/login.php`, `app/Views/auth/verify_otp.php` | `users` (`2025-08-19-130510_CreateUsersTable.php`), `otp_verifications` (`2025-11-28-000003_CreateOtpVerificationsTable.php`), `email_verifications` (`2025-11-30-000001_CreateEmailVerificationsTable.php`), `captcha` (`2026-02-19-121829_CreateCaptchaTable.php`) |
| Admin management workflows | `/admin/manage_users`, `/admin/manage_departments`, `/admin/manage_courses`, `/admin/manage_offerings`, `/admin/manage_assignments`, `/admin/manage_enrollments` (`app/Config/Routes.php:40-76`) | `User::manageUsers`, `Department::manageDepartments`, `Course::manageCourses`, `CourseOfferings::manageOfferings`, `Assignment::manageAssignments`, `Enrollment::manageEnrollments` | `UserModel`, `CourseModel`, `CourseOfferingModel`, `AssignmentModel`, `EnrollmentModel` | Admin management views under `app/Views/admin/*` | Foundational entities in course/enrollment/assignment migrations (`2025-08-19-130519`, `130524`, `130527`, `130536`) |
| Teacher delivery workflows | `/teacher/courses`, `/teacher/assignments`, `/teacher/submissions`, `/teacher/enroll_student`, `/teacher/enrolled_students` (`app/Config/Routes.php:82-87`, `138-142`) | `CourseInstructors::teacherCourses`, `Assignment::teacherAssignments`, `Assignment::viewSubmissions`, `Enrollment::teacherEnrollStudent`, `Enrollment::teacherEnrolledStudents` | `CourseInstructorModel`, `AssignmentModel`, `SubmissionModel`, `EnrollmentModel` | `app/Views/teacher/courses.php`, `assignments.php`, `view_submissions.php`, `enroll_student.php`, `enrolled_students.php` | `course_instructors` (`2025-08-19-130526_CreateCourseInstructorsTable.php`), `submissions` (`2025-08-19-130540_CreateSubmissionsTable.php`) |
| Student assignment lifecycle | `/student/assignments`, `/student/assignment/(:num)`, `/student/submit_assignment`, `/submission/download/(:num)` (`app/Config/Routes.php:144-148`) | `Submission::studentAssignments -> Submission::viewAssignment -> Submission::submit -> Submission::downloadSubmission` (`app/Controllers/Submission.php:36`, `100`, `228`, `421`) | `SubmissionModel`, `AssignmentModel`, `EnrollmentModel` | `app/Views/student/assignments.php`, `assignment_detail.php`, `view_assignment.php` | Submission state and grading fields in submissions table (`2025-08-19-130540_CreateSubmissionsTable.php`, `2025-12-14-000001_AddGradingFieldsToSubmissions.php`) |
| Student material access | `/student/materials`, `/student/course/(:num)/materials`, `/material/download/(:num)`, `/material/view/(:num)` (`app/Config/Routes.php:104-105`, `123-124`) | `Auth::studentMaterials`, `Auth::studentCourseMaterials`, `Material::download`, `Material::view` | `MaterialModel`, `EnrollmentModel` | `app/Views/student/materials.php`, `course_materials.php` | `materials` table (`2025-10-13-133560_CreateMaterialsTable.php`) |
| Notifications and cron reminders | `/notifications*`, `/cron/notify-overdue-assignments`, `/cron/notify-upcoming-deadlines` (`app/Config/Routes.php:127-135`, `151-154`) | `Notifications::*`, `Submission::notifyOverdueAssignments`, `Submission::notifyUpcomingDeadlines` (`app/Controllers/Notifications.php`, `app/Controllers/Submission.php:520`, `587`) | `NotificationModel`, `SubmissionModel`, `EnrollmentModel`, `AssignmentModel` | JSON notification API and scheduled processing paths | `notifications` table lacks explicit `type` column (`2025-10-21-153240_CreateNotificationsTable.php`) |

### Authentication Lifecycle
- Route coverage: `/register`, `/verify-email/(:any)`, `/login`, `/verify-otp`, `/resend-otp` (`app/Config/Routes.php:12-29`).
- Controller chain: `Auth::register -> Auth::verifyEmail -> Auth::login -> Auth::verifyOtp` (`app/Controllers/Auth.php:71`, `813`, `262`, `951`).
- Model interactions: `UserModel`, `EmailVerificationModel`, `OtpModel`, `CaptchaModel`, `RoleModel` (`app/Controllers/Auth.php:55-67`).
- Output surfaces: `app/Views/auth/register.php`, `app/Views/auth/login.php`, `app/Views/auth/verify_otp.php`.

### Admin Workflows
- Users, departments, terms, courses, offerings, instructors, enrollments, programs/curriculum, and assignments are directly routed and controller-backed (`app/Config/Routes.php:40-76`, `58-64`).
- CRUD and search surfaces are present with dedicated endpoints for most entities (`app/Config/Routes.php:160-169`).

### Teacher Workflows
- Assigned courses and enrollment support are available through teacher routes (`app/Config/Routes.php:82-87`, `89`).
- Assignment lifecycle includes create/publish/edit and grading submission flow (`app/Config/Routes.php:138-142`, `app/Controllers/Assignment.php:39`, `330`, `415`).

### Student Workflows
- Student assignment browsing, viewing, and submission flow is end-to-end mapped (`app/Config/Routes.php:144-147`, `app/Controllers/Submission.php:36`, `100`, `228`).
- Student course and materials discovery flows are routed and view-backed (`app/Config/Routes.php:103-105`, `app/Controllers/Auth.php:1065`, `1171`, `1259`).

### File and Material Delivery
- Upload paths for admin/teacher are defined (`app/Config/Routes.php:108-111`) and file handling in material upload validates extension/size and stores in writeable upload directories (`app/Controllers/Material.php:92-113`, `132`).
- Download/view flows for materials include enrollment and path traversal protections (`app/Controllers/Material.php:346`, `360-364`, `429`, `443-447`).

## Findings by Severity

### Critical (must fix)
- `app/Controllers/Submission.php::downloadSubmission` uses `WRITEPATH . 'uploads/' . $submission['file_path']` without `realpath` boundary validation (`app/Controllers/Submission.php:464-470`).  
  Impact: potential path traversal if file path data is compromised.  
  Recommended fix: mirror `Material::download` hardening (`realpath` + prefix check) before serving file.

### Important (should fix)
- `app/Models/NotificationModel::createNotification` accepts `type` but schema and persistence do not store it (`app/Models/NotificationModel.php:134-143`, `app/Database/Migrations/2025-10-21-153240_CreateNotificationsTable.php:24-38`).  
  Impact: typed filtering/UI semantics degrade and `getNotificationsByType` cannot truly filter (`app/Models/NotificationModel.php:167-173`).  
  Recommended fix: add `type` column migration and persist/filter by it.
- Cron reminder endpoints are exposed as both GET and CLI (`app/Config/Routes.php:151-154`).  
  Impact: accidental/public triggering risks.  
  Recommended fix: restrict to CLI or protect HTTP invocations via secret/auth layer.
- OTP/email verification records include expiry fields but no observed cleanup workflow (`app/Database/Migrations/2025-11-28-000003_CreateOtpVerificationsTable.php:40-43`, `app/Database/Migrations/2025-11-30-000001_CreateEmailVerificationsTable.php:35-38`).  
  Impact: long-term table growth and degraded lookup performance.  
  Recommended fix: scheduled purge task for expired/used verification records.

### Suggestions (nice to have)
- Convert status literals in submission lifecycle to shared constants/enums across model/controller (`app/Models/SubmissionModel.php:48`, `app/Controllers/Submission.php`).
- Add audit logging for enrollment state transitions in approval flow (`app/Controllers/Enrollment.php:1394-1410`).
- Consider normalizing duplicate material download route declarations (`app/Config/Routes.php:120`, `123`) to reduce ambiguity.

## Remediation Roadmap
1. Critical-first: harden `Submission::downloadSubmission` with canonical path validation and write-path boundary checks.
2. Important follow-up: add notification `type` storage and filtering, then secure cron route exposure.
3. Reliability/data hygiene: introduce OTP/email verification cleanup schedule and add enrollment transition audit trail.
4. Consistency pass: reduce route duplication and formalize submission status constants.

## Plan-Deviation Notes
- Beneficial deviation: due to environment cache-write failures, `php spark routes` command output could not be collected directly; route evidence was captured statically from `app/Config/Routes.php` line-level references.
- Beneficial deviation: findings were adjusted to current code state where `Material::download` already includes enrollment and path traversal protections; the analogous gap remains in `Submission::downloadSubmission`.
- No scope-expanding deviations were introduced beyond requested workflow-first architecture review and prioritization.

## Verification Log
- `php spark routes`: BLOCKED in this environment (`Cache unable to write to writable/cache/` in `writable/logs/log-2026-04-01.log`); route evidence gathered via static route file analysis.
- `vendor\bin\phpunit --filter HealthTest`: FAIL (pre-existing environment issue: writable cache permission/writeability).
- `vendor\bin\phpunit`: FAIL (same pre-existing writable cache issue, 5 errors).
