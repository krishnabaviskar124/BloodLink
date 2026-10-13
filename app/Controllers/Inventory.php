<?php

namespace App\Controllers;

use App\Models\BloodGroupModel;
use App\Models\BloodInventoryModel;
use App\Models\InventoryLogModel;
use App\Models\NotificationModel;

class Inventory extends BaseController
{
    protected BloodInventoryModel $inventoryModel;
    protected BloodGroupModel $bloodGroupModel;
    protected NotificationModel $notificationModel;
    protected InventoryLogModel $inventoryLogModel;

    public function __construct()
    {
        $this->inventoryModel    = new BloodInventoryModel();
        $this->bloodGroupModel   = new BloodGroupModel();
        $this->notificationModel = new NotificationModel();
        $this->inventoryLogModel = new InventoryLogModel();
    }

    /**
     * View Blood Inventory directory, stock overview, and transaction history.
     */
    public function index()
    {
        $city = $this->request->getGet('city');

        if (! empty($city)) {
            $inventory = $this->inventoryModel->getInventoryByCity($city);
        } else {
            $inventory = $this->inventoryModel->getInventoryWithDetails();
        }

        $totals = $this->inventoryModel->getTotalUnitsByBloodGroup();

        // Detect low stock items (< 5 units)
        $lowStockItems = array_filter($inventory, function ($item) {
            return (int) $item['units_available'] < 5;
        });

        // Fetch recent inventory transaction history
        $inventoryLogs = $this->inventoryLogModel->getRecentLogs(25);

        $data = [
            'inventory'     => $inventory,
            'totals'        => $totals,
            'selectedCity'  => $city,
            'lowStockItems' => $lowStockItems,
            'inventoryLogs' => $inventoryLogs,
        ];

        return view('inventory/index', $data);
    }

    /**
     * Show form to add new blood inventory stock (Authorized personnel only).
     */
    public function create()
    {
        $userRole = strtolower((string) session()->get('role_name'));
        if (! in_array($userRole, ['admin', 'blood bank manager', 'hospital'], true)) {
            session()->setFlashdata('error', 'Unauthorized action: You do not have permission to manage inventory.');
            return redirect()->to('/inventory');
        }

        $data['bloodGroups'] = $this->bloodGroupModel->findAll();

        return view('inventory/create', $data);
    }

