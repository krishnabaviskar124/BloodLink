<?= $this->extend('layout') ?>

<?= $this->section('title') ?>Donation Details #<?= $donation['id'] ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="max-width: 720px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2>Donation Record Details #<?= $donation['id'] ?></h2>
            <p style="color: var(--text-muted);">Verified contribution record.</p>
        </div>
        <a href="<?= base_url('donations') ?>" class="btn btn-outline-dark">&larr; Back to History</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Record Details</h3>
            <span class="blood-badge" style="font-size: 1.1rem; padding: 0.35rem 0.9rem;">
                <?= esc($donation['blood_group_name']) ?>
            </span>
        </div>
        <div class="card-body">
            <div class="grid grid-2" style="row-gap: 1.25rem;">
                <div>
                    <label style="color: var(--text-muted); font-size: 0.85rem;">Donor Name</label>
                    <div style="font-size: 1.1rem; font-weight: 700; color: var(--text-dark);"><?= esc($donation['donor_name'] ?? 'N/A') ?></div>
                </div>

                <div>
                    <label style="color: var(--text-muted); font-size: 0.85rem;">Donor Phone</label>
                    <div style="font-size: 1.05rem; font-weight: 600; color: var(--text-dark);"><?= esc($donation['donor_phone'] ?? 'N/A') ?></div>
                </div>

                <div>
                    <label style="color: var(--text-muted); font-size: 0.85rem;">Donation Date</label>
                    <div style="font-size: 1.05rem; font-weight: 600; color: var(--text-dark);"><?= date('M d, Y', strtotime($donation['donation_date'])) ?></div>
                </div>

                <div>
                    <label style="color: var(--text-muted); font-size: 0.85rem;">Units Donated</label>
                    <div style="font-size: 1.1rem; font-weight: 700; color: var(--primary);"><?= esc($donation['units_donated']) ?> Unit(s)</div>
                </div>

                <div style="grid-column: span 2;">
                    <label style="color: var(--text-muted); font-size: 0.85rem;">Status</label>
                    <div>
                        <?php $st = strtolower($donation['status']); ?>
                        <span class="badge <?= $st === 'completed' ? 'badge-success' : ($st === 'scheduled' ? 'badge-info' : 'badge-secondary') ?>" style="font-size: 0.85rem; padding: 0.35rem 0.75rem;">
                            <?= ucfirst($st) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
