<?php
/**
 * Admin Dashboard
 */
$pageTitle = 'Dashboard';
?>

<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">Welcome back, <?= htmlspecialchars($auth['user']['username'] ?? 'Admin') ?></p>
</div>

<!-- Stats -->
<div class="stats-grid mb-8">
    <div class="stat-card">
        <div class="stat-value"><?= $stats['projects'] ?? 0 ?></div>
        <div class="stat-label">Projects</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $stats['posts'] ?? 0 ?></div>
        <div class="stat-label">Published Posts</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $stats['drafts'] ?? 0 ?></div>
        <div class="stat-label">Drafts</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $stats['users'] ?? 0 ?></div>
        <div class="stat-label">Users</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $stats['comments'] ?? 0 ?></div>
        <div class="stat-label">Comments</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $unreadMessages ?? 0 ?></div>
        <div class="stat-label">Unread Messages</div>
    </div>
</div>

<!-- Quick Actions -->
<section class="mb-8">
    <h2 class="h2 mb-4">Quick Actions</h2>
    <div class="quick-actions">
        <a href="/admin/projects/create" class="quick-action">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                <line x1="12" y1="11" x2="12" y2="17"/>
                <line x1="9" y1="14" x2="15" y2="14"/>
            </svg>
            New Project
        </a>
        <a href="/admin/blog/create" class="quick-action">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14,2 14,8 20,8"/>
                <line x1="12" y1="18" x2="12" y2="12"/>
                <line x1="9" y1="15" x2="15" y2="15"/>
            </svg>
            New Post
        </a>
        <a href="/admin/media" class="quick-action">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21,15 16,10 5,21"/>
            </svg>
            Upload Media
        </a>
        <a href="/admin/messages" class="quick-action">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            Messages
            <?php if (($unreadMessages ?? 0) > 0): ?>
                <span class="badge badge-magenta"><?= $unreadMessages ?></span>
            <?php endif; ?>
        </a>
    </div>
</section>

<!-- Recent Activity -->
<div class="grid grid-2">
    <!-- Recent Projects -->
    <section>
        <div class="flex items-center justify-between mb-4">
            <h2 class="h2">Recent Projects</h2>
            <a href="/admin/projects" class="btn btn-ghost btn-sm">View all</a>
        </div>
        <div class="card">
            <?php if (empty($recentProjects)): ?>
                <p class="text-secondary">No projects yet.</p>
            <?php else: ?>
                <div class="recent-list">
                    <?php foreach ($recentProjects as $project): ?>
                        <a href="/admin/projects/<?= $project['id'] ?>/edit" class="recent-item">
                            <span class="recent-item-title"><?= htmlspecialchars($project['title']) ?></span>
                            <span class="recent-item-time"><?= date('M j', strtotime($project['updated_at'])) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Recent Drafts -->
    <section>
        <div class="flex items-center justify-between mb-4">
            <h2 class="h2">Recent Drafts</h2>
            <a href="/admin/blog?status=draft" class="btn btn-ghost btn-sm">View all</a>
        </div>
        <div class="card">
            <?php if (empty($recentPosts)): ?>
                <p class="text-secondary">No drafts.</p>
            <?php else: ?>
                <div class="recent-list">
                    <?php foreach ($recentPosts as $post): ?>
                        <a href="/admin/blog/<?= $post['id'] ?>/edit" class="recent-item">
                            <span class="recent-item-title"><?= htmlspecialchars($post['title']) ?></span>
                            <span class="recent-item-time"><?= date('M j', strtotime($post['updated_at'])) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>