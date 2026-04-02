<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AssignmentControllerEnrollmentJoinTest extends CIUnitTestCase
{
    public function testAssignmentControllerUsesStudentsToReachUserRecord(): void
    {
        $source = file_get_contents(APPPATH . 'Controllers/Assignment.php');

        $this->assertNotFalse($source);
        $this->assertStringNotContainsString("->join('users u', 'u.id = e.student_id')", $source);
        $this->assertStringContainsString("->join('students st', 'st.id = e.student_id')", $source);
        $this->assertStringContainsString("->join('users u', 'u.id = st.user_id')", $source);
    }
}
