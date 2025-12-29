<?php
/**
 * Admin - Blog Posts List
 */
$pageTitle = 'Blog';
$breadcrumbs = [['label' => 'Blog']];
?>

<div class="page-header">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="page-title">Blog Posts</h1>
            <p class="page-subtitle"><?= count($posts) ?> total posts</p>
        </div>
        <a href="/admin/blog/create" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            New Post
        </a>
    </div>
</div>

<!-- Filter Pills -->
<div class="filter-pills mb-6">
    <a href="/admin/blog" class="filter-pill <?= empty($currentStatus) ? 'active' : '' ?>">All</a>
    <a href="/admin/blog?status=draft" class="filter-pill <?= $currentStatus === 'draft' ? 'active' : '' ?>">Drafts</a>
    <a href="/admin/blog?status=published" class="filter-pill <?= $currentStatus === 'published' ? 'active' : '' ?>">Published</a>
</div>

<?php if (empty($posts)): ?>
    <div class="card">
        <div class="empty-state">
            <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14,2 14,8 20,8"/>
            </svg>
            <p class="empty-state-title">No posts yet</p>
            <p>Create your first blog post to get started.</p>
            <div class="mt-4">
                <a href="/admin/blog/create" class="btn btn-primary">Create Post</a>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="admin-list">
        <?php foreach ($posts as $post): ?>
            <div class="admin-list-item">
                <div class="admin-list-item-info">
                    <?php if ($post['status'] === 'published'): ?>
                        <span class="badge badge-cyan">Published</span>
                    <?php else: ?>
                        <span class="badge">Draft</span>
                    <?php endif; ?>
                    <div>
                        <div class="admin-list-item-title">
                            <?= htmlspecialchars($post['title']) ?>
                        </div>
                        <div class="admin-list-item-meta">
                            <?php if ($post['status'] === 'published' && !empty($post['published_at'])): ?>
                                Published <?= date('M j, Y', strtotime($post['published_at'])) ?>
                            <?php else: ?>
                                Last edited <?= date('M j, Y', strtotime($post['updated_at'])) ?>
                            <?php endif; ?>
                            <?php if (!empty($post['author_name'])): ?>
                                · by <?= htmlspecialchars($post['author_name']) ?>
                            <?php endif; ?>
                            <?php if (!empty($post['category_name'])): ?>
                                · <?= htmlspecialchars($post['category_name']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="admin-list-item-actions">
                    <?php if ($post['status'] === 'published'): ?>
                        <a href="/blog/<?= htmlspecialchars($post['slug']) ?>" class="btn btn-ghost btn-sm" target="_blank" title="View">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                <polyline points="15,3 21,3 21,9"/>
                                <line x1="10" y1="14" x2="21" y2="3"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                    <a href="/admin/blog/<?= $post['id'] ?>/edit" class="btn btn-secondary btn-sm">Edit</a>
                    <form method="POST" action="/admin/blog/<?= $post['id'] ?>/delete" style="display: inline;">
                        <?= $csrf ?>
                        <button type="submit" class="btn btn-ghost btn-sm" data-confirm="Delete &quot;<?= htmlspecialchars($post['title']) ?>&quot;? This cannot be undone.">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3,6 5,6 21,6"/>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>