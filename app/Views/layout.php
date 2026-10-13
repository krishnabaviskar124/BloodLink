<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - BloodLink Application</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom BloodLink Design System CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>

    <!-- Responsive Navbar -->
    <nav class="navbar">
        <div class="container">
            <a href="<?= base_url() ?>" class="navbar-brand">
                <svg viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>
                BloodLink
            </a>

            <button class="mobile-toggle" id="mobileMenuBtn" aria-label="Toggle navigation menu">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <ul class="navbar-nav" id="navbarNav">
                <li class="nav-item"><a href="<?= base_url() ?>" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="<?= base_url('find-blood') ?>" class="nav-link">Find Donors</a></li>

                <?php if (session()->get('isLoggedIn')): ?>
                    <?php
                        $role = strtolower((string) session()->get('role_name'));
                        $dashboardUrl = match ($role) {
                            'admin'              => base_url('admin/dashboard'),
                            'donor'              => base_url('donor/dashboard'),
                            'hospital'           => base_url('hospital/dashboard'),
                            'blood bank manager' => base_url('bloodbank/dashboard'),
                            default              => base_url('/'),
                        };
                        $unreadCount = (new \App\Models\NotificationModel())->getUnreadCount(session()->get('user_id'));
                    ?>
                    <li class="nav-item"><a href="<?= $dashboardUrl ?>" class="nav-link">Dashboard</a></li>
                    <li class="nav-item">
                        <a href="<?= base_url('notifications') ?>" class="nav-link">
                            Notifications
                            <?php if ($unreadCount > 0): ?>
                                <span class="badge badge-primary" style="padding: 0.15rem 0.45rem; font-size: 0.7rem; margin-left: 0.25rem;"><?= $unreadCount ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <span class="user-badge">
                            <?= esc(session()->get('name')) ?>
                            <span class="role-tag"><?= esc(session()->get('role_name')) ?></span>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('logout') ?>" class="btn btn-outline btn-sm">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a href="<?= base_url('login') ?>" class="nav-link">Sign In</a></li>
                    <li class="nav-item">
                        <a href="<?= base_url('register/donor') ?>" class="btn btn-primary btn-sm">Become a Donor</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="container">

            <!-- Global Flash Messages Area -->
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    <div><?= session()->getFlashdata('success') ?></div>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                    </svg>
                    <div><?= session()->getFlashdata('error') ?></div>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('warning')): ?>
                <div class="alert alert-warning">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                    </svg>
                    <div><?= session()->getFlashdata('warning') ?></div>
                </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>

        </div>
    </main>

    <!-- Global Footer -->
    <footer class="footer">
        <div class="container">
            <div class="grid grid-3">
                <div>
                    <h4>BloodLink Platform</h4>
                    <p style="color: #94a3b8; font-size: 0.9rem;">Connecting voluntary blood donors with critical patients and hospitals across the nation.</p>
                </div>
                <div>
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="<?= base_url() ?>">Home</a></li>
                        <li><a href="<?= base_url('find-blood') ?>">Find Blood Donors</a></li>
                        <li><a href="<?= base_url('register/donor') ?>">Register as Donor</a></li>
                        <li><a href="<?= base_url('register/hospital') ?>">Hospital Portal</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Emergency Contact</h4>
                    <p style="color: #94a3b8; font-size: 0.9rem;">Urgent Blood Helpline: <strong>1-800-BLOOD-LINK</strong></p>
                    <p style="color: #94a3b8; font-size: 0.9rem;">Support: <strong>help@bloodlink.org</strong></p>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; <?= date('Y') ?> BloodLink System. All Rights Reserved. Designed for Life-Saving Impact.
            </div>
        </div>
    </footer>

    <!-- Mobile Menu Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('mobileMenuBtn');
            const nav = document.getElementById('navbarNav');

            if (btn && nav) {
                btn.addEventListener('click', function () {
                    nav.classList.toggle('active');
                });
            }
        });
    </script>
</body>
</html>
