<?php

namespace App\Models;

use CodeIgniter\Model;

class CourseScheduleModel extends Model
{
    protected $table            = 'course_schedules';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;    
    protected $allowedFields    = [
        'course_offering_id',
        'session_type',
        'day_of_week',
        'start_time',
        'end_time',
        'room'
    ];protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';  
    protected $validationRules = [
        'course_offering_id' => 'required|integer',
        'session_type'       => 'required|in_list[lecture,lab]',
        'day_of_week'        => 'required|in_list[Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday]',
        'start_time'         => 'required',
        'end_time'           => 'required',
        'room'               => 'permit_empty|string|max_length[50]'
    ];

    protected $validationMessages = [
        'course_offering_id' => [
            'required' => 'Course offering is required'
        ],
        'session_type' => [
            'required' => 'Session type is required',
            'in_list'  => 'Session type must be either lecture or lab'
        ],
        'day_of_week' => [
            'required' => 'Day of week is required'
        ]
    ];

    // Callbacks
    protected $allowCallbacks = true;

    /**
     * Get schedules for a course offering
     */
    public function getOfferingSchedules($offeringId)
    {
        return $this->where('course_offering_id', $offeringId)
                    ->orderBy('FIELD(day_of_week, "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday")', '', false)
                    ->orderBy('start_time', 'ASC')
                    ->findAll();
    }

    /**
     * Get formatted schedule string for an offering
     */
    public function getFormattedSchedule($offeringId)
    {
        $schedules = $this->getOfferingSchedules($offeringId);
        
        if (empty($schedules)) {
            return 'No schedule set';
        }
        
        $formatted = [];
        foreach ($schedules as $schedule) {
            $formatted[] = sprintf(
                '%s %s-%s (%s)',
                $schedule['day_of_week'],
                date('g:i A', strtotime($schedule['start_time'])),
                date('g:i A', strtotime($schedule['end_time'])),
                $schedule['room'] ?? 'TBA'
            );
        }
        
        return implode(', ', $formatted);
    }

    /**
     * Check for schedule conflicts for a student
     */
    public function checkStudentConflict($studentId, $offeringId)
    {
        $db = \Config\Database::connect();
        
        // Get schedules for the new offering
        $newSchedules = $this->where('course_offering_id', $offeringId)->findAll();
        
        // Get student's current schedules
        $existingSchedules = $db->table('course_schedules cs')
            ->select('cs.*')
            ->join('enrollments e', 'e.course_offering_id = cs.course_offering_id')
            ->where('e.student_id', $studentId)
            ->whereIn('e.enrollment_status', ['pending', 'enrolled'])
            ->get()
            ->getResultArray();
        
        // Check for conflicts
        foreach ($newSchedules as $new) {
            foreach ($existingSchedules as $existing) {
                if ($new['day_of_week'] === $existing['day_of_week']) {
                    // Check time overlap
                    if (
                        ($new['start_time'] < $existing['end_time']) &&
                        ($new['end_time'] > $existing['start_time'])
                    ) {
                        return [
                            'conflict' => true,
                            'day'      => $new['day_of_week'],
                            'time'     => sprintf(
                                '%s - %s',
                                date('g:i A', strtotime($new['start_time'])),
                                date('g:i A', strtotime($new['end_time']))
                            )
                        ];
                    }
                }
            }
        }
        
        return ['conflict' => false];
    }

    /**
     * Check for instructor schedule conflict
     */
    public function checkInstructorConflict($instructorId, $offeringId)
    {
        $db = \Config\Database::connect();
        
        // Get schedules for the new offering
        $newSchedules = $this->where('course_offering_id', $offeringId)->findAll();
        
        // Get instructor's current schedules
        $existingSchedules = $db->table('course_schedules cs')
            ->select('cs.*')
            ->join('course_instructors ci', 'ci.course_offering_id = cs.course_offering_id')
            ->where('ci.instructor_id', $instructorId)
            ->get()
            ->getResultArray();
        
        // Check for conflicts
        foreach ($newSchedules as $new) {
            foreach ($existingSchedules as $existing) {
                if ($new['day_of_week'] === $existing['day_of_week']) {
                    if (
                        ($new['start_time'] < $existing['end_time']) &&
                        ($new['end_time'] > $existing['start_time'])
                    ) {
                        return [
                            'conflict' => true,
                            'day'      => $new['day_of_week'],
                            'time'     => sprintf(
                                '%s - %s',
                                date('g:i A', strtotime($new['start_time'])),
                                date('g:i A', strtotime($new['end_time']))
                            )
                        ];
                    }
                }
            }
        }
        
        return ['conflict' => false];
    }

    /**
     * Get schedules by day of week
     */
    public function getSchedulesByDay($dayOfWeek)
    {
        return $this->select('
                course_schedules.*,
                co.section,
                c.course_code,
                c.title
            ')
            ->join('course_offerings co', 'co.id = course_schedules.course_offering_id')
            ->join('courses c', 'c.id = co.course_id')
            ->where('course_schedules.day_of_week', $dayOfWeek)
            ->orderBy('course_schedules.start_time', 'ASC')
            ->findAll();
    }

    /**
     * Get all schedules for a room
     */
    public function getRoomSchedules($room)
    {
        return $this->select('
                course_schedules.*,
                co.section,
                c.course_code,
                c.title
            ')
            ->join('course_offerings co', 'co.id = course_schedules.course_offering_id')
            ->join('courses c', 'c.id = co.course_id')
            ->where('course_schedules.room', $room)
            ->orderBy('course_schedules.day_of_week')
            ->orderBy('course_schedules.start_time', 'ASC')
            ->findAll();
    }
}