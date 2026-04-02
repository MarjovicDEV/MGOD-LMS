# Gradebook Runtime Fixes Design (Checkpoint 1 Continuation)

## Problem Statement

Two runtime failures need a focused fix:

1. `admin/gradebook/audit` was querying `users.role`, but the schema uses `users.role_id` with `roles.role_name`.
2. Grade recalculation fails with `Undefined array key "term_id"` when `course_offerings.term_id` is missing in runtime data.

Scope is intentionally limited to these runtime failures only.

## Proposed Approach

Keep the admin audit query aligned with the role schema and introduce a resilient grading-period resolver in `GradeCalculator` so grade recalculation works even when `term_id` is absent.

## Architecture and Component Boundaries

### 1) Admin audit trail query path

- Keep `Gradebook::auditTrail()` using:
  - `users u`
  - `join roles r on r.id = u.role_id`
  - `where r.role_name = 'teacher'`
- No broader controller refactor in this pass.

### 2) Grade period resolution path

Add one private resolver in `App\Libraries\GradeCalculator`:

- `resolveGradingPeriodsForEnrollment(int $enrollmentId): array`

Responsibilities:

- Resolve grading periods once, in deterministic priority order:
  1. By `course_offerings.term_id` (current expected path).
  2. Fallback from distinct `assignments.grading_period_id` for the enrollment course offering.
  3. Fallback from existing `gradebook_entries.grading_period_id` for the enrollment.
- Return period records suitable for recalculation and final-grade computation.

Consumers:

- `recalculateEnrollmentGrades()`
- `calculateFinalGrade()`

This keeps period-resolution logic in one place and avoids duplicated branching.

## Data Flow

### Recalculate enrollment grades

1. Load enrollment by ID.
2. Resolve periods via `resolveGradingPeriodsForEnrollment()`.
3. If no periods are found:
   - return `['success' => false, 'message' => 'No grading periods configured for enrollment {enrollmentId}']`
   - emit one explicit error log entry with enrollment context.
4. Recalculate each resolved period.
5. Recalculate final grade from resulting period entries.

### Calculate final grade

1. Load enrollment.
2. Resolve periods via the same resolver.
3. Aggregate weighted period grades from gradebook entries.
4. Normalize only when total weight differs from 100 and total weight is non-zero.
5. Update/create final entry (`grading_period_id = NULL`) as currently designed.

## Error Handling Rules

- No silent success when periods cannot be resolved.
- If resolver returns empty:
  - explicit failure return payload,
  - explicit single error log.
- Preserve existing successful behavior where data is complete.

## Ordering and Determinism

- Prefer `grading_periods.period_order ASC` when available.
- If fallback data lacks period order, keep stable ordering by period ID to ensure deterministic recalculation sequence.

## Testing and Verification Scope

1. Keep existing tests green.
2. Add/adjust focused unit coverage for GradeCalculator period resolution behavior:
   - term-based resolution path,
   - assignment-period fallback path when `term_id` is missing,
   - no-period path returns explicit failure.
3. Confirm admin audit path no longer references `users.role`.

## Out of Scope

- Full route/header re-audit across Admin/Teacher/Student pages.
- Broad gradebook refactoring unrelated to these two runtime errors.
- Data migration backfills for legacy records (can be handled separately if needed).

