<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\GradeCalculator;

class RecalculateGrades extends BaseCommand
{
    protected $group       = 'Gradebook';
    protected $name        = 'grades:recalculate';
    protected $description = 'Recalculate grades for a specific enrollment';

    protected $usage = 'grades:recalculate [enrollment_id]';
    protected $arguments = [
        'enrollment_id' => 'The enrollment ID to recalculate grades for'
    ];

    public function run(array $params)
    {
        $enrollmentId = $params[0] ?? CLI::prompt('Enter enrollment ID');

        if (!$enrollmentId || !is_numeric($enrollmentId)) {
            CLI::error('Invalid enrollment ID');
            return;
        }

        CLI::write("Recalculating grades for enrollment #{$enrollmentId}...", 'yellow');

        $calculator = new GradeCalculator();
        $result = $calculator->recalculateEnrollmentGrades((int) $enrollmentId);

        if ($result['success']) {
            CLI::write("Success! Final grade: {$result['grade']}", 'green');
            
            // Show breakdown
            $db = \Config\Database::connect();
            $entries = $db->table('gradebook_entries ge')
                ->select('ge.*, gp.period_name, gp.period_order')
                ->join('grading_periods gp', 'gp.id = ge.grading_period_id', 'left')
                ->where('ge.enrollment_id', $enrollmentId)
                ->orderBy('gp.period_order', 'ASC')
                ->get()
                ->getResultArray();

            CLI::newLine();
            CLI::write("Gradebook Entries:", 'cyan');
            foreach ($entries as $entry) {
                $period = $entry['period_name'] ?? 'Final Grade';
                $calc = number_format($entry['calculated_grade'], 2);
                $final = number_format($entry['final_grade'], 2);
                CLI::write("  {$period}: Calculated={$calc}, Final={$final}");
            }
        } else {
            CLI::error("Failed: {$result['message']}");
        }
    }
}
