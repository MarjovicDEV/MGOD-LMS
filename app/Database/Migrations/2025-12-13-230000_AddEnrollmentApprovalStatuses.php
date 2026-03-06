<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEnrollmentApprovalStatuses extends Migration
{
    public function up()
    {
        // Modify enrollment_status ENUM to add new approval statuses
        $this->db->query("
            ALTER TABLE enrollments 
            MODIFY COLUMN enrollment_status ENUM(
                'pending', 
                'pending_student_approval', 
                'pending_teacher_approval', 
                'enrolled', 
                'rejected', 
                'dropped', 
                'withdrawn', 
                'completed'
            ) DEFAULT 'pending'
        ");
    }

    public function down()
    {
        // Revert to original ENUM values
        $this->db->query("
            ALTER TABLE enrollments 
            MODIFY COLUMN enrollment_status ENUM(
                'pending', 
                'enrolled', 
                'dropped', 
                'withdrawn', 
                'completed'
            ) DEFAULT 'pending'
        ");
    }
}
