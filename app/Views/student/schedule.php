<?= $this->include('templates/header') ?>

<!-- Student Schedule View - Shows course schedule by day and room -->
<div class="lms-dashboard lms-role-view min-vh-100 student-schedule-page">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body bg-primary text-white p-4 rounded-3 role-hero">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-2 fw-bold"><i class="fas fa-calendar-alt me-2"></i>My Schedule</h2>
                                <p class="mb-0 opacity-75">
                                    View your class schedule for 
                                    <?php if ($currentTerm): ?>
                                        <?= esc($currentTerm['semester_name']) ?> <?= esc($currentTerm['academic_year']) ?>
                                    <?php else: ?>
                                        the current term
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div>
                                <a href="<?= base_url('student/dashboard') ?>" class="btn btn-outline-light btn-sm">
                                    ← Back to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedule Statistics Cards -->
        <div class="row mb-4 g-4 role-stats">
            <!-- Total Courses -->
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm text-white bg-primary text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-3"><i class="fas fa-book"></i></div>
                    <div class="display-5 fw-bold"><?= $totalCourses ?></div>
                    <div class="fw-semibold">Enrolled Courses</div>
                    <small class="opacity-75">This term</small>
                </div>
            </div>
            
            <!-- Total Classes -->
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm text-white bg-success text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-3"><i class="fas fa-chalkboard-teacher"></i></div>
                    <div class="display-5 fw-bold"><?= $totalSchedules ?></div>
                    <div class="fw-semibold">Class Sessions</div>
                    <small class="opacity-75">Per week</small>
                </div>
            </div>
            
            <!-- Hours Per Week -->
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm text-white bg-info text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-3"><i class="fas fa-clock"></i></div>
                    <div class="display-5 fw-bold"><?= $totalHoursPerWeek ?></div>
                    <div class="fw-semibold">Hours/Week</div>
                    <small class="opacity-75">Total class time</small>
                </div>
            </div>
            
            <!-- Current Day -->
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm text-white bg-warning text-center p-4 rounded-3 h-100">
                    <div class="display-4 mb-3"><i class="fas fa-calendar-day"></i></div>
                    <div class="display-5 fw-bold">
                        <?php 
                            $today = date('l');
                            $todayClasses = count($scheduleByDay[$today] ?? []);
                            echo $todayClasses;
                        ?>
                    </div>
                    <div class="fw-semibold">Today's Classes</div>
                    <small class="opacity-75"><?= $today ?></small>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Schedule Content -->
        <?php if (empty($allSchedules)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> No class schedule found. 
                <?php if ($totalCourses == 0): ?>
                    <a href="<?= base_url('student/courses') ?>" class="alert-link">Enroll in courses first</a>
                <?php else: ?>
                    Course schedules may not have been set yet.
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- View Toggle -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-th-large me-2"></i>Weekly Schedule</h5>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="viewToggle" id="weekView" checked>
                            <label class="btn btn-outline-primary btn-sm" for="weekView">
                                <i class="fas fa-calendar-week me-1"></i>Week View
                            </label>
                            <input type="radio" class="btn-check" name="viewToggle" id="listView">
                            <label class="btn btn-outline-primary btn-sm" for="listView">
                                <i class="fas fa-list me-1"></i>List View
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Week View (Schedule Grid) -->
            <div id="weekViewContent">
                <div class="row g-4">
                    <?php 
                    $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    $dayColors = [
                        'Monday' => 'primary',
                        'Tuesday' => 'success',
                        'Wednesday' => 'info',
                        'Thursday' => 'warning',
                        'Friday' => 'danger',
                        'Saturday' => 'secondary',
                        'Sunday' => 'dark'
                    ];
                    $today = date('l');
                    
                    foreach ($daysOfWeek as $day): 
                        $daySchedules = $scheduleByDay[$day] ?? [];
                        $isToday = ($day === $today);
                        $dayColor = $dayColors[$day];
                    ?>
                        <div class="col-lg-6 col-xl-4">
                            <div class="card h-100 border-0 shadow-sm rounded-3 <?= $isToday ? 'border-primary border-2' : '' ?>">
                                <div class="card-header bg-<?= $dayColor ?> text-white py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-bold">
                                            <i class="fas fa-calendar-day me-2"></i><?= $day ?>
                                        </h6>
                                        <?php if ($isToday): ?>
                                            <span class="badge bg-light text-<?= $dayColor ?>">Today</span>
                                        <?php endif; ?>
                                        <span class="badge bg-light text-<?= $dayColor ?>">
                                            <?= count($daySchedules) ?> class<?= count($daySchedules) !== 1 ? 'es' : '' ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <?php if (empty($daySchedules)): ?>
                                        <div class="text-center text-muted py-5">
                                            <i class="fas fa-coffee fa-2x mb-2 opacity-50"></i>
                                            <p class="mb-0">No classes</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($daySchedules as $schedule): ?>
                                                <div class="list-group-item border-0 px-3 py-3">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <div>
                                                            <h6 class="mb-1 fw-bold text-<?= $dayColor ?>">
                                                                <?= esc($schedule['course_code']) ?>
                                                            </h6>
                                                            <small class="text-muted"><?= esc($schedule['course_title']) ?></small>
                                                        </div>
                                                        <span class="badge bg-<?= $schedule['session_type'] === 'lab' ? 'warning' : 'info' ?>-subtle text-<?= $schedule['session_type'] === 'lab' ? 'warning' : 'info' ?>">
                                                            <?= ucfirst($schedule['session_type']) ?>
                                                        </span>
                                                    </div>
                                                    <div class="row g-2 small">
                                                        <div class="col-6">
                                                            <i class="fas fa-clock text-muted me-1"></i>
                                                            <strong><?= $schedule['start_time_formatted'] ?></strong>
                                                            <span class="text-muted">-</span>
                                                            <strong><?= $schedule['end_time_formatted'] ?></strong>
                                                        </div>
                                                        <div class="col-6 text-end">
                                                            <i class="fas fa-door-open text-muted me-1"></i>
                                                            <strong><?= esc($schedule['room']) ?></strong>
                                                        </div>
                                                    </div>
                                                    <div class="row g-2 small mt-1">
                                                        <div class="col-6">
                                                            <i class="fas fa-layer-group text-muted me-1"></i>
                                                            Section: <?= esc($schedule['section']) ?>
                                                        </div>
                                                        <div class="col-6 text-end">
                                                            <i class="fas fa-user text-muted me-1"></i>
                                                            <?= esc($schedule['instructor_name']) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- List View (Table) -->
            <div id="listViewContent" style="display: none;">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4 py-3">Day</th>
                                        <th class="px-3 py-3">Time</th>
                                        <th class="px-3 py-3">Course</th>
                                        <th class="px-3 py-3">Section</th>
                                        <th class="px-3 py-3">Type</th>
                                        <th class="px-3 py-3">Room</th>
                                        <th class="px-3 py-3">Instructor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Sort all schedules by day then by time
                                    $dayOrder = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7];
                                    usort($allSchedules, function($a, $b) use ($dayOrder) {
                                        $dayCompare = ($dayOrder[$a['day_of_week']] ?? 8) - ($dayOrder[$b['day_of_week']] ?? 8);
                                        if ($dayCompare !== 0) return $dayCompare;
                                        return strtotime($a['start_time']) - strtotime($b['start_time']);
                                    });
                                    
                                    foreach ($allSchedules as $schedule): 
                                        $isToday = ($schedule['day_of_week'] === date('l'));
                                        $dayColor = $dayColors[$schedule['day_of_week']] ?? 'secondary';
                                    ?>
                                        <tr class="<?= $isToday ? 'table-primary' : '' ?>">
                                            <td class="px-4 py-3">
                                                <span class="badge bg-<?= $dayColor ?>">
                                                    <?= $schedule['day_of_week'] ?>
                                                </span>
                                                <?php if ($isToday): ?>
                                                    <small class="text-primary ms-1">(Today)</small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-3 py-3">
                                                <strong><?= $schedule['start_time_formatted'] ?></strong>
                                                <span class="text-muted">-</span>
                                                <strong><?= $schedule['end_time_formatted'] ?></strong>
                                            </td>
                                            <td class="px-3 py-3">
                                                <div class="fw-semibold"><?= esc($schedule['course_code']) ?></div>
                                                <small class="text-muted"><?= esc($schedule['course_title']) ?></small>
                                            </td>
                                            <td class="px-3 py-3"><?= esc($schedule['section']) ?></td>
                                            <td class="px-3 py-3">
                                                <span class="badge bg-<?= $schedule['session_type'] === 'lab' ? 'warning' : 'info' ?>-subtle text-<?= $schedule['session_type'] === 'lab' ? 'warning' : 'info' ?>">
                                                    <?= ucfirst($schedule['session_type']) ?>
                                                </span>
                                            </td>
                                            <td class="px-3 py-3">
                                                <i class="fas fa-door-open text-muted me-1"></i>
                                                <strong><?= esc($schedule['room']) ?></strong>
                                            </td>
                                            <td class="px-3 py-3"><?= esc($schedule['instructor_name']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Classes Highlight -->
            <?php 
            $todaySchedules = $scheduleByDay[date('l')] ?? [];
            if (!empty($todaySchedules)): 
            ?>
                <div class="card border-0 shadow-sm rounded-3 mt-4 border-primary border-2">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0">
                            <i class="fas fa-bell me-2"></i>Today's Classes (<?= date('l, F j, Y') ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <?php foreach ($todaySchedules as $schedule): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card border h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title fw-bold text-primary mb-0">
                                                    <?= esc($schedule['course_code']) ?>
                                                </h6>
                                                <span class="badge bg-<?= $schedule['session_type'] === 'lab' ? 'warning' : 'info' ?>">
                                                    <?= ucfirst($schedule['session_type']) ?>
                                                </span>
                                            </div>
                                            <p class="card-text small text-muted mb-2"><?= esc($schedule['course_title']) ?></p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="fas fa-clock text-primary me-1"></i>
                                                    <strong><?= $schedule['start_time_formatted'] ?> - <?= $schedule['end_time_formatted'] ?></strong>
                                                </div>
                                                <div>
                                                    <i class="fas fa-door-open text-success me-1"></i>
                                                    <strong><?= esc($schedule['room']) ?></strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// View Toggle functionality
document.getElementById('weekView')?.addEventListener('change', function() {
    document.getElementById('weekViewContent').style.display = 'block';
    document.getElementById('listViewContent').style.display = 'none';
});

document.getElementById('listView')?.addEventListener('change', function() {
    document.getElementById('weekViewContent').style.display = 'none';
    document.getElementById('listViewContent').style.display = 'block';
});
</script>

</body>
</html>
