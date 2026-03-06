<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\AcademicYearModel;

class AcademicYearSeeder extends Seeder
{
    public function run()
    {
        $academicYearModel = new AcademicYearModel();        // Check if table already has data
        $existing = $academicYearModel->countAll();
        if ($existing > 0) {
            echo "Academic Years table already contains data. Skipping seeder.\n";
            return;
        }

        /**
         * Academic Year Logic:
         * - is_current: The ONE academic year that is actively running RIGHT NOW
         * - is_active: Academic years that can be used (present or future)
         * - We only seed PRESENT and FUTURE academic years (no past years)
         * 
         * Example for November 2025:
         * - AY 2025-2026: is_current=1, is_active=1 (Currently running)
         * - AY 2026-2027: is_current=0, is_active=1 (Future, can enroll)
         * - AY 2027-2028: is_current=0, is_active=0 (Future, not yet open)
         */

        // Get current calendar year
        $currentYear = date('Y'); // 2025
        
        $academicYears = [
            // CURRENT Academic Year (2025-2026) - Running NOW
            [
                'year_code'    => 'AY' . $currentYear . '-' . ($currentYear + 1),
                'year_name'    => 'Academic Year ' . $currentYear . '-' . ($currentYear + 1),
                'start_date'   => $currentYear . '-08-01',      // August 1, 2025
                'end_date'     => ($currentYear + 1) . '-07-31', // July 31, 2026
                'is_current'   => 1, // This is the current active academic year
                'is_active'    => 1  // Active for enrollment and use
            ],
            // NEXT Academic Year (2026-2027) - Future, can plan/enroll
            [
                'year_code'    => 'AY' . ($currentYear + 1) . '-' . ($currentYear + 2),
                'year_name'    => 'Academic Year ' . ($currentYear + 1) . '-' . ($currentYear + 2),
                'start_date'   => ($currentYear + 1) . '-08-01',  // August 1, 2026
                'end_date'     => ($currentYear + 2) . '-07-31',  // July 31, 2027
                'is_current'   => 0, // Not current yet
                'is_active'    => 1  // Active for future enrollment
            ],
            // FUTURE Academic Year (2027-2028) - Not yet open
            [
                'year_code'    => 'AY' . ($currentYear + 2) . '-' . ($currentYear + 3),
                'year_name'    => 'Academic Year ' . ($currentYear + 2) . '-' . ($currentYear + 3),
                'start_date'   => ($currentYear + 2) . '-08-01',  // August 1, 2027
                'end_date'     => ($currentYear + 3) . '-07-31',  // July 31, 2028
                'is_current'   => 0, // Not current
                'is_active'    => 0  // Not yet active (for planning only)
            ]
        ];        echo "Seeding academic years...\n";
        echo "Current calendar year: {$currentYear}\n\n";
        
        foreach ($academicYears as $academicYear) {
            try {
                $result = $academicYearModel->insert($academicYear);
                
                if ($result) {
                    $currentStatus = $academicYear['is_current'] ? '[CURRENT]' : '';
                    $activeStatus = $academicYear['is_active'] ? '[ACTIVE]' : '[Inactive]';
                    echo "✓ Created: {$academicYear['year_code']} {$activeStatus} {$currentStatus}\n";
                } else {
                    echo "✗ Failed to create: {$academicYear['year_code']}\n";
                    print_r($academicYearModel->errors());
                }
            } catch (\Exception $e) {
                echo "✗ Error creating academic year {$academicYear['year_code']}: " . $e->getMessage() . "\n";
            }
        }

        echo "\nAcademic Year seeding completed successfully!\n";
        echo "Total academic years created: " . count($academicYears) . "\n";
        echo "Current active academic year: AY{$currentYear}-" . ($currentYear + 1) . "\n";
        echo "\nNote: Only PRESENT and FUTURE academic years are seeded (no past years).\n";
    }
}
