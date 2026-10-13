<?= $this->extend('layout') ?>

<?= $this->section('title') ?>Edit Profile<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="max-width: 720px; margin: 0 auto;">
    <div style="margin-bottom: 2rem;">
        <h2>Edit Donor Profile</h2>
        <p style="color: var(--text-muted);">Keep your donor contact information up to date.</p>
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
            <form action="<?= base_url('donor/profile/update') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" value="<?= old('name', $donor['donor_name'] ?? $user['name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Email Address (Protected)</label>
                        <input type="email" value="<?= esc($user['email']) ?>" disabled style="background-color: #f1f5f9; cursor: not-allowed;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="text" id="phone" name="phone" value="<?= old('phone', $donor['phone'] ?? $user['phone']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="city">City / Region *</label>
                        <input type="text" id="city" name="city" value="<?= old('city', $donor['city'] ?? $user['city']) ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="blood_group_id">Blood Group *</label>
                        <select id="blood_group_id" name="blood_group_id" required>
                            <option value="">-- Select Blood Group --</option>
                            <?php foreach ($bloodGroups as $bg): ?>
                                <option value="<?= $bg['id'] ?>" <?= old('blood_group_id', $donor['blood_group_id'] ?? '') == $bg['id'] ? 'selected' : '' ?>>
                                    <?= esc($bg['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender">
                            <option value="">-- Select Gender --</option>
                            <option value="male" <?= old('gender', $donor['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= old('gender', $donor['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= old('gender', $donor['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="date_of_birth">Date of Birth</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" value="<?= old('date_of_birth', $donor['date_of_birth'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="last_donation">Last Donation Date</label>
                        <input type="date" id="last_donation" name="last_donation" value="<?= old('last_donation', $donor['last_donation_date'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Full Address</label>
                    <input type="text" id="address" name="address" value="<?= old('address', $donor['address'] ?? '') ?>" placeholder="Street, landmark, etc.">
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">Save Profile Changes</button>
                    <a href="<?= base_url('donor/profile') ?>" class="btn btn-outline-dark">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
