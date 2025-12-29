<?php
/**
 * Admin - Blog Post Create/Edit Form
 */
$isEdit = !empty($post);
$pageTitle = $isEdit ? 'Edit Post' : 'New Post';
$breadcrumbs = [
    ['label' => 'Blog', 'url' => '/admin/blog'],
    ['label' => $isEdit ? $post['title'] : 'New']
];
?>

<div class="page-header">
    <div class="flex items-center justify-between">
        <h1 class="page-title"><?= $pageTitle ?></h1>
        
        <?php if ($isEdit): ?>
            <div class="flex gap-3">
                <?php if ($post['status'] === 'draft'): ?>
                    <form method="POST" action="/admin/blog/<?= $post['id'] ?>/publish" style="display: inline;">
                        <?= $csrf ?>
                        <button type="submit" class="btn btn-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            Publish
                        </button>
                    </form>
                <?php else: ?>
                    <form method="POST" action="/admin/blog/<?= $post['id'] ?>/unpublish" style="display: inline;">
                        <?= $csrf ?>
                        <button type="submit" class="btn btn-secondary">
                            Unpublish
                        </button>
                    </form>
                    <a href="/blog/<?= htmlspecialchars($post['slug']) ?>" class="btn btn-ghost" target="_blank">
                        View Post →
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if ($isEdit): ?>
        <p class="page-subtitle">
            <?php if ($post['status'] === 'published'): ?>
                <span class="badge badge-cyan">Published</span>
                <?php if (!empty($post['published_at'])): ?>
                    on <?= date('F j, Y \a\t g:i A', strtotime($post['published_at'])) ?>
                <?php endif; ?>
            <?php else: ?>
                <span class="badge">Draft</span>
            <?php endif; ?>
        </p>
    <?php endif; ?>
</div>

