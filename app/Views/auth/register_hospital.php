<?= $this->extend('auth/layout') ?>

<?= $this->section('title') ?>Hospital Registration<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="auth-card">
    <div class="auth-header">
        <h1>Register Hospital Account</h1>
        <p>Post blood requests and manage unit allocations</p>
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

    <form action="<?= base_url('register/hospital') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="name">Hospital / Medical Center Name *</label>
            <input type="text" id="name" name="name" value="<?= old('name') ?>" placeholder="City General Hospital" required>
        </div>

        <div class="form-group">
            <label for="email">Official Email Address *</label>
            <input type="email" id="email" name="email" value="<?= old('email') ?>" placeholder="bloodbank@hospital.org" required>
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
                <label for="phone">Phone / Emergency Contact *</label>
                <input type="text" id="phone" name="phone" value="<?= old('phone') ?>" placeholder="+1 234 567 8900" required>
            </div>
            <div class="form-group">
                <label for="city">City / Region *</label>
                <input type="text" id="city" name="city" value="<?= old('city') ?>" placeholder="e.g. Chicago" required>
            </div>
        </div>

        <button type="submit" class="btn-primary">Register Hospital Account</button>
    </form>

    <div class="auth-footer">
        <p>Already registered? <a href="<?= base_url('login') ?>">Log in here</a></p>
    </div>
</div>
<?= $this->endSection() ?>
