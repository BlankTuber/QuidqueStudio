<?php
/**
 * Admin - Messages Inbox
 */
$pageTitle = 'Messages';
$breadcrumbs = [['label' => 'Messages']];

$totalUnread = 0;
foreach ($conversations as $conv) {
    $totalUnread += $conv['unread_count'] ?? 0;
}
?>

<div class="page-header">
    <h1 class="page-title">Messages</h1>
    <p class="page-subtitle">
        <?= count($conversations) ?> conversation<?= count($conversations) !== 1 ? 's' : '' ?>
        <?php if ($totalUnread > 0): ?>
            · <span class="text-magenta"><?= $totalUnread ?> unread</span>
        <?php endif; ?>
    </p>
</div>

<?php if (empty($conversations)): ?>
    <div class="card">
        <div class="empty-state">
            <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            <p class="empty-state-title">No messages yet</p>
            <p>User messages will appear here.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="conversation-list">
            <?php foreach ($conversations as $conv): ?>
                <a href="/admin/messages/<?= $conv['user_id'] ?>" class="conversation-item <?= $conv['unread_count'] > 0 ? 'unread' : '' ?>">
                    <div class="conversation-avatar">
                        <?= strtoupper(substr($conv['username'], 0, 1)) ?>
                        <?php if ($conv['unread_count'] > 0): ?>
                            <span class="unread-dot"></span>
                        <?php endif; ?>
                    </div>
                    <div class="conversation-info">
                        <div class="conversation-header">
                            <span class="conversation-name"><?= htmlspecialchars($conv['username']) ?></span>
                            <span class="conversation-time"><?= $this->timeAgo($conv['created_at']) ?></span>
                        </div>
                        <div class="conversation-preview">
                            <?php if ($conv['is_from_admin']): ?>
                                <span class="text-secondary">You: </span>
                            <?php endif; ?>
                            <?= htmlspecialchars(mb_substr($conv['content'] ?? '[Image]', 0, 80)) ?>
                            <?= mb_strlen($conv['content'] ?? '') > 80 ? '...' : '' ?>
                        </div>
                    </div>
                    <?php if ($conv['unread_count'] > 0): ?>
                        <span class="unread-badge"><?= $conv['unread_count'] ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>