<form method="POST" action="<?= $isEdit ? '/admin/blog/' . $post['id'] : '/admin/blog' ?>" id="post-form">
    <?= $csrf ?>
    
    <div class="form-section">
        <h2 class="form-section-title">Post Details</h2>
        
        <div class="form-group">
            <label for="title">Title *</label>
            <input type="text" id="title" name="title" required
                   value="<?= htmlspecialchars($post['title'] ?? '') ?>"
                   placeholder="My Awesome Post">
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="slug">Slug</label>
                <input type="text" id="slug" name="slug"
                       value="<?= htmlspecialchars($post['slug'] ?? '') ?>"
                       placeholder="my-awesome-post">
                <p class="form-hint">Leave empty to auto-generate from title</p>
            </div>
            
            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id">
                    <option value="">No category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" 
                                <?= ($post['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    
    <div class="form-section">
        <h2 class="form-section-title">Tags</h2>
        
        <?php if (empty($tags)): ?>
            <p class="text-secondary">No tags yet. <a href="/admin/tags">Add some →</a></p>
        <?php else: ?>
            <div class="checkbox-group">
                <?php foreach ($tags as $tag): ?>
                    <label class="checkbox-label">
                        <input type="checkbox" name="tags[]" value="<?= $tag['id'] ?>"
                               <?= in_array($tag['id'], $selectedTags ?? []) ? 'checked' : '' ?>>
                        <span><?= htmlspecialchars($tag['name']) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if ($isEdit && !empty($blockTypes)): ?>
        <div class="form-section" id="blocks-section">
            <h2 class="form-section-title">Content</h2>
            
            <div class="block-list mb-4" id="block-list">
                <?php if (empty($blocks)): ?>
                    <p class="text-secondary" id="no-blocks-message">No content blocks yet. Add one below.</p>
                <?php else: ?>
                    <?php foreach ($blocks as $block): ?>
                        <?php
                        $blockData = json_decode($block['data'], true) ?? [];
                        ?>
                        <div class="block-item" data-block-id="<?= $block['id'] ?>">
                            <div class="block-header">
                                <span class="block-type">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="3" y1="12" x2="21" y2="12"/>
                                        <line x1="3" y1="6" x2="21" y2="6"/>
                                        <line x1="3" y1="18" x2="21" y2="18"/>
                                    </svg>
                                    <?= htmlspecialchars($block['block_type_name']) ?>
                                </span>
                                <button type="button" class="btn btn-ghost btn-sm delete-block-btn" 
                                        data-block-id="<?= $block['id'] ?>"
                                        data-confirm="Delete this block?">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18"/>
                                        <line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="block-content">
                                <?php if ($block['block_type_slug'] === 'heading'): ?>
                                    <div class="form-row">
                                        <div class="form-group" style="flex: 2;">
                                            <label>Heading Text</label>
                                            <input type="text" name="blocks[<?= $block['id'] ?>][text]"
                                                   value="<?= htmlspecialchars($blockData['text'] ?? '') ?>"
                                                   placeholder="Section Title">
                                        </div>
                                        <div class="form-group" style="flex: 1;">
                                            <label>Level</label>
                                            <select name="blocks[<?= $block['id'] ?>][level]">
                                                <option value="2" <?= ($blockData['level'] ?? '2') === '2' ? 'selected' : '' ?>>H2</option>
                                                <option value="3" <?= ($blockData['level'] ?? '') === '3' ? 'selected' : '' ?>>H3</option>
                                                <option value="4" <?= ($blockData['level'] ?? '') === '4' ? 'selected' : '' ?>>H4</option>
                                            </select>
                                        </div>
                                    </div>
                                <?php elseif ($block['block_type_slug'] === 'text'): ?>
                                    <div class="form-group">
                                        <label>Content</label>
                                        <textarea name="blocks[<?= $block['id'] ?>][content]" rows="6"
                                                  placeholder="Write your content here..."><?= htmlspecialchars($blockData['content'] ?? '') ?></textarea>
                                        <p class="form-hint">Markdown supported</p>
                                    </div>
                                <?php elseif ($block['block_type_slug'] === 'image'): ?>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Image</label>
                                            <?php if (!empty($blockData['media_id'])): ?>
                                                <?php $media = \Quidque\Models\Media::find($blockData['media_id']); ?>
                                                <?php if ($media): ?>
                                                    <img src="/uploads/<?= htmlspecialchars($media['file_path']) ?>" 
                                                         class="image-preview mb-2" alt="">
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <select name="blocks[<?= $block['id'] ?>][media_id]">
                                                <option value="">Select image...</option>
                                                <?php foreach ($allMedia as $media): ?>
                                                    <?php if ($media['file_type'] === 'image'): ?>
                                                        <option value="<?= $media['id'] ?>"
                                                                <?= ($blockData['media_id'] ?? '') == $media['id'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($media['alt_text'] ?: basename($media['file_path'])) ?>
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Caption</label>
                                            <input type="text" name="blocks[<?= $block['id'] ?>][caption]"
                                                   value="<?= htmlspecialchars($blockData['caption'] ?? '') ?>"
                                                   placeholder="Image caption (optional)">
                                        </div>
                                    </div>
                                <?php elseif ($block['block_type_slug'] === 'code'): ?>
                                    <div class="form-group">
                                        <label>Code</label>
                                        <textarea name="blocks[<?= $block['id'] ?>][content]" rows="8" class="code"
                                                  placeholder="// Your code here"><?= htmlspecialchars($blockData['content'] ?? '') ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Language</label>
                                        <input type="text" name="blocks[<?= $block['id'] ?>][language]"
                                               value="<?= htmlspecialchars($blockData['language'] ?? '') ?>"
                                               placeholder="javascript, php, python, etc.">
                                    </div>
                                <?php elseif ($block['block_type_slug'] === 'link'): ?>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>URL</label>
                                            <input type="url" name="blocks[<?= $block['id'] ?>][url]"
                                                   value="<?= htmlspecialchars($blockData['url'] ?? '') ?>"
                                                   placeholder="https://...">
                                        </div>
                                        <div class="form-group">
                                            <label>Label</label>
                                            <input type="text" name="blocks[<?= $block['id'] ?>][label]"
                                                   value="<?= htmlspecialchars($blockData['label'] ?? '') ?>"
                                                   placeholder="Link text">
                                        </div>
                                    </div>
                                <?php elseif ($block['block_type_slug'] === 'project_link'): ?>
                                    <div class="form-group">
                                        <label>Link to Project</label>
                                        <select name="blocks[<?= $block['id'] ?>][project_id]">
                                            <option value="">Select project...</option>
                                            <?php foreach ($allProjects as $proj): ?>
                                                <option value="<?= $proj['id'] ?>"
                                                        <?= ($blockData['project_id'] ?? '') == $proj['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($proj['title']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="toolbar">
                <div class="toolbar-left flex-wrap">
                    <span class="text-secondary text-small">Add block:</span>
                    <?php foreach ($blockTypes as $type): ?>
                        <button type="button" class="btn btn-ghost btn-sm add-block-btn" 
                                data-block-type="<?= $type['id'] ?>">
                            <?= htmlspecialchars($type['name']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">
            <?= $isEdit ? 'Save Changes' : 'Create Post' ?>
        </button>
        <a href="/admin/blog" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php if ($isEdit): ?>
<!-- Separate forms for block actions -->
<div id="block-action-forms" style="display: none;">
    <form id="add-block-form" method="POST" action="/admin/blog/<?= $post['id'] ?>/blocks">
        <?= $csrf ?>
        <input type="hidden" name="block_type_id" id="add-block-type-id" value="">
    </form>
    
    <form id="delete-block-form" method="POST" action="">
        <?= $csrf ?>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const postId = <?= $post['id'] ?>;
    
    // Add block
    document.querySelectorAll('.add-block-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const typeId = this.dataset.blockType;
            document.getElementById('add-block-type-id').value = typeId;
            document.getElementById('add-block-form').submit();
        });
    });
    
    // Delete block
    document.querySelectorAll('.delete-block-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const blockId = this.dataset.blockId;
            const confirmMsg = this.dataset.confirm || 'Delete this block?';
            
            if (confirm(confirmMsg)) {
                const form = document.getElementById('delete-block-form');
                form.action = '/admin/blog/' + postId + '/blocks/' + blockId + '/delete';
                form.submit();
            }
        });
    });
});
</script>
<?php endif; ?>