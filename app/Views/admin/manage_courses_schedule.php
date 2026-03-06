<?= $this->include('templates/header') ?>

<!-- Manage Course Schedules View - Admin only functionality -->
<div class="bg-light min-vh-100">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-2 fw-bold">🕐 Manage Course Schedules</h2>
                                <p class="mb-0 opacity-75">Create and manage class schedules for course offerings</p>
                            </div>
                            <div>
                                <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-light btn-sm">
                                    ← Back to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>        
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>✅ Success!</strong> <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('warning')): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>⚠️ Warning!</strong><br>
                <?= session()->getFlashdata('warning') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('info')): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <strong>ℹ️ Info:</strong><br>
                <?= session()->getFlashdata('info') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>❌ Error!</strong> <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>❌ Validation Errors:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Course Offering Filter -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 pb-0">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-3 fw-bold text-dark">🎯 Select Course Offering</h5>
                                <select class="form-select" id="offeringFilter" onchange="filterByOffering(this.value)">
                                    <option value="">-- All Course Offerings --</option>
                                    <?php foreach ($offerings as $offering): ?>
                                        <option value="<?= $offering['id'] ?>" <?= $selectedOfferingId == $offering['id'] ? 'selected' : '' ?>>
                                            <?= esc($offering['course_code']) ?> - <?= esc($offering['title']) ?> 
                                            (<?= esc($offering['section'] ?: 'No Section') ?>) - <?= esc($offering['term_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                <?php if ($selectedOfferingId): ?>
                                    <a href="<?= base_url('admin/manage_courses_schedule?action=create&offering_id=' . $selectedOfferingId) ?>" class="btn btn-success">
                                        ➕ Add Schedule
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-success" disabled title="Please select a course offering first">
                                        ➕ Add Schedule
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selected Offering Info -->
        <?php if ($selectedOffering): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-info">
                    <div class="card-body bg-light">
                        <h5 class="mb-2 fw-bold text-info">📚 Selected Course:</h5>
                        <h4 class="mb-1"><?= esc($selectedOffering['course_code']) ?> - <?= esc($selectedOffering['title']) ?></h4>
                        <p class="text-muted mb-0">
                            <strong>Section:</strong> <?= esc($selectedOffering['section'] ?: 'No Section') ?> | 
                            <strong>Term:</strong> <?= esc($selectedOffering['term_name']) ?> | 
                            <strong>Schedules:</strong> <?= count($schedules) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Create Schedule Form -->
        <?php if ($showCreateForm && $selectedOfferingId): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-success">
                    <div class="card-header bg-success text-white border-0">
                        <h5 class="mb-0">➕ Add Course Schedule</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_courses_schedule?action=create') ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="course_offering_id" value="<?= $selectedOfferingId ?>">
                              <div class="row">
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="session_type" class="form-label fw-semibold">Session Type <span class="text-danger">*</span></label>
                                        <select class="form-select" id="session_type" name="session_type" required>
                                            <option value="">-- Select --</option>
                                            <option value="lecture" <?= old('session_type') == 'lecture' ? 'selected' : '' ?>>Lecture</option>
                                            <option value="lab" <?= old('session_type') == 'lab' ? 'selected' : '' ?>>Lab</option>
                                        </select>
                                        <small class="text-muted">Lecture or Lab</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="day_of_week" class="form-label fw-semibold">Day of Week <span class="text-danger">*</span></label>
                                        <select class="form-select" id="day_of_week" name="day_of_week" required>
                                            <option value="">-- Select Day --</option>
                                            <option value="Monday" <?= old('day_of_week') == 'Monday' ? 'selected' : '' ?>>Monday</option>
                                            <option value="Tuesday" <?= old('day_of_week') == 'Tuesday' ? 'selected' : '' ?>>Tuesday</option>
                                            <option value="Wednesday" <?= old('day_of_week') == 'Wednesday' ? 'selected' : '' ?>>Wednesday</option>
                                            <option value="Thursday" <?= old('day_of_week') == 'Thursday' ? 'selected' : '' ?>>Thursday</option>
                                            <option value="Friday" <?= old('day_of_week') == 'Friday' ? 'selected' : '' ?>>Friday</option>
                                            <option value="Saturday" <?= old('day_of_week') == 'Saturday' ? 'selected' : '' ?>>Saturday</option>
                                            <option value="Sunday" <?= old('day_of_week') == 'Sunday' ? 'selected' : '' ?>>Sunday</option>
                                        </select>
                                        <small class="text-muted">Class day</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="start_time" class="form-label fw-semibold">Start Time <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" id="start_time" name="start_time" 
                                               value="<?= old('start_time') ?>" required>
                                        <small class="text-muted">Class starts</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="end_time" class="form-label fw-semibold">End Time <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" id="end_time" name="end_time" 
                                               value="<?= old('end_time') ?>" required>
                                        <small class="text-muted">Class ends</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="room" class="form-label fw-semibold">Room</label>
                                        <input type="text" class="form-control" id="room" name="room" 
                                               value="<?= old('room') ?>" maxlength="50" 
                                               placeholder="e.g., Room 101">
                                        <small class="text-muted">Optional</small>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?= base_url('admin/manage_courses_schedule?offering_id=' . $selectedOfferingId) ?>" class="btn btn-secondary">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-success">
                                    ➕ Create Schedule
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Edit Schedule Form -->
        <?php if ($showEditForm && isset($editSchedule)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 border-warning">
                    <div class="card-header bg-warning text-dark border-0">
                        <h5 class="mb-0">✏️ Edit Course Schedule</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('admin/manage_courses_schedule?action=edit&id=' . $editSchedule['id']) ?>">
                            <?= csrf_field() ?>
                              <div class="row">
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="session_type" class="form-label fw-semibold">Session Type <span class="text-danger">*</span></label>
                                        <select class="form-select" id="session_type" name="session_type" required>
                                            <option value="">-- Select --</option>
                                            <option value="lecture" <?= old('session_type', $editSchedule['session_type']) == 'lecture' ? 'selected' : '' ?>>Lecture</option>
                                            <option value="lab" <?= old('session_type', $editSchedule['session_type']) == 'lab' ? 'selected' : '' ?>>Lab</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="day_of_week" class="form-label fw-semibold">Day of Week <span class="text-danger">*</span></label>
                                        <select class="form-select" id="day_of_week" name="day_of_week" required>
                                            <option value="">-- Select Day --</option>
                                            <option value="Monday" <?= old('day_of_week', $editSchedule['day_of_week']) == 'Monday' ? 'selected' : '' ?>>Monday</option>
                                            <option value="Tuesday" <?= old('day_of_week', $editSchedule['day_of_week']) == 'Tuesday' ? 'selected' : '' ?>>Tuesday</option>
                                            <option value="Wednesday" <?= old('day_of_week', $editSchedule['day_of_week']) == 'Wednesday' ? 'selected' : '' ?>>Wednesday</option>
                                            <option value="Thursday" <?= old('day_of_week', $editSchedule['day_of_week']) == 'Thursday' ? 'selected' : '' ?>>Thursday</option>
                                            <option value="Friday" <?= old('day_of_week', $editSchedule['day_of_week']) == 'Friday' ? 'selected' : '' ?>>Friday</option>
                                            <option value="Saturday" <?= old('day_of_week', $editSchedule['day_of_week']) == 'Saturday' ? 'selected' : '' ?>>Saturday</option>
                                            <option value="Sunday" <?= old('day_of_week', $editSchedule['day_of_week']) == 'Sunday' ? 'selected' : '' ?>>Sunday</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="start_time" class="form-label fw-semibold">Start Time <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" id="start_time" name="start_time" 
                                               value="<?= old('start_time', $editSchedule['start_time']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="end_time" class="form-label fw-semibold">End Time <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" id="end_time" name="end_time" 
                                               value="<?= old('end_time', $editSchedule['end_time']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="room" class="form-label fw-semibold">Room</label>
                                        <input type="text" class="form-control" id="room" name="room" 
                                               value="<?= old('room', $editSchedule['room']) ?>" maxlength="50">
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?= base_url('admin/manage_courses_schedule?offering_id=' . $editSchedule['course_offering_id']) ?>" class="btn btn-secondary">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-warning">
                                    💾 Update Schedule
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Schedules Table -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold text-dark">📋 Schedules List</h5>
                            <div class="text-muted small">
                                <?php if ($selectedOffering): ?>
                                    Total: <?= count($schedules) ?> schedule(s)
                                <?php else: ?>
                                    Select a course offering to view schedules
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">                                <thead class="table-light">
                                    <tr>
                                        <th class="fw-semibold border-0 text-center">ID</th>
                                        <th class="fw-semibold border-0">Type</th>
                                        <th class="fw-semibold border-0">Day</th>
                                        <th class="fw-semibold border-0">Start Time</th>
                                        <th class="fw-semibold border-0">End Time</th>
                                        <th class="fw-semibold border-0">Duration</th>
                                        <th class="fw-semibold border-0">Room</th>
                                        <th class="fw-semibold border-0 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($schedules)): ?>
                                        <?php foreach ($schedules as $schedule): ?>
                                        <?php
                                            // Calculate duration
                                            $start = new DateTime($schedule['start_time']);
                                            $end = new DateTime($schedule['end_time']);
                                            $duration = $start->diff($end);
                                            $hours = $duration->h;
                                            $minutes = $duration->i;
                                        ?>
                                        <tr class="border-bottom">
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark">#<?= $schedule['id'] ?></span>
                                            </td>
                                            <td>
                                                <?php if (isset($schedule['session_type']) && $schedule['session_type'] === 'lab'): ?>
                                                    <span class="badge bg-primary">🔬 Lab</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark">📚 Lecture</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?= esc($schedule['day_of_week']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-success"><?= date('g:i A', strtotime($schedule['start_time'])) ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger"><?= date('g:i A', strtotime($schedule['end_time'])) ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php if ($hours > 0): ?>
                                                        <?= $hours ?>h <?= $minutes ?>m
                                                    <?php else: ?>
                                                        <?= $minutes ?>m
                                                    <?php endif; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= $schedule['room'] ? '<span class="badge bg-secondary">' . esc($schedule['room']) . '</span>' : '<span class="text-muted">Not set</span>' ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="<?= base_url('admin/manage_courses_schedule?action=edit&id=' . $schedule['id']) ?>" 
                                                       class="btn btn-sm btn-warning text-white" 
                                                       title="Edit Schedule">
                                                        ✏️
                                                    </a>
                                                    <a href="<?= base_url('admin/manage_courses_schedule?action=delete&id=' . $schedule['id']) ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       title="Delete Schedule"
                                                       onclick="return confirm('Are you sure you want to delete this schedule? This action cannot be undone.')">
                                                        🗑️
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-5 text-muted">
                                                <div class="display-1 mb-3">🕐</div>
                                                <h5>No schedules found</h5>
                                                <p class="mb-0">
                                                    <?php if ($selectedOfferingId): ?>
                                                        This course offering has no schedules yet. Click "Add Schedule" to create one.
                                                    <?php else: ?>
                                                        Please select a course offering above to view and manage schedules.
                                                    <?php endif; ?>
                                                </p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function filterByOffering(offeringId) {
    if (offeringId) {
        window.location.href = '<?= base_url('admin/manage_courses_schedule') ?>?offering_id=' + offeringId;
    } else {
        window.location.href = '<?= base_url('admin/manage_courses_schedule') ?>';
    }
}
</script>
