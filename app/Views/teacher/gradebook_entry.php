<?= $this->include('templates/header') ?>

<div class="lms-dashboard lms-role-view min-vh-100 teacher-gradebook-entry-page">
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3 role-hero">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1 fw-bold"><i class="fas fa-edit me-2"></i>Grade Entry</h3>
                                <p class="mb-0 opacity-75">
                                    <?= esc($course['course_code'] ?? '') ?> - <?= esc($course['title'] ?? '') ?>
                                    <?php if (!empty($course['section'])): ?>
                                        <span class="ms-2">Section <?= esc($course['section']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($course['term_name'])): ?>
                                        <span class="ms-2">| <?= esc($course['term_name']) ?></span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div>
                                <a href="<?= base_url('teacher/gradebook') ?>" class="btn btn-light btn-sm">
                                    <i class="fas fa-arrow-left me-1"></i>Back to Gradebook
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="gradebookAlert"></div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= esc(session()->getFlashdata('success')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-3 mb-4 role-section-card">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-calendar-alt me-2"></i>Grading Periods</h5>
            </div>
            <div class="card-body">
                <?php if (empty($grading_periods)): ?>
                    <div class="alert alert-warning mb-0">
                        No grading periods found for this term.
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($grading_periods as $period): ?>
                            <div class="col-lg-3 col-md-6">
                                <div class="border rounded-3 p-3 h-100 bg-light">
                                    <div class="small text-muted mb-1">Period <?= (int) ($period['period_order'] ?? 0) ?></div>
                                    <div class="fw-bold"><?= esc($period['period_name'] ?? 'Period') ?></div>
                                    <div class="text-primary small mt-1">Weight: <?= number_format((float) ($period['weight_percentage'] ?? 0), 2) ?>%</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 role-section-card">
            <div class="card-header bg-white border-0 py-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Student Grade Grid</h5>
                <button id="bulkSaveBtn" class="btn btn-success btn-sm" type="button">
                    <i class="fas fa-save me-1"></i>Bulk Save
                    <span id="changedCount" class="badge bg-light text-dark ms-1">0</span>
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($enrollments)): ?>
                    <div class="text-center py-5 role-empty-state rounded-3">
                        <i class="fas fa-user-graduate text-muted mb-3" style="font-size: 3rem; opacity: 0.35;"></i>
                        <p class="text-muted mb-0">No enrolled students found for this course.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 120px;">Student ID</th>
                                    <th style="min-width: 220px;">Name</th>
                                    <?php foreach ($grading_periods as $period): ?>
                                        <th style="min-width: 170px;">
                                            <?= esc($period['period_name'] ?? 'Period') ?>
                                            <small class="d-block text-muted"><?= number_format((float) ($period['weight_percentage'] ?? 0), 2) ?>%</small>
                                        </th>
                                    <?php endforeach; ?>
                                    <th style="min-width: 110px;">Final Grade</th>
                                    <th style="min-width: 130px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($enrollments as $enrollment): ?>
                                    <?php
                                        $studentGrades = $grades[$enrollment['id']] ?? [];
                                        $periodMap = [];
                                        $finalGrade = null;
                                        $finalEntryId = null;
                                        $firstEntryId = null;

                                        foreach ($studentGrades as $entry) {
                                            if ($firstEntryId === null && !empty($entry['id'])) {
                                                $firstEntryId = (int) $entry['id'];
                                            }

                                            if (array_key_exists('grading_period_id', $entry) && $entry['grading_period_id'] !== null) {
                                                $periodMap[(string) $entry['grading_period_id']] = $entry;
                                            } else {
                                                $finalGrade = $entry['final_grade'] ?? null;
                                                $finalEntryId = !empty($entry['id']) ? (int) $entry['id'] : null;
                                            }
                                        }

                                        $studentName = trim(
                                            ($enrollment['last_name'] ?? '') . ', ' .
                                            ($enrollment['first_name'] ?? '') . ' ' .
                                            ($enrollment['middle_name'] ?? '')
                                        );

                                        $overrideEntryId = $finalEntryId ?? $firstEntryId;
                                    ?>
                                    <tr>
                                        <td><?= esc($enrollment['student_id_number'] ?? '') ?></td>
                                        <td>
                                            <div class="fw-semibold"><?= esc($studentName) ?></div>
                                            <small class="text-muted">
                                                <?= esc($enrollment['email'] ?? '') ?>
                                                <?php if (!empty($enrollment['year_level_name'])): ?>
                                                    • <?= esc($enrollment['year_level_name']) ?>
                                                <?php endif; ?>
                                            </small>
                                        </td>
                                        <?php foreach ($grading_periods as $period): ?>
                                            <?php $cellEntry = $periodMap[(string) $period['id']] ?? null; ?>
                                            <td>
                                                <?php if (!empty($cellEntry['id'])): ?>
                                                    <input
                                                        type="number"
                                                        min="0"
                                                        max="100"
                                                        step="0.01"
                                                        class="form-control form-control-sm grade-input"
                                                        value="<?= esc((string) $cellEntry['final_grade'], 'attr') ?>"
                                                        data-entry-id="<?= (int) $cellEntry['id'] ?>"
                                                        data-original="<?= esc((string) $cellEntry['final_grade'], 'attr') ?>"
                                                    >
                                                <?php else: ?>
                                                    <input
                                                        type="number"
                                                        class="form-control form-control-sm"
                                                        value=""
                                                        placeholder="No entry"
                                                        disabled
                                                        readonly
                                                    >
                                                    <small class="text-muted">Read-only: no entry record.</small>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                        <td class="text-center">
                                            <?php if ($finalGrade !== null): ?>
                                                <span class="fw-bold"><?= number_format((float) $finalGrade, 2) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-warning override-btn"
                                                data-entry-id="<?= (int) ($overrideEntryId ?? 0) ?>"
                                                data-student-name="<?= esc($studentName, 'attr') ?>"
                                                data-current-grade="<?= esc((string) ($finalGrade ?? ''), 'attr') ?>"
                                                <?= $overrideEntryId ? '' : 'disabled' ?>
                                            >
                                                <i class="fas fa-user-edit me-1"></i>Override
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="overrideModal" tabindex="-1" aria-labelledby="overrideModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="overrideForm">
                <?= csrf_field() ?>
                <input type="hidden" id="overrideEntryId" name="entry_id" value="">
                <div class="modal-header bg-warning-subtle">
                    <h5 class="modal-title" id="overrideModalLabel">Grade Override</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Student: <strong id="overrideStudentName">—</strong></p>
                    <p class="text-muted small">Current displayed final grade: <span id="overrideCurrentGrade">N/A</span></p>

                    <div class="mb-3">
                        <label for="overrideNewGrade" class="form-label fw-semibold">New Grade (0-100)</label>
                        <input type="number" class="form-control" id="overrideNewGrade" name="new_grade" min="0" max="100" step="0.01" required>
                    </div>
                    <div class="mb-2">
                        <label for="overrideReason" class="form-label fw-semibold">Reason</label>
                        <textarea class="form-control" id="overrideReason" name="reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Submit Override</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(() => {
    const bulkSaveBtn = document.getElementById('bulkSaveBtn');
    const changedCountBadge = document.getElementById('changedCount');
    const alertContainer = document.getElementById('gradebookAlert');
    const gradeInputs = Array.from(document.querySelectorAll('.grade-input[data-entry-id]'));
    const csrfTokenName = '<?= esc(csrf_token(), 'js') ?>';
    const csrfHashValue = '<?= esc(csrf_hash(), 'js') ?>';
    const overrideModalElement = document.getElementById('overrideModal');
    const overrideModal = overrideModalElement ? new bootstrap.Modal(overrideModalElement) : null;

    const showAlert = (type, message) => {
        alertContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    };

    const isNumericInRange = (value) => {
        const parsed = Number(value);
        return Number.isFinite(parsed) && parsed >= 0 && parsed <= 100;
    };

    const refreshChangedCount = () => {
        let count = 0;
        gradeInputs.forEach((input) => {
            const current = input.value.trim();
            const original = (input.dataset.original || '').trim();
            const changed = current !== original;
            input.classList.toggle('border-warning', changed);
            if (changed) {
                count += 1;
            }
        });
        changedCountBadge.textContent = String(count);
    };

    gradeInputs.forEach((input) => {
        input.addEventListener('input', refreshChangedCount);
        input.addEventListener('change', refreshChangedCount);
    });

    refreshChangedCount();

    bulkSaveBtn?.addEventListener('click', async () => {
        const updates = [];

        for (const input of gradeInputs) {
            const current = input.value.trim();
            const original = (input.dataset.original || '').trim();

            if (current === original) {
                continue;
            }

            if (!isNumericInRange(current)) {
                showAlert('danger', `Invalid grade value detected. Please enter numbers from 0 to 100.`);
                input.focus();
                return;
            }

            updates.push({
                entry_id: Number(input.dataset.entryId),
                grade: Number(current)
            });
        }

        if (updates.length === 0) {
            showAlert('info', 'No changed grades to save.');
            return;
        }

        bulkSaveBtn.disabled = true;
        const originalLabel = bulkSaveBtn.innerHTML;
        bulkSaveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

        try {
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            };

            if (csrfTokenName && csrfHashValue) {
                headers['X-CSRF-TOKEN'] = csrfHashValue;
                headers[csrfTokenName] = csrfHashValue;
            }

            const response = await fetch('<?= base_url('teacher/gradebook/bulk-update') ?>', {
                method: 'POST',
                headers,
                body: JSON.stringify(updates)
            });

            const data = await response.json();

            if (data.success) {
                updates.forEach((item) => {
                    const input = document.querySelector(`.grade-input[data-entry-id="${item.entry_id}"]`);
                    if (input) {
                        input.dataset.original = String(item.grade);
                        input.value = String(item.grade);
                    }
                });
                refreshChangedCount();
                showAlert('success', data.message || 'Grades saved successfully.');
            } else {
                showAlert('danger', data.message || 'Failed to save grades.');
            }
        } catch (error) {
            showAlert('danger', 'An unexpected error occurred while saving grades.');
        } finally {
            bulkSaveBtn.disabled = false;
            bulkSaveBtn.innerHTML = originalLabel;
        }
    });

    document.querySelectorAll('.override-btn[data-entry-id]').forEach((button) => {
        button.addEventListener('click', () => {
            const entryId = Number(button.dataset.entryId || 0);
            if (!entryId || !overrideModal) {
                return;
            }

            document.getElementById('overrideEntryId').value = String(entryId);
            document.getElementById('overrideStudentName').textContent = button.dataset.studentName || '—';
            document.getElementById('overrideCurrentGrade').textContent = button.dataset.currentGrade || 'N/A';
            document.getElementById('overrideNewGrade').value = button.dataset.currentGrade || '';
            document.getElementById('overrideReason').value = '';
            overrideModal.show();
        });
    });

    document.getElementById('overrideForm')?.addEventListener('submit', async (event) => {
        event.preventDefault();

        const form = event.currentTarget;
        const entryId = Number(document.getElementById('overrideEntryId').value || 0);
        const newGrade = document.getElementById('overrideNewGrade').value.trim();

        if (!entryId) {
            showAlert('danger', 'Missing entry for override.');
            return;
        }

        if (!isNumericInRange(newGrade)) {
            showAlert('danger', 'Override grade must be between 0 and 100.');
            return;
        }

        const formData = new FormData(form);

        if (csrfTokenName && csrfHashValue && !formData.get(csrfTokenName)) {
            formData.append(csrfTokenName, csrfHashValue);
        }

        try {
            const headers = { 'Accept': 'application/json' };
            if (csrfTokenName && csrfHashValue) {
                headers['X-CSRF-TOKEN'] = csrfHashValue;
            }

            const response = await fetch(`<?= base_url('teacher/gradebook/override') ?>/${entryId}`, {
                method: 'POST',
                headers,
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                showAlert('success', data.message || 'Override submitted successfully.');
                overrideModal?.hide();
            } else {
                showAlert('danger', data.message || 'Failed to submit override.');
            }
        } catch (error) {
            showAlert('danger', 'An unexpected error occurred while submitting override.');
        }
    });
})();
</script>
