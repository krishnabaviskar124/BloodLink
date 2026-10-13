<?= $this->extend('layout') ?>

<?= $this->section('title') ?>Blood Bank Manager Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2>Blood Bank Management Dashboard</h2>
        <p style="color: var(--text-muted);">Manage inventory stock, donor entries, and local fulfillment.</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="<?= base_url('inventory/create') ?>" class="btn btn-primary">+ Add Stock</a>
        <a href="<?= base_url('donations/create') ?>" class="btn btn-outline">+ Record Donation</a>
    </div>
</div>

<!-- Key Stats -->
<div class="grid grid-3" style="margin-bottom: 2.5rem;">
    <div class="stat-card">
        <div class="stat-number"><?= $totalUnits ?></div>
        <div class="stat-label">Total Blood Units in Stock</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: var(--info);"><?= count($inventory) ?></div>
        <div class="stat-label">Stock Facility Records</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: var(--warning);"><?= count($pendingReqs) ?></div>
        <div class="stat-label">Pending Emergency Requests</div>
    </div>
</div>

<!-- Stock Overview & Actions -->
<div class="grid grid-2" style="margin-bottom: 2.5rem;">

    <!-- Recent Pending Requests -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Pending Emergency Requests</h3>
            <a href="<?= base_url('requests') ?>" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="card-body">
            <?php if (! empty($pendingReqs)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Hospital</th>
                                <th>Group</th>
                                <th>Units</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingReqs as $req): ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($req['hospital_name']) ?></strong><br>
                                        <small style="color: var(--text-muted);"><?= esc($req['city']) ?></small>
                                    </td>
                                    <td><span class="blood-badge"><?= esc($req['blood_group_name']) ?></span></td>
                                    <td><?= esc($req['units_required']) ?></td>
                                    <td>
                                        <a href="<?= base_url('requests/' . $req['id']) ?>" class="btn btn-outline btn-sm">Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="color: var(--text-muted); text-align: center; padding: 1.5rem 0;">No pending requests found.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Donations Received -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Donations Logged</h3>
            <a href="<?= base_url('donations') ?>" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="card-body">
            <?php if (! empty($recentDonations)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Donor</th>
                                <th>Group</th>
                                <th>Date</th>
                                <th>Units</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentDonations as $d): ?>
                                <tr>
                                    <td><strong><?= esc($d['donor_name']) ?></strong></td>
                                    <td><span class="blood-badge"><?= esc($d['blood_group_name']) ?></span></td>
                                    <td><?= date('M d, Y', strtotime($d['donation_date'])) ?></td>
                                    <td><?= esc($d['units_donated']) ?> Unit(s)</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="color: var(--text-muted); text-align: center; padding: 1.5rem 0;">No donations logged recently.</p>
            <?php endif; ?>
        </div>
    </div>

</div>
<?= $this->endSection() ?>
