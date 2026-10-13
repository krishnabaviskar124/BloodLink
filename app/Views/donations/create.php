<?= $this->extend('layout') ?>

<?= $this->section('title') ?>Record New Donation<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="max-width: 650px; margin: 0 auto;">
    <div style="margin-bottom: 2rem;">
        <h2>Record New Blood Donation</h2>
        <p style="color: var(--text-muted);">Enter details for a completed or scheduled donor contribution.</p>
    </div>

    <?php if (session('errors')): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form action="<?= base_url('donations/store') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="donor_id">Select Registered Donor *</label>
                    <select id="donor_id" name="donor_id" required>
                        <option value="">-- Select Donor --</option>
                        <?php foreach ($donors as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= old('donor_id') == $d['id'] ? 'selected' : '' ?>>
                                <?= esc($d['name']) ?> (<?= esc($d['email']) ?> - <?= esc($d['city']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="blood_group_id">Blood Group *</label>
                        <select id="blood_group_id" name="blood_group_id" required>
                            <option value="">-- Select Blood Group --</option>
                            <?php foreach ($bloodGroups as $bg): ?>
                                <option value="<?= $bg['id'] ?>" <?= old('blood_group_id') == $bg['id'] ? 'selected' : '' ?>>
                                    <?= esc($bg['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="units_donated">Units Donated *</label>
                        <input type="number" id="units_donated" name="units_donated" value="<?= old('units_donated', 1) ?>" min="1" max="10" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="donation_date">Donation Date *</label>
                        <input type="date" id="donation_date" name="donation_date" value="<?= old('donation_date', date('Y-m-d')) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="status">Donation Status</label>
                        <select id="status" name="status">
                            <option value="completed" <?= old('status') === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="scheduled" <?= old('status') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                            <option value="cancelled" <?= old('status') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">Save Donation Record</button>
                    <a href="<?= base_url('donations') ?>" class="btn btn-outline-dark">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
