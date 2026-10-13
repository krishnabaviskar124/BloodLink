<?= $this->extend('layout') ?>

<?= $this->section('title') ?>Hospital Emergency Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2>Hospital Emergency Dashboard</h2>
        <p style="color: var(--text-muted);">Manage emergency blood requests & track real-time fulfillment for <?= esc(session('name')) ?>.</p>
    </div>
    <a href="<?= base_url('requests/create') ?>" class="btn btn-primary" style="background: var(--primary); color: #ffffff;">+ Create Emergency Blood Request</a>
</div>

<!-- Dynamic Stats Summary Grid -->
<div class="grid grid-4" style="margin-bottom: 2.5rem; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
    <div class="stat-card" style="border-left: 4px solid var(--primary);">
        <div class="stat-number"><?= $totalCount ?></div>
        <div class="stat-label">Total Requests</div>
    </div>
    <div class="stat-card" style="border-left: 4px solid #f59e0b;">
        <div class="stat-number" style="color: #d97706;"><?= $pendingCount ?></div>
        <div class="stat-label">Active / Pending</div>
    </div>
    <div class="stat-card" style="border-left: 4px solid #ef4444;">
        <div class="stat-number" style="color: #dc2626;"><?= $urgentCount ?></div>
        <div class="stat-label">Urgent Requests</div>
    </div>
    <div class="stat-card" style="border-left: 4px solid #991b1b; background: #fff5f5;">
        <div class="stat-number" style="color: #991b1b; font-weight: 800;"><?= $criticalCount ?></div>
        <div class="stat-label" style="color: #991b1b; font-weight: 700;">CRITICAL REQUESTS</div>
    </div>
</div>

<!-- Hospital Requests Listing -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Hospital Emergency Requests</h3>
        <span class="badge badge-info"><?= $totalCount ?> Total Posted</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (! empty($requests)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Blood Group</th>
                            <th>Units</th>
                            <th>Urgency</th>
                            <th>Required Date</th>
                            <th>Status</th>
                            <th>Posted Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $index => $req): ?>
                            <?php $urg = strtolower($req['urgency']); ?>
                            <tr style="<?= $urg === 'critical' ? 'background-color: #fff1f2;' : '' ?>">
                                <td><strong>#<?= $req['id'] ?></strong></td>
                                <td><span class="blood-badge"><?= esc($req['blood_group_name']) ?></span></td>
                                <td><strong style="color: var(--primary);"><?= esc($req['units_required']) ?></strong> Unit(s)</td>
                                <td>
                                    <?php if ($urg === 'critical'): ?>
                                        <span class="badge badge-danger" style="background: #991b1b; color: #ffffff; padding: 0.35rem 0.75rem; font-weight: 700; animation: pulse 2s infinite;">
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
                                    <?php $st = strtolower($req['status']); ?>
                                    <span class="badge <?= $st === 'fulfilled' ? 'badge-success' : ($st === 'cancelled' ? 'badge-secondary' : 'badge-warning') ?>" style="padding: 0.35rem 0.75rem;">
                                        <?= ucfirst(str_replace('_', ' ', $st)) ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y H:i', strtotime($req['created_at'])) ?></td>
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
                <p style="color: var(--text-muted); font-size: 1.05rem;">Your hospital has not submitted any emergency blood requests yet.</p>
                <a href="<?= base_url('requests/create') ?>" class="btn btn-primary" style="margin-top: 1rem;">Create First Request</a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
