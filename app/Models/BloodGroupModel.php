<?php

namespace App\Models;

use CodeIgniter\Model;

class BloodGroupModel extends Model
{
    protected $table            = 'blood_groups';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|max_length[10]|is_unique[blood_groups.name,id,{id}]',
    ];

    protected $validationMessages = [
        'name' => [
            'required'  => 'Blood group name is required.',
            'is_unique' => 'This blood group already exists.',
        ],
    ];

    /**
     * Get a blood group record by name (e.g., 'A+').
     */
    public function getBloodGroupByName(string $name): ?array
    {
        return $this->where('name', $name)->first();
    }
}
