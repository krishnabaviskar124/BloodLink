<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateBloodRequestsEnum extends Migration
{
    public function up()
    {
        // Expand urgency and status ENUMs backward-compatibly
        $this->db->query("ALTER TABLE blood_requests 
            MODIFY COLUMN urgency ENUM('normal', 'urgent', 'critical', 'low', 'medium', 'high') NOT NULL DEFAULT 'normal',
            MODIFY COLUMN status ENUM('pending', 'matching', 'donors_notified', 'accepted', 'approved', 'fulfilled', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE blood_requests 
            MODIFY COLUMN urgency ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
            MODIFY COLUMN status ENUM('pending', 'approved', 'fulfilled', 'cancelled') NOT NULL DEFAULT 'pending'");
    }
}
