<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * MainSeeder - Master seeder that runs all seeders in the correct order
 * 
 * This seeder runs all database seeders in the proper sequence to ensure
 * that dependencies are met (e.g., roles must exist before users, etc.)
 * 
 * Usage: php spark db:seed MainSeeder
 */
class MainSeeder extends Seeder
{
    public function run()
    {
        // Step 1: Seed basic lookup tables (no dependencies)
        echo "Seeding Roles...\n";
        $this->call('RoleSeeder');
        
        echo "Seeding Year Levels...\n";
        $this->call('YearLevelSeeder');
        
        echo "Seeding Categories...\n";
        $this->call('CategorySeeder');
        
        // Step 2: Seed academic structure tables
        echo "Seeding Academic Years...\n";
        $this->call('AcademicYearSeeder');
        
        echo "Seeding Semesters...\n";
        $this->call('SemesterSeeder');
        
        echo "Seeding Terms...\n";
        $this->call('TermSeeder');
        
        // Step 3: Seed grading-related tables
        echo "Seeding Assignment Types...\n";
        $this->call('AssignmentTypeSeeder');
        
        echo "Seeding Grading Periods...\n";
        $this->call('GradingPeriodSeeder');

        $this->call('UserSeeder');
        $this->call('DepartmentSeeder');
        $this->call('ProgramSeeder');
        $this->call('CourseSeeder');

        echo "\n✅ All seeders completed successfully!\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Summary:\n";
        echo "  • Roles: 3 (Admin, Instructor, Student)\n";
        echo "  • Year Levels: 4 (1st - 4th Year)\n";
        echo "  • Categories: 76 (12 parent + 64 subcategories)\n";
        echo "  • Academic Years: 3 (current + 2 future)\n";
        echo "  • Semesters: 3 (1st, 2nd, Summer)\n";
        echo "  • Terms: 7 (Term 1, 2, 3 for each semester)\n";
        echo "  • Assignment Types: 5\n";
        echo "  • Grading Periods: 21 (3 per term)\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    }
}
