# Gradebook Runtime Fixes Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Eliminate the remaining runtime failures on `student/gradebook` and `admin/gradebook/audit` by making grade period resolution resilient and ensuring audit teacher filtering matches the role schema.

**Architecture:** Keep the admin audit query schema-correct (`users.role_id` + `roles.role_name`) and centralize grading period discovery in a new private resolver inside `GradeCalculator`. Both `recalculateEnrollmentGrades()` and `calculateFinalGrade()` must call the same resolver so behavior is consistent for complete and legacy data paths.

**Tech Stack:** PHP 8, CodeIgniter 4, PHPUnit 11, SQLite-backed unit tests

---

## File Structure (planned changes)

- **Modify:** `app/Libraries/GradeCalculator.php`
  - Add `resolveGradingPeriodsForEnrollment(int $enrollmentId): array` (private helper)
  - Update `calculateFinalGrade($enrollmentId)` to use resolver
  - Update `recalculateEnrollmentGrades($enrollmentId)` to use resolver and explicit failure result/log when unresolved
- **Modify:** `app/Controllers/Gradebook.php`
  - Keep/confirm `auditTrail()` instructor query using `users u` + `roles r` join and `r.role_name = 'teacher'`
- **Create:** `tests/unit/GradeCalculatorRuntimeResilienceTest.php`
  - Add string-level guard tests that lock in resolver usage and fallback query paths in `GradeCalculator`
  - Add guard test that prevents `users.role` usage in `Gradebook::auditTrail()`

### Task 1: Add failing tests for resilient grade period resolution

**Files:**
- Create: `tests/unit/GradeCalculatorRuntimeResilienceTest.php`
- Modify: none
- Test: `tests/unit/GradeCalculatorRuntimeResilienceTest.php`

- [ ] **Step 1: Write the failing test file**

```php
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
```

- [ ] **Step 2: Run test to verify it fails**

Run:
```bash
php vendor/bin/phpunit --no-coverage --testdox tests/unit/GradeCalculatorRuntimeResilienceTest.php
```

Expected: FAIL with missing `resolveGradingPeriodsForEnrollment` and missing fallback query assertions.

- [ ] **Step 3: Commit failing test**

```bash
git add tests/unit/GradeCalculatorRuntimeResilienceTest.php
git commit -m "test(gradebook): add runtime resilience guards for period resolution"
```

### Task 2: Implement central grading period resolver in GradeCalculator

**Files:**
- Modify: `app/Libraries/GradeCalculator.php`
- Test: `tests/unit/GradeCalculatorRuntimeResilienceTest.php`

- [ ] **Step 1: Implement resolver method and replace direct term_id lookups**

In `app/Libraries/GradeCalculator.php`, add this private method inside the class:

```php
private function resolveGradingPeriodsForEnrollment(int $enrollmentId): array
{
    $enrollment = $this->enrollmentModel->find($enrollmentId);
    if (!$enrollment || empty($enrollment['course_offering_id'])) {
        return [];
    }

    $courseOffering = $this->db->table('course_offerings')
        ->select('id, term_id')
        ->where('id', (int) $enrollment['course_offering_id'])
        ->get()
        ->getRowArray();

    if (!$courseOffering) {
        return [];
    }

    // Primary path: resolve by term_id when present.
    if (!empty($courseOffering['term_id'])) {
        $byTerm = $this->gradingPeriodModel
            ->where('term_id', (int) $courseOffering['term_id'])
            ->orderBy('period_order', 'ASC')
            ->findAll();

        if (!empty($byTerm)) {
            return $byTerm;
        }
    }

    // Fallback 1: resolve periods used by assignments in this offering.
    $byAssignments = $this->db->table('grading_periods gp')
        ->distinct()
        ->select('gp.*')
        ->join('assignments a', 'a.grading_period_id = gp.id')
        ->where('a.course_offering_id', (int) $enrollment['course_offering_id'])
        ->where('a.grading_period_id IS NOT NULL')
        ->orderBy('gp.period_order', 'ASC')
        ->orderBy('gp.id', 'ASC')
        ->get()
        ->getResultArray();

    if (!empty($byAssignments)) {
        return $byAssignments;
    }

    // Fallback 2: resolve periods already present in gradebook entries.
    $byGradebook = $this->db->table('grading_periods gp')
        ->distinct()
        ->select('gp.*')
        ->join('gradebook_entries ge', 'ge.grading_period_id = gp.id')
        ->where('ge.enrollment_id', (int) $enrollmentId)
        ->where('ge.grading_period_id IS NOT NULL')
        ->orderBy('gp.period_order', 'ASC')
        ->orderBy('gp.id', 'ASC')
        ->get()
        ->getResultArray();

    return $byGradebook ?: [];
}
```

