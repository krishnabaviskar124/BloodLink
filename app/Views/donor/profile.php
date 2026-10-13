<?= $this->extend('layout') ?>

<?= $this->section('title') ?>Donor Profile<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="max-width: 800px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2>Donor Profile</h2>
            <p style="color: var(--text-muted);">Personal information and medical eligibility overview.</p>
        </div>
        <a href="<?= base_url('donor/profile/edit') ?>" class="btn btn-primary">Edit Profile</a>
    </div>

    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h3 class="card-title">Profile Details</h3>
            <span class="blood-badge" style="font-size: 1.1rem; padding: 0.35rem 0.9rem;">
                <?= esc($donor['blood_group_name'] ?? 'Not set') ?>
            </span>
        </div>
        <div class="card-body">
            <div class="grid grid-2" style="row-gap: 1.25rem;">
                <div>
                    <label style="color: var(--text-muted); font-size: 0.85rem;">Full Name</label>
                    <div style="font-size: 1.05rem; font-weight: 600; color: var(--text-dark);"><?= esc($donor['donor_name'] ?? session('name')) ?></div>
                </div>
                <div>
                    <label style="color: var(--text-muted); font-size: 0.85rem;">Email Address</label>
                    <div style="font-size: 1.05rem; font-weight: 600; color: var(--text-dark);"><?= esc($donor['email'] ?? session('email')) ?></div>
                </div>
                <div>
                    <label style="color: var(--text-muted); font-size: 0.85rem;">Phone Number</label>
                    <div style="font-size: 1.05rem; font-weight: 600; color: var(--text-dark);"><?= esc($donor['phone'] ?? 'N/A') ?></div>
                </div>
                <div>
                    <label style="color: var(--text-muted); font-size: 0.85rem;">City / Region</label>
                    <div style="font-size: 1.05rem; font-weight: 600; color: var(--text-dark);"><?= esc($donor['city'] ?? 'N/A') ?></div>
                </div>
                <div>
                    <label style="color: var(--text-muted); font-size: 0.85rem;">Gender</label>
                    <div style="font-size: 1.05rem; font-weight: 600; color: var(--text-dark);"><?= ! empty($donor['gender']) ? ucfirst(esc($donor['gender'])) : 'N/A' ?></div>
                </div>
                <div>
                    <label style="color: var(--text-muted); font-size: 0.85rem;">Date of Birth</label>
                    <div style="font-size: 1.05rem; font-weight: 600; color: var(--text-dark);"><?= ! empty($donor['date_of_birth']) ? date('M d, Y', strtotime($donor['date_of_birth'])) : 'N/A' ?></div>
                </div>
                <div>
                    <label style="color: var(--text-muted); font-size: 0.85rem;">Medical Eligibility</label>
                    <div>
                        <?php
                            $isElig = strtolower($donor['medical_eligibility'] ?? 'eligible') === 'eligible';
                            $label  = $isElig ? 'ELIGIBLE' : 'NOT ELIGIBLE';
                        ?>
                        <span class="badge <?= $isElig ? 'badge-success' : 'badge-danger' ?>">
                            <?= esc($label) ?>
                        </span>
                    </div>
                </div>

                <div>
                    <label style="color: var(--text-muted); font-size: 0.85rem;">Last Donation Date</label>
                    <div style="font-size: 1.05rem; font-weight: 600; color: var(--text-dark);"><?= ! empty($donor['last_donation_date']) ? date('M d, Y', strtotime($donor['last_donation_date'])) : 'No recorded donations' ?></div>
                </div>
                <div style="grid-column: span 2;">
                    <label style="color: var(--text-muted); font-size: 0.85rem;">Address</label>
                    <div style="font-size: 1rem; color: var(--text-dark);"><?= esc($donor['address'] ?? 'N/A') ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
