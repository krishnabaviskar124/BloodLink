<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventoryLogsTable extends Migration
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
            'inventory_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'change_type' => [
                'type'       => 'ENUM',
                'constraint' => ['add', 'update', 'deduct', 'fulfillment', 'restoration'],
                'default'    => 'update',
            ],
            'units' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'previous_units' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'new_units' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'notes' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('inventory_id');
        $this->forge->addKey('user_id');

        $this->forge->createTable('inventory_logs', true);
    }

    public function down()
    {
        $this->forge->dropTable('inventory_logs', true);
    }
}
