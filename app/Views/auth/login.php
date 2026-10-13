<?= $this->extend('auth/layout') ?>

<?= $this->section('title') ?>Login<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="auth-card">
    <div class="auth-header">
        <h1>Welcome Back</h1>
        <p>Sign in to your BloodLink account</p>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('success') ?>
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

    <form action="<?= base_url('login') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?= old('email') ?>" placeholder="name@example.com" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-primary">Sign In</button>
    </form>

    <div class="auth-footer">
        <p>Don't have an account?</p>
        <p style="margin-top: 0.5rem;">
            <a href="<?= base_url('register/donor') ?>">Register as Donor</a> &bull; 
            <a href="<?= base_url('register/hospital') ?>">Register as Hospital</a>
        </p>
    </div>
</div>
<?= $this->endSection() ?>
