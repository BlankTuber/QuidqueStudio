<?php
/**
 * Admin - User Details
 */
$pageTitle = $user['username'];
$breadcrumbs = [
    ['label' => 'Users', 'url' => '/admin/users'],
    ['label' => $user['username']]
];

$isCurrentUser = ($auth['user']['user_id'] ?? null) === $user['id'];
?>

<div class="page-header">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="user-avatar user-avatar-lg">
                <?= strtoupper(substr($user['username'], 0, 1)) ?>
            </div>
            <div>
                <h1 class="page-title">
                    <?= htmlspecialchars($user['username']) ?>
                    <?php if ($user['is_admin']): ?>
                        <span class="badge badge-magenta">Admin</span>
                    <?php endif; ?>
                </h1>
                <p class="page-subtitle"><?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>
        
        <?php if (!$isCurrentUser): ?>
            <div class="flex gap-3">
                <form method="POST" action="/admin/users/<?= $user['id'] ?>/toggle-admin" style="display: inline;">
                    <?= $csrf ?>
                    <button type="submit" class="btn btn-secondary">
                        <?= $user['is_admin'] ? 'Remove Admin' : 'Make Admin' ?>
                    </button>
                </form>
                <form method="POST" action="/admin/users/<?= $user['id'] ?>/delete" style="display: inline;">
                    <?= $csrf ?>
                    <button type="submit" class="btn btn-danger" 
                            data-confirm="Delete user &quot;<?= htmlspecialchars($user['username']) ?>&quot;? This will also delete their comments and messages.">
                        Delete User
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="admin-grid">
    <!-- User Info -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Account Info</h2>
        </div>
        <div class="card-body">
            <dl class="info-list">
                <div class="info-item">
                    <dt>User ID</dt>
                    <dd><?= $user['id'] ?></dd>
                </div>
                <div class="info-item">
                    <dt>Username</dt>
                    <dd><?= htmlspecialchars($user['username']) ?></dd>
                </div>
                <div class="info-item">
                    <dt>Email</dt>
                    <dd><?= htmlspecialchars($user['email']) ?></dd>
                </div>
                <div class="info-item">
                    <dt>Status</dt>
                    <dd>
                        <?php if ($user['is_confirmed']): ?>
                            <span class="badge badge-cyan">Confirmed</span>
                        <?php else: ?>
                            <span class="badge">Pending</span>
                        <?php endif; ?>
                    </dd>
                </div>
                <div class="info-item">
                    <dt>Role</dt>
                    <dd>
                        <?php if ($user['is_admin']): ?>
                            <span class="badge badge-magenta">Admin</span>
                        <?php else: ?>
                            <span class="text-secondary">User</span>
                        <?php endif; ?>
                    </dd>
                </div>
                <div class="info-item">
                    <dt>Joined</dt>
                    <dd><?= date('F j, Y \a\t g:i A', strtotime($user['created_at'])) ?></dd>
                </div>
            </dl>
        </div>
    </div>
    
    <!-- Active Sessions -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Active Sessions (<?= count($sessions) ?>)</h2>
        </div>
        <div class="card-body">
            <?php if (empty($sessions)): ?>
                <p class="text-secondary">No active sessions.</p>
            <?php else: ?>
                <div class="session-list">
                    <?php foreach ($sessions as $session): ?>
                        <div class="session-item">
                            <div class="session-info">
                                <div class="session-device">
                                    <?php
                                    $ua = $session['user_agent'] ?? 'Unknown';
                                    $browser = 'Unknown Browser';
                                    if (strpos($ua, 'Firefox') !== false) $browser = 'Firefox';
                                    elseif (strpos($ua, 'Chrome') !== false) $browser = 'Chrome';
                                    elseif (strpos($ua, 'Safari') !== false) $browser = 'Safari';
                                    elseif (strpos($ua, 'Edge') !== false) $browser = 'Edge';
                                    ?>
                                    <?= $browser ?>
                                </div>
                                <div class="session-meta">
                                    <?= htmlspecialchars($session['ip_address'] ?? 'Unknown IP') ?>
                                    <?php if (!empty($session['city']) || !empty($session['country'])): ?>
                                        · <?= htmlspecialchars(trim(($session['city'] ?? '') . ', ' . ($session['country'] ?? ''), ', ')) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="session-meta">
                                    Last active: <?= date('M j, g:i A', strtotime($session['last_active_at'])) ?>
                                </div>
                            </div>
                            <form method="POST" action="/admin/users/<?= $user['id'] ?>/sessions/<?= $session['id'] ?>/delete">
                                <?= $csrf ?>
                                <button type="submit" class="btn btn-ghost btn-sm" title="Revoke session">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18"/>
                                        <line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Recent Comments -->
<div class="card mt-6">
    <div class="card-header">
        <h2 class="card-title">Recent Comments (<?= count($comments) ?>)</h2>
    </div>
    <div class="card-body">
        <?php if (empty($comments)): ?>
            <p class="text-secondary">No comments yet.</p>
        <?php else: ?>
            <div class="comment-list">
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-item <?= $comment['deleted_by'] ? 'comment-deleted' : '' ?>">
                        <div class="comment-meta">
                            <a href="/projects/<?= htmlspecialchars($comment['project_slug']) ?>" class="comment-project">
                                <?= htmlspecialchars($comment['project_title']) ?>
                            </a>
                            <span class="text-secondary">·</span>
                            <span class="text-secondary"><?= date('M j, Y', strtotime($comment['created_at'])) ?></span>
                            <?php if ($comment['deleted_by']): ?>
                                <span class="badge">Deleted by <?= $comment['deleted_by'] ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="comment-content">
                            <?= htmlspecialchars($comment['content']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>