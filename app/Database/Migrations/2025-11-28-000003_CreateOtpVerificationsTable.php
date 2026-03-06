<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOtpVerificationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => false,
            ],
            'otp_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => false,
            ],
            'otp_type' => [
                'type'       => 'ENUM',
                'constraint' => ['login', 'registration', 'password_reset', '2fa', 'email_verification'],
                'default'    => 'login',
                
            ],
            'expires_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
            ],
            'verified_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'When the OTP was successfully verified',
            ],
            'attempts' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'max_attempts' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 3,
            
            ],            
            'is_used' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
              
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
                
            ],
            'user_agent' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        // Primary key
        $this->forge->addKey('id', true);
        
        // Indexes for performance
        $this->forge->addKey('user_id');
        $this->forge->addKey('email');
        $this->forge->addKey('otp_code');
        $this->forge->addKey('expires_at');
        $this->forge->addKey(['otp_code', 'email', 'is_used']); // Composite index for verification
        
        // Foreign key
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('otp_verifications');
    }

    public function down()
    {
        $this->forge->dropTable('otp_verifications');
    }
}
