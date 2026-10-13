<?= $this->extend('layout') ?>

<?= $this->section('title') ?>My Donation History<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="margin-bottom: 2rem;">
    <h2>My Donation History</h2>
    <p style="color: var(--text-muted);">Verified records of your past blood donations.</p>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Completed & Scheduled Donations</h3>
        <span class="badge badge-info"><?= count($donations) ?> Records</span>
    </div>
    <div class="card-body">
        <?php if (! empty($donations)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Donation Date</th>
                            <th>Blood Group</th>
                            <th>Units Donated</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donations as $index => $item): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><strong><?= date('M d, Y', strtotime($item['donation_date'])) ?></strong></td>
                                <td><span class="blood-badge"><?= esc($item['blood_group_name']) ?></span></td>
                                <td><?= esc($item['units_donated']) ?> Unit(s)</td>
                                <td>
                                    <?php $status = strtolower($item['status']); ?>
                                    <span class="badge <?= $status === 'completed' ? 'badge-success' : ($status === 'scheduled' ? 'badge-info' : 'badge-secondary') ?>">
                                        <?= ucfirst($status) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 2.5rem 1rem;">
                <p style="color: var(--text-muted);">You have no recorded blood donations yet.</p>
                <p style="font-size: 0.9rem; color: var(--text-muted);">When you donate blood at partner blood banks or hospitals, your verified donation history will appear here.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
