<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CourseScheduleModel;
use App\Models\CourseOfferingModel;
use App\Models\CourseModel;
use App\Models\TermModel;

class CourseSchedules extends BaseController
{
    protected $session;
    protected $validation;
    protected $db;
    protected $scheduleModel;
    protected $offeringModel;
    protected $courseModel;
    protected $termModel;

    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->validation = \Config\Services::validation();
        $this->db = \Config\Database::connect();
        
        $this->scheduleModel = new CourseScheduleModel();
        $this->offeringModel = new CourseOfferingModel();
        $this->courseModel = new CourseModel();
        $this->termModel = new TermModel();
    }

    /**
     * Manage Course Schedules - Main method
     */
    public function manageSchedules()
    {
        // Security check
        if ($this->session->get('isLoggedIn') !== true) {
            $this->session->setFlashdata('error', 'Please login to access this page.');
            return redirect()->to(base_url('login'));
        }
        
        if ($this->session->get('role') !== 'admin') {
            $this->session->setFlashdata('error', 'Access denied. You do not have permission to access this page.');
            $userRole = $this->session->get('role');
            return redirect()->to(base_url($userRole . '/dashboard'));
        }

        $action = $this->request->getGet('action');
        $scheduleID = $this->request->getGet('id');
        $offeringID = $this->request->getGet('offering_id');

        // Route to appropriate action
        if ($action === 'create' && $this->request->getMethod() === 'POST') {
            return $this->createSchedule();
        }

        if ($action === 'edit' && $scheduleID) {
            return $this->editSchedule($scheduleID);
        }

        if ($action === 'delete' && $scheduleID) {
            return $this->deleteSchedule($scheduleID);
        }

        // Display schedule management interface
        return $this->displayScheduleManagement($offeringID);
    }    /**
     * Create a new schedule
     */    private function createSchedule()
    {
        // Validation rules
        $rules = [
            'course_offering_id' => 'required|integer',
            'session_type'       => 'required|in_list[lecture,lab]',
            'day_of_week'        => 'required|in_list[Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday]',
            'start_time'         => 'required',
            'end_time'           => 'required',
            'room'               => 'permit_empty|string|max_length[50]'
        ];

        $messages = [
            'course_offering_id' => [
                'required' => 'Course offering is required.',
                'integer'  => 'Please select a valid course offering.'
            ],
            'session_type' => [
                'required' => 'Session type is required.',
                'in_list'  => 'Session type must be either lecture or lab.'
            ],
            'day_of_week' => [
                'required' => 'Day of week is required.',
                'in_list'  => 'Invalid day selected.'
            ],
            'start_time' => [
                'required' => 'Start time is required.'
            ],
            'end_time' => [
                'required' => 'End time is required.'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            $this->session->setFlashdata('errors', $this->validator->getErrors());
            $this->session->setFlashdata('error', 'Please fix the validation errors below.');
            return redirect()->to(base_url('admin/manage_courses_schedule?action=create&offering_id=' . $this->request->getPost('course_offering_id')))->withInput();
        }

        // Validate time order
        $startTime = $this->request->getPost('start_time');
        $endTime = $this->request->getPost('end_time');
        
        if (strtotime($startTime) >= strtotime($endTime)) {
            $this->session->setFlashdata('error', 'End time must be after start time.');
            return redirect()->to(base_url('admin/manage_courses_schedule?action=create&offering_id=' . $this->request->getPost('course_offering_id')))->withInput();
        }

        // Get course offering details with course info
        $offeringId = $this->request->getPost('course_offering_id');
        $offering = $this->db->table('course_offerings co')
            ->select('co.*, c.credits, c.lecture_hours, c.lab_hours, c.course_code, c.title')
            ->join('courses c', 'c.id = co.course_id')
            ->where('co.id', $offeringId)
            ->get()
            ->getRowArray();

        if (!$offering) {
            $this->session->setFlashdata('error', 'Course offering not found.');
            return redirect()->to(base_url('admin/manage_courses_schedule'));
        }        // Calculate hours for new schedule
        $newScheduleHours = $this->calculateHours($startTime, $endTime);
        $newSessionType = $this->request->getPost('session_type');

        // Get existing schedules and calculate total hours by type
        $existingSchedules = $this->scheduleModel->getOfferingSchedules($offeringId);
        $totalLectureHours = 0;
        $totalLabHours = 0;
        
        foreach ($existingSchedules as $schedule) {
            $hours = $this->calculateHours($schedule['start_time'], $schedule['end_time']);
            if ($schedule['session_type'] === 'lecture') {
                $totalLectureHours += $hours;
            } else {
                $totalLabHours += $hours;
            }
        }

        // Add new schedule hours to appropriate total
        if ($newSessionType === 'lecture') {
            $totalLectureHours += $newScheduleHours;
        } else {
            $totalLabHours += $newScheduleHours;
        }

        // Get course credit breakdown
        $courseLectureHours = $offering['lecture_hours'] ?? 0;
        $courseLabHours = $offering['lab_hours'] ?? 0;
        $courseCredits = $offering['credits'];

        // Build validation message
        $messages = [];
        $hasWarning = false;
        $hasInfo = false;

        // Validate lecture hours
        if ($courseLectureHours > 0) {
            if ($totalLectureHours > $courseLectureHours) {
                $messages[] = sprintf(
                    '<strong>Lecture Warning:</strong> Scheduled lecture hours (%.1f) exceed required hours (%.1f) by %.1f hours.',
                    $totalLectureHours,
                    $courseLectureHours,
                    $totalLectureHours - $courseLectureHours
                );
                $hasWarning = true;
            } elseif ($totalLectureHours < $courseLectureHours) {
                $messages[] = sprintf(
                    '<strong>Lecture Info:</strong> Need %.1f more lecture hours to match required %.1f hours.',
                    $courseLectureHours - $totalLectureHours,
                    $courseLectureHours
                );
                $hasInfo = true;
            } else {
                $messages[] = sprintf(
                    '<strong>Lecture Perfect:</strong> Scheduled lecture hours (%.1f) match required hours!',
                    $totalLectureHours
                );
            }
        }

        // Validate lab hours
        if ($courseLabHours > 0) {
            if ($totalLabHours > $courseLabHours) {
                $messages[] = sprintf(
                    '<strong>Lab Warning:</strong> Scheduled lab hours (%.1f) exceed required hours (%.1f) by %.1f hours.',
                    $totalLabHours,
                    $courseLabHours,
                    $totalLabHours - $courseLabHours
                );
                $hasWarning = true;
            } elseif ($totalLabHours < $courseLabHours) {
                $messages[] = sprintf(
                    '<strong>Lab Info:</strong> Need %.1f more lab hours to match required %.1f hours.',
                    $courseLabHours - $totalLabHours,
                    $courseLabHours
                );
                $hasInfo = true;
            } else {
                $messages[] = sprintf(
                    '<strong>Lab Perfect:</strong> Scheduled lab hours (%.1f) match required hours!',
                    $totalLabHours
                );
            }
        }

        // Overall validation
        $totalScheduledHours = $totalLectureHours + $totalLabHours;
        $totalRequiredHours = $courseLectureHours + $courseLabHours;

        if ($totalRequiredHours > 0) {
            if ($totalScheduledHours === $totalRequiredHours && !$hasWarning && !$hasInfo) {
                $this->session->setFlashdata('success', 
                    'Course schedule created successfully! ' . implode(' ', $messages)
                );
            } elseif ($hasWarning) {
                $this->session->setFlashdata('warning', implode('<br>', $messages));
            } else {
                $this->session->setFlashdata('info', implode('<br>', $messages));
            }
        } else {
            // No lecture/lab hours defined, fall back to credit-based validation
            if ($totalScheduledHours > $courseCredits) {
                $this->session->setFlashdata('warning', sprintf(
                    'Warning: Total scheduled hours (%.1f) exceed course credits (%d units) by %.1f hours.',
                    $totalScheduledHours,
                    $courseCredits,
                    $totalScheduledHours - $courseCredits
                ));
            } elseif ($totalScheduledHours < $courseCredits) {
                $this->session->setFlashdata('info', sprintf(
                    'Info: Need %.1f more hours to match %d credit units. Current: %.1f hours.',
                    $courseCredits - $totalScheduledHours,
                    $courseCredits,
                    $totalScheduledHours
                ));
            }
        }        // Prepare schedule data
        $scheduleData = [
            'course_offering_id' => $offeringId,
            'session_type'       => $newSessionType,
            'day_of_week'        => $this->request->getPost('day_of_week'),
            'start_time'         => $startTime,
            'end_time'           => $endTime,
            'room'               => $this->request->getPost('room') ?: null
        ];

        // Create schedule
        if ($this->scheduleModel->insert($scheduleData)) {
            if (!$this->session->has('success') && !$this->session->has('warning') && !$this->session->has('info')) {
                $this->session->setFlashdata('success', 'Course schedule created successfully!');
            }
            return redirect()->to(base_url('admin/manage_courses_schedule?offering_id=' . $scheduleData['course_offering_id']));
        } else {
            $this->session->setFlashdata('errors', $this->scheduleModel->errors());
            $this->session->setFlashdata('error', 'Failed to create schedule. Please try again.');
            return redirect()->to(base_url('admin/manage_courses_schedule?action=create&offering_id=' . $this->request->getPost('course_offering_id')))->withInput();
        }
    }

    /**
     * Calculate hours between two times
     */
    private function calculateHours($startTime, $endTime)
    {
        $start = new \DateTime($startTime);
        $end = new \DateTime($endTime);
        $diff = $start->diff($end);
        
        // Convert to decimal hours
        return $diff->h + ($diff->i / 60);
    }

    /**
     * Edit an existing schedule
     */
    private function editSchedule($scheduleID)
    {
        $scheduleToEdit = $this->scheduleModel->find($scheduleID);

        if (!$scheduleToEdit) {
            $this->session->setFlashdata('error', 'Schedule not found.');
            return redirect()->to(base_url('admin/manage_courses_schedule'));
        }        // Handle POST request (update)
        if ($this->request->getMethod() === 'POST') {
            // Validation rules
            $rules = [
                'session_type' => 'required|in_list[lecture,lab]',
                'day_of_week' => 'required|in_list[Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday]',
                'start_time'  => 'required',
                'end_time'    => 'required',
                'room'        => 'permit_empty|string|max_length[50]'
            ];

            $messages = [
                'session_type' => [
                    'required' => 'Session type is required.',
                    'in_list'  => 'Session type must be either lecture or lab.'
                ],
                'day_of_week' => [
                    'required' => 'Day of week is required.',
                    'in_list'  => 'Invalid day selected.'
                ],
                'start_time' => [
                    'required' => 'Start time is required.'
                ],
                'end_time' => [
                    'required' => 'End time is required.'
                ]
            ];

            if (!$this->validate($rules, $messages)) {
                $this->session->setFlashdata('errors', $this->validator->getErrors());
                $this->session->setFlashdata('error', 'Please fix the validation errors below.');
                return redirect()->to(base_url('admin/manage_courses_schedule?action=edit&id=' . $scheduleID))->withInput();
            }

            // Validate time order
            $startTime = $this->request->getPost('start_time');
            $endTime = $this->request->getPost('end_time');
            
            if (strtotime($startTime) >= strtotime($endTime)) {
                $this->session->setFlashdata('error', 'End time must be after start time.');
                return redirect()->to(base_url('admin/manage_courses_schedule?action=edit&id=' . $scheduleID))->withInput();
            }

            // Prepare update data
            $updateData = [
                'session_type' => $this->request->getPost('session_type'),
                'day_of_week' => $this->request->getPost('day_of_week'),
                'start_time'  => $startTime,
                'end_time'    => $endTime,
                'room'        => $this->request->getPost('room') ?: null
            ];

            // Update schedule
            if ($this->scheduleModel->update($scheduleID, $updateData)) {
                $this->session->setFlashdata('success', 'Course schedule updated successfully!');
                return redirect()->to(base_url('admin/manage_courses_schedule?offering_id=' . $scheduleToEdit['course_offering_id']));
            } else {
                $this->session->setFlashdata('errors', $this->scheduleModel->errors());
                $this->session->setFlashdata('error', 'Failed to update schedule. Please try again.');
                return redirect()->to(base_url('admin/manage_courses_schedule?action=edit&id=' . $scheduleID))->withInput();
            }
        }        // Get offering details
        $offering = $this->db->table('course_offerings co')
            ->select('co.*, c.course_code, c.title, t.term_name')
            ->join('courses c', 'c.id = co.course_id')
            ->join('terms t', 't.id = co.term_id')
            ->where('co.id', $scheduleToEdit['course_offering_id'])
            ->get()
            ->getRowArray();

        // Get all offerings for the dropdown
        $allOfferings = $this->db->table('course_offerings co')
            ->select('co.*, c.course_code, c.title, c.credits, t.term_name')
            ->join('courses c', 'c.id = co.course_id')
            ->join('terms t', 't.id = co.term_id')
            ->orderBy('t.id', 'DESC')
            ->orderBy('c.course_code', 'ASC')
            ->get()
            ->getResultArray();

        // Get all schedules for this offering
        $schedules = $this->scheduleModel->getOfferingSchedules($scheduleToEdit['course_offering_id']);

        // Show edit form
        $data = [
            'user' => [
                'userID' => $this->session->get('userID'),
                'name'   => $this->session->get('name'),
                'email'  => $this->session->get('email'),
                'role'   => $this->session->get('role')
            ],
            'title' => 'Edit Course Schedule - Admin Dashboard',
            'schedules' => $schedules,
            'offerings' => $allOfferings,
            'editSchedule' => $scheduleToEdit,
            'selectedOffering' => $offering,
            'selectedOfferingId' => $scheduleToEdit['course_offering_id'],
            'showCreateForm' => false,
            'showEditForm' => true
        ];

        return view('admin/manage_courses_schedule', $data);
    }

    /**
     * Delete a schedule
     */
    private function deleteSchedule($scheduleID)
    {
        $scheduleToDelete = $this->scheduleModel->find($scheduleID);

        if (!$scheduleToDelete) {
            $this->session->setFlashdata('error', 'Schedule not found.');
            return redirect()->to(base_url('admin/manage_courses_schedule'));
        }

        $offeringId = $scheduleToDelete['course_offering_id'];

        if ($this->scheduleModel->delete($scheduleID)) {
            $this->session->setFlashdata('success', 'Course schedule deleted successfully!');
        } else {
            $this->session->setFlashdata('error', 'Failed to delete schedule. Please try again.');
        }

        return redirect()->to(base_url('admin/manage_courses_schedule?offering_id=' . $offeringId));
    }

    /**
     * Display schedule management interface
     */
    private function displayScheduleManagement($offeringID = null)
    {
        // Get all course offerings with details
        $offerings = $this->db->table('course_offerings co')
            ->select('co.*, c.course_code, c.title, c.credits, t.term_name')
            ->join('courses c', 'c.id = co.course_id')
            ->join('terms t', 't.id = co.term_id')
            ->orderBy('t.id', 'DESC')
            ->orderBy('c.course_code', 'ASC')
            ->get()
            ->getResultArray();

        // Get schedules
        $schedules = [];
        $selectedOffering = null;
        
        if ($offeringID) {
            $schedules = $this->scheduleModel->getOfferingSchedules($offeringID);
            $selectedOffering = $this->db->table('course_offerings co')
                ->select('co.*, c.course_code, c.title, t.term_name')
                ->join('courses c', 'c.id = co.course_id')
                ->join('terms t', 't.id = co.term_id')
                ->where('co.id', $offeringID)
                ->get()
                ->getRowArray();
        }

        $data = [
            'user' => [
                'userID' => $this->session->get('userID'),
                'name'   => $this->session->get('name'),
                'email'  => $this->session->get('email'),
                'role'   => $this->session->get('role')
            ],
            'title' => 'Manage Course Schedules - Admin Dashboard',
            'offerings' => $offerings,
            'schedules' => $schedules,
            'selectedOffering' => $selectedOffering,
            'selectedOfferingId' => $offeringID,
            'editSchedule' => null,
            'showCreateForm' => $this->request->getGet('action') === 'create',
            'showEditForm' => false
        ];

        return view('admin/manage_courses_schedule', $data);
    }
}
