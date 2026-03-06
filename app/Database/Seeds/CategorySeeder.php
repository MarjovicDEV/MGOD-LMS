<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\CategoryModel;

class CategorySeeder extends Seeder
{
    public function run()
    {
        // Initialize CategoryModel
        $categoryModel = new CategoryModel();
        
        // Check if categories table is empty before inserting
        if ($categoryModel->countAll() > 0) {
            echo "Categories table already contains data. Skipping seeding.\n";
            echo "Current categories count: " . $categoryModel->countAll() . "\n";
            echo "To re-seed, truncate the table first or delete existing categories.\n";
            return;
        }

        echo "Starting Category Seeding...\n\n";

        // ===== PARENT CATEGORIES =====
        $parentCategories = [
            // 1. Technology & IT
            [
                'category_name' => 'Technology & IT',
                'category_code' => 'TECH',
                'description'   => 'Courses related to Information Technology, Computer Science, and Software Development',
                'parent_id'     => null,
                'is_active'     => 1
            ],
            // 2. Business & Management
            [
                'category_name' => 'Business & Management',
                'category_code' => 'BUS',
                'description'   => 'Business Administration, Management, Economics, and Entrepreneurship courses',
                'parent_id'     => null,
                'is_active'     => 1
            ],
            // 3. Engineering
            [
                'category_name' => 'Engineering',
                'category_code' => 'ENG',
                'description'   => 'Engineering disciplines including Civil, Mechanical, Electrical, and more',
                'parent_id'     => null,
                'is_active'     => 1
            ],
            // 4. Health Sciences
            [
                'category_name' => 'Health Sciences',
                'category_code' => 'HEALTH',
                'description'   => 'Medical, Nursing, Public Health, and Allied Health courses',
                'parent_id'     => null,
                'is_active'     => 1
            ],
            // 5. Arts & Humanities
            [
                'category_name' => 'Arts & Humanities',
                'category_code' => 'ARTS',
                'description'   => 'Literature, History, Philosophy, Languages, and Fine Arts',
                'parent_id'     => null,
                'is_active'     => 1
            ],
            // 6. Natural Sciences
            [
                'category_name' => 'Natural Sciences',
                'category_code' => 'SCI',
                'description'   => 'Physics, Chemistry, Biology, Mathematics, and Environmental Sciences',
                'parent_id'     => null,
                'is_active'     => 1
            ],
            // 7. Social Sciences
            [
                'category_name' => 'Social Sciences',
                'category_code' => 'SOCSCI',
                'description'   => 'Psychology, Sociology, Political Science, and Anthropology',
                'parent_id'     => null,
                'is_active'     => 1
            ],
            // 8. Education
            [
                'category_name' => 'Education',
                'category_code' => 'EDU',
                'description'   => 'Teaching, Pedagogy, Educational Psychology, and Curriculum Development',
                'parent_id'     => null,
                'is_active'     => 1
            ],
            // 9. Communication & Media
            [
                'category_name' => 'Communication & Media',
                'category_code' => 'COMM',
                'description'   => 'Journalism, Broadcasting, Public Relations, and Digital Media',
                'parent_id'     => null,
                'is_active'     => 1
            ],
            // 10. Law & Legal Studies
            [
                'category_name' => 'Law & Legal Studies',
                'category_code' => 'LAW',
                'description'   => 'Legal Studies, Jurisprudence, Criminal Justice, and Paralegal courses',
                'parent_id'     => null,
                'is_active'     => 1
            ],
            // 11. Agriculture & Food Science
            [
                'category_name' => 'Agriculture & Food Science',
                'category_code' => 'AGRI',
                'description'   => 'Agriculture, Agribusiness, Food Technology, and Environmental Management',
                'parent_id'     => null,
                'is_active'     => 1
            ],
            // 12. Architecture & Design
            [
                'category_name' => 'Architecture & Design',
                'category_code' => 'ARCH',
                'description'   => 'Architecture, Interior Design, Urban Planning, and Graphic Design',
                'parent_id'     => null,
                'is_active'     => 1
            ]
        ];

        // Insert parent categories
        $parentIds = [];
        foreach ($parentCategories as $category) {
            if ($categoryModel->insert($category)) {
                $parentIds[$category['category_code']] = $categoryModel->getInsertID();
                echo "✓ Parent Category: {$category['category_name']} (Code: {$category['category_code']})\n";
            } else {
                echo "✗ Failed to create parent category: {$category['category_name']}\n";
                $errors = $categoryModel->errors();
                if (!empty($errors)) {
                    echo "  Errors: " . implode(', ', $errors) . "\n";
                }
            }
        }

        echo "\n--- Creating Subcategories ---\n\n";

        // ===== SUBCATEGORIES =====
        $subcategories = [
            // Technology & IT Subcategories
            [
                'category_name' => 'Computer Science',
                'category_code' => 'TECH-CS',
                'description'   => 'Algorithms, Data Structures, Computer Architecture',
                'parent_id'     => $parentIds['TECH'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Software Development',
                'category_code' => 'TECH-DEV',
                'description'   => 'Programming, Web Development, Mobile Development',
                'parent_id'     => $parentIds['TECH'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Data Science & AI',
                'category_code' => 'TECH-DS',
                'description'   => 'Machine Learning, Artificial Intelligence, Big Data Analytics',
                'parent_id'     => $parentIds['TECH'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Cybersecurity',
                'category_code' => 'TECH-SEC',
                'description'   => 'Network Security, Ethical Hacking, Information Security',
                'parent_id'     => $parentIds['TECH'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Database Management',
                'category_code' => 'TECH-DB',
                'description'   => 'SQL, NoSQL, Database Design and Administration',
                'parent_id'     => $parentIds['TECH'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Network Administration',
                'category_code' => 'TECH-NET',
                'description'   => 'Network Infrastructure, System Administration, Cloud Computing',
                'parent_id'     => $parentIds['TECH'],
                'is_active'     => 1
            ],

            // Business & Management Subcategories
            [
                'category_name' => 'Accounting & Finance',
                'category_code' => 'BUS-ACC',
                'description'   => 'Financial Accounting, Management Accounting, Auditing',
                'parent_id'     => $parentIds['BUS'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Marketing',
                'category_code' => 'BUS-MKT',
                'description'   => 'Digital Marketing, Brand Management, Marketing Strategy',
                'parent_id'     => $parentIds['BUS'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Human Resource Management',
                'category_code' => 'BUS-HR',
                'description'   => 'Talent Management, Organizational Behavior, Employee Relations',
                'parent_id'     => $parentIds['BUS'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Entrepreneurship',
                'category_code' => 'BUS-ENT',
                'description'   => 'Startup Management, Business Planning, Innovation',
                'parent_id'     => $parentIds['BUS'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Operations Management',
                'category_code' => 'BUS-OPS',
                'description'   => 'Supply Chain, Logistics, Project Management',
                'parent_id'     => $parentIds['BUS'],
                'is_active'     => 1
            ],

            // Engineering Subcategories
            [
                'category_name' => 'Computer Engineering',
                'category_code' => 'ENG-COMP',
                'description'   => 'Hardware Design, Embedded Systems, Computer Architecture',
                'parent_id'     => $parentIds['ENG'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Electrical Engineering',
                'category_code' => 'ENG-ELEC',
                'description'   => 'Power Systems, Electronics, Control Systems',
                'parent_id'     => $parentIds['ENG'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Mechanical Engineering',
                'category_code' => 'ENG-MECH',
                'description'   => 'Thermodynamics, Fluid Mechanics, Machine Design',
                'parent_id'     => $parentIds['ENG'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Civil Engineering',
                'category_code' => 'ENG-CIV',
                'description'   => 'Structural Engineering, Transportation, Geotechnical Engineering',
                'parent_id'     => $parentIds['ENG'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Chemical Engineering',
                'category_code' => 'ENG-CHEM',
                'description'   => 'Process Engineering, Chemical Plant Design, Biotechnology',
                'parent_id'     => $parentIds['ENG'],
                'is_active'     => 1
            ],

            // Health Sciences Subcategories
            [
                'category_name' => 'Nursing',
                'category_code' => 'HEALTH-NUR',
                'description'   => 'Patient Care, Clinical Nursing, Community Health Nursing',
                'parent_id'     => $parentIds['HEALTH'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Medical Technology',
                'category_code' => 'HEALTH-MEDTECH',
                'description'   => 'Laboratory Diagnostics, Clinical Chemistry, Microbiology',
                'parent_id'     => $parentIds['HEALTH'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Pharmacy',
                'category_code' => 'HEALTH-PHAR',
                'description'   => 'Pharmaceutical Sciences, Pharmacology, Drug Development',
                'parent_id'     => $parentIds['HEALTH'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Public Health',
                'category_code' => 'HEALTH-PH',
                'description'   => 'Epidemiology, Health Policy, Environmental Health',
                'parent_id'     => $parentIds['HEALTH'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Physical Therapy',
                'category_code' => 'HEALTH-PT',
                'description'   => 'Rehabilitation, Therapeutic Exercise, Manual Therapy',
                'parent_id'     => $parentIds['HEALTH'],
                'is_active'     => 1
            ],

            // Arts & Humanities Subcategories
            [
                'category_name' => 'Literature',
                'category_code' => 'ARTS-LIT',
                'description'   => 'Creative Writing, Literary Analysis, World Literature',
                'parent_id'     => $parentIds['ARTS'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'History',
                'category_code' => 'ARTS-HIST',
                'description'   => 'World History, Philippine History, Historical Research',
                'parent_id'     => $parentIds['ARTS'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Philosophy',
                'category_code' => 'ARTS-PHIL',
                'description'   => 'Ethics, Logic, Metaphysics, Political Philosophy',
                'parent_id'     => $parentIds['ARTS'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Languages',
                'category_code' => 'ARTS-LANG',
                'description'   => 'English, Filipino, Foreign Languages, Linguistics',
                'parent_id'     => $parentIds['ARTS'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Fine Arts',
                'category_code' => 'ARTS-FA',
                'description'   => 'Painting, Sculpture, Visual Arts, Art History',
                'parent_id'     => $parentIds['ARTS'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Music',
                'category_code' => 'ARTS-MUS',
                'description'   => 'Music Theory, Performance, Composition, Music Education',
                'parent_id'     => $parentIds['ARTS'],
                'is_active'     => 1
            ],

            // Natural Sciences Subcategories
            [
                'category_name' => 'Mathematics',
                'category_code' => 'SCI-MATH',
                'description'   => 'Calculus, Statistics, Algebra, Applied Mathematics',
                'parent_id'     => $parentIds['SCI'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Physics',
                'category_code' => 'SCI-PHYS',
                'description'   => 'Classical Mechanics, Quantum Physics, Thermodynamics',
                'parent_id'     => $parentIds['SCI'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Chemistry',
                'category_code' => 'SCI-CHEM',
                'description'   => 'Organic Chemistry, Inorganic Chemistry, Analytical Chemistry',
                'parent_id'     => $parentIds['SCI'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Biology',
                'category_code' => 'SCI-BIO',
                'description'   => 'Molecular Biology, Genetics, Ecology, Microbiology',
                'parent_id'     => $parentIds['SCI'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Environmental Science',
                'category_code' => 'SCI-ENV',
                'description'   => 'Climate Science, Conservation, Environmental Management',
                'parent_id'     => $parentIds['SCI'],
                'is_active'     => 1
            ],

            // Social Sciences Subcategories
            [
                'category_name' => 'Psychology',
                'category_code' => 'SOCSCI-PSY',
                'description'   => 'Clinical Psychology, Developmental Psychology, Social Psychology',
                'parent_id'     => $parentIds['SOCSCI'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Sociology',
                'category_code' => 'SOCSCI-SOC',
                'description'   => 'Social Theory, Cultural Sociology, Urban Sociology',
                'parent_id'     => $parentIds['SOCSCI'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Political Science',
                'category_code' => 'SOCSCI-POL',
                'description'   => 'Government, International Relations, Public Policy',
                'parent_id'     => $parentIds['SOCSCI'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Economics',
                'category_code' => 'SOCSCI-ECON',
                'description'   => 'Microeconomics, Macroeconomics, Development Economics',
                'parent_id'     => $parentIds['SOCSCI'],
                'is_active'     => 1
            ],

            // Education Subcategories
            [
                'category_name' => 'Early Childhood Education',
                'category_code' => 'EDU-ECE',
                'description'   => 'Preschool Education, Child Development, Early Learning',
                'parent_id'     => $parentIds['EDU'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Elementary Education',
                'category_code' => 'EDU-ELEM',
                'description'   => 'Teaching Methods, Classroom Management, Curriculum Design',
                'parent_id'     => $parentIds['EDU'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Secondary Education',
                'category_code' => 'EDU-SEC',
                'description'   => 'Subject Specialization, Adolescent Development, Assessment',
                'parent_id'     => $parentIds['EDU'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Special Education',
                'category_code' => 'EDU-SPED',
                'description'   => 'Inclusive Education, Learning Disabilities, Adaptive Teaching',
                'parent_id'     => $parentIds['EDU'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Educational Technology',
                'category_code' => 'EDU-TECH',
                'description'   => 'E-Learning, Instructional Design, Digital Learning Tools',
                'parent_id'     => $parentIds['EDU'],
                'is_active'     => 1
            ],

            // Communication & Media Subcategories
            [
                'category_name' => 'Journalism',
                'category_code' => 'COMM-JOUR',
                'description'   => 'News Writing, Investigative Journalism, Media Ethics',
                'parent_id'     => $parentIds['COMM'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Broadcasting',
                'category_code' => 'COMM-BROAD',
                'description'   => 'Radio Production, Television Production, Multimedia',
                'parent_id'     => $parentIds['COMM'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Public Relations',
                'category_code' => 'COMM-PR',
                'description'   => 'Corporate Communication, Crisis Management, Media Relations',
                'parent_id'     => $parentIds['COMM'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Digital Media',
                'category_code' => 'COMM-DIG',
                'description'   => 'Social Media, Content Creation, Digital Storytelling',
                'parent_id'     => $parentIds['COMM'],
                'is_active'     => 1
            ],

            // Law & Legal Studies Subcategories
            [
                'category_name' => 'Civil Law',
                'category_code' => 'LAW-CIV',
                'description'   => 'Contracts, Property Law, Family Law',
                'parent_id'     => $parentIds['LAW'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Criminal Law',
                'category_code' => 'LAW-CRIM',
                'description'   => 'Criminal Justice, Forensics, Criminal Procedure',
                'parent_id'     => $parentIds['LAW'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Corporate Law',
                'category_code' => 'LAW-CORP',
                'description'   => 'Business Law, Securities, Mergers and Acquisitions',
                'parent_id'     => $parentIds['LAW'],
                'is_active'     => 1
            ],

            // Agriculture & Food Science Subcategories
            [
                'category_name' => 'Crop Science',
                'category_code' => 'AGRI-CROP',
                'description'   => 'Plant Production, Soil Science, Crop Management',
                'parent_id'     => $parentIds['AGRI'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Animal Science',
                'category_code' => 'AGRI-ANIM',
                'description'   => 'Livestock Management, Veterinary Science, Animal Nutrition',
                'parent_id'     => $parentIds['AGRI'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Food Technology',
                'category_code' => 'AGRI-FOOD',
                'description'   => 'Food Processing, Food Safety, Product Development',
                'parent_id'     => $parentIds['AGRI'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Agribusiness',
                'category_code' => 'AGRI-BUS',
                'description'   => 'Agricultural Economics, Farm Management, Marketing',
                'parent_id'     => $parentIds['AGRI'],
                'is_active'     => 1
            ],

            // Architecture & Design Subcategories
            [
                'category_name' => 'Architecture',
                'category_code' => 'ARCH-ARC',
                'description'   => 'Architectural Design, Building Systems, Construction',
                'parent_id'     => $parentIds['ARCH'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Interior Design',
                'category_code' => 'ARCH-INT',
                'description'   => 'Space Planning, Furniture Design, Lighting Design',
                'parent_id'     => $parentIds['ARCH'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Landscape Architecture',
                'category_code' => 'ARCH-LAND',
                'description'   => 'Site Planning, Garden Design, Urban Landscaping',
                'parent_id'     => $parentIds['ARCH'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Graphic Design',
                'category_code' => 'ARCH-GRAPH',
                'description'   => 'Visual Communication, Typography, Branding',
                'parent_id'     => $parentIds['ARCH'],
                'is_active'     => 1
            ],
            [
                'category_name' => 'Industrial Design',
                'category_code' => 'ARCH-IND',
                'description'   => 'Product Design, Manufacturing, User Experience',
                'parent_id'     => $parentIds['ARCH'],
                'is_active'     => 1
            ]
        ];

        // Insert subcategories
        $subcategoryCount = 0;
        foreach ($subcategories as $category) {
            if ($categoryModel->insert($category)) {
                $subcategoryCount++;
                echo "  ✓ Subcategory: {$category['category_name']} (Code: {$category['category_code']})\n";
            } else {
                echo "  ✗ Failed to create subcategory: {$category['category_name']}\n";
                $errors = $categoryModel->errors();
                if (!empty($errors)) {
                    echo "    Errors: " . implode(', ', $errors) . "\n";
                }
            }
        }

        // Final summary
        echo "\n" . str_repeat('=', 60) . "\n";
        echo "Category Seeding Complete!\n";
        echo str_repeat('=', 60) . "\n";
        echo "✓ Parent Categories Created: " . count($parentIds) . "\n";
        echo "✓ Subcategories Created: " . $subcategoryCount . "\n";
        echo "✓ Total Categories: " . (count($parentIds) + $subcategoryCount) . "\n";
        echo str_repeat('=', 60) . "\n";
    }
}
