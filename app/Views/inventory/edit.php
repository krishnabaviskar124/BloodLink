<?= $this->extend('layout') ?>

<?= $this->section('title') ?>Edit Inventory Record #<?= $item['id'] ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="max-width: 650px; margin: 0 auto;">
    <div style="margin-bottom: 2rem;">
        <h2>Edit Inventory Stock Record #<?= $item['id'] ?></h2>
        <p style="color: var(--text-muted);">Update available blood unit counts and facility information.</p>
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
            <form action="<?= base_url('inventory/' . $item['id'] . '/update') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="blood_bank_name">Blood Bank / Facility Name *</label>
                    <input type="text" id="blood_bank_name" name="blood_bank_name" value="<?= old('blood_bank_name', $item['blood_bank_name']) ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="blood_group_id">Blood Group *</label>
                        <select id="blood_group_id" name="blood_group_id" required>
                            <option value="">-- Select Blood Group --</option>
                            <?php foreach ($bloodGroups as $bg): ?>
                                <option value="<?= $bg['id'] ?>" <?= old('blood_group_id', $item['blood_group_id']) == $bg['id'] ? 'selected' : '' ?>>
                                    <?= esc($bg['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="city">City / Region *</label>
                        <input type="text" id="city" name="city" value="<?= old('city', $item['city']) ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="units_available">Units Available *</label>
                        <input type="number" id="units_available" name="units_available" value="<?= old('units_available', $item['units_available']) ?>" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="expiry_date">Expiration Date</label>
                        <input type="date" id="expiry_date" name="expiry_date" value="<?= old('expiry_date', $item['expiry_date']) ?>">
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">Update Stock Record</button>
                    <a href="<?= base_url('inventory') ?>" class="btn btn-outline-dark">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
