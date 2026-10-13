<?= $this->extend('layout') ?>

<?= $this->section('title') ?>Home - Emergency Blood Donor Platform<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Hero Banner Section -->
<section class="hero">
    <div class="hero-content">
        <span class="badge badge-primary" style="margin-bottom: 1rem; padding: 0.35rem 0.85rem; background: rgba(220, 38, 38, 0.2); color: #fca5a5; border: 1px solid rgba(252, 165, 165, 0.3);">
            Fast • Reliable • Lifesaving
        </span>
        <h1>Connecting Blood Donors with Emergency Requests</h1>
        <p>BloodLink is a dedicated digital network bridging voluntary blood donors, patients in urgent need, and accredited medical institutions to ensure no life is lost due to blood shortage.</p>
        
        <div class="hero-btns">
            <a href="<?= base_url('register/donor') ?>" class="btn btn-primary btn-lg">Become a Blood Donor</a>
            <a href="<?= base_url('login') ?>" class="btn btn-outline-dark btn-lg" style="background: rgba(255, 255, 255, 0.1); color: #ffffff; border-color: rgba(255, 255, 255, 0.3);">
                Sign In to Account
            </a>
        </div>
    </div>
</section>

<!-- Impact Key Statistics Cards -->
<section style="margin-bottom: 3.5rem;">
    <div class="grid grid-4">
        <div class="stat-card">
            <div class="stat-number"><?= number_format($stats['total_donors'] ?? 1000) ?>+</div>
            <div class="stat-label">Registered Donors</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= number_format($stats['blood_requests'] ?? 450) ?>+</div>
            <div class="stat-label">Requests Fulfilled</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= number_format($stats['units_available'] ?? 300) ?></div>
            <div class="stat-label">Units Available</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= number_format($stats['hospitals'] ?? 40) ?>+</div>
            <div class="stat-label">Partner Hospitals</div>
        </div>
    </div>
</section>

<!-- How BloodLink Works Section -->
<section style="margin-bottom: 3.5rem;">
    <div style="text-align: center; max-width: 650px; margin: 0 auto 2.5rem;">
        <h2>How BloodLink Works</h2>
        <p style="color: var(--text-muted);">Three simple steps to save a life or request emergency blood units in real-time.</p>
    </div>

    <div class="grid grid-3">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div class="feature-icon" style="margin: 0 auto 1.25rem;">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                </div>
                <h3>1. Register Profile</h3>
                <p style="font-size: 0.925rem; color: var(--text-muted);">Sign up as a voluntary blood donor or verified hospital account in less than 2 minutes.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div class="feature-icon" style="margin: 0 auto 1.25rem;">
                    <svg viewBox="0 0 24 24">
                        <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                    </svg>
                </div>
                <h3>2. Search & Match</h3>
                <p style="font-size: 0.925rem; color: var(--text-muted);">Instantly search compatible blood groups by city and location for emergency needs.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div class="feature-icon" style="margin: 0 auto 1.25rem;">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                </div>
                <h3>3. Save Lives</h3>
                <p style="font-size: 0.925rem; color: var(--text-muted);">Connect directly with donors or blood bank inventories to fulfill emergency requirements.</p>
            </div>
        </div>
    </div>
</section>

<!-- Blood Group Compatibility Section -->
<section style="margin-bottom: 3.5rem;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Blood Group Compatibility Chart</h3>
            <span class="badge badge-info">Donor Awareness</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Blood Group</th>
                            <th>Can Donate To</th>
                            <th>Can Receive From</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="blood-badge">A+</span></td>
                            <td>A+, AB+</td>
                            <td>A+, A-, O+, O-</td>
                        </tr>
                        <tr>
                            <td><span class="blood-badge">A-</span></td>
                            <td>A+, A-, AB+, AB-</td>
                            <td>A-, O-</td>
                        </tr>
                        <tr>
                            <td><span class="blood-badge">B+</span></td>
                            <td>B+, AB+</td>
                            <td>B+, B-, O+, O-</td>
                        </tr>
                        <tr>
                            <td><span class="blood-badge">B-</span></td>
                            <td>B+, B-, AB+, AB-</td>
                            <td>B-, O-</td>
                        </tr>
                        <tr>
                            <td><span class="blood-badge">AB+</span></td>
                            <td>AB+ Only (Universal Recipient)</td>
                            <td>All Blood Groups</td>
                        </tr>
                        <tr>
                            <td><span class="blood-badge">AB-</span></td>
                            <td>AB+, AB-</td>
                            <td>AB-, A-, B-, O-</td>
                        </tr>
                        <tr>
                            <td><span class="blood-badge">O+</span></td>
                            <td>O+, A+, B+, AB+</td>
                            <td>O+, O-</td>
                        </tr>
                        <tr>
                            <td><span class="blood-badge">O-</span></td>
                            <td>All Blood Groups (Universal Donor)</td>
                            <td>O- Only</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Call To Action Section -->
<section style="background: var(--primary-light); border: 1px solid #fecaca; border-radius: var(--radius-lg); padding: 3rem 2rem; text-align: center; margin-bottom: 2rem;">
    <h2 style="color: var(--primary-dark); margin-bottom: 0.75rem;">Ready to Make a Life-Saving Difference?</h2>
    <p style="max-width: 600px; margin: 0 auto 1.5rem; color: #7f1d1d;">Every donation can save up to 3 lives. Register as a donor or partner hospital account today.</p>
    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
        <a href="<?= base_url('register/donor') ?>" class="btn btn-primary btn-lg">Register as Donor</a>
        <a href="<?= base_url('register/hospital') ?>" class="btn btn-secondary btn-lg">Register Hospital Account</a>
    </div>
</section>

<?= $this->endSection() ?>
