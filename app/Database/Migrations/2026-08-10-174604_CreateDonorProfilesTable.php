<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDonorProfilesTable extends Migration
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
                'unique'     => true,
            ],
            'blood_group_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'gender' => [
                'type'       => 'ENUM',
                'constraint' => ['male', 'female', 'other'],
                'null'       => true,
            ],
            'date_of_birth' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'medical_eligibility' => [
                'type'       => 'ENUM',
                'constraint' => ['eligible', 'ineligible', 'pending'],
                'default'    => 'eligible',
            ],
            'last_donation_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'city' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
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

        $this->forge->addKey('id', true);
        $this->forge->addKey('city');
        $this->forge->addKey('blood_group_id');
        $this->forge->addKey('medical_eligibility');

        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('blood_group_id', 'blood_groups', 'id', 'RESTRICT', 'CASCADE');

        $this->forge->createTable('donor_profiles', true);
    }

    public function down()
    {
        $this->forge->dropTable('donor_profiles', true);
    }
}
