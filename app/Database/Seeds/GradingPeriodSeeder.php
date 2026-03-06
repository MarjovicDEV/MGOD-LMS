<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\GradingPeriodModel;
use App\Models\TermModel;

class GradingPeriodSeeder extends Seeder
{
    public function run()
    {
        $gradingPeriodModel = new GradingPeriodModel();
        $termModel = new TermModel();

        // Check if table already has data
        $existing = $gradingPeriodModel->countAll();
        if ($existing > 0) {
            echo "Grading Periods table already contains data. Skipping seeder.\n";
            return;
        }

        /**
         * Grading Period Structure:
         * 
         * Each TERM has THREE grading periods:
         * 1. Prelim (PREL)   - 33.33% - Preliminary examination period
         * 2. Midterm (MID)   - 33.33% - Midterm examination period  
         * 3. Finals (FIN)    - 33.34% - Final examination period (adjusted to total 100%)
         * 
         * These periods divide the term into three parts for assessment.
         * Dates (start_date, end_date) will be set by admin based on academic calendar.
         * 
         * Note: We create grading periods for ALL terms (7 terms = 21 grading periods total)
         */

        // Get all active terms
        $terms = $termModel->where('is_active', 1)->findAll();
        
        if (empty($terms)) {
            echo "Error: No active terms found. Please run TermSeeder first.\n";
            return;
        }

        echo "Found " . count($terms) . " active terms. Creating 3 grading periods for each term...\n\n";

        // Define the 3 grading periods template
        $periodTemplates = [
            [
                'period_name'       => 'Prelim',
                'period_order'      => 1,
                'weight_percentage' => 33.33,
                'description'       => 'Preliminary examination period'
            ],
            [
                'period_name'       => 'Midterm',
                'period_order'      => 2,
                'weight_percentage' => 33.33,
                'description'       => 'Midterm examination period'
            ],
            [
                'period_name'       => 'Finals',
                'period_order'      => 3,
                'weight_percentage' => 33.34, // Adjusted to make total 100%
                'description'       => 'Final examination period'
            ]
        ];

        $totalCreated = 0;
        $termCount = 0;

        // Create grading periods for each term
        foreach ($terms as $term) {
            $termCount++;
            echo "Term #{$termCount}: {$term['term_name']} (ID: {$term['id']})\n";

            foreach ($periodTemplates as $template) {
                $gradingPeriod = [
                    'term_id'           => $term['id'],
                    'period_name'       => $template['period_name'],
                    'period_order'      => $template['period_order'],
                    'weight_percentage' => $template['weight_percentage'],
                    'start_date'        => null, // Admin will set actual dates
                    'end_date'          => null, // Admin will set actual dates
                    'is_active'         => 1
                ];

                try {
                    $result = $gradingPeriodModel->insert($gradingPeriod);
                    
                    if ($result) {
                        echo "  ✓ Created: {$template['period_name']} ({$template['weight_percentage']}%)\n";
                        $totalCreated++;
                    } else {
                        echo "  ✗ Failed to create: {$template['period_name']}\n";
                        print_r($gradingPeriodModel->errors());
                    }
                } catch (\Exception $e) {
                    echo "  ✗ Error creating {$template['period_name']}: " . $e->getMessage() . "\n";
                }
            }
            
            echo "\n";
        }

    }
}
