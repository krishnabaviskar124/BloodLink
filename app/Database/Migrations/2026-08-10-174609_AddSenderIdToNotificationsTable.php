<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSenderIdToNotificationsTable extends Migration
{
    public function up()
    {
        $fields = [
            'sender_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'user_id',
            ],
        ];

        $this->forge->addColumn('notifications', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('notifications', 'sender_id');
    }
}
