<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAvailabilityToDonorProfilesTable extends Migration
{
    public function up()
    {
        $fields = [
            'availability' => [
                'type'       => 'ENUM',
                'constraint' => ['available', 'temporarily_unavailable', 'not_available'],
                'default'    => 'available',
                'after'      => 'medical_eligibility',
            ],
        ];

        $this->forge->addColumn('donor_profiles', $fields);
        $this->forge->addKey('availability');
    }

    public function down()
    {
        $this->forge->dropColumn('donor_profiles', 'availability');
    }
}
