<?php
/**
 * Admin - Tags & Tech Management
 */
$pageTitle = 'Tags & Tech';
$breadcrumbs = [['label' => 'Tags & Tech']];

$tierNames = [1 => 'Core Languages', 2 => 'Frameworks', 3 => 'Libraries', 4 => 'Tools'];
?>

<div class="page-header">
    <h1 class="page-title">Tags & Tech Stack</h1>
    <p class="page-subtitle">Manage project tags, tech stack, blog tags, and categories</p>
</div>

<div class="admin-grid">
    <!-- Project Tags -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Project Tags</h2>
        </div>
        <div class="card-body">
            <div class="tag-list mb-4" id="project-tags">
                <?php if (empty($tags)): ?>
                    <p class="text-secondary text-small">No tags yet.</p>
                <?php else: ?>
                    <?php foreach ($tags as $tag): ?>
                        <div class="tag-item">
                            <span class="tag"><?= htmlspecialchars($tag['name']) ?></span>
                            <form method="POST" action="/admin/tags/<?= $tag['id'] ?>/delete" class="inline-form">
                                <?= $csrf ?>
                                <button type="submit" class="btn btn-ghost btn-sm" 
                                        data-confirm="Delete tag &quot;<?= htmlspecialchars($tag['name']) ?>&quot;?">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18"/>
                                        <line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <form method="POST" action="/admin/tags" class="inline-add-form">
                <?= $csrf ?>
                <div class="flex gap-2">
                    <input type="text" name="name" placeholder="New tag name" required class="flex-1">
                    <button type="submit" class="btn btn-primary btn-sm">Add</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Blog Categories -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Blog Categories</h2>
        </div>
        <div class="card-body">
            <div class="tag-list mb-4" id="blog-categories">
                <?php if (empty($blogCategories)): ?>
                    <p class="text-secondary text-small">No categories yet.</p>
                <?php else: ?>
                    <?php foreach ($blogCategories as $category): ?>
                        <div class="tag-item">
                            <span class="tag"><?= htmlspecialchars($category['name']) ?></span>
                            <form method="POST" action="/admin/categories/<?= $category['id'] ?>/delete" class="inline-form">
                                <?= $csrf ?>
                                <button type="submit" class="btn btn-ghost btn-sm"
                                        data-confirm="Delete category &quot;<?= htmlspecialchars($category['name']) ?>&quot;?">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18"/>
                                        <line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <form method="POST" action="/admin/categories" class="inline-add-form">
                <?= $csrf ?>
                <div class="flex gap-2">
                    <input type="text" name="name" placeholder="New category name" required class="flex-1">
                    <button type="submit" class="btn btn-primary btn-sm">Add</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Blog Tags -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Blog Tags</h2>
        </div>
        <div class="card-body">
            <div class="tag-list mb-4" id="blog-tags">
                <?php if (empty($blogTags)): ?>
                    <p class="text-secondary text-small">No tags yet.</p>
                <?php else: ?>
                    <?php foreach ($blogTags as $tag): ?>
                        <div class="tag-item">
                            <span class="tag"><?= htmlspecialchars($tag['name']) ?></span>
                            <form method="POST" action="/admin/blog-tags/<?= $tag['id'] ?>/delete" class="inline-form">
                                <?= $csrf ?>
                                <button type="submit" class="btn btn-ghost btn-sm"
                                        data-confirm="Delete tag &quot;<?= htmlspecialchars($tag['name']) ?>&quot;?">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18"/>
                                        <line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <form method="POST" action="/admin/blog-tags" class="inline-add-form">
                <?= $csrf ?>
                <div class="flex gap-2">
                    <input type="text" name="name" placeholder="New tag name" required class="flex-1">
                    <button type="submit" class="btn btn-primary btn-sm">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Tech Stack (Full Width) -->
<div class="card mt-6">
    <div class="card-header">
        <h2 class="card-title">Tech Stack</h2>
    </div>
    <div class="card-body">
        <?php foreach ($tierNames as $tier => $tierName): ?>
            <div class="tech-tier mb-6">
                <h3 class="text-small text-secondary mb-3"><?= $tierName ?> (Tier <?= $tier ?>)</h3>
                <div class="tag-list mb-3">
                    <?php if (empty($techStack[$tier])): ?>
                        <p class="text-secondary text-small">No items in this tier.</p>
                    <?php else: ?>
                        <?php foreach ($techStack[$tier] as $tech): ?>
                            <div class="tag-item">
                                <span class="tag tag-tech"><?= htmlspecialchars($tech['name']) ?></span>
                                <form method="POST" action="/admin/tech/<?= $tech['id'] ?>/delete" class="inline-form">
                                    <?= $csrf ?>
                                    <button type="submit" class="btn btn-ghost btn-sm"
                                            data-confirm="Delete &quot;<?= htmlspecialchars($tech['name']) ?>&quot; from tech stack?">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="18" y1="6" x2="6" y2="18"/>
                                            <line x1="6" y1="6" x2="18" y2="18"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <hr class="my-6">
        
        <form method="POST" action="/admin/tech" class="inline-add-form">
            <?= $csrf ?>
            <div class="flex gap-2 flex-wrap">
                <input type="text" name="name" placeholder="New tech name" required style="flex: 2; min-width: 200px;">
                <select name="tier" style="flex: 1; min-width: 150px;">
                    <?php foreach ($tierNames as $tier => $tierName): ?>
                        <option value="<?= $tier ?>"><?= $tierName ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Add Tech</button>
            </div>
        </form>
    </div>
</div>