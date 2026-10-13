<?= $this->extend('layout') ?>

<?= $this->section('title') ?>Admin Analytics & Operations Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Chart.js Frontend Visualization Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2 style="font-weight: 800; color: var(--text-dark);">Executive System Analytics & Operations</h2>
        <p style="color: var(--text-muted); margin: 0;">Real-time database-backed analytics for donors, emergency requests, locations, and inventory.</p>
    </div>
</div>

<!-- Date, Blood Group & Location Filters -->
<div class="card" style="margin-bottom: 2rem; border-top: 4px solid var(--primary);">
    <div class="card-body" style="padding: 1.25rem;">
        <form action="<?= base_url('admin/dashboard') ?>" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 1; min-width: 150px;">
                <label for="start_date" style="font-size: 0.85rem; font-weight: 600;">Start Date</label>
                <input type="date" id="start_date" name="start_date" value="<?= esc($selectedStartDate) ?>" style="padding: 0.5rem;">
            </div>

            <div style="flex: 1; min-width: 150px;">
                <label for="end_date" style="font-size: 0.85rem; font-weight: 600;">End Date</label>
                <input type="date" id="end_date" name="end_date" value="<?= esc($selectedEndDate) ?>" style="padding: 0.5rem;">
            </div>

            <div style="flex: 1; min-width: 160px;">
                <label for="blood_group_id" style="font-size: 0.85rem; font-weight: 600;">Blood Group</label>
                <select id="blood_group_id" name="blood_group_id" style="padding: 0.5rem;">
                    <option value="">-- All Groups --</option>
                    <?php foreach ($bloodGroups as $bg): ?>
                        <option value="<?= $bg['id'] ?>" <?= $selectedGroup == $bg['id'] ? 'selected' : '' ?>>
                            <?= esc($bg['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="flex: 1; min-width: 160px;">
                <label for="city" style="font-size: 0.85rem; font-weight: 600;">City / Location</label>
                <input type="text" id="city" name="city" value="<?= esc($selectedCity) ?>" placeholder="e.g. Mumbai" style="padding: 0.5rem;">
            </div>

            <button type="submit" class="btn btn-primary" style="padding: 0.55rem 1.25rem; height: fit-content;">Apply Filters</button>
            <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-outline-dark" style="padding: 0.55rem 1rem; height: fit-content;">Reset</a>
        </form>
    </div>
</div>

<!-- 10 Core Metric Summary Cards -->
<div style="margin-bottom: 2rem;">
    <h4 style="margin-bottom: 1rem; color: var(--text-dark); font-weight: 700;">1. Primary System Overview (10 Core Metrics)</h4>
    <div class="grid grid-4" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
        
        <div class="stat-card" style="border-left: 4px solid #ef4444;">
            <div class="stat-number"><?= $totalDonors ?></div>
            <div class="stat-label">1. Total Donors</div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #3b82f6;">
            <div class="stat-number" style="color: #2563eb;"><?= $activeDonors ?></div>
            <div class="stat-label">2. Active Donors</div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #10b981;">
            <div class="stat-number" style="color: #059669;"><?= $availableDonors ?></div>
            <div class="stat-label">3. Available Donors</div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #059669;">
            <div class="stat-number" style="color: #047857;"><?= $eligibleDonors ?></div>
            <div class="stat-label">4. Eligible Donors</div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #6366f1;">
            <div class="stat-number"><?= $totalRequests ?></div>
            <div class="stat-label">5. Total Requests</div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #f59e0b;">
            <div class="stat-number" style="color: #d97706;"><?= $pendingRequests ?></div>
            <div class="stat-label">6. Pending Requests</div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #0284c7;">
            <div class="stat-number" style="color: #0284c7;"><?= $approvedRequests ?></div>
            <div class="stat-label">7. Approved Requests</div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #10b981;">
            <div class="stat-number" style="color: #10b981;"><?= $fulfilledRequests ?></div>
            <div class="stat-label">8. Fulfilled / Completed</div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #991b1b; background: #fff5f5;">
            <div class="stat-number" style="color: #991b1b; font-weight: 800;"><?= $criticalRequests ?></div>
            <div class="stat-label" style="color: #991b1b; font-weight: 700;">9. Critical Requests</div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #dc2626;">
            <div class="stat-number" style="color: #dc2626;"><?= $urgentRequests ?></div>
            <div class="stat-label">10. Urgent Requests</div>
        </div>

    </div>
</div>

<!-- Visual Interactive Charts Section -->
<div style="margin-bottom: 2.5rem;">
    <h4 style="margin-bottom: 1rem; color: var(--text-dark); font-weight: 700;">2. Visual Analytics Charts</h4>
    
    <div class="grid grid-2" style="gap: 1.5rem; margin-bottom: 1.5rem;">
        
        <!-- Chart 1: Blood Group Demand -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Blood Group Demand Distribution</h3>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 260px;">
                    <canvas id="bloodGroupDemandChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Chart 2: Request Status Distribution -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Request Status Breakdown</h3>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 260px;">
                    <canvas id="requestStatusChart"></canvas>
                </div>
            </div>
        </div>

    </div>

    <div class="grid grid-2" style="gap: 1.5rem;">
        
        <!-- Chart 3: Emergency / Urgency Distribution -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Request Urgency Level Distribution</h3>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 260px;">
                    <canvas id="urgencyDistributionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Chart 4: City-wise Blood Demand -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Top Locations by Blood Demand</h3>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 260px;">
                    <canvas id="cityDemandChart"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Detailed Data Tables Grid -->
<div class="grid grid-2" style="margin-bottom: 2.5rem; gap: 1.5rem;">

    <!-- Blood Group Donor Analytics Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Blood Group Donor Availability</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Group</th>
                            <th>Registered Donors</th>
                            <th>Available Donors</th>
                            <th>Eligible Donors</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bloodGroupDonors as $bgd): ?>
                            <tr>
                                <td><span class="blood-badge"><?= esc($bgd['name']) ?></span></td>
                                <td><strong><?= $bgd['registered'] ?></strong></td>
                                <td><span style="color: var(--success); font-weight: 700;"><?= $bgd['available'] ?></span></td>
                                <td><span style="color: var(--info); font-weight: 700;"><?= $bgd['eligible'] ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Blood Request Analytics Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Blood Request Status by Group</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Group</th>
                            <th>Total</th>
                            <th>Pending</th>
                            <th>Approved</th>
                            <th>Fulfilled</th>
                            <th>Cancelled</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bloodGroupRequests as $bgr): ?>
                            <tr>
                                <td><span class="blood-badge"><?= esc($bgr['name']) ?></span></td>
                                <td><strong><?= $bgr['total'] ?></strong></td>
                                <td><span style="color: #d97706; font-weight: 700;"><?= $bgr['pending'] ?></span></td>
                                <td><span style="color: #0284c7; font-weight: 700;"><?= $bgr['approved'] ?></span></td>
                                <td><span style="color: #10b981; font-weight: 700;"><?= $bgr['fulfilled'] ?></span></td>
                                <td><span style="color: var(--text-muted);"><?= $bgr['cancelled'] ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Inventory & Location Demand Grid -->
