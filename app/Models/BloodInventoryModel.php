<?php

namespace App\Models;

use CodeIgniter\Model;

class BloodInventoryModel extends Model
{
    protected $table            = 'blood_inventory';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'blood_group_id',
        'blood_bank_name',
        'city',
        'units_available',
        'expiry_date',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'blood_group_id'  => 'required|is_natural_no_zero|is_not_unique[blood_groups.id]',
        'blood_bank_name' => 'required|max_length[150]',
        'city'            => 'required|max_length[100]',
        'units_available' => 'required|integer|greater_than_equal_to[0]',
        'expiry_date'     => 'permit_empty|valid_date',
    ];

    protected $validationMessages = [
        'blood_group_id' => [
            'is_not_unique' => 'Selected blood group does not exist.',
        ],
        'units_available' => [
            'greater_than_equal_to' => 'Units available cannot be negative.',
        ],
    ];

    /**
     * Automatically calculate stock status based on stock thresholds.
     */
    public function calculateStockStatus(int $units): array
    {
        if ($units >= 5) {
            return [
                'status' => 'available',
                'label'  => 'Available',
                'badge'  => 'badge-success',
            ];
        }

        if ($units > 0) {
            return [
                'status' => 'low_stock',
                'label'  => 'Low Stock',
                'badge'  => 'badge-warning',
            ];
        }

        return [
            'status' => 'out_of_stock',
            'label'  => 'Out of Stock',
            'badge'  => 'badge-danger',
        ];
    }

    /**
     * Get inventory list with blood group names and stock status info.
     */
    public function getInventoryWithDetails(): array
    {
        $items = $this->select('blood_inventory.*, blood_groups.name as blood_group_name')
                      ->join('blood_groups', 'blood_groups.id = blood_inventory.blood_group_id', 'left')
                      ->orderBy('blood_inventory.city', 'ASC')
                      ->findAll();

        foreach ($items as &$item) {
            $item['stock_info'] = $this->calculateStockStatus((int) $item['units_available']);
        }
        unset($item);

        return $items;
    }

    /**
     * Get inventory records for a specific city.
     */
    public function getInventoryByCity(string $city): array
    {
        $items = $this->select('blood_inventory.*, blood_groups.name as blood_group_name')
                      ->join('blood_groups', 'blood_groups.id = blood_inventory.blood_group_id', 'left')
                      ->like('blood_inventory.city', $city)
                      ->findAll();

        foreach ($items as &$item) {
            $item['stock_info'] = $this->calculateStockStatus((int) $item['units_available']);
        }
        unset($item);

        return $items;
    }

    /**
     * Get aggregate available units by blood group across all banks.
     */
    public function getTotalUnitsByBloodGroup(): array
    {
        return $this->select('blood_groups.name as blood_group_name, SUM(blood_inventory.units_available) as total_units')
                    ->join('blood_groups', 'blood_groups.id = blood_inventory.blood_group_id', 'right')
                    ->groupBy('blood_groups.id')
                    ->findAll();
    }

    /**
     * Safely adjust stock with negative stock validation, transaction history logging, and stock alert notifications.
     */
    public function adjustStock(int $inventoryId, int $deltaUnits, int $userId, string $changeType = 'update', string $notes = ''): array
    {
        $item = $this->find($inventoryId);
        if (! $item) {
            return ['success' => false, 'error' => 'Inventory record not found.'];
        }

        $prevUnits = (int) $item['units_available'];
        $newUnits  = $prevUnits + $deltaUnits;

        // Negative stock prevention
        if ($newUnits < 0) {
            return ['success' => false, 'error' => 'Insufficient stock! Action would cause negative stock (' . $newUnits . ').'];
        }

        // Update inventory record
        $this->update($inventoryId, [
            'units_available' => $newUnits,
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);

        // Insert inventory log entry
        $logModel = new InventoryLogModel();
        $logModel->insert([
            'inventory_id'   => $inventoryId,
            'user_id'        => $userId,
            'change_type'    => $changeType,
            'units'          => $deltaUnits,
            'previous_units' => $prevUnits,
            'new_units'      => $newUnits,
            'notes'          => $notes,
        ]);

        // Low / Out of stock alert notification
        if ($newUnits < 5) {
            $notificationModel = new NotificationModel();
            $alertTitle        = ($newUnits === 0) ? '🚨 Out of Blood Stock Alert' : '⚠️ Low Blood Stock Alert';
            $alertMsg          = "Facility {$item['blood_bank_name']} ({$item['city']}) has " . ($newUnits === 0 ? 'OUT OF STOCK' : "LOW STOCK ({$newUnits} units left)") . ".";
            
            $notificationModel->notifyRole(1, $alertTitle, $alertMsg, 'inventory_alert', $userId, null, 'high');
            $notificationModel->notifyRole(4, $alertTitle, $alertMsg, 'inventory_alert', $userId, null, 'high');
        }

        return ['success' => true, 'previous_units' => $prevUnits, 'new_units' => $newUnits];
    }

    /**
     * Safely deduct inventory stock upon blood request fulfillment.
     */
    public function deductForFulfillment(int $bloodGroupId, string $city, int $unitsToDeduct, int $userId, int $requestId): array
    {
        $matching = $this->where('blood_group_id', $bloodGroupId)
                         ->like('city', trim($city))
                         ->where('units_available >=', $unitsToDeduct)
                         ->first();

        if (! $matching) {
            // Fallback: search any inventory for this blood group with sufficient units
            $matching = $this->where('blood_group_id', $bloodGroupId)
                             ->where('units_available >=', $unitsToDeduct)
                             ->first();
        }

        if (! $matching) {
            return ['success' => false, 'error' => 'No single inventory entry has sufficient stock to fulfill ' . $unitsToDeduct . ' units.'];
        }

        return $this->adjustStock(
            (int) $matching['id'],
            -$unitsToDeduct,
            $userId,
            'fulfillment',
            "Fulfilled emergency blood request #{$requestId}"
        );
    }

    /**
     * Restore stock upon request cancellation/reversal.
     */
    public function restoreForCancellation(int $bloodGroupId, string $city, int $unitsToRestore, int $userId, int $requestId): array
    {
        $matching = $this->where('blood_group_id', $bloodGroupId)
                         ->like('city', trim($city))
                         ->first();

        if (! $matching) {
            $matching = $this->where('blood_group_id', $bloodGroupId)->first();
        }

        if (! $matching) {
            return ['success' => false, 'error' => 'No inventory record found to restore stock.'];
        }

        return $this->adjustStock(
            (int) $matching['id'],
            $unitsToRestore,
            $userId,
            'restoration',
            "Restored stock from cancelled request #{$requestId}"
        );
    }
}
