<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $data = [
            [
                'name'        => 'Admin',
                'description' => 'System administrator with full access to manage users, inventory, and system settings.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Donor',
                'description' => 'Blood donor who can register, manage profile, view donation history, and respond to requests.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Hospital',
                'description' => 'Hospital / Recipient account that can post urgent blood requests and request units.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Blood Bank Manager',
                'description' => 'Blood Bank personnel responsible for managing local blood unit inventories and fulfillment.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ];

        // Insert batch ignore duplicates
        $builder = $this->db->table('roles');
        foreach ($data as $row) {
            if ($builder->where('name', $row['name'])->countAllResults() === 0) {
                $builder->insert($row);
            }
        }
    }
}
