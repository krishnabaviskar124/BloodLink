<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateNotificationsPriorityEnum extends Migration
{
    public function up()
    {
        $fields = [
            'priority' => [
                'type'       => 'ENUM',
                'constraint' => ['critical', 'high', 'urgent', 'normal', 'low'],
                'default'    => 'normal',
            ],
        ];

        $this->forge->modifyColumn('notifications', $fields);
    }

    public function down()
    {
        $fields = [
            'priority' => [
                'type'       => 'ENUM',
                'constraint' => ['critical', 'urgent', 'normal'],
                'default'    => 'normal',
            ],
        ];

        $this->forge->modifyColumn('notifications', $fields);
    }
}
