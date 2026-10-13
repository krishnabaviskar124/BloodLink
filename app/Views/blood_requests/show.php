<?= $this->extend('layout') ?>

<?= $this->section('title') ?>Emergency Request Details #<?= $req['id'] ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="max-width: 900px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2>Emergency Blood Request #<?= $req['id'] ?></h2>
            <p style="color: var(--text-muted);">Posted by <?= esc($req['requester_name']) ?> on <?= date('M d, Y H:i', strtotime($req['created_at'])) ?></p>
        </div>
        <div>
            <a href="<?= base_url('requests') ?>" class="btn btn-outline-dark">&larr; Back to Requests</a>
        </div>
    </div>

    <?php $urg = strtolower($req['urgency']); ?>

    <!-- Main Requirement Card -->
    <div class="card" style="margin-bottom: 2rem; <?= $urg === 'critical' ? 'border-color: #ef4444; box-shadow: 0 0 15px rgba(220, 38, 38, 0.15);' : '' ?>">
        <div class="card-header" style="<?= $urg === 'critical' ? 'background: #fef2f2;' : '' ?>">
            <h3 class="card-title">Emergency Requirement Summary</h3>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <?php if ($urg === 'critical'): ?>
                    <span class="badge badge-danger" style="font-size: 0.95rem; padding: 0.4rem 1rem; background: #991b1b; color: #ffffff; font-weight: 700;">
                        CRITICAL EMERGENCY
                    </span>
                <?php elseif ($urg === 'urgent' || $urg === 'high'): ?>
                    <span class="badge badge-danger" style="font-size: 0.95rem; padding: 0.4rem 1rem;">
                        URGENT
                    </span>
                <?php else: ?>
                    <span class="badge badge-info" style="font-size: 0.95rem; padding: 0.4rem 1rem;">
                        NORMAL PRIORITY
                    </span>
                <?php endif; ?>

                <span class="blood-badge" style="font-size: 1.2rem; padding: 0.4rem 1rem;">
                    <?= esc($req['blood_group_name']) ?>
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="grid grid-2" style="row-gap: 1.25rem;">
                <div>
                    <label style="color: var(--text-muted); font-size: 0.85rem;">Hospital / Medical Facility</label>
                    <div style="font-size: 1.1rem; font-weight: 700; color: var(--text-dark);"><?= esc($req['hospital_name']) ?></div>
                </div>

                <div>
                    <label style="color: var(--text-muted); font-size: 0.85rem;">City / Location</label>
                    <div style="font-size: 1.1rem; font-weight: 700; color: var(--text-dark);"><?= esc($req['city']) ?></div>
                </div>

                <div>
                    <label style="color: var(--text-muted); font-size: 0.85rem;">Units Required</label>
                    <div style="font-size: 1.15rem; font-weight: 700; color: var(--primary);"><?= esc($req['units_required']) ?> Unit(s)</div>
                </div>

                <div>
                    <label style="color: var(--text-muted); font-size: 0.85rem;">Required By Date</label>
                    <div style="font-size: 1.05rem; font-weight: 600; color: var(--text-dark);"><?= date('M d, Y', strtotime($req['required_date'])) ?></div>
                </div>

                <div>
                    <label style="color: var(--text-muted); font-size: 0.85rem;">Created Timestamp</label>
                    <div style="font-size: 0.95rem; color: var(--text-dark);"><?= date('M d, Y H:i:s', strtotime($req['created_at'])) ?></div>
                </div>

                <div>
                    <label style="color: var(--text-muted); font-size: 0.85rem;">Current Status</label>
                    <div>
                        <?php
                            $st = strtolower($req['status']);
                            $badgeClass = match ($st) {
                                'fulfilled', 'completed' => 'badge-success',
                                'approved'              => 'badge-info',
                                'cancelled'             => 'badge-secondary',
                                default                 => 'badge-warning',
                            };
                        ?>
                        <span class="badge <?= $badgeClass ?>" style="font-size: 0.85rem; padding: 0.35rem 0.75rem;">
                            <?= ucfirst(str_replace('_', ' ', $st)) ?>
                        </span>
                    </div>
                </div>

                <div style="grid-column: span 2;">
                    <label style="color: var(--text-muted); font-size: 0.85rem;">Hospital Address</label>
                    <div style="font-size: 1rem; color: var(--text-dark);"><?= esc($req['hospital_address'] ?: 'Address not specified') ?></div>
                </div>

                <div style="grid-column: span 2;">
                    <label style="color: var(--text-muted); font-size: 0.85rem;">Reason / Patient Requirement Notes</label>
                    <div style="font-size: 1rem; color: var(--text-dark); background: var(--bg-light); padding: 1rem; border-radius: var(--radius); border: 1px solid var(--border);">
                        <?= esc($req['description'] ?: 'No additional patient notes provided.') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Management Card (Authorized Users: Requester, Blood Bank Manager, Admin) -->
    <?php
        $userId   = session('user_id');
        $userRole = strtolower((string) session('role_name'));
        if ($userId == $req['requester_id'] || in_array($userRole, ['admin', 'blood bank manager'], true)):
    ?>
        <div class="card" style="margin-bottom: 2rem; background: #fff8f8; border-color: #fca5a5;">
            <div class="card-header" style="background: transparent;">
                <h3 class="card-title">Manage Request Status Lifecycle</h3>
            </div>
            <div class="card-body">
                <form action="<?= base_url('requests/' . $req['id'] . '/status') ?>" method="POST" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                    <?= csrf_field() ?>
                    <div style="flex: 1; min-width: 220px;">
                        <label for="status">Update Lifecycle Status</label>
                        <select id="status" name="status" required>
                            <option value="pending" <?= $req['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="approved" <?= $req['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="matching" <?= $req['status'] === 'matching' ? 'selected' : '' ?>>Matching Donors</option>
                            <option value="donors_notified" <?= $req['status'] === 'donors_notified' ? 'selected' : '' ?>>Donors Notified</option>
                            <option value="accepted" <?= $req['status'] === 'accepted' ? 'selected' : '' ?>>Accepted by Donor</option>
                            <option value="fulfilled" <?= $req['status'] === 'fulfilled' ? 'selected' : '' ?>>Fulfilled</option>
                            <option value="completed" <?= $req['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= $req['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </form>

                <?php if (! in_array($st, ['fulfilled', 'completed', 'cancelled'], true) || $userRole === 'admin'): ?>
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #fca5a5; display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Quick Lifecycle Transition:</span>
                        <?php if ($st === 'pending'): ?>
                            <form action="<?= base_url('requests/' . $req['id'] . '/status') ?>" method="POST" style="display:inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="btn btn-outline btn-sm" style="font-size:0.8rem;">Mark Approved</button>
                            </form>
                        <?php endif; ?>
                        <?php if (in_array($st, ['pending', 'approved', 'matching', 'donors_notified', 'accepted'], true)): ?>
                            <form action="<?= base_url('requests/' . $req['id'] . '/status') ?>" method="POST" style="display:inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="status" value="fulfilled">
                                <button type="submit" class="btn btn-success btn-sm" style="font-size:0.8rem; color:#fff; background: var(--success); border-color: var(--success);">Mark Fulfilled</button>
                            </form>
                            <form action="<?= base_url('requests/' . $req['id'] . '/status') ?>" method="POST" style="display:inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="btn btn-outline-dark btn-sm" style="font-size:0.8rem;">Cancel Request</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Compatible Blood Inventory Stock -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h3 class="card-title">Compatible Inventory Stock</h3>
            <span class="badge badge-success"><?= count($matchingInventory) ?> Stock Found</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (! empty($matchingInventory)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Blood Bank Facility</th>
                                <th>Group</th>
                                <th>City</th>
                                <th>Available Stock</th>
                                <th>Stock Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($matchingInventory as $inv): ?>
                                <?php
                                    $u = (int) $inv['units_available'];
                                    $invBadge = match (true) {
                                        $u >= 5 => '<span class="badge badge-success">🟢 Available</span>',
                                        $u > 0  => '<span class="badge badge-warning">🟡 Low Stock</span>',
                                        default => '<span class="badge badge-danger">🔴 Out of Stock</span>',
                                    };
                                ?>
                                <tr>
                                    <td><strong><?= esc($inv['blood_bank_name']) ?></strong></td>
                                    <td><span class="blood-badge"><?= esc($inv['blood_group_name']) ?></span></td>
                                    <td><?= esc($inv['city']) ?></td>
                                    <td><strong style="color: <?= $u >= 5 ? 'var(--success)' : ($u > 0 ? '#d97706' : '#dc2626') ?>;"><?= $u ?></strong> Units</td>
                                    <td><?= $invBadge ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="color: var(--text-muted); text-align: center; padding: 1.5rem 0;">No compatible inventory stock currently available.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Compatible Active Donors List -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h3 class="card-title">Compatible Voluntary Donors (Smart Match Ranking)</h3>
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
                                <th>Group</th>
                                <th>City</th>
                                <th>Status</th>
                                <th>Contact Action</th>
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
                                    $detailsId = 'match-details-show-' . (int) ($donor['id'] ?? rand(1000, 9999));
                                ?>
                                <tr>
                                    <td>
                                        <div style="position: relative; display: inline-block;">
                                            <div style="display: flex; flex-direction: column; gap: 0.35rem; align-items: flex-start;">
                                                <span class="badge" style="font-size: 0.9rem; padding: 0.35rem 0.75rem; font-weight: 700; border-radius: 20px; background: <?= $badgeBg ?>; color: <?= $badgeFg ?>; border: <?= $badgeBorder ?>;">
                                                    <?= $score ?>% Match
                                                </span>
                                                <button type="button" class="btn-link" onclick="toggleMatchDetailsShow('<?= $detailsId ?>')" style="font-size: 0.78rem; color: var(--primary); text-decoration: underline; background: none; border: none; padding: 0; cursor: pointer; font-weight: 600;">
                                                    Why this match?
                                                </button>
                                            </div>

                                            <!-- Match Score Factor Breakdown Modal/Drawer -->
                                            <div id="<?= $detailsId ?>" style="display: none; position: absolute; top: 100%; left: 0; z-index: 100; background: #ffffff; border: 1px solid var(--border); box-shadow: 0 10px 25px rgba(0,0,0,0.18); border-radius: 8px; padding: 1rem; width: 270px; margin-top: 0.4rem;">
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.4rem;">
                                                    <strong style="font-size: 0.88rem; color: var(--text-dark);">Match Score Factors</strong>
                                                    <button type="button" onclick="toggleMatchDetailsShow('<?= $detailsId ?>')" style="border: none; background: transparent; font-size: 1.1rem; cursor: pointer; color: var(--text-muted);">&times;</button>
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

                                    <td>
                                        <?php if (session('isLoggedIn')): ?>
                                            <?php
                                                $donorUserId   = (int) $donor['user_id'];
                                                $cRecord       = $contactMap[$donorUserId] ?? null;
                                                $cStatus       = $cRecord ? strtolower($cRecord['status']) : null;
                                                $isOwnerOrAdmin = (session('user_id') == $req['requester_id'] || strtolower((string) session('role_name')) === 'admin');
                                            ?>
                                            <div style="display: flex; flex-direction: column; gap: 0.4rem;">
                                                <?php if ($cStatus === 'accepted'): ?>
                                                    <div style="font-size: 0.85rem; color: var(--success); font-weight: 600;">
                                                        🔓 <strong>Phone:</strong> <?= esc($donor['phone'] ?: 'N/A') ?>
                                                    </div>
                                                    <div style="display: flex; gap: 0.35rem; flex-wrap: wrap; align-items: center;">
                                                        <?php if (! empty($donor['phone'])): ?>
                                                            <a href="tel:<?= esc(trim($donor['phone'])) ?>" class="btn btn-outline btn-sm" style="font-size: 0.78rem;">
                                                                Call <?= esc($donor['phone']) ?>
                                                            </a>
                                                            <button type="button" class="btn btn-outline-dark btn-sm" onclick="navigator.clipboard.writeText('<?= esc(trim($donor['phone'])) ?>'); alert('Phone number copied to clipboard!');" style="font-size: 0.78rem;">
                                                                Copy Number
                                                            </button>
                                                        <?php endif; ?>
                                                        <span class="badge badge-success" style="font-size: 0.75rem;">Contact Accepted</span>
                                                    </div>
                                                <?php elseif ($cStatus === 'pending'): ?>
                                                    <span class="badge badge-warning" style="font-size: 0.8rem; padding: 0.35rem 0.65rem;">
                                                        ⏳ Contact Request Pending Approval
                                                    </span>
                                                    <small style="color: var(--text-muted); font-size: 0.75rem;">Phone hidden until donor accepts request.</small>
                                                <?php elseif ($cStatus === 'declined'): ?>
                                                    <span class="badge badge-danger" style="font-size: 0.8rem; padding: 0.35rem 0.65rem;">
                                                        ❌ Request Declined by Donor
                                                    </span>
                                                    <small style="color: var(--text-muted); font-size: 0.75rem;">Phone number remains private.</small>
                                                <?php else: ?>
                                                    <small style="color: var(--text-muted); font-size: 0.75rem;">🔒 Phone hidden for donor privacy</small>
                                                    <form action="<?= base_url('contact-requests') ?>" method="POST" style="display: inline-block; margin: 0;">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="donor_id" value="<?= $donorUserId ?>">
                                                        <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                                        <button type="submit" class="btn btn-primary btn-sm" style="cursor: pointer; font-size: 0.8rem;">
                                                            Request Donor Contact
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <a href="<?= base_url('login') ?>" class="btn btn-outline btn-sm" style="cursor: pointer;">
                                                Login to Request Contact
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="color: var(--text-muted); text-align: center; padding: 1.5rem 0;">No matching donors found in the system for this requirement.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function toggleMatchDetailsShow(id) {
        var el = document.getElementById(id);
        if (el) {
            el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
        }
    }
    </script>

</div>
<?= $this->endSection() ?>

