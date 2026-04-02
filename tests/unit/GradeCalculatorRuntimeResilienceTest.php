<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class GradeCalculatorRuntimeResilienceTest extends CIUnitTestCase
{
    public function testGradeCalculatorUsesCentralResolverInFinalAndRecalculatePaths(): void
    {
        $source = file_get_contents(APPPATH . 'Libraries/GradeCalculator.php');

        $this->assertNotFalse($source);
        $this->assertStringContainsString('private function resolveGradingPeriodsForEnrollment', $source);
        $this->assertStringContainsString('$gradingPeriods = $this->resolveGradingPeriodsForEnrollment((int) $enrollmentId);', $source);
        $this->assertSame(2, substr_count($source, 'resolveGradingPeriodsForEnrollment((int) $enrollmentId)'));
    }

    public function testGradeCalculatorIncludesAssignmentAndGradebookFallbackQueries(): void
    {
        $source = file_get_contents(APPPATH . 'Libraries/GradeCalculator.php');

        $this->assertNotFalse($source);
        $this->assertStringContainsString("->join('assignments a', 'a.grading_period_id = gp.id')", $source);
        $this->assertStringContainsString("->join('gradebook_entries ge', 'ge.grading_period_id = gp.id')", $source);
        $this->assertStringContainsString("->where('a.course_offering_id', (int) \$enrollment['course_offering_id'])", $source);
        $this->assertStringContainsString("->where('ge.enrollment_id', (int) \$enrollmentId)", $source);
    }

    public function testGradeCalculatorReturnsExplicitFailureMessageWhenNoPeriodsResolved(): void
    {
        $source = file_get_contents(APPPATH . 'Libraries/GradeCalculator.php');

        $this->assertNotFalse($source);
        $this->assertStringContainsString('No grading periods configured for enrollment', $source);
        $this->assertStringContainsString('GradeCalculator recalculation failed: no grading periods resolved for enrollment', $source);
    }

    public function testGradebookAuditTrailDoesNotQueryUsersRoleColumn(): void
    {
        $source = file_get_contents(APPPATH . 'Controllers/Gradebook.php');

        $this->assertNotFalse($source);
        $this->assertStringNotContainsString("->where('role', 'teacher')", $source);
        $this->assertStringContainsString("->join('roles r', 'r.id = u.role_id')", $source);
        $this->assertStringContainsString("->where('r.role_name', 'teacher')", $source);
    }
}

