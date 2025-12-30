<?php
/**
 * Admin - Project Create/Edit Form
 */
$isEdit = !empty($project);
$pageTitle = $isEdit ? 'Edit Project' : 'New Project';
$breadcrumbs = [
    ['label' => 'Projects', 'url' => '/admin/projects'],
    ['label' => $isEdit ? $project['title'] : 'New']
];

$settings = [];
if ($isEdit && !empty($project['settings'])) {
    $settings = json_decode($project['settings'], true) ?? [];
}
?>

<div class="page-header">
    <h1 class="page-title"><?= $pageTitle ?></h1>
</div>

<form method="POST" action="<?= $isEdit ? '/admin/projects/' . $project['id'] : '/admin/projects' ?>" id="project-form">
    <?= $csrf ?>
    
    <div class="form-section">
        <h2 class="form-section-title">Basic Info</h2>
        
        <div class="form-row">
            <div class="form-group">
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" required
                       value="<?= htmlspecialchars($project['title'] ?? '') ?>"
                       placeholder="My Awesome Project">
            </div>
            
            <div class="form-group">
                <label for="slug">Slug</label>
                <input type="text" id="slug" name="slug"
                       value="<?= htmlspecialchars($project['slug'] ?? '') ?>"
                       placeholder="my-awesome-project">
                <p class="form-hint">Leave empty to auto-generate from title</p>
            </div>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5"
                      placeholder="Describe your project..."><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" <?= ($project['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="complete" <?= ($project['status'] ?? '') === 'complete' ? 'selected' : '' ?>>Complete</option>
                    <option value="on_hold" <?= ($project['status'] ?? '') === 'on_hold' ? 'selected' : '' ?>>On Hold</option>
                    <option value="archived" <?= ($project['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>&nbsp;</label>
                <label class="checkbox-label">
                    <input type="checkbox" name="is_featured" value="1"
                           <?= ($project['is_featured'] ?? false) ? 'checked' : '' ?>>
                    <span>Featured project</span>
                </label>
            </div>
        </div>
    </div>
    
    <div class="form-section">
        <h2 class="form-section-title">Tags</h2>
        <p class="text-secondary text-small mb-4">Select up to 2 tags</p>
        
        <div class="checkbox-group">
            <?php foreach ($tags as $tag): ?>
                <label class="checkbox-label">
                    <input type="checkbox" name="tags[]" value="<?= $tag['id'] ?>"
                           <?= in_array($tag['id'], $selectedTags ?? []) ? 'checked' : '' ?>>
                    <span><?= htmlspecialchars($tag['name']) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="form-section">
        <h2 class="form-section-title">Tech Stack</h2>
        
        <?php foreach ($techStack as $tier => $techs): ?>
            <?php
            $tierNames = [1 => 'Core Languages', 2 => 'Frameworks', 3 => 'Libraries', 4 => 'Tools'];
            ?>
            <div class="mb-4">
                <label class="text-small text-secondary mb-2" style="display: block;">
                    <?= $tierNames[$tier] ?? "Tier $tier" ?>
                </label>
                <div class="checkbox-group">
                    <?php foreach ($techs as $tech): ?>
                        <label class="checkbox-label">
                            <input type="checkbox" name="tech[]" value="<?= $tech['id'] ?>"
                                   <?= in_array($tech['id'], $selectedTech ?? []) ? 'checked' : '' ?>>
                            <span><?= htmlspecialchars($tech['name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($techStack)): ?>
            <p class="text-secondary">No tech stack items yet. <a href="/admin/tags">Add some →</a></p>
        <?php endif; ?>
    </div>
    
    <div class="form-section">
        <h2 class="form-section-title">Settings</h2>
        
        <div class="checkbox-group-vertical">
            <label class="checkbox-label">
                <input type="checkbox" name="devlog_enabled" value="1"
                       <?= ($settings['devlog_enabled'] ?? false) ? 'checked' : '' ?>>
                <span>Enable devlog for this project</span>
            </label>
            
            <label class="checkbox-label">
                <input type="checkbox" name="comments_enabled" value="1"
                       <?= ($settings['comments_enabled'] ?? false) ? 'checked' : '' ?>>
                <span>Enable comments for this project</span>
            </label>
        </div>
    </div>
    
    <?php if ($isEdit && !empty($blockTypes)): ?>
        <div class="form-section" id="blocks-section">
            <h2 class="form-section-title">Content Blocks</h2>
            
            <div class="block-list mb-4" id="block-list">
                <?php if (empty($blocks)): ?>
                    <p class="text-secondary" id="no-blocks-message">No blocks yet. Add one below.</p>
                <?php else: ?>
                    <?php foreach ($blocks as $block): ?>
                        <?php
                        $blockData = json_decode($block['data'], true) ?? [];
                        $blockPreview = '';
                        
                        switch ($block['block_type_slug']) {
                            case 'link':
                                $blockPreview = $blockData['label'] ?? $blockData['url'] ?? '';
                                break;
                            case 'download':
                                $blockPreview = $blockData['label'] ?? $blockData['file_path'] ?? '';
                                break;
                            case 'audio':
                            case 'video':
                                $blockPreview = $blockData['label'] ?? $blockData['url'] ?? '';
                                break;
                            case 'embed':
                                $blockPreview = $blockData['label'] ?? '[embed]';
                                break;
                            case 'gallery':
                                $galleryCount = count(\Quidque\Models\GalleryItem::getForBlock($block['id']));
                                $blockPreview = $galleryCount . ' image' . ($galleryCount !== 1 ? 's' : '');
                                break;
                        }
                        $blockPreview = htmlspecialchars(mb_substr($blockPreview, 0, 60));
                        ?>
                        <div class="block-item" data-block-id="<?= $block['id'] ?>">
                            <div class="block-header">
                                <div class="block-header-left">
                                    <span class="block-type">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="3" y1="12" x2="21" y2="12"/>
                                            <line x1="3" y1="6" x2="21" y2="6"/>
                                            <line x1="3" y1="18" x2="21" y2="18"/>
                                        </svg>
                                        <?= htmlspecialchars($block['block_type_name']) ?>
                                    </span>
                                    <?php if ($blockPreview): ?>
                                        <span class="block-preview"><?= $blockPreview ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="block-header-right">
                                    <button type="button" class="btn btn-ghost btn-sm delete-block-btn" 
                                            data-block-id="<?= $block['id'] ?>"
                                            data-confirm="Delete this block?">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="18" y1="6" x2="6" y2="18"/>
                                            <line x1="6" y1="6" x2="18" y2="18"/>
                                        </svg>
                                    </button>
                                    <span class="block-toggle">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="6,9 12,15 18,9"/>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                            <div class="block-content">
                                <?php if ($block['block_type_slug'] === 'link'): ?>
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
                                                   placeholder="GitHub">
                                        </div>
                                    </div>
                                <?php elseif ($block['block_type_slug'] === 'download'): ?>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>File Path</label>
                                            <input type="text" name="blocks[<?= $block['id'] ?>][file_path]"
                                                   value="<?= htmlspecialchars($blockData['file_path'] ?? '') ?>"
                                                   placeholder="/uploads/file.zip">
                                        </div>
                                        <div class="form-group">
                                            <label>Label</label>
                                            <input type="text" name="blocks[<?= $block['id'] ?>][label]"
                                                   value="<?= htmlspecialchars($blockData['label'] ?? '') ?>"
                                                   placeholder="Download v1.0">
                                        </div>
                                    </div>
                                <?php elseif ($block['block_type_slug'] === 'audio' || $block['block_type_slug'] === 'video'): ?>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Embed URL</label>
                                            <input type="url" name="blocks[<?= $block['id'] ?>][url]"
                                                   value="<?= htmlspecialchars($blockData['url'] ?? '') ?>"
                                                   placeholder="https://youtube.com/...">
                                        </div>
                                        <div class="form-group">
                                            <label>Label</label>
                                            <input type="text" name="blocks[<?= $block['id'] ?>][label]"
                                                   value="<?= htmlspecialchars($blockData['label'] ?? '') ?>"
                                                   placeholder="Demo Video">
                                        </div>
                                    </div>
                                <?php elseif ($block['block_type_slug'] === 'embed'): ?>
                                    <div class="form-group">
                                        <label>Embed Code</label>
                                        <textarea name="blocks[<?= $block['id'] ?>][embed_code]" rows="4" class="code"
                                                  placeholder="<iframe>...</iframe>"><?= htmlspecialchars($blockData['embed_code'] ?? '') ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Label</label>
                                        <input type="text" name="blocks[<?= $block['id'] ?>][label]"
                                               value="<?= htmlspecialchars($blockData['label'] ?? '') ?>"
                                               placeholder="Interactive Demo">
                                    </div>
                                <?php elseif ($block['block_type_slug'] === 'gallery'): ?>
                                    <div class="gallery-block" data-block-id="<?= $block['id'] ?>">
                                        <?php 
                                        $galleryItems = \Quidque\Models\GalleryItem::getForBlock($block['id']);
                                        ?>
                                        <div class="gallery-items mb-4" id="gallery-items-<?= $block['id'] ?>">
                                            <?php if (empty($galleryItems)): ?>
                                                <p class="text-secondary text-small no-gallery-msg">No images yet.</p>
                                            <?php else: ?>
                                                <div class="media-grid">
                                                    <?php foreach ($galleryItems as $item): ?>
                                                        <div class="media-item gallery-item" data-item-id="<?= $item['id'] ?>">
                                                            <img src="/uploads/<?= htmlspecialchars($item['file_path']) ?>" 
                                                                 alt="<?= htmlspecialchars($item['alt_text'] ?? '') ?>">
                                                            <div class="media-item-overlay">
                                                                <button type="button" class="btn btn-sm btn-danger remove-gallery-item"
                                                                        data-item-id="<?= $item['id'] ?>">
                                                                    Remove
                                                                </button>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="flex gap-3 flex-wrap">
                                            <button type="button" class="btn btn-secondary btn-sm open-media-picker"
                                                    data-block-id="<?= $block['id'] ?>">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                                    <polyline points="21,15 16,10 5,21"/>
                                                </svg>
                                                Select from Library
                                            </button>
                                            
                                            <div class="inline-upload">
                                                <input type="file" 
                                                       class="gallery-upload-input" 
                                                       data-block-id="<?= $block['id'] ?>"
                                                       accept="image/*"
                                                       multiple
                                                       id="gallery-upload-<?= $block['id'] ?>">
                                                <label for="gallery-upload-<?= $block['id'] ?>" class="btn btn-secondary btn-sm">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                                        <polyline points="17,8 12,3 7,8"/>
                                                        <line x1="12" y1="3" x2="12" y2="15"/>
                                                    </svg>
                                                    Upload New
                                                </label>
                                            </div>
                                        </div>
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
            <?= $isEdit ? 'Save Changes' : 'Create Project' ?>
        </button>
        <a href="/admin/projects" class="btn btn-secondary">Cancel</a>
        
        <?php if ($isEdit): ?>
            <a href="/projects/<?= htmlspecialchars($project['slug']) ?>" class="btn btn-ghost" target="_blank">
                View Project →
            </a>
        <?php endif; ?>
    </div>
</form>

<?php if ($isEdit): ?>
<div id="block-action-forms" style="display: none;">
    <form id="add-block-form" method="POST" action="/admin/projects/<?= $project['id'] ?>/blocks">
        <?= $csrf ?>
        <input type="hidden" name="block_type_id" id="add-block-type-id" value="">
    </form>
    
    <form id="delete-block-form" method="POST" action="">
        <?= $csrf ?>
    </form>
    
    <form id="add-gallery-item-form" method="POST" action="">
        <?= $csrf ?>
        <input type="hidden" name="media_id" id="gallery-media-id" value="">
    </form>
    
    <form id="remove-gallery-item-form" method="POST" action="">
        <?= $csrf ?>
    </form>
</div>

<div id="media-picker-modal" class="modal" style="display: none;">
    <div class="modal-backdrop"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Select Images</h3>
            <button type="button" class="btn btn-ghost btn-icon close-modal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="media-grid" id="media-picker-grid">
                <?php if (!empty($allMedia)): ?>
                    <?php foreach ($allMedia as $media): ?>
                        <?php if ($media['file_type'] === 'image'): ?>
                            <div class="media-item selectable" data-media-id="<?= $media['id'] ?>">
                                <img src="/uploads/<?= htmlspecialchars($media['file_path']) ?>" 
                                     alt="<?= htmlspecialchars($media['alt_text'] ?? '') ?>">
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-secondary">No images in media library. <a href="/admin/media">Upload some →</a></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary close-modal">Cancel</button>
            <button type="button" class="btn btn-primary" id="confirm-media-selection">Add Selected</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const projectId = <?= $project['id'] ?>;
    const csrfToken = document.querySelector('input[name="csrf_token"]')?.value || '';
    
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
        btn.addEventListener('click', async function(e) {
            e.stopPropagation();
            const blockId = this.dataset.blockId;
            const message = this.dataset.confirm || 'Delete this block?';
            
            const confirmed = await window.showConfirm(message, 'Delete Block');
            
            if (confirmed) {
                const form = document.getElementById('delete-block-form');
                form.action = '/admin/projects/' + projectId + '/blocks/' + blockId + '/delete';
                form.submit();
            }
        });
    });
    
    // Remove gallery item
    document.querySelectorAll('.remove-gallery-item').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            const itemId = this.dataset.itemId;
            
            const confirmed = await window.showConfirm('Remove this image from gallery?', 'Remove Image');
            
            if (confirmed) {
                const form = document.getElementById('remove-gallery-item-form');
                form.action = '/admin/projects/' + projectId + '/gallery/' + itemId + '/delete';
                form.submit();
            }
        });
    });
    
    // Block accordion
    const blockList = document.getElementById('block-list');
    if (blockList) {
        const blocks = blockList.querySelectorAll('.block-item');
        
        blocks.forEach((block) => {
            const header = block.querySelector('.block-header');
            
            header.addEventListener('click', function(e) {
                if (e.target.closest('.delete-block-btn')) return;
                
                const isExpanded = block.classList.contains('expanded');
                
                blocks.forEach(b => b.classList.remove('expanded'));
                
                if (!isExpanded) {
                    block.classList.add('expanded');
                }
            });
        });
    }
    
    // Media picker modal
    let currentBlockId = null;
    const modal = document.getElementById('media-picker-modal');
    
    document.querySelectorAll('.open-media-picker').forEach(btn => {
        btn.addEventListener('click', function() {
            currentBlockId = this.dataset.blockId;
            modal.style.display = 'flex';
        });
    });
    
    document.querySelectorAll('.close-modal, .modal-backdrop').forEach(el => {
        el.addEventListener('click', function() {
            modal.style.display = 'none';
            currentBlockId = null;
            document.querySelectorAll('.media-item.selected').forEach(item => {
                item.classList.remove('selected');
            });
        });
    });
    
    // Toggle selection
    document.querySelectorAll('#media-picker-grid .media-item.selectable').forEach(item => {
        item.addEventListener('click', function() {
            this.classList.toggle('selected');
        });
    });
    
    // Confirm selection
    document.getElementById('confirm-media-selection')?.addEventListener('click', async function() {
        const selected = document.querySelectorAll('#media-picker-grid .media-item.selected');
        if (selected.length === 0) {
            await window.showConfirm('Please select at least one image.', 'No Selection');
            return;
        }
        
        const mediaIds = Array.from(selected).map(el => el.dataset.mediaId);
        
        const form = document.getElementById('add-gallery-item-form');
        form.action = '/admin/projects/' + projectId + '/blocks/' + currentBlockId + '/gallery';
        
        mediaIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'media_ids[]';
            input.value = id;
            form.appendChild(input);
        });
        
        form.submit();
    });
    
    // Inline gallery upload
    document.querySelectorAll('.gallery-upload-input').forEach(input => {
        input.addEventListener('change', async function() {
            const blockId = this.dataset.blockId;
            const files = this.files;
            
            if (!files.length) return;
            
            const label = this.nextElementSibling;
            const originalHTML = label.innerHTML;
            label.innerHTML = '<span class="loading"></span> Uploading...';
            
            let uploadedCount = 0;
            
            for (const file of files) {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('csrf_token', csrfToken);
                
                try {
                    const response = await fetch('/admin/media/ajax', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success && data.media) {
                        uploadedCount++;
                    } else if (data.error) {
                        console.error('Upload error:', data.error);
                    }
                } catch (err) {
                    console.error('Upload failed:', err);
                }
            }
            
            label.innerHTML = originalHTML;
            this.value = '';
            
            if (uploadedCount > 0) {
                // Reload page to show new images (simplest solution)
                // In production, you'd update the gallery via AJAX
                window.location.reload();
            } else {
                await window.showConfirm('Upload failed. Please try again.', 'Error');
            }
        });
    });
});
</script>
<?php endif; ?>