<div class="grid grid-2" style="margin-bottom: 2.5rem; gap: 1.5rem;">

    <!-- Integrated Blood Bank Inventory Analytics -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Blood Bank Inventory Analytics</h3>
            <span class="badge badge-success"><?= $inventory['totalUnits'] ?> Total Stock Units</span>
        </div>
        <div class="card-body">
            <div style="display: flex; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap;">
                <span class="badge badge-warning">Low Stock Groups: <?= $inventory['lowStockCount'] ?></span>
                <span class="badge badge-danger">Out of Stock Groups: <?= $inventory['outOfStockCount'] ?></span>
            </div>
            <div class="grid grid-2" style="gap: 0.75rem;">
                <?php if (! empty($inventory['totals'])): ?>
                    <?php foreach ($inventory['totals'] as $inv): ?>
                        <?php $u = (int) ($inv['total_units'] ?? 0); ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.6rem 0.8rem; background: var(--bg-light); border-radius: var(--radius); border: 1px solid var(--border);">
                            <span class="blood-badge"><?= esc($inv['blood_group_name']) ?></span>
                            <span style="font-weight: 700; color: <?= $u >= 5 ? 'var(--success)' : ($u > 0 ? '#d97706' : '#dc2626') ?>;">
                                <?= $u ?> Units
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: var(--text-muted);">No inventory stock recorded.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- City / Location Demand Ranking Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Top Locations by Blood Demand</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (! empty($cityDemand)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th># Rank</th>
                                <th>City / Location</th>
                                <th>Total Requests</th>
                                <th>Demand Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cityDemand as $rank => $cd): ?>
                                <tr>
                                    <td><strong>#<?= $rank + 1 ?></strong></td>
                                    <td><strong><?= esc($cd['city']) ?></strong></td>
                                    <td><strong style="color: var(--primary);"><?= $cd['request_count'] ?></strong> Request(s)</td>
                                    <td>
                                        <span class="badge <?= $cd['request_count'] >= 10 ? 'badge-danger' : ($cd['request_count'] >= 5 ? 'badge-warning' : 'badge-info') ?>">
                                            <?= $cd['request_count'] >= 10 ? 'High Demand' : ($cd['request_count'] >= 5 ? 'Moderate Demand' : 'Normal Demand') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="color: var(--text-muted); text-align: center; padding: 1.5rem 0;">No city location demand data recorded yet.</p>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Recent System Activity Stream -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h3 class="card-title">Recent System Activity Stream</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="grid grid-2" style="gap: 0;">
            
            <!-- Recent Users -->
            <div style="padding: 1rem; border-right: 1px solid var(--border);">
                <h5 style="margin-bottom: 0.75rem; color: var(--text-dark); font-weight: 700;">Recent User Registrations</h5>
                <?php if (! empty($recentUsers)): ?>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <?php foreach ($recentUsers as $ru): ?>
                            <li style="padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9; font-size: 0.88rem;">
                                <strong><?= esc($ru['name']) ?></strong> (<?= esc($ru['role_name'] ?: 'User') ?>)<br>
                                <small style="color: var(--text-muted);"><?= esc($ru['email']) ?> &bull; <?= date('M d, Y H:i', strtotime($ru['created_at'])) ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="color: var(--text-muted); font-size: 0.85rem;">No recent user registrations.</p>
                <?php endif; ?>
            </div>

            <!-- Recent Emergency Requests -->
            <div style="padding: 1rem;">
                <h5 style="margin-bottom: 0.75rem; color: var(--text-dark); font-weight: 700;">Recent Emergency Requests</h5>
                <?php if (! empty($recentRequests)): ?>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <?php foreach ($recentRequests as $rr): ?>
                            <li style="padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9; font-size: 0.88rem;">
                                <strong>Request #<?= $rr['id'] ?></strong> - <span class="blood-badge" style="font-size: 0.75rem;"><?= esc($rr['blood_group_name']) ?></span> (<?= esc($rr['hospital_name']) ?>)<br>
                                <small style="color: var(--text-muted);"><?= ucfirst(esc($rr['status'])) ?> &bull; <?= date('M d, Y H:i', strtotime($rr['created_at'])) ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="color: var(--text-muted); font-size: 0.85rem;">No recent emergency requests.</p>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<!-- Chart.js Interactive Initialization Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Blood Group Demand Chart (Bar)
    const ctxBg = document.getElementById('bloodGroupDemandChart').getContext('2d');
    new Chart(ctxBg, {
        type: 'bar',
        data: {
            labels: <?= $chartBgLabelsJson ?>,
            datasets: [{
                label: 'Total Requests',
                data: <?= $chartBgDataJson ?>,
                backgroundColor: 'rgba(239, 68, 68, 0.75)',
                borderColor: '#ef4444',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });

    // 2. Request Status Chart (Doughnut)
    const ctxStatus = document.getElementById('requestStatusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Approved/Matched', 'Fulfilled'],
            datasets: [{
                data: <?= $chartStatusDataJson ?>,
                backgroundColor: ['#f59e0b', '#0284c7', '#10b981']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // 3. Urgency Distribution Chart (Pie)
    const ctxUrg = document.getElementById('urgencyDistributionChart').getContext('2d');
    new Chart(ctxUrg, {
        type: 'pie',
        data: {
            labels: ['Critical', 'Urgent', 'Normal'],
            datasets: [{
                data: <?= $chartUrgencyDataJson ?>,
                backgroundColor: ['#991b1b', '#dc2626', '#3b82f6']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // 4. City Demand Chart (Bar - Horizontal)
    const ctxCity = document.getElementById('cityDemandChart').getContext('2d');
    new Chart(ctxCity, {
        type: 'bar',
        data: {
            labels: <?= $chartCityLabelsJson ?>,
            datasets: [{
                label: 'Requests by City',
                data: <?= $chartCityDataJson ?>,
                backgroundColor: 'rgba(59, 130, 246, 0.75)',
                borderColor: '#3b82f6',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
});
</script>
<?= $this->endSection() ?>
