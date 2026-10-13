<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCompletedToBloodRequestsEnum extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE blood_requests 
            MODIFY COLUMN status ENUM('pending', 'matching', 'donors_notified', 'accepted', 'approved', 'fulfilled', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE blood_requests 
            MODIFY COLUMN status ENUM('pending', 'matching', 'donors_notified', 'accepted', 'approved', 'fulfilled', 'cancelled') NOT NULL DEFAULT 'pending'");
    }
}
