<?= $this->extend('layout') ?>

<?= $this->section('title') ?>Donation Records<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2>Blood Donation Records</h2>
        <p style="color: var(--text-muted);">Verified logs of blood donation drives and unit contributions.</p>
    </div>
    <?php if ($userRole === 'admin' || $userRole === 'blood bank manager'): ?>
        <a href="<?= base_url('donations/create') ?>" class="btn btn-primary">+ Record New Donation</a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Donation Logs</h3>
        <span class="badge badge-info"><?= count($donations) ?> Total Records</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (! empty($donations)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Donor Name</th>
                            <th>Group</th>
                            <th>Donation Date</th>
                            <th>Units Donated</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donations as $index => $item): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><strong><?= esc($item['donor_name'] ?? session('name')) ?></strong></td>
                                <td><span class="blood-badge"><?= esc($item['blood_group_name']) ?></span></td>
                                <td><?= date('M d, Y', strtotime($item['donation_date'])) ?></td>
                                <td><?= esc($item['units_donated']) ?> Unit(s)</td>
                                <td>
                                    <?php $st = strtolower($item['status']); ?>
                                    <span class="badge <?= $st === 'completed' ? 'badge-success' : ($st === 'scheduled' ? 'badge-info' : 'badge-secondary') ?>">
                                        <?= ucfirst($st) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= base_url('donations/' . $item['id']) ?>" class="btn btn-outline btn-sm">Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                </div>
                <h3>No Donation Records Logged</h3>
                <p>There are currently no recorded blood donations in the system log.</p>
                <?php if ($userRole === 'admin' || $userRole === 'blood bank manager'): ?>
                    <a href="<?= base_url('donations/create') ?>" class="btn btn-primary">+ Record New Donation</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
