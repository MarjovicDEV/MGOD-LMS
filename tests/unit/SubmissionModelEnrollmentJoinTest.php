<?php

use App\Models\SubmissionModel;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * @internal
 */
final class SubmissionModelEnrollmentJoinTest extends CIUnitTestCase
{
    private \CodeIgniter\Database\BaseConnection $testDb;
    private string $p;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testDb = Database::connect('tests');
        $this->p      = $this->testDb->getPrefix();
        $this->resetSchema();
        $this->seedData();
    }

    public function testStudentSubmissionQueriesUseEnrollmentLink(): void
    {
        $model = new SubmissionModel();

        $details = $model->getSubmissionWithDetails(30);
        $this->assertNotNull($details);
        $this->assertSame('Alice Student', $this->normalizeName($details['student_name']));

        $studentSubmission = $model->getStudentSubmission(20, 10);
        $this->assertNotNull($studentSubmission);
        $this->assertSame(30, (int) $studentSubmission['id']);

        $assignmentSubmissions = $model->getAssignmentSubmissions(20);
        $this->assertCount(1, $assignmentSubmissions);
        $this->assertSame('Alice Student', $this->normalizeName($assignmentSubmissions[0]['student_name']));

        $pending = $model->getPendingSubmissions();
        $this->assertCount(1, $pending);
        $this->assertSame('Alice Student', $this->normalizeName($pending[0]['student_name']));

        $courseSubmissions = $model->getStudentCourseSubmissions(10, 50);
        $this->assertCount(1, $courseSubmissions);
    }

    public function testStudentStatsAndLateSubmissionsUseEnrollmentLink(): void
    {
        $model = new SubmissionModel();

        $stats = $model->getStudentStats(10, 50);
        $this->assertSame(1, (int) $stats->total_submissions);
        $this->assertSame(0, (int) $stats->graded_count);

        $late = $model->getLateSubmissions(20);
        $this->assertCount(1, $late);
        $this->assertSame('Alice Student', $this->normalizeName($late[0]['student_name']));
    }

    public function testSubmitAssignmentResolvesEnrollmentFromStudentAndCourse(): void
    {
        $model = new SubmissionModel();

        $ok = $model->submitAssignment(20, 10, [
            'submission_text' => 'new answer',
            'file_path'       => null,
        ]);

        $this->assertTrue((bool) $ok);

        $rows = $this->testDb->query("SELECT * FROM {$this->p}submissions WHERE assignment_id = 20 AND enrollment_id = 40")->getResultArray();
        $this->assertCount(1, $rows);
        $this->assertSame('new answer', $rows[0]['submission_text']);
    }

    private function resetSchema(): void
    {
        foreach ([
            'submissions',
            'assignments',
            'assignment_types',
            'enrollments',
            'students',
            'users',
            'course_offerings',
            'courses',
        ] as $table) {
            $this->testDb->query("DROP TABLE IF EXISTS {$this->p}{$table}");
        }

        $this->testDb->query("CREATE TABLE {$this->p}users (
            id INTEGER PRIMARY KEY,
            first_name TEXT,
            middle_name TEXT,
            last_name TEXT,
            user_code TEXT
        )");

        $this->testDb->query("CREATE TABLE {$this->p}students (
            id INTEGER PRIMARY KEY,
            user_id INTEGER
        )");

        $this->testDb->query("CREATE TABLE {$this->p}courses (
            id INTEGER PRIMARY KEY,
            course_code TEXT,
            title TEXT
        )");

        $this->testDb->query("CREATE TABLE {$this->p}course_offerings (
            id INTEGER PRIMARY KEY,
            course_id INTEGER,
            section TEXT
        )");

        $this->testDb->query("CREATE TABLE {$this->p}assignment_types (
            id INTEGER PRIMARY KEY,
            type_name TEXT
        )");

        $this->testDb->query("CREATE TABLE {$this->p}assignments (
            id INTEGER PRIMARY KEY,
            course_offering_id INTEGER,
            assignment_type_id INTEGER,
            title TEXT,
            max_score REAL,
            due_date TEXT
        )");

        $this->testDb->query("CREATE TABLE {$this->p}enrollments (
            id INTEGER PRIMARY KEY,
            student_id INTEGER,
            course_offering_id INTEGER,
            enrollment_status TEXT
        )");

        $this->testDb->query("CREATE TABLE {$this->p}submissions (
            id INTEGER PRIMARY KEY,
            enrollment_id INTEGER,
            assignment_id INTEGER,
            submission_text TEXT,
            file_path TEXT,
            submitted_at TEXT,
            attempt_number INTEGER,
            is_late INTEGER,
            status TEXT,
            score REAL,
            feedback TEXT,
            graded_by INTEGER,
            graded_at TEXT,
            created_at TEXT,
            updated_at TEXT
        )");
    }

    private function seedData(): void
    {
        $this->testDb->query("INSERT INTO {$this->p}users (id, first_name, middle_name, last_name, user_code) VALUES (1, 'Alice', '', 'Student', 'STU001')");
        $this->testDb->query("INSERT INTO {$this->p}students (id, user_id) VALUES (10, 1)");
        $this->testDb->query("INSERT INTO {$this->p}courses (id, course_code, title) VALUES (60, 'MATH101', 'Mathematics')");
        $this->testDb->query("INSERT INTO {$this->p}course_offerings (id, course_id, section) VALUES (50, 60, 'A')");
        $this->testDb->query("INSERT INTO {$this->p}assignment_types (id, type_name) VALUES (70, 'Quiz')");
        $this->testDb->query("INSERT INTO {$this->p}assignments (id, course_offering_id, assignment_type_id, title, max_score, due_date) VALUES (20, 50, 70, 'Quiz 1', 100, '2025-01-01 00:00:00')");
        $this->testDb->query("INSERT INTO {$this->p}enrollments (id, student_id, course_offering_id, enrollment_status) VALUES (40, 10, 50, 'enrolled')");
        $this->testDb->query("INSERT INTO {$this->p}submissions (id, enrollment_id, assignment_id, submission_text, submitted_at, attempt_number, is_late, status, created_at, updated_at) VALUES (30, 40, 20, 'answer', '2025-01-02 00:00:00', 1, 1, 'submitted', '2025-01-02 00:00:00', '2025-01-02 00:00:00')");
    }

    private function normalizeName(string $value): string
    {
        return trim((string) preg_replace('/\s+/', ' ', $value));
    }
}
