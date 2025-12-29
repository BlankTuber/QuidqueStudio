<?php
/**
 * Home Page
 */
$pageTitle = 'Home';
$sidebarCollapsed = false; // Expanded on home page
?>

<div class="page-header">
    <h1 class="page-title">Welcome to Quidque Studio</h1>
    <p class="page-subtitle">Projects, experiments, and thoughts from Blank.</p>
</div>

<!-- Featured Projects -->
<section class="mb-8">
    <div class="flex items-center justify-between mb-4">
        <h2 class="h2">Featured Projects</h2>
        <a href="/projects" class="btn btn-ghost btn-sm">View all →</a>
    </div>
    
    <?php if (empty($projects)): ?>
        <div class="card">
            <div class="empty-state">
                <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                </svg>
                <p class="empty-state-title">No projects yet</p>
                <p>Featured projects will appear here.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="grid grid-auto">
            <?php foreach ($projects as $project): ?>
                <a href="/projects/<?= htmlspecialchars($project['slug']) ?>" class="card card-clickable">
                    <div class="card-header">
                        <h3 class="card-title"><?= htmlspecialchars($project['title']) ?></h3>
                        <?php if (!empty($project['description'])): ?>
                            <p class="card-description">
                                <?= htmlspecialchars(mb_strimwidth($project['description'], 0, 120, '...')) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <div class="status status-<?= htmlspecialchars($project['status']) ?>">
                            <span class="status-dot"></span>
                            <span><?= ucfirst(htmlspecialchars($project['status'])) ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Recent Blog Posts -->
<section class="mb-8">
    <div class="flex items-center justify-between mb-4">
        <h2 class="h2">Latest Posts</h2>
        <a href="/blog" class="btn btn-ghost btn-sm">View all →</a>
    </div>
    
    <?php if (empty($posts)): ?>
        <div class="card">
            <div class="empty-state">
                <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14,2 14,8 20,8"/>
                </svg>
                <p class="empty-state-title">No posts yet</p>
                <p>Blog posts will appear here.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="flex flex-col gap-3">
            <?php foreach ($posts as $post): ?>
                <a href="/blog/<?= htmlspecialchars($post['slug']) ?>" class="card card-clickable">
                    <div class="flex items-center justify-between">
                        <h3 class="card-title"><?= htmlspecialchars($post['title']) ?></h3>
                        <span class="text-small text-secondary">
                            <?= date('M j, Y', strtotime($post['published_at'] ?? $post['created_at'])) ?>
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>