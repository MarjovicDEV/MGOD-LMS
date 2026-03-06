<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class YearLevelSeeder extends Seeder
{
    public function run()
    {
        // Year levels data for students
        $data = [
            [
                'year_level_name'  => '1st Year',
                'year_level_order' => 1,
                'description'      => 'First Year - Freshmen',
                'is_active'        => 1,
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s')
            ],
            [
                'year_level_name'  => '2nd Year',
                'year_level_order' => 2,
                'description'      => 'Second Year - Sophomores',
                'is_active'        => 1,
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s')
            ],
            [
                'year_level_name'  => '3rd Year',
                'year_level_order' => 3,
                'description'      => 'Third Year - Juniors',
                'is_active'        => 1,
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s')
            ],
            [
                'year_level_name'  => '4th Year',
                'year_level_order' => 4,
                'description'      => 'Fourth Year - Seniors',
                'is_active'        => 1,
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s')
            ]
        ];

        // Check if table is empty before inserting
        $builder = $this->db->table('year_levels');
        
        if ($builder->countAll() == 0) {
            // Insert year levels
            $builder->insertBatch($data);
            
            echo "Year levels seeded successfully!\n";
            echo "✓ 1st Year - Freshmen\n";
            echo "✓ 2nd Year - Sophomores\n";
            echo "✓ 3rd Year - Juniors\n";
            echo "✓ 4th Year - Seniors\n";
        } else {
            echo "Year levels table already contains data. Skipping seeding.\n";
            echo "To re-seed, truncate the table first.\n";
        }
    }
}
