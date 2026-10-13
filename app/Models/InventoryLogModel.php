<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryLogModel extends Model
{
    protected $table            = 'inventory_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'inventory_id',
        'user_id',
        'change_type',
        'units',
        'previous_units',
        'new_units',
        'notes',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /**
     * Get recent transaction history logs with user and inventory details.
     */
    public function getRecentLogs(int $limit = 25): array
    {
        return $this->select('inventory_logs.*, users.name as user_name, users.role_id, blood_inventory.blood_bank_name, blood_inventory.city, blood_groups.name as blood_group_name')
                    ->join('users', 'users.id = inventory_logs.user_id', 'left')
                    ->join('blood_inventory', 'blood_inventory.id = inventory_logs.inventory_id', 'left')
                    ->join('blood_groups', 'blood_groups.id = blood_inventory.blood_group_id', 'left')
                    ->orderBy('inventory_logs.created_at', 'DESC')
                    ->findAll($limit);
    }
}
