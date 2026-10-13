<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BloodGroupsSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

        $builder = $this->db->table('blood_groups');
        foreach ($groups as $name) {
            if ($builder->where('name', $name)->countAllResults() === 0) {
                $builder->insert([
                    'name'       => $name,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
