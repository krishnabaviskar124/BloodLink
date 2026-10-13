<?= $this->extend('layout') ?>

<?= $this->section('title') ?>My Notifications & Priority Alerts<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="max-width: 900px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2>Notifications & Emergency Alerts</h2>
            <p style="color: var(--text-muted);">Manage incoming emergency alerts prioritized by urgency level, and track read receipts.</p>
        </div>
        <?php if ($unreadCount > 0): ?>
            <a href="<?= base_url('notifications/mark-all-read') ?>" class="btn btn-outline-dark">Mark All as Read</a>
        <?php endif; ?>
    </div>

    <!-- Received Notifications Card -->
    <div class="card" style="margin-bottom: 2.5rem;">
        <div class="card-header">
            <h3 class="card-title">1. Priority Incoming Alerts</h3>
            <span class="badge badge-info"><?= $unreadCount ?> Unread Alert(s)</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (! empty($notifications)): ?>
                <div style="display: flex; flex-direction: column;">
                    <?php foreach ($notifications as $note): ?>
                        <?php
                            $prio = strtolower((string) ($note['priority'] ?? 'normal'));
                            if ($prio === 'urgent') {
                                $prio = 'high';
                            }
                            $isUnread = ! $note['is_read'];

                            $bgColor = $isUnread 
                                ? match ($prio) {
                                    'critical' => '#fff1f2',
                                    'high'     => '#fff8f6',
                                    'low'      => '#f8fafc',
                                    default    => '#f0f9ff',
                                }
                                : '#ffffff';

                            $borderColor = match ($prio) {
                                'critical' => '#fca5a5',
                                'high'     => '#fdba74',
                                'low'      => '#cbd5e1',
                                default    => 'var(--border)',
                            };
                        ?>
                        <div style="padding: 1.25rem; border-bottom: 1px solid <?= $borderColor ?>; background-color: <?= $bgColor ?>; transition: var(--transition);">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
                                <div>
                                    <strong style="font-size: 1.1rem; color: <?= $prio === 'critical' ? '#991b1b' : ($prio === 'high' ? '#c2410c' : 'var(--text-dark)') ?>;">
                                        <?= esc($note['title']) ?>
                                    </strong>
                                    <?php if (! empty($note['sender_name'])): ?>
                                        <small style="color: var(--text-muted); display: block;">From: <?= esc($note['sender_name']) ?></small>
                                    <?php endif; ?>
                                </div>
                                <div style="display: flex; gap: 0.4rem; align-items: center;">
                                    <?php if ($prio === 'critical'): ?>
                                        <span class="badge badge-danger" style="background: #991b1b; color: #ffffff; padding: 0.3rem 0.65rem; font-weight: 700;">
                                            CRITICAL PRIORITY
                                        </span>
                                    <?php elseif ($prio === 'high'): ?>
                                        <span class="badge badge-danger" style="background: #ea580c; color: #ffffff; padding: 0.3rem 0.65rem;">
                                            HIGH PRIORITY
                                        </span>
                                    <?php elseif ($prio === 'low'): ?>
                                        <span class="badge badge-secondary" style="padding: 0.3rem 0.65rem;">
                                            LOW PRIORITY
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-info" style="padding: 0.3rem 0.65rem;">
                                            NORMAL PRIORITY
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($isUnread): ?>
                                        <span class="badge badge-warning" style="padding: 0.3rem 0.65rem;">Unread</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary" style="padding: 0.3rem 0.65rem;">Read</span>
                                    <?php endif; ?>
                                </div>
                            </div>


                            <!-- Structured Notification Content -->
                            <div style="margin-bottom: 0.75rem; color: var(--text-body); font-size: 0.95rem; white-space: pre-line;">
                                <?= esc($note['message']) ?>
                            </div>

                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                                <small style="color: var(--text-muted); font-size: 0.8rem;"><?= date('M d, Y g:i A', strtotime($note['created_at'])) ?></small>

                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <?php if (! empty($note['request_id'])): ?>
                                        <?php if (! empty($note['linked_request_id']) || ! empty($note['hospital_name'])): ?>
                                            <a href="<?= base_url('requests/' . $note['request_id']) ?>" class="btn btn-primary btn-sm" style="font-size: 0.8rem; padding: 0.25rem 0.75rem;">
                                                View Request
                                            </a>
                                        <?php else: ?>
                                            <span class="badge badge-secondary" style="font-size: 0.8rem; padding: 0.25rem 0.75rem; opacity: 0.8;" title="This blood request is no longer active or was removed">
                                                Request Unavailable
                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($isUnread): ?>
                                        <a href="<?= base_url('notifications/' . $note['id'] . '/read') ?>" class="btn btn-outline btn-sm" style="font-size: 0.8rem; padding: 0.25rem 0.75rem; cursor: pointer;">
                                            Mark as Read
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state" style="padding: 2.5rem 1rem;">
                    <p style="color: var(--text-muted);">You have no notifications in your inbox.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sent Notifications & Read Receipts Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">2. Sent Alerts & Read Receipts</h3>
            <span class="badge badge-secondary"><?= count($sentNotifications) ?> Sent</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (! empty($sentNotifications)): ?>
                <div style="display: flex; flex-direction: column;">
                    <?php foreach ($sentNotifications as $sent): ?>
                        <div style="padding: 1.25rem; border-bottom: 1px solid var(--border); background-color: #ffffff;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.35rem; flex-wrap: wrap; gap: 0.5rem;">
                                <div>
                                    <strong style="font-size: 1.05rem; color: var(--text-dark);"><?= esc($sent['title']) ?></strong>
                                    <small style="color: var(--text-muted); display: block;">To Recipient: <?= esc($sent['recipient_name'] ?: 'System User') ?></small>
                                </div>
                                <div>
                                    <?php if ($sent['is_read']): ?>
                                        <span class="badge badge-success" style="font-size: 0.75rem; padding: 0.3rem 0.65rem;">
                                            Read on <?= date('M d, Y g:i A', strtotime($sent['updated_at'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-warning" style="font-size: 0.75rem; padding: 0.3rem 0.65rem;">
                                            Unread / Pending
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <p style="margin-bottom: 0.25rem; color: var(--text-body); font-size: 0.95rem; white-space: pre-line;"><?= esc($sent['message']) ?></p>
                            <small style="color: var(--text-muted); font-size: 0.78rem;">Sent on: <?= date('M d, Y g:i A', strtotime($sent['created_at'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state" style="padding: 2.5rem 1rem;">
                    <p style="color: var(--text-muted);">You have not sent any notifications or contact alerts yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>
<?= $this->endSection() ?>
