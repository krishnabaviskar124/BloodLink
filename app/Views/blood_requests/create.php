<?= $this->extend('layout') ?>

<?= $this->section('title') ?>Post Emergency Blood Request<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="max-width: 750px; margin: 0 auto;">
    <div style="margin-bottom: 2rem;">
        <h2>Post Emergency Blood Request</h2>
        <p style="color: var(--text-muted);">Submit an urgent blood request for patient requirement. Compatible voluntary donors and nearby inventory stock will be notified immediately.</p>
    </div>

    <!-- Error Alert Component -->
    <?php if (session()->has('errors')): ?>
        <div style="background-color: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem;">
            <strong style="display: block; margin-bottom: 0.5rem;">Please correct the following errors:</strong>
            <ul style="margin: 0; padding-left: 1.25rem;">
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form action="<?= base_url('requests/store') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="grid grid-2" style="gap: 1.25rem; margin-bottom: 1.25rem;">
                    <div>
                        <label for="blood_group_id">Required Blood Group *</label>
                        <select id="blood_group_id" name="blood_group_id" required>
                            <option value="">-- Select Blood Group --</option>
                            <?php foreach ($bloodGroups as $bg): ?>
                                <option value="<?= $bg['id'] ?>" <?= old('blood_group_id') == $bg['id'] ? 'selected' : '' ?>>
                                    <?= esc($bg['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="units_required">Units Required *</label>
                        <input type="number" id="units_required" name="units_required" min="1" max="50" value="<?= esc(old('units_required', 1)) ?>" required placeholder="e.g. 2">
                    </div>
                </div>

                <div class="grid grid-2" style="gap: 1.25rem; margin-bottom: 1.25rem;">
                    <div>
                        <label for="urgency">Urgency Priority *</label>
                        <select id="urgency" name="urgency" required style="font-weight: 600;">
                            <option value="normal" <?= old('urgency') === 'normal' ? 'selected' : '' ?>>Normal Priority</option>
                            <option value="urgent" <?= old('urgency') === 'urgent' ? 'selected' : '' ?>>Urgent Priority</option>
                            <option value="critical" <?= old('urgency') === 'critical' ? 'selected' : '' ?> style="color: #dc2626; font-weight: 700;">CRITICAL EMERGENCY</option>
                        </select>
                    </div>

                    <div>
                        <label for="required_date">Required By Date *</label>
                        <input type="date" id="required_date" name="required_date" min="<?= date('Y-m-d') ?>" value="<?= esc(old('required_date', date('Y-m-d'))) ?>" required>
                    </div>
                </div>

                <div class="grid grid-2" style="gap: 1.25rem; margin-bottom: 1.25rem;">
                    <div>
                        <label for="hospital_name">Hospital / Medical Facility *</label>
                        <input type="text" id="hospital_name" name="hospital_name" value="<?= esc(old('hospital_name', session('name'))) ?>" required placeholder="Hospital name">
                    </div>

                    <div>
                        <label for="city">City / Region *</label>
                        <input type="text" id="city" name="city" value="<?= esc(old('city', session('city'))) ?>" required placeholder="e.g. Mumbai">
                    </div>
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label for="hospital_address">Full Hospital Address (Optional)</label>
                    <input type="text" id="hospital_address" name="hospital_address" value="<?= esc(old('hospital_address')) ?>" placeholder="Building, Ward, Street address">
                </div>

                <div style="margin-bottom: 1.75rem;">
                    <label for="description">Reason / Patient Requirement Notes (Optional)</label>
                    <textarea id="description" name="description" rows="3" placeholder="Provide medical reason, patient condition, ward/ICU info or contact details..."><?= esc(old('description')) ?></textarea>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <a href="<?= base_url('requests') ?>" class="btn btn-outline-dark">Cancel</a>
                    <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.75rem; background: var(--primary); color: #ffffff;">
                        Submit Emergency Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