Then update `calculateFinalGrade($enrollmentId)` to replace term-based period loading with:

```php
$gradingPeriods = $this->resolveGradingPeriodsForEnrollment((int) $enrollmentId);

if (empty($gradingPeriods)) {
    return ['success' => false, 'message' => 'No grading periods configured for enrollment ' . (int) $enrollmentId];
}
```

And update `recalculateEnrollmentGrades($enrollmentId)` to replace term-based period loading with:

```php
$gradingPeriods = $this->resolveGradingPeriodsForEnrollment((int) $enrollmentId);

if (empty($gradingPeriods)) {
    log_message(
        'error',
        'GradeCalculator recalculation failed: no grading periods resolved for enrollment {enrollmentId}',
        ['enrollmentId' => (int) $enrollmentId]
    );
    return ['success' => false, 'message' => 'No grading periods configured for enrollment ' . (int) $enrollmentId];
}
```

- [ ] **Step 2: Run targeted test to verify it now passes**

Run:
```bash
php vendor/bin/phpunit --no-coverage --testdox tests/unit/GradeCalculatorRuntimeResilienceTest.php
```

Expected: PASS (all tests in `GradeCalculatorRuntimeResilienceTest`).

- [ ] **Step 3: Commit implementation**

```bash
git add app/Libraries/GradeCalculator.php tests/unit/GradeCalculatorRuntimeResilienceTest.php
git commit -m "fix(gradebook): add resilient grading period resolution"
```

### Task 3: Confirm admin audit query remains schema-correct and run full suite

**Files:**
- Modify: `app/Controllers/Gradebook.php` (only if query is not already in schema-correct form)
- Test: `tests/unit/GradeCalculatorRuntimeResilienceTest.php`, full `tests/`

- [ ] **Step 1: Ensure `auditTrail()` instructor query is role-schema compliant**

In `app/Controllers/Gradebook.php` `auditTrail()` method, ensure this exact query shape exists:

```php
$instructors = $this->db->table('users u')
    ->select('u.id, u.first_name, u.middle_name, u.last_name, u.email')
    ->join('roles r', 'r.id = u.role_id')
    ->where('r.role_name', 'teacher')
    ->orderBy('u.last_name', 'ASC')
    ->get()
    ->getResultArray();
```

- [ ] **Step 2: Run focused test file again**

Run:
```bash
php vendor/bin/phpunit --no-coverage --testdox tests/unit/GradeCalculatorRuntimeResilienceTest.php
```

Expected: PASS.

- [ ] **Step 3: Run full test suite**

Run:
```bash
php vendor/bin/phpunit --no-coverage --testdox
```

Expected: PASS summary for existing tests plus new runtime resilience tests.

- [ ] **Step 4: Commit final verification adjustments**

```bash
git add app/Controllers/Gradebook.php tests/unit/GradeCalculatorRuntimeResilienceTest.php
git commit -m "test(gradebook): guard audit role filter and runtime error regressions"
```

### Task 4: Final smoke checks on impacted routes

**Files:**
- Modify: none
- Test: runtime route behavior

- [ ] **Step 1: Manual smoke test student gradebook**

Run in browser:

```text
http://localhost/MGOD-LMS/student/gradebook
```

Expected:
- page renders without template/file error
- no new `Undefined array key "term_id"` for the grade recalculation flow triggered by grading updates

- [ ] **Step 2: Manual smoke test admin audit trail**

Run in browser:

```text
http://localhost/MGOD-LMS/admin/gradebook/audit
```

Expected:
- page renders without SQL error
- instructor filter list loads from teacher-role users

- [ ] **Step 3: Commit (if any small follow-up code edits were required during smoke checks)**

```bash
git add app/Libraries/GradeCalculator.php app/Controllers/Gradebook.php tests/unit/GradeCalculatorRuntimeResilienceTest.php
git commit -m "fix(gradebook): finalize runtime route stability"
```

