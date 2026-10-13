<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPriorityAndRequestIdToNotificationsTable extends Migration
{
    public function up()
    {
        $fields = [
            'request_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'sender_id',
            ],
            'priority' => [
                'type'       => 'ENUM',
                'constraint' => ['critical', 'urgent', 'normal'],
                'default'    => 'normal',
                'after'      => 'type',
            ],
        ];

        $this->forge->addColumn('notifications', $fields);
        $this->forge->addKey('request_id');
        $this->forge->addKey('priority');
    }

    public function down()
    {
        $this->forge->dropColumn('notifications', ['request_id', 'priority']);
    }
}
