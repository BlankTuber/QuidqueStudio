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
        
        <div class="checkbox-group mb-4" id="tags-container">
            <?php foreach ($tags as $tag): ?>
                <label class="checkbox-label">
                    <input type="checkbox" name="tags[]" value="<?= $tag['id'] ?>"
                           <?= in_array($tag['id'], $selectedTags ?? []) ? 'checked' : '' ?>>
                    <span><?= htmlspecialchars($tag['name']) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
        
        <div class="inline-form" id="add-tag-form">
            <div class="form-group">
                <label for="new-tag-name">Add new tag</label>
                <input type="text" id="new-tag-name" placeholder="Tag name">
            </div>
            <button type="button" class="btn btn-secondary" id="add-tag-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Add
            </button>
        </div>
        <p class="form-hint">Press Enter or click Add to create a new tag</p>
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
                        $blockPreview = '';
                        
                        switch ($block['block_type_slug']) {
                            case 'heading':
                                $blockPreview = $blockData['text'] ?? '';
                                break;
                            case 'text':
                                $blockPreview = $blockData['content'] ?? '';
                                break;
                            case 'code':
                                $blockPreview = $blockData['language'] ? '[' . $blockData['language'] . ']' : '[code]';
                                break;
                            case 'image':
                                $blockPreview = $blockData['caption'] ?? '[image]';
                                break;
                            case 'link':
                                $blockPreview = $blockData['label'] ?? $blockData['url'] ?? '';
                                break;
                            case 'project_link':
                                $blockPreview = '[project link]';
                                break;
                        }
                        $blockPreview = htmlspecialchars(mb_substr($blockPreview, 0, 60));
                        if (mb_strlen($blockData['text'] ?? $blockData['content'] ?? '') > 60) {
                            $blockPreview .= '...';
                        }
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
                                <?php if ($block['block_type_slug'] === 'heading'): ?>
                                    <div class="form-row">
                                        <div class="form-group" style="flex: 2;">
                                            <label>Heading Text</label>
                                            <input type="text" name="blocks[<?= $block['id'] ?>][text]"
                                                   value="<?= htmlspecialchars($blockData['text'] ?? '') ?>"
                                                   placeholder="Section heading">
                                        </div>
                                        <div class="form-group">
                                            <label>Level</label>
                                            <select name="blocks[<?= $block['id'] ?>][level]">
                                                <option value="2" <?= ($blockData['level'] ?? 2) == 2 ? 'selected' : '' ?>>H2</option>
                                                <option value="3" <?= ($blockData['level'] ?? 2) == 3 ? 'selected' : '' ?>>H3</option>
                                                <option value="4" <?= ($blockData['level'] ?? 2) == 4 ? 'selected' : '' ?>>H4</option>
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
                                    <div class="image-block-content" data-block-id="<?= $block['id'] ?>">
                                        <div class="image-preview-container mb-3">
                                            <?php if (!empty($blockData['media_id'])): ?>
                                                <?php $media = \Quidque\Models\Media::find($blockData['media_id']); ?>
                                                <?php if ($media): ?>
                                                    <img src="/uploads/<?= htmlspecialchars($media['file_path']) ?>" 
                                                         class="image-preview" alt="" id="preview-<?= $block['id'] ?>">
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <img src="" class="image-preview hidden" alt="" id="preview-<?= $block['id'] ?>">
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label>Select from library</label>
                                                <select name="blocks[<?= $block['id'] ?>][media_id]" 
                                                        class="image-select" 
                                                        data-block-id="<?= $block['id'] ?>">
                                                    <option value="">Select image...</option>
                                                    <?php foreach ($allMedia as $media): ?>
                                                        <?php if ($media['file_type'] === 'image'): ?>
                                                            <option value="<?= $media['id'] ?>"
                                                                    data-url="/uploads/<?= htmlspecialchars($media['file_path']) ?>"
                                                                    <?= ($blockData['media_id'] ?? '') == $media['id'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($media['alt_text'] ?: basename($media['file_path'])) ?>
                                                            </option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Or upload new</label>
                                                <div class="inline-upload">
                                                    <input type="file" 
                                                           class="inline-upload-input" 
                                                           data-block-id="<?= $block['id'] ?>"
                                                           accept="image/*"
                                                           id="upload-<?= $block['id'] ?>">
                                                    <label for="upload-<?= $block['id'] ?>" class="btn btn-secondary w-full">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                                            <polyline points="17,8 12,3 7,8"/>
                                                            <line x1="12" y1="3" x2="12" y2="15"/>
                                                        </svg>
                                                        Upload Image
                                                    </label>
                                                </div>
                                            </div>
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
<div id="block-action-forms" style="display: none;">
    <form id="add-block-form" method="POST" action="/admin/blog/<?= $post['id'] ?>/blocks">
        <?= $csrf ?>
        <input type="hidden" name="block_type_id" id="add-block-type-id" value="">
    </form>
    
    <form id="delete-block-form" method="POST" action="">
        <?= $csrf ?>
    </form>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('input[name="csrf_token"]')?.value || '';
    
    // Inline tag creation
    const tagInput = document.getElementById('new-tag-name');
    const addTagBtn = document.getElementById('add-tag-btn');
    const tagsContainer = document.getElementById('tags-container');
    
    async function createTag() {
        const name = tagInput.value.trim();
        if (!name) return;
        
        addTagBtn.disabled = true;
        addTagBtn.innerHTML = '<span class="loading"></span>';
        
        try {
            const response = await fetch('/admin/blog/tags', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `name=${encodeURIComponent(name)}&csrf_token=${encodeURIComponent(csrfToken)}`
            });
            
            const data = await response.json();
            
            if (data.success && data.tag) {
                const exists = tagsContainer.querySelector(`input[value="${data.tag.id}"]`);
                if (!exists) {
                    const label = document.createElement('label');
                    label.className = 'checkbox-label';
                    label.innerHTML = `
                        <input type="checkbox" name="tags[]" value="${data.tag.id}" checked>
                        <span>${data.tag.name}</span>
                    `;
                    tagsContainer.appendChild(label);
                } else {
                    exists.checked = true;
                }
                tagInput.value = '';
            } else if (data.error) {
                await window.showConfirm(data.error, 'Error');
            }
        } catch (err) {
            console.error('Failed to create tag:', err);
            await window.showConfirm('Failed to create tag. Please try again.', 'Error');
        }
        
        addTagBtn.disabled = false;
        addTagBtn.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Add
        `;
        tagInput.focus();
    }
    
    addTagBtn?.addEventListener('click', createTag);
    
    tagInput?.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            createTag();
        }
    });
    
    // Image select preview update
    document.querySelectorAll('.image-select').forEach(select => {
        select.addEventListener('change', function() {
            const blockId = this.dataset.blockId;
            const preview = document.getElementById('preview-' + blockId);
            const selected = this.options[this.selectedIndex];
            const url = selected.dataset.url;
            
            if (url && preview) {
                preview.src = url;
                preview.classList.remove('hidden');
            } else if (preview) {
                preview.classList.add('hidden');
            }
        });
    });
    
    // Inline image upload
    document.querySelectorAll('.inline-upload-input').forEach(input => {
        input.addEventListener('change', async function() {
            const blockId = this.dataset.blockId;
            const file = this.files[0];
            
            if (!file) return;
            
            const label = this.nextElementSibling;
            const originalHTML = label.innerHTML;
            label.innerHTML = '<span class="loading"></span> Uploading...';
            
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
                    // Update the select dropdown
                    const select = document.querySelector(`.image-select[data-block-id="${blockId}"]`);
                    const option = document.createElement('option');
                    option.value = data.media.id;
                    option.dataset.url = data.media.url;
                    option.textContent = data.media.alt_text || file.name;
                    option.selected = true;
                    select.appendChild(option);
                    
                    // Update preview
                    const preview = document.getElementById('preview-' + blockId);
                    if (preview) {
                        preview.src = data.media.url;
                        preview.classList.remove('hidden');
                    }
                    
                    // Clear the file input
                    this.value = '';
                } else if (data.error) {
                    await window.showConfirm(data.error, 'Upload Error');
                }
            } catch (err) {
                console.error('Upload failed:', err);
                await window.showConfirm('Upload failed. Please try again.', 'Error');
            }
            
            label.innerHTML = originalHTML;
        });
    });
    
    <?php if ($isEdit): ?>
    const postId = <?= $post['id'] ?>;
    
    document.querySelectorAll('.add-block-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const typeId = this.dataset.blockType;
            document.getElementById('add-block-type-id').value = typeId;
            document.getElementById('add-block-form').submit();
        });
    });
    
    document.querySelectorAll('.delete-block-btn').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.stopPropagation();
            const blockId = this.dataset.blockId;
            const message = this.dataset.confirm || 'Delete this block?';
            
            const confirmed = await window.showConfirm(message, 'Delete Block');
            
            if (confirmed) {
                const form = document.getElementById('delete-block-form');
                form.action = '/admin/blog/' + postId + '/blocks/' + blockId + '/delete';
                form.submit();
            }
        });
    });
    
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
    <?php endif; ?>
});
</script>