    /**
     * Store new blood inventory entry with transaction logging.
     */
    public function store()
    {
        $userRole = strtolower((string) session()->get('role_name'));
        if (! in_array($userRole, ['admin', 'blood bank manager', 'hospital'], true)) {
            session()->setFlashdata('error', 'Unauthorized action: You do not have permission to manage inventory.');
            return redirect()->to('/inventory');
        }

        $rules = [
            'blood_group_id'  => 'required|is_natural_no_zero|is_not_unique[blood_groups.id]',
            'blood_bank_name' => 'required|max_length[150]',
            'city'            => 'required|max_length[100]',
            'units_available' => 'required|integer|greater_than_equal_to[0]',
            'expiry_date'     => 'permit_empty|valid_date',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $units = (int) $this->request->getPost('units_available');
        if ($units < 0) {
            return redirect()->back()->withInput()->with('errors', ['units_available' => 'Units available cannot be negative.']);
        }

        $stockData = [
            'blood_group_id'  => $this->request->getPost('blood_group_id'),
            'blood_bank_name' => $this->request->getPost('blood_bank_name'),
            'city'            => $this->request->getPost('city'),
            'units_available' => $units,
            'expiry_date'     => $this->request->getPost('expiry_date') ?: null,
        ];

        $inventoryId = $this->inventoryModel->insert($stockData);
        $userId      = (int) session()->get('user_id');

        // Log transaction history for creation
        $this->inventoryLogModel->insert([
            'inventory_id'   => $inventoryId,
            'user_id'        => $userId,
            'change_type'    => 'add',
            'units'          => $units,
            'previous_units' => 0,
            'new_units'      => $units,
            'notes'          => 'Initial inventory stock entry created',
        ]);

        // Low stock detection alert
        if ($units < 5) {
            $alertTitle = ($units === 0) ? '🚨 Out of Blood Stock Alert' : '⚠️ Low Blood Stock Alert';
            $alertMsg   = 'Facility ' . $stockData['blood_bank_name'] . ' (' . $stockData['city'] . ') has ' . ($units === 0 ? 'OUT OF STOCK' : "LOW STOCK ({$units} units left)") . '.';
            $this->notificationModel->notifyRole(4, $alertTitle, $alertMsg, 'inventory_alert', $userId, null, 'high');
            $this->notificationModel->notifyRole(1, $alertTitle, $alertMsg, 'inventory_alert', $userId, null, 'high');
        }

        session()->setFlashdata('success', 'Blood inventory entry added successfully!');

        return redirect()->to('/inventory');
    }

    /**
     * Show form to edit existing blood inventory stock entry.
     */
    public function edit($id = null)
    {
        $userRole = strtolower((string) session()->get('role_name'));
        if (! in_array($userRole, ['admin', 'blood bank manager', 'hospital'], true)) {
            session()->setFlashdata('error', 'Unauthorized action: You do not have permission to edit inventory.');
            return redirect()->to('/inventory');
        }

        $item = $this->inventoryModel->find((int) $id);

        if (! $item) {
            session()->setFlashdata('error', 'Inventory record not found.');
            return redirect()->to('/inventory');
        }

        $data = [
            'item'        => $item,
            'bloodGroups' => $this->bloodGroupModel->findAll(),
        ];

        return view('inventory/edit', $data);
    }

    /**
     * Update blood inventory entry with negative stock prevention and history logging.
     */
    public function update($id = null)
    {
        $userRole = strtolower((string) session()->get('role_name'));
        if (! in_array($userRole, ['admin', 'blood bank manager', 'hospital'], true)) {
            session()->setFlashdata('error', 'Unauthorized action: You do not have permission to edit inventory.');
            return redirect()->to('/inventory');
        }

        $item = $this->inventoryModel->find((int) $id);

        if (! $item) {
            session()->setFlashdata('error', 'Inventory record not found.');
            return redirect()->to('/inventory');
        }

        $rules = [
            'blood_group_id'  => 'required|is_natural_no_zero|is_not_unique[blood_groups.id]',
            'blood_bank_name' => 'required|max_length[150]',
            'city'            => 'required|max_length[100]',
            'units_available' => 'required|integer|greater_than_equal_to[0]',
            'expiry_date'     => 'permit_empty|valid_date',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $newUnits  = (int) $this->request->getPost('units_available');
        $prevUnits = (int) $item['units_available'];
        $delta     = $newUnits - $prevUnits;

        if ($newUnits < 0) {
            return redirect()->back()->withInput()->with('errors', ['units_available' => 'Units available cannot be negative.']);
        }

        $stockData = [
            'blood_group_id'  => $this->request->getPost('blood_group_id'),
            'blood_bank_name' => $this->request->getPost('blood_bank_name'),
            'city'            => $this->request->getPost('city'),
            'units_available' => $newUnits,
            'expiry_date'     => $this->request->getPost('expiry_date') ?: null,
        ];

        $this->inventoryModel->update($id, $stockData);
        $userId = (int) session()->get('user_id');

        // Log transaction history
        $this->inventoryLogModel->insert([
            'inventory_id'   => $id,
            'user_id'        => $userId,
            'change_type'    => ($delta >= 0 ? 'add' : 'deduct'),
            'units'          => $delta,
            'previous_units' => $prevUnits,
            'new_units'      => $newUnits,
            'notes'          => 'Manual inventory update by ' . session()->get('name'),
        ]);

        // Low stock detection alert
        if ($newUnits < 5) {
            $alertTitle = ($newUnits === 0) ? '🚨 Out of Blood Stock Alert' : '⚠️ Low Blood Stock Alert';
            $alertMsg   = 'Facility ' . $stockData['blood_bank_name'] . ' (' . $stockData['city'] . ') has ' . ($newUnits === 0 ? 'OUT OF STOCK' : "LOW STOCK ({$newUnits} units left)") . '.';
            $this->notificationModel->notifyRole(4, $alertTitle, $alertMsg, 'inventory_alert', $userId, null, 'high');
            $this->notificationModel->notifyRole(1, $alertTitle, $alertMsg, 'inventory_alert', $userId, null, 'high');
        }

        session()->setFlashdata('success', 'Blood inventory entry updated successfully!');

        return redirect()->to('/inventory');
    }
}
