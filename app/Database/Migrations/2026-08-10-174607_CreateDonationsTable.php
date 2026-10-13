<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDonationsTable extends Migration
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
            'donor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'blood_group_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'donation_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'units_donated' => [
                'type'       => 'INT',
                'constraint' => 5,
                'default'    => 1,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['scheduled', 'completed', 'cancelled'],
                'default'    => 'completed',
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
        $this->forge->addKey('donor_id');
        $this->forge->addKey('blood_group_id');
        $this->forge->addKey('donation_date');

        $this->forge->addForeignKey('donor_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('blood_group_id', 'blood_groups', 'id', 'RESTRICT', 'CASCADE');

        $this->forge->createTable('donations', true);
    }

    public function down()
    {
        $this->forge->dropTable('donations', true);
    }
}
