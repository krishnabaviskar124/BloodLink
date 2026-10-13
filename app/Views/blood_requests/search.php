<?= $this->extend('layout') ?>

<?= $this->section('title') ?>Smart Blood & Donor Search<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="margin-bottom: 2rem;">
    <h2>Smart Blood & Donor Finder</h2>
    <p style="color: var(--text-muted);">Instantly search compatible blood bank inventories and active voluntary donors by location.</p>
</div>

<!-- Search Controls Card -->
<div class="card" style="margin-bottom: 2.5rem;">
    <div class="card-body">
        <form action="<?= base_url('find-blood') ?>" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 1; min-width: 200px;">
                <label for="blood_group_id">Select Required Blood Group *</label>
                <select id="blood_group_id" name="blood_group_id" required>
                    <option value="">-- Choose Blood Group --</option>
                    <?php foreach ($bloodGroups as $bg): ?>
                        <option value="<?= $bg['id'] ?>" <?= $selectedGroup == $bg['id'] ? 'selected' : '' ?>>
                            <?= esc($bg['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="flex: 1; min-width: 200px;">
                <label for="city">City / Region (Optional)</label>
                <input type="text" id="city" name="city" value="<?= esc($selectedCity) ?>" placeholder="e.g. New York">
            </div>

            <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem;">Find Donors & Stock</button>
            <a href="<?= base_url('find-blood') ?>" class="btn btn-outline-dark">Reset</a>
        </form>
    </div>
</div>

<?php if (true): ?>

    <!-- Matching Blood Inventory Section -->
    <div class="card" style="margin-bottom: 2.5rem;">
        <div class="card-header">
            <h3 class="card-title">1. Available Blood Bank Stock (Compatible Groups)</h3>
            <span class="badge badge-success"><?= count($matchingInventory) ?> Stock Found</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (! empty($matchingInventory)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Blood Bank / Facility</th>
                                <th>Group</th>
                                <th>City</th>
                                <th>Available Units</th>
                                <th>Last Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($matchingInventory as $item): ?>
                                <tr>
                                    <td><strong><?= esc($item['blood_bank_name']) ?></strong></td>
                                    <td><span class="blood-badge"><?= esc($item['blood_group_name']) ?></span></td>
                                    <td><?= esc($item['city']) ?></td>
                                    <td>
                                        <strong style="color: var(--success); font-size: 1.1rem;"><?= esc($item['units_available']) ?></strong> Units
                                    </td>
                                    <td><?= ! empty($item['updated_at']) ? date('M d, Y H:i', strtotime($item['updated_at'])) : 'N/A' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p style="color: var(--text-muted);">No blood bank stock currently recorded for this blood group.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Matching Eligible Donors Section -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">2. Compatible Active Donors (Smart Match Ranking)</h3>
            <span class="badge badge-info"><?= count($matchingDonors) ?> Donors Found</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (! empty($matchingDonors)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Match Score</th>
                                <th>Donor Name</th>
                                <th>Blood Group</th>
                                <th>City / Location</th>
                                <th>Eligibility</th>
                                <th>Last Donation</th>
                                <th>Contact Donor Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($matchingDonors as $donor): ?>
                                <?php
                                    $score = (int) ($donor['match_score'] ?? 100);
                                    $bd    = $donor['match_breakdown'] ?? [
                                        'blood_compatibility'  => 40,
                                        'availability'         => 20,
                                        'eligibility'          => 15,
                                        'city_match'           => 15,
                                        'donation_suitability' => 10,
                                        'total'                => $score,
                                    ];
                                    
                                    if ($score >= 80) {
                                        $badgeBg = '#dcfce7'; $badgeFg = '#15803d'; $badgeBorder = '1px solid #86efac';
                                    } elseif ($score >= 60) {
                                        $badgeBg = '#dbeafe'; $badgeFg = '#1d4ed8'; $badgeBorder = '1px solid #93c5fd';
                                    } else {
                                        $badgeBg = '#fef3c7'; $badgeFg = '#b45309'; $badgeBorder = '1px solid #fcd34d';
                                    }
                                    $detailsId = 'match-details-search-' . (int) ($donor['id'] ?? rand(1000, 9999));
                                ?>
                                <tr>
                                    <td>
                                        <div style="position: relative; display: inline-block;">
                                            <div style="display: flex; flex-direction: column; gap: 0.35rem; align-items: flex-start;">
                                                <span class="badge" style="font-size: 0.9rem; padding: 0.35rem 0.75rem; font-weight: 700; border-radius: 20px; background: <?= $badgeBg ?>; color: <?= $badgeFg ?>; border: <?= $badgeBorder ?>;">
                                                    <?= $score ?>% Match
                                                </span>
                                                <button type="button" class="btn-link" onclick="toggleMatchDetails('<?= $detailsId ?>')" style="font-size: 0.78rem; color: var(--primary); text-decoration: underline; background: none; border: none; padding: 0; cursor: pointer; font-weight: 600;">
                                                    Why this match?
                                                </button>
                                            </div>

                                            <!-- Match Score Factor Breakdown Modal/Drawer -->
                                            <div id="<?= $detailsId ?>" style="display: none; position: absolute; top: 100%; left: 0; z-index: 100; background: #ffffff; border: 1px solid var(--border); box-shadow: 0 10px 25px rgba(0,0,0,0.18); border-radius: 8px; padding: 1rem; width: 270px; margin-top: 0.4rem;">
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.4rem;">
                                                    <strong style="font-size: 0.88rem; color: var(--text-dark);">Match Score Factors</strong>
                                                    <button type="button" onclick="toggleMatchDetails('<?= $detailsId ?>')" style="border: none; background: transparent; font-size: 1.1rem; cursor: pointer; color: var(--text-muted);">&times;</button>
                                                </div>
                                                <div style="font-size: 0.82rem; display: flex; flex-direction: column; gap: 0.4rem;">
                                                    <div style="display: flex; justify-content: space-between;">
                                                        <span style="color: var(--text-muted);">Blood Compatibility</span>
                                                        <strong style="color: #10b981;">+<?= (int) ($bd['blood_compatibility'] ?? 0) ?></strong>
                                                    </div>
                                                    <div style="display: flex; justify-content: space-between;">
                                                        <span style="color: var(--text-muted);">Availability</span>
                                                        <strong style="color: #10b981;">+<?= (int) ($bd['availability'] ?? 0) ?></strong>
                                                    </div>
                                                    <div style="display: flex; justify-content: space-between;">
                                                        <span style="color: var(--text-muted);">Eligibility</span>
                                                        <strong style="color: #10b981;">+<?= (int) ($bd['eligibility'] ?? 0) ?></strong>
                                                    </div>
                                                    <div style="display: flex; justify-content: space-between;">
                                                        <span style="color: var(--text-muted);">City Match</span>
                                                        <strong style="color: #10b981;">+<?= (int) ($bd['city_match'] ?? 0) ?></strong>
                                                    </div>
                                                    <div style="display: flex; justify-content: space-between;">
                                                        <span style="color: var(--text-muted);">Donation Suitability</span>
                                                        <strong style="color: #10b981;">+<?= (int) ($bd['donation_suitability'] ?? 0) ?></strong>
                                                    </div>
                                                    <div style="display: flex; justify-content: space-between; border-top: 1px solid #e2e8f0; padding-top: 0.4rem; margin-top: 0.2rem; font-weight: 700; font-size: 0.88rem;">
                                                        <span>Total Match Score</span>
                                                        <span style="color: var(--primary);"><?= $score ?>%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><strong><?= esc($donor['donor_name']) ?></strong></td>
                                    <td><span class="blood-badge"><?= esc($donor['blood_group_name']) ?></span></td>
                                    <td><?= esc($donor['city']) ?></td>
                                    <td>
                                        <?php
                                            $isElig = strtolower($donor['medical_eligibility'] ?? 'eligible') === 'eligible';
                                            $label  = $donor['eligibility_label'] ?? ($isElig ? 'ELIGIBLE' : 'NOT ELIGIBLE');
                                            $reason = $donor['eligibility_reason'] ?? '';
                                        ?>
                                        <span class="badge <?= $isElig ? 'badge-success' : 'badge-danger' ?>" title="<?= esc($reason) ?>">
                                            <?= esc($label) ?>
                                        </span>
                                    </td>

                                    <td><?= ! empty($donor['last_donation_date']) ? date('M d, Y', strtotime($donor['last_donation_date'])) : 'No prior donations' ?></td>
                                    <td>
                                        <?php if (session('isLoggedIn')): ?>
                                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                                <div style="font-size: 0.85rem; color: var(--text-dark);">
                                                    <strong>Phone:</strong> <?= esc($donor['phone'] ?: 'N/A') ?><br>
                                                    <strong>Email:</strong> <?= esc($donor['email'] ?: 'N/A') ?>
                                                </div>

                                                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap; align-items: center;">
                                                    <?php if (! empty($donor['phone'])): ?>
                                                        <!-- Mobile Dial Link -->
                                                        <a href="tel:<?= esc(trim($donor['phone'])) ?>" class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; justify-content: center; cursor: pointer; text-decoration: none; position: relative; z-index: 10;">
                                                            Call <?= esc($donor['phone']) ?>
                                                        </a>

                                                        <!-- Desktop Copy Number Fallback -->
                                                        <button type="button" class="btn btn-outline-dark btn-sm" onclick="navigator.clipboard.writeText('<?= esc(trim($donor['phone'])) ?>'); alert('Phone number <?= esc(trim($donor['phone'])) ?> copied to clipboard!');" style="cursor: pointer;">
                                                            Copy Number
                                                        </button>
                                                    <?php endif; ?>

                                                    <!-- Notify Donor Action -->
                                                    <form action="<?= base_url('requests/contact-donor/' . $donor['user_id']) ?>" method="POST" style="display: inline-block; margin: 0;">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="btn btn-primary btn-sm" style="cursor: pointer;">
                                                            Notify Donor
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                                                <span class="badge badge-secondary" style="width: fit-content;">Protected Contact</span>
                                                <a href="<?= base_url('login') ?>" class="btn btn-outline btn-sm" style="cursor: pointer;">
                                                    Login to View Phone & Email
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p style="color: var(--text-muted);">No eligible donors registered for this blood group in the selected area.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function toggleMatchDetails(id) {
        var el = document.getElementById(id);
        if (el) {
            el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
        }
    }
    </script>


<?php else: ?>
    <!-- Prompt state -->
    <div style="text-align: center; padding: 4rem 1rem; background: #ffffff; border: 1px solid var(--border); border-radius: var(--radius-lg);">
        <div class="feature-icon" style="margin: 0 auto 1.5rem; width: 64px; height: 64px;">
            <svg viewBox="0 0 24 24" style="width: 32px; height: 32px;">
                <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
            </svg>
        </div>
        <h3 style="margin-bottom: 0.5rem;">Select a Blood Group to Search</h3>
        <p style="color: var(--text-muted); max-width: 500px; margin: 0 auto;">Choose a blood group above to view live inventory stock and compatible voluntary donors instantly.</p>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
