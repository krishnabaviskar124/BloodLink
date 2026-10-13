<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div style="display: flex; justify-content: center; align-items: center; min-height: 70vh; padding: 1.5rem 0;">
    <?= $this->renderSection('content') ?>
</div>
<?= $this->endSection() ?>
