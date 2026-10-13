<?= $this->extend('layout') ?>

<?= $this->section('title') ?>Add Inventory Stock<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="max-width: 650px; margin: 0 auto;">
    <div style="margin-bottom: 2rem;">
        <h2>Add Blood Inventory Stock</h2>
        <p style="color: var(--text-muted);">Record new available units at a blood bank or medical facility.</p>
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
            <form action="<?= base_url('inventory/store') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="blood_bank_name">Blood Bank / Facility Name *</label>
                    <input type="text" id="blood_bank_name" name="blood_bank_name" value="<?= old('blood_bank_name') ?>" placeholder="e.g. Central Red Cross Blood Bank" required>
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
                        <label for="city">City / Region *</label>
                        <input type="text" id="city" name="city" value="<?= old('city', session('city')) ?>" placeholder="e.g. New York" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="units_available">Units Available *</label>
                        <input type="number" id="units_available" name="units_available" value="<?= old('units_available', 10) ?>" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="expiry_date">Expiration Date</label>
                        <input type="date" id="expiry_date" name="expiry_date" value="<?= old('expiry_date') ?>">
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">Save Inventory Record</button>
                    <a href="<?= base_url('inventory') ?>" class="btn btn-outline-dark">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
