<?= $this->extend('layout') ?>

<?= $this->section('title') ?>Donor Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="margin-bottom: 2rem;">
    <h2>Welcome Back, <?= esc($donor['donor_name'] ?? session('name')) ?>!</h2>
    <p style="color: var(--text-muted);">Thank you for being an active voluntary blood donor on BloodLink.</p>
</div>

<?php $avail = strtolower($donor['availability'] ?? 'available'); ?>

<!-- Donation Availability Management Section -->
<div class="card" style="margin-bottom: 2.5rem; border-left: 4px solid <?= $avail === 'available' ? 'var(--success)' : ($avail === 'temporarily_unavailable' ? '#f59e0b' : '#ef4444') ?>;">
    <div class="card-header">
        <h3 class="card-title">Donation Availability Status</h3>
        <span class="badge <?= $avail === 'available' ? 'badge-success' : ($avail === 'temporarily_unavailable' ? 'badge-warning' : 'badge-danger') ?>">
            <?= $avail === 'available' ? '🟢 Active' : ($avail === 'temporarily_unavailable' ? '🟡 Paused' : '🔴 Inactive') ?>
        </span>
    </div>
    <div class="card-body">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.25rem;">
            <div>
                <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.35rem;">Current Emergency Matching Status:</div>
                <div style="font-size: 1.15rem; font-weight: 700;">
                    <?php if ($avail === 'available'): ?>
                        <span style="color: var(--success);">🟢 AVAILABLE FOR EMERGENCY MATCHING</span>
                    <?php elseif ($avail === 'temporarily_unavailable'): ?>
                        <span style="color: #d97706;">🟡 TEMPORARILY UNAVAILABLE</span>
                    <?php else: ?>
                        <span style="color: #dc2626;">🔴 NOT AVAILABLE</span>
                    <?php endif; ?>
                </div>
                <small style="color: var(--text-muted); display: block; margin-top: 0.35rem;">
                    Last updated: <?= ! empty($donor['updated_at']) ? date('M d, Y g:i A', strtotime($donor['updated_at'])) : 'Recently' ?>
                </small>
            </div>

            <!-- Quick Status Change Controls -->
            <form action="<?= base_url('donor/availability/update') ?>" method="POST" style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin: 0;">
                <?= csrf_field() ?>
                <button type="submit" name="availability" value="available" class="btn btn-sm <?= $avail === 'available' ? 'btn-primary' : 'btn-outline-dark' ?>" style="cursor: pointer;">
                    Available
                </button>
                <button type="submit" name="availability" value="temporarily_unavailable" class="btn btn-sm <?= $avail === 'temporarily_unavailable' ? 'btn-primary' : 'btn-outline-dark' ?>" style="cursor: pointer;">
                    Temporarily Unavailable
                </button>
                <button type="submit" name="availability" value="not_available" class="btn btn-sm <?= $avail === 'not_available' ? 'btn-primary' : 'btn-outline-dark' ?>" style="cursor: pointer;">
                    Not Available
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Summary Stats Cards -->
<div class="grid grid-4" style="margin-bottom: 2.5rem;">
    <div class="stat-card">
        <div class="stat-label">Blood Group</div>
        <div style="margin-top: 0.5rem;">
            <span class="blood-badge" style="font-size: 1.25rem; padding: 0.4rem 1.1rem;">
                <?= esc($donor['blood_group_name'] ?? 'Not Set') ?>
            </span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-label">Eligibility Status</div>
        <div style="margin-top: 0.5rem;">
            <?php
                $isElig = strtolower($donor['medical_eligibility'] ?? 'eligible') === 'eligible';
                $label  = $isElig ? 'ELIGIBLE' : 'NOT ELIGIBLE';
            ?>
            <span class="badge <?= $isElig ? 'badge-success' : 'badge-danger' ?>" style="font-size: 0.85rem; padding: 0.4rem 0.85rem;">
                <?= esc($label) ?>
            </span>
        </div>
    </div>


    <div class="stat-card">
        <div class="stat-number"><?= $donationCount ?></div>
        <div class="stat-label">Total Donations</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Last Donation Date</div>
        <div style="font-weight: 700; font-size: 1.1rem; color: var(--text-dark); margin-top: 0.5rem;">
            <?= ! empty($donor['last_donation_date']) ? date('M d, Y', strtotime($donor['last_donation_date'])) : 'No Record' ?>
        </div>
    </div>
</div>

