<?= $this->extend('layout') ?>

<?= $this->section('title') ?>Emergency Blood Requests Directory<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2>Emergency Blood Requests</h2>
        <p style="color: var(--text-muted);">Real-time blood requirement registry prioritized by urgency level.</p>
    </div>
    <?php
        $userRole = strtolower((string) session('role_name'));
        if (session('isLoggedIn') && $userRole !== 'donor'):
    ?>
        <a href="<?= base_url('requests/create') ?>" class="btn btn-primary">+ Create Emergency Request</a>
    <?php endif; ?>
</div>

<!-- Search & Filtering Controls Card -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-body">
        <form action="<?= base_url('requests') ?>" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 1; min-width: 160px;">
                <label for="blood_group_id">Blood Group</label>
                <select id="blood_group_id" name="blood_group_id">
                    <option value="">All Blood Groups</option>
                    <?php foreach ($bloodGroups as $bg): ?>
                        <option value="<?= $bg['id'] ?>" <?= $selectedGroup == $bg['id'] ? 'selected' : '' ?>>
                            <?= esc($bg['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="flex: 1; min-width: 140px;">
                <label for="urgency">Urgency Level</label>
                <select id="urgency" name="urgency">
                    <option value="">All Urgencies</option>
                    <option value="critical" <?= $selectedUrgency === 'critical' ? 'selected' : '' ?>>Critical Only</option>
                    <option value="urgent" <?= $selectedUrgency === 'urgent' ? 'selected' : '' ?>>Urgent Only</option>
                    <option value="normal" <?= $selectedUrgency === 'normal' ? 'selected' : '' ?>>Normal Only</option>
                </select>
            </div>

            <div style="flex: 1; min-width: 140px;">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="all" <?= $selectedStatus === 'all' ? 'selected' : '' ?>>All Statuses</option>
                    <option value="pending" <?= $selectedStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= $selectedStatus === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="fulfilled" <?= $selectedStatus === 'fulfilled' ? 'selected' : '' ?>>Fulfilled</option>
                    <option value="completed" <?= $selectedStatus === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= $selectedStatus === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>

            <div style="flex: 1; min-width: 160px;">
                <label for="city">City / Region</label>
                <input type="text" id="city" name="city" value="<?= esc($selectedCity) ?>" placeholder="e.g. Mumbai">
            </div>

            <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.25rem;">Filter Requests</button>
            <a href="<?= base_url('requests') ?>" class="btn btn-outline-dark">Reset</a>
        </form>
    </div>
</div>

<!-- Requests Table Card -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Active Blood Requirements</h3>
        <span class="badge badge-info"><?= count($requests) ?> Requirements Found</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (! empty($requests)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Group Needed</th>
                            <th>Units Required</th>
                            <th>Hospital / Facility</th>
                            <th>Location</th>
                            <th>Urgency</th>
                            <th>Date Needed</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $req): ?>
                            <?php $urg = strtolower($req['urgency']); ?>
                            <tr style="<?= $urg === 'critical' ? 'background-color: #fff1f2;' : '' ?>">
                                <td><strong>#<?= $req['id'] ?></strong></td>
                                <td><span class="blood-badge"><?= esc($req['blood_group_name']) ?></span></td>
                                <td><strong style="color: var(--primary);"><?= esc($req['units_required']) ?></strong> Unit(s)</td>
                                <td><strong><?= esc($req['hospital_name']) ?></strong></td>
                                <td><?= esc($req['city']) ?></td>
                                <td>
                                    <?php if ($urg === 'critical'): ?>
                                        <span class="badge badge-danger" style="background: #991b1b; color: #ffffff; padding: 0.35rem 0.75rem; font-weight: 700;">
                                            CRITICAL
                                        </span>
                                    <?php elseif ($urg === 'urgent' || $urg === 'high'): ?>
                                        <span class="badge badge-danger" style="padding: 0.35rem 0.75rem;">
                                            Urgent
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-info" style="padding: 0.35rem 0.75rem;">
                                            Normal
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M d, Y', strtotime($req['required_date'])) ?></td>
                                <td>
                                    <?php
                                        $st = strtolower($req['status']);
                                        $badgeClass = match ($st) {
                                            'fulfilled', 'completed' => 'badge-success',
                                            'approved'              => 'badge-info',
                                            'cancelled'             => 'badge-secondary',
                                            default                 => 'badge-warning',
                                        };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>" style="padding: 0.35rem 0.75rem;">
                                        <?= ucfirst(str_replace('_', ' ', $st)) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= base_url('requests/' . $req['id']) ?>" class="btn btn-outline btn-sm">View Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state" style="padding: 3rem 1rem;">
                <p style="color: var(--text-muted); font-size: 1.05rem;">No emergency blood requests matched your search criteria.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
