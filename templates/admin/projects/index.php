<?php
/**
 * Admin - Projects List
 */
$pageTitle = 'Projects';
$breadcrumbs = [['label' => 'Projects']];
?>

<div class="page-header">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="page-title">Projects</h1>
            <p class="page-subtitle"><?= count($projects) ?> total projects</p>
        </div>
        <a href="/admin/projects/create" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            New Project
        </a>
    </div>
</div>

<?php if (empty($projects)): ?>
    <div class="card">
        <div class="empty-state">
            <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
            </svg>
            <p class="empty-state-title">No projects yet</p>
            <p>Create your first project to get started.</p>
            <div class="mt-4">
                <a href="/admin/projects/create" class="btn btn-primary">Create Project</a>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="admin-list">
        <?php foreach ($projects as $project): ?>
            <div class="admin-list-item">
                <div class="admin-list-item-info">
                    <div class="status status-<?= htmlspecialchars($project['status']) ?>">
                        <span class="status-dot"></span>
                    </div>
                    <div>
                        <div class="admin-list-item-title">
                            <?= htmlspecialchars($project['title']) ?>
                            <?php if ($project['is_featured']): ?>
                                <span class="badge badge-cyan">Featured</span>
                            <?php endif; ?>
                        </div>
                        <div class="admin-list-item-meta">
                            /projects/<?= htmlspecialchars($project['slug']) ?>
                            <?php if (!empty($project['tag_names'])): ?>
                                · <?= htmlspecialchars($project['tag_names']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="admin-list-item-actions">
                    <a href="/projects/<?= htmlspecialchars($project['slug']) ?>" class="btn btn-ghost btn-sm" target="_blank" title="View">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                            <polyline points="15,3 21,3 21,9"/>
                            <line x1="10" y1="14" x2="21" y2="3"/>
                        </svg>
                    </a>
                    <a href="/admin/projects/<?= $project['id'] ?>/edit" class="btn btn-secondary btn-sm">Edit</a>
                    <form method="POST" action="/admin/projects/<?= $project['id'] ?>/delete" style="display: inline;">
                        <?= $csrf ?>
                        <button type="submit" class="btn btn-ghost btn-sm" data-confirm="Delete &quot;<?= htmlspecialchars($project['title']) ?>&quot;? This cannot be undone.">
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