<!-- Incoming Contact Requests Section -->
<div class="card" style="margin-bottom: 2.5rem;">
    <div class="card-header">
        <h3 class="card-title">Incoming Contact Requests</h3>
        <span class="badge badge-info"><?= count($contactRequests ?? []) ?> Total Request(s)</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (! empty($contactRequests)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Requester / Hospital</th>
                            <th>Related Request</th>
                            <th>Date Received</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contactRequests as $cr): ?>
                            <?php $st = strtolower($cr['status']); ?>
                            <tr>
                                <td>
                                    <strong><?= esc($cr['requester_name']) ?></strong><br>
                                    <small style="color: var(--text-muted);"><?= esc($cr['requester_phone'] ?: 'Phone hidden') ?></small>
                                </td>
                                <td>
                                    <?php if (! empty($cr['request_id'])): ?>
                                        <a href="<?= base_url('requests/' . $cr['request_id']) ?>">Request #<?= $cr['request_id'] ?> (<?= esc($cr['hospital_name'] ?: 'Emergency') ?>)</a>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">General Contact</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M d, Y g:i A', strtotime($cr['created_at'])) ?></td>
                                <td>
                                    <?php if ($st === 'accepted'): ?>
                                        <span class="badge badge-success">Accepted</span>
                                    <?php elseif ($st === 'declined'): ?>
                                        <span class="badge badge-danger">Declined</span>
                                    <?php elseif ($st === 'cancelled'): ?>
                                        <span class="badge badge-secondary">Cancelled</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pending Response</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($st === 'pending'): ?>
                                        <div style="display: flex; gap: 0.35rem;">
                                            <form action="<?= base_url('contact-requests/' . $cr['id'] . '/respond') ?>" method="POST" style="display:inline;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="accept">
                                                <button type="submit" class="btn btn-primary btn-sm" style="font-size:0.78rem; padding: 0.25rem 0.6rem;">Accept</button>
                                            </form>
                                            <form action="<?= base_url('contact-requests/' . $cr['id'] . '/respond') ?>" method="POST" style="display:inline;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="decline">
                                                <button type="submit" class="btn btn-outline-dark btn-sm" style="font-size:0.78rem; padding: 0.25rem 0.6rem;">Decline</button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span style="font-size: 0.8rem; color: var(--text-muted);"><?= ucfirst($st) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="color: var(--text-muted); text-align: center; padding: 1.5rem 0;">You have no incoming contact requests.</p>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid-2" style="margin-bottom: 2.5rem;">

    <!-- Matching Emergency Requests -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Matching Emergency Requests</h3>
            <span class="badge badge-primary"><?= count($matchingRequests) ?> Available</span>
        </div>
        <div class="card-body">
            <?php if (! empty($matchingRequests)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Hospital / City</th>
                                <th>Group</th>
                                <th>Units</th>
                                <th>Urgency</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($matchingRequests as $req): ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($req['hospital_name']) ?></strong><br>
                                        <small style="color: var(--text-muted);"><?= esc($req['city']) ?></small>
                                    </td>
                                    <td><span class="blood-badge"><?= esc($req['blood_group_name']) ?></span></td>
                                    <td><?= esc($req['units_required']) ?></td>
                                    <td>
                                        <span class="badge <?= $req['urgency'] === 'critical' || $req['urgency'] === 'high' ? 'badge-primary' : 'badge-info' ?>">
                                            <?= ucfirst(esc($req['urgency'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('requests/' . $req['id']) ?>" class="btn btn-outline btn-sm">View Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="color: var(--text-muted); text-align: center; padding: 1.5rem 0;">No active requests currently match your blood group in your area.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Profile Overview & Actions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Donor Actions & Profile Summary</h3>
        </div>
        <div class="card-body">
            <div style="margin-bottom: 1.5rem;">
                <p><strong>Email:</strong> <?= esc($donor['email'] ?? session('email')) ?></p>
                <p><strong>Phone:</strong> <?= esc($donor['phone'] ?? 'Not set') ?></p>
                <p><strong>Location:</strong> <?= esc($donor['city'] ?? 'Not set') ?></p>
                <p><strong>Address:</strong> <?= esc($donor['address'] ?? 'Not set') ?></p>
            </div>

            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <a href="<?= base_url('donor/profile') ?>" class="btn btn-primary">View Full Profile</a>
                <a href="<?= base_url('donor/profile/edit') ?>" class="btn btn-outline">Edit Profile</a>
                <a href="<?= base_url('donor/donations') ?>" class="btn btn-outline-dark">Donation History</a>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>
