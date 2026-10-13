<?= $this->extend('layout') ?>

<?= $this->section('title') ?>Blood Inventory Directory<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2>Blood Stock & Bank Inventory</h2>
        <p style="color: var(--text-muted);">Real-time tracking of available blood units across blood banks and medical facilities.</p>
    </div>
    <?php
        $userRole = strtolower((string) session()->get('role_name'));
        if (in_array($userRole, ['admin', 'blood bank manager', 'hospital'], true)):
    ?>
        <a href="<?= base_url('inventory/create') ?>" class="btn btn-primary">+ Add Inventory Stock</a>
    <?php endif; ?>
</div>

<!-- Group-wise Stock Summary Cards -->
<div style="margin-bottom: 2.5rem;">
    <h3 style="margin-bottom: 1rem;">Stock Summary by Blood Group</h3>
    <div class="grid grid-4">
        <?php if (! empty($totals)): ?>
            <?php foreach ($totals as $tot): ?>
                <?php
                    $units = (int) ($tot['total_units'] ?? 0);
                    $badgeClass = ($units >= 5) ? 'badge-success' : (($units > 0) ? 'badge-warning' : 'badge-danger');
                ?>
                <div class="stat-card">
                    <div class="stat-label" style="display: flex; justify-content: space-between; align-items: center;">
                        <span class="blood-badge" style="font-size: 1.1rem; padding: 0.35rem 0.85rem; display: inline-block;">
                            <?= esc($tot['blood_group_name']) ?>
                        </span>
                        <span class="badge <?= $badgeClass ?>" style="font-size: 0.75rem;">
                            <?= ($units >= 5) ? 'Available' : (($units > 0) ? 'Low Stock' : 'Out of Stock') ?>
                        </span>
                    </div>
                    <div class="stat-number" style="font-size: 1.8rem; margin-top: 0.5rem;">
                        <?= $units ?> <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: 500;">Units</span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Location Filter Form -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-body" style="padding: 1.25rem;">
        <form action="<?= base_url('inventory') ?>" method="GET" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <label for="city">Filter by City / Region</label>
                <input type="text" id="city" name="city" value="<?= esc($selectedCity) ?>" placeholder="e.g. Mumbai">
            </div>
            <button type="submit" class="btn btn-secondary">Search Inventory</button>
            <a href="<?= base_url('inventory') ?>" class="btn btn-outline-dark">Reset</a>
        </form>
    </div>
</div>

<!-- Detailed Inventory Table -->
<div class="card" style="margin-bottom: 2.5rem;">
    <div class="card-header">
        <h3 class="card-title">Detailed Stock Records</h3>
        <span class="badge badge-info"><?= count($inventory) ?> Records</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (! empty($inventory)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Blood Bank / Facility</th>
                            <th>Group</th>
                            <th>Location</th>
                            <th>Available Units</th>
                            <th>Stock Status</th>
                            <th>Expiry Date</th>
                            <?php if (in_array($userRole, ['admin', 'blood bank manager', 'hospital'], true)): ?>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventory as $item): ?>
                            <?php
                                $units = (int) $item['units_available'];
                                $statusBadge = match (true) {
                                    $units >= 5 => '<span class="badge badge-success" style="padding: 0.3rem 0.65rem;">🟢 Available</span>',
                                    $units > 0  => '<span class="badge badge-warning" style="padding: 0.3rem 0.65rem;">🟡 Low Stock</span>',
                                    default     => '<span class="badge badge-danger" style="padding: 0.3rem 0.65rem;">🔴 Out of Stock</span>',
                                };
                            ?>
                            <tr>
                                <td><strong><?= esc($item['blood_bank_name']) ?></strong></td>
                                <td><span class="blood-badge"><?= esc($item['blood_group_name']) ?></span></td>
                                <td><?= esc($item['city']) ?></td>
                                <td>
                                    <strong style="font-size: 1.05rem; color: <?= $units >= 5 ? 'var(--success)' : ($units > 0 ? '#d97706' : '#dc2626') ?>;">
                                        <?= $units ?>
                                    </strong> Units
                                </td>
                                <td><?= $statusBadge ?></td>
                                <td><?= ! empty($item['expiry_date']) ? date('M d, Y', strtotime($item['expiry_date'])) : 'N/A' ?></td>
                                <?php if (in_array($userRole, ['admin', 'blood bank manager', 'hospital'], true)): ?>
                                    <td>
                                        <a href="<?= base_url('inventory/' . $item['id'] . '/edit') ?>" class="btn btn-outline btn-sm">Edit Stock</a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M20 13H4c-.55 0-1 .45-1 1v6c0 .55.45 1 1 1h16c.55 0 1-.45 1-1v-6c0-.55-.45-1-1-1zM7 19c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zM20 3H4c-.55 0-1 .45-1 1v6c0 .55.45 1 1 1h16c.55 0 1-.45 1-1V4c0-.55-.45-1-1-1zM7 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/>
                    </svg>
                </div>
                <h3>No Inventory Records Available</h3>
                <p>There are no blood stock records matching your city filter.</p>
                <?php if (in_array($userRole, ['admin', 'blood bank manager', 'hospital'], true)): ?>
                    <a href="<?= base_url('inventory/create') ?>" class="btn btn-primary">+ Add Inventory Stock</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Inventory Transaction History Log -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Inventory Transaction History Log</h3>
        <span class="badge badge-secondary"><?= count($inventoryLogs ?? []) ?> Audit Entries</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (! empty($inventoryLogs)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Facility & Location</th>
                            <th>Group</th>
                            <th>Type</th>
                            <th>Units Delta</th>
                            <th>Stock Transition</th>
                            <th>Performed By</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventoryLogs as $log): ?>
                            <?php
                                $type = strtolower((string) $log['change_type']);
                                $typeBadge = match ($type) {
                                    'add'         => '<span class="badge badge-success">Add</span>',
                                    'fulfillment' => '<span class="badge badge-primary">Fulfillment</span>',
                                    'restoration' => '<span class="badge badge-info">Restoration</span>',
                                    'deduct'      => '<span class="badge badge-warning">Deduct</span>',
                                    default       => '<span class="badge badge-secondary">Update</span>',
                                };
                                $deltaStr = ($log['units'] > 0 ? '+' : '') . (int) $log['units'];
                            ?>
                            <tr>
                                <td><small style="color: var(--text-muted);"><?= date('M d, Y g:i A', strtotime($log['created_at'])) ?></small></td>
                                <td>
                                    <strong><?= esc($log['blood_bank_name'] ?: 'Facility #' . $log['inventory_id']) ?></strong><br>
                                    <small style="color: var(--text-muted);"><?= esc($log['city']) ?></small>
                                </td>
                                <td><span class="blood-badge"><?= esc($log['blood_group_name'] ?: 'N/A') ?></span></td>
                                <td><?= $typeBadge ?></td>
                                <td><strong style="color: <?= $log['units'] >= 0 ? 'var(--success)' : 'var(--primary)' ?>;"><?= $deltaStr ?></strong></td>
                                <td><small><?= (int) $log['previous_units'] ?> &rarr; <strong><?= (int) $log['new_units'] ?></strong></small></td>
                                <td><strong><?= esc($log['user_name'] ?: 'System User') ?></strong></td>
                                <td><small style="color: var(--text-muted);"><?= esc($log['notes'] ?: '-') ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="color: var(--text-muted); text-align: center; padding: 1.5rem 0;">No stock transaction logs recorded yet.</p>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
