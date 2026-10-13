<?= $this->extend('auth/layout') ?>

<?= $this->section('title') ?>Donor Registration<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="auth-card auth-card-wide">
    <div class="auth-header">
        <h1>Register as a Blood Donor</h1>
        <p>Join BloodLink and save lives in your community</p>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <?php if (session('errors')): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('register/donor') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="form-row">
            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name" value="<?= old('name') ?>" placeholder="John Doe" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" value="<?= old('email') ?>" placeholder="john@example.com" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" placeholder="At least 6 characters" required>
            </div>
            <div class="form-group">
                <label for="pass_confirm">Confirm Password *</label>
                <input type="password" id="pass_confirm" name="pass_confirm" placeholder="Repeat password" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="text" id="phone" name="phone" value="<?= old('phone') ?>" placeholder="+1 234 567 8900" required>
            </div>
            <div class="form-group">
                <label for="city">City / Location *</label>
                <input type="text" id="city" name="city" value="<?= old('city') ?>" placeholder="e.g. New York" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="blood_group_id">Blood Group *</label>
                <select id="blood_group_id" name="blood_group_id" required>
                    <option value="">-- Select Blood Group --</option>
                    <?php if (! empty($bloodGroups)): ?>
                        <?php foreach ($bloodGroups as $bg): ?>
                            <option value="<?= $bg['id'] ?>" <?= old('blood_group_id') == $bg['id'] ? 'selected' : '' ?>>
                                <?= esc($bg['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender">
                    <option value="">-- Select Gender --</option>
                    <option value="male" <?= old('gender') === 'male' ? 'selected' : '' ?>>Male</option>
                    <option value="female" <?= old('gender') === 'female' ? 'selected' : '' ?>>Female</option>
                    <option value="other" <?= old('gender') === 'other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="date_of_birth">Date of Birth</label>
                <input type="date" id="date_of_birth" name="date_of_birth" value="<?= old('date_of_birth') ?>">
            </div>
            <div class="form-group">
                <label for="last_donation_date">Last Donation Date (If Any)</label>
                <input type="date" id="last_donation_date" name="last_donation_date" value="<?= old('last_donation_date') ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="address">Full Address</label>
            <input type="text" id="address" name="address" value="<?= old('address') ?>" placeholder="Street, landmark, etc.">
        </div>

        <button type="submit" class="btn-primary">Complete Donor Registration</button>
    </form>

    <div class="auth-footer">
        <p>Already have an account? <a href="<?= base_url('login') ?>">Log in here</a></p>
    </div>
</div>
<?= $this->endSection() ?>
