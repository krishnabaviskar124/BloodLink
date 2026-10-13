<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBloodInventoryTable extends Migration
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
            'blood_group_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'blood_bank_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => false,
            ],
            'city' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'units_available' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'expiry_date' => [
                'type' => 'DATE',
                'null' => true,
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
        $this->forge->addKey('blood_group_id');
        $this->forge->addKey('city');

        $this->forge->addForeignKey('blood_group_id', 'blood_groups', 'id', 'RESTRICT', 'CASCADE');

        $this->forge->createTable('blood_inventory', true);
    }

    public function down()
    {
        $this->forge->dropTable('blood_inventory', true);
    